<?php
/**
 * 留言板 API
 * 
 * GET 请求：
 * - 无参数：获取所有已展示的留言（公开）
 * - ?admin=1：获取所有留言（需要密码验证）
 * - ?page=1&limit=10：分页获取已展示的留言
 * 
 * POST 请求：
 * - 提交留言（公开）：action=submit, nickname=xxx, email=xxx, content=xxx
 * - 获取所有留言（需要密码）：action=get_all, password_hash=xxx
 * - 切换展示状态（需要密码）：action=toggle_display, id=xxx, password_hash=xxx
 * - 删除留言（需要密码）：action=delete, id=xxx, password_hash=xxx
 * - 批量操作（需要密码）：action=batch_toggle, ids=[...], display=1/0, password_hash=xxx
 * - 批量删除（需要密码）：action=batch_delete, ids=[...], password_hash=xxx
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 加载认证和数据库配置
$config = require __DIR__ . '/auth_config.php';
$dbConfig = $config['database'];
$authConfig = $config['auth'];

// 生成密码的哈希值
$expectedPasswordHash = hash('sha256', $authConfig['admin_password']);

// 连接数据库
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '数据库连接失败'], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * 验证密码哈希
 */
function verifyPasswordHash($passwordHash, $expectedHash) {
    return isset($passwordHash) && $passwordHash === $expectedHash;
}

/**
 * 获取 JSON 输入
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

/**
 * 验证邮箱格式
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * 清理输入内容
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * 获取客户端 IP 地址
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// 处理不同请求方法
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGet($pdo);
        break;
    
    case 'POST':
        handlePost($pdo, $expectedPasswordHash);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => '不支持的请求方法'], JSON_UNESCAPED_UNICODE);
}

/**
 * 处理 GET 请求
 */
function handleGet($pdo) {
    // 检查是否是管理员请求
    if (isset($_GET['admin']) && $_GET['admin'] === '1') {
        // 管理员获取所有留言（需要密码验证在 POST 中处理，这里只返回公开数据）
        echo json_encode(['success' => false, 'error' => '请使用 POST 请求获取所有留言'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 分页参数
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;
    
    // 获取总数
    $countStmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE is_displayed = 1");
    $total = $countStmt->fetch()['total'];
    
    // 获取留言列表（按时间倒序）
    $stmt = $pdo->prepare("SELECT id, nickname, email, content, ip_address, created_at FROM messages WHERE is_displayed = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $messages = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'messages' => $messages,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => intval($total),
                'totalPages' => ceil($total / $limit)
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * 处理 POST 请求
 */
function handlePost($pdo, $expectedPasswordHash) {
    $data = getJsonInput();
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '无效的请求数据'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'submit':
            // 提交留言（公开，不需要密码）
            handleMessageSubmit($pdo, $data);
            break;
        
        case 'get_all':
            // 获取所有留言（需要密码）
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 分页参数
            $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;
            $limit = isset($data['limit']) ? max(1, min(100, intval($data['limit']))) : 20;
            $offset = ($page - 1) * $limit;
            
            // 搜索和过滤
            $whereClause = "1=1";
            $params = [];
            
            if (!empty($data['search_nickname'])) {
                $whereClause .= " AND nickname LIKE ?";
                $params[] = '%' . $data['search_nickname'] . '%';
            }
            
            if (isset($data['filter_displayed'])) {
                $whereClause .= " AND is_displayed = ?";
                $params[] = intval($data['filter_displayed']);
            }
            
            if (!empty($data['filter_date_from'])) {
                $whereClause .= " AND created_at >= ?";
                $params[] = $data['filter_date_from'];
            }
            
            if (!empty($data['filter_date_to'])) {
                $whereClause .= " AND created_at <= ?";
                $params[] = $data['filter_date_to'];
            }
            
            // 获取总数
            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM messages WHERE $whereClause");
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
            
            // 获取留言列表
            $sql = "SELECT * FROM messages WHERE $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $messages = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'messages' => $messages,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => intval($total),
                        'totalPages' => ceil($total / $limit)
                    ]
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;
        
        case 'toggle_display':
            // 切换展示状态（需要密码）
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少留言 ID'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->prepare("UPDATE messages SET is_displayed = NOT is_displayed WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => '展示状态已更新'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该留言'], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'delete':
            // 删除留言（需要密码）
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少留言 ID'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => '留言已删除'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该留言'], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'batch_toggle':
            // 批量切换展示状态（需要密码）
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['ids']) || !is_array($data['ids'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少留言 ID 列表'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $display = isset($data['display']) && $data['display'] ? 1 : 0;
            $placeholders = str_repeat('?,', count($data['ids']) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE messages SET is_displayed = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$display], $data['ids']));
            
            echo json_encode([
                'success' => true,
                'message' => '批量更新成功',
                'affected' => $stmt->rowCount()
            ], JSON_UNESCAPED_UNICODE);
            break;
        
        case 'batch_delete':
            // 批量删除留言（需要密码）
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['ids']) || !is_array($data['ids'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少留言 ID 列表'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $placeholders = str_repeat('?,', count($data['ids']) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id IN ($placeholders)");
            $stmt->execute($data['ids']);
            
            echo json_encode([
                'success' => true,
                'message' => '批量删除成功',
                'affected' => $stmt->rowCount()
            ], JSON_UNESCAPED_UNICODE);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 action 参数'], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 处理留言提交
 */
function handleMessageSubmit($pdo, $data) {
    // 验证必填字段（昵称和内容必填，邮箱可选）
    if (empty($data['nickname']) || empty($data['content'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '请填写所有必填字段：昵称、内容'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 验证邮箱格式（仅当填写了邮箱时验证）
    if (!empty($data['email']) && !isValidEmail($data['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '邮箱格式不正确'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 验证内容长度
    if (strlen($data['content']) < 5) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '留言内容至少需要 5 个字符'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    if (strlen($data['content']) > 2000) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '留言内容不能超过 2000 个字符'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 验证昵称长度
    if (strlen($data['nickname']) > 50) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => '昵称不能超过 50 个字符'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 清理输入
    $nickname = sanitizeInput($data['nickname']);
    $email = !empty($data['email']) ? sanitizeInput($data['email']) : '';
    $content = sanitizeInput($data['content']);
    $ipAddress = getClientIP();
    
    // 插入数据库
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (nickname, email, content, ip_address, is_displayed) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$nickname, $email, $content, $ipAddress]);
        
        // 获取插入的留言 ID
        $messageId = $pdo->lastInsertId();
        
        // 生成一次性审核令牌
        $approveToken = hash('sha256', $messageId . time() . rand(1000, 9999));
        
        // 存储审核令牌到数据库（创建临时表或使用 session）
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours')); // 24 小时有效
        $stmt = $pdo->prepare("CREATE TABLE IF NOT EXISTS approve_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL,
            message_id INT NOT NULL,
            expires_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_message_id (message_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $stmt->execute();
        
        $stmt = $pdo->prepare("INSERT INTO approve_tokens (token, message_id, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$approveToken, $messageId, $tokenExpiry]);
        
        // 发送邮件通知
        sendApprovalEmail($nickname, $email, $content, $ipAddress, $messageId, $approveToken);
        
        echo json_encode([
            'success' => true,
            'message' => '留言提交成功，请等待管理员审核'
        ], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => '留言提交失败',
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 发送审核邮件通知
 */
function sendApprovalEmail($nickname, $email, $content, $ipAddress, $messageId, $approveToken) {
    // 加载邮件配置
    $config = require __DIR__ . '/auth_config.php';
    $emailConfig = $config['email'];
    
    // 构建邮件 API 完整 URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $mailApiUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $emailConfig['mail_api_url'];
    
    // 构建审核链接（使用相同协议）
    $approveUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/api/approve.php?token=' . $approveToken;
    
    // 格式化时间
    $submitTime = date('Y-m-d H:i:s');
    
    // 构建 HTML 邮件内容
    $htmlContent = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <h2 style='color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px;'>新留言通知</h2>
        
        <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <p><strong>留言人：</strong>{$nickname}</p>
            <p><strong>邮箱：</strong>" . ($email ?: '未填写') . "</p>
            <p><strong>IP 地址：</strong>{$ipAddress}</p>
            <p><strong>提交时间：</strong>{$submitTime}</p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 15px 0;'>
            <p><strong>留言内容：</strong></p>
            <div style='background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #667eea;'>
                " . nl2br(htmlspecialchars($content)) . "
            </div>
        </div>
        
        <div style='text-align: center; margin: 30px 0;'>
            <a href='{$approveUrl}' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);'>
                ✓ 快速通过审核
            </a>
            <p style='color: #999; font-size: 12px; margin-top: 10px;'>此链接为一次性有效，24 小时后过期</p>
        </div>
        
        <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin: 0; color: #856404; font-size: 14px;'>
                <strong>提示：</strong>点击上面的按钮即可快速通过该留言审核。此链接只能使用一次，使用后失效。
            </p>
        </div>
        
        <hr style='border: none; border-top: 1px solid #ddd; margin: 30px 0;'>
        <p style='color: #999; font-size: 12px; text-align: center;'>
            此邮件由系统自动发送，请勿回复。<br>
            如果您不是管理员，请忽略此邮件。
        </p>
    </div>
    ";
    
    // 构建邮件数据
    $mailData = [
        'email' => $emailConfig['admin_email'],
        'title' => "新留言通知 - {$nickname}",
        'body' => $htmlContent
    ];
    
    // 发送邮件
    try {
        $ch = curl_init($mailApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($mailData))
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // 记录邮件发送结果（可选）
        if ($httpCode !== 200) {
            error_log("邮件发送失败: HTTP {$httpCode}, Response: {$response}");
        }
    } catch (Exception $e) {
        error_log("邮件发送异常: " . $e->getMessage());
    }
}