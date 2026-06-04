<?php
/**
 * 作品展示 API
 * 
 * GET 请求：
 * - 无参数：获取页面配置和所有已启用的作品（公开）
 * - ?admin=1：提示使用 POST 请求获取所有数据
 * 
 * POST 请求：
 * - 获取所有数据（需要密码）：action=get_all, password_hash=xxx
 * - 获取页面配置（需要密码）：action=get_config, password_hash=xxx
 * - 更新页面配置（需要密码）：action=update_config, title=xxx, description=xxx, password_hash=xxx
 * - 创建作品（需要密码）：action=create_item, title=xxx, description=xxx, image=xxx, tags=[], link=xxx, github=xxx, password_hash=xxx
 * - 更新作品（需要密码）：action=update_item, id=xxx, title=xxx, description=xxx, image=xxx, tags=[], link=xxx, github=xxx, password_hash=xxx
 * - 删除作品（需要密码）：action=delete_item, id=xxx, password_hash=xxx
 * - 切换作品状态（需要密码）：action=toggle_item, id=xxx, password_hash=xxx
 * - 更新作品排序（需要密码）：action=update_sort, items=[{id, sort_order}], password_hash=xxx
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
 * 清理输入内容
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * 生成随机 ID
 */
function generateId() {
    return bin2hex(random_bytes(8));
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
    // 获取页面配置
    $stmt = $pdo->query("SELECT * FROM portfolio_config LIMIT 1");
    $config = $stmt->fetch();
    
    // 获取所有已启用的作品（按排序）
    $stmt = $pdo->query("SELECT * FROM portfolio_items WHERE is_active = '1' ORDER BY sort_order ASC, created_at DESC");
    $items = $stmt->fetchAll();
    
    // 处理标签（JSON 字符串转数组）
    foreach ($items as &$item) {
        $item['tags'] = json_decode($item['tags'], true) ?? [];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'config' => $config,
            'items' => $items
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
        case 'get_all':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 获取页面配置
            $stmt = $pdo->query("SELECT * FROM portfolio_config LIMIT 1");
            $config = $stmt->fetch();
            
            // 获取所有作品（包括未启用的）
            $stmt = $pdo->query("SELECT * FROM portfolio_items ORDER BY sort_order ASC, created_at DESC");
            $items = $stmt->fetchAll();
            
            // 处理标签
            foreach ($items as &$item) {
                $item['tags'] = json_decode($item['tags'], true) ?? [];
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'config' => $config,
                    'items' => $items
                ]
            ], JSON_UNESCAPED_UNICODE);
            break;
        
        case 'get_config':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->query("SELECT * FROM portfolio_config LIMIT 1");
            $config = $stmt->fetch();
            
            echo json_encode(['success' => true, 'data' => $config], JSON_UNESCAPED_UNICODE);
            break;
        
        case 'update_config':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['title'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：title'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE portfolio_config SET title = ?, description = ? WHERE id = 1");
                $stmt->execute([
                    sanitizeInput($data['title']),
                    sanitizeInput($data['description'] ?? '')
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '配置更新成功',
                    'data' => [
                        'title' => $data['title'],
                        'description' => $data['description'] ?? ''
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'create_item':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['title']) || empty($data['description'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：title, description'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = generateId();
            $sortOrder = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
            $tags = !empty($data['tags']) && is_array($data['tags']) ? json_encode($data['tags'], JSON_UNESCAPED_UNICODE) : '[]';
            
            try {
                $stmt = $pdo->prepare("INSERT INTO portfolio_items (id, title, description, image, tags, link, github, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, '1', ?)");
                $stmt->execute([
                    $id,
                    sanitizeInput($data['title']),
                    sanitizeInput($data['description']),
                    sanitizeInput($data['image'] ?? ''),
                    $tags,
                    sanitizeInput($data['link'] ?? ''),
                    sanitizeInput($data['github'] ?? ''),
                    $sortOrder
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '作品创建成功',
                    'data' => [
                        'id' => $id,
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'image' => $data['image'] ?? '',
                        'tags' => json_decode($tags, true),
                        'link' => $data['link'] ?? '',
                        'github' => $data['github'] ?? '',
                        'is_active' => '1',
                        'sort_order' => $sortOrder
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '创建失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update_item':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id']) || empty($data['title']) || empty($data['description'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：id, title, description'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->prepare("SELECT * FROM portfolio_items WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该作品'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $sortOrder = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
            $tags = !empty($data['tags']) && is_array($data['tags']) ? json_encode($data['tags'], JSON_UNESCAPED_UNICODE) : '[]';
            $isActive = isset($data['is_active']) && $data['is_active'] ? '1' : '0';
            
            try {
                $stmt = $pdo->prepare("UPDATE portfolio_items SET title = ?, description = ?, image = ?, tags = ?, link = ?, github = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([
                    sanitizeInput($data['title']),
                    sanitizeInput($data['description']),
                    sanitizeInput($data['image'] ?? ''),
                    $tags,
                    sanitizeInput($data['link'] ?? ''),
                    sanitizeInput($data['github'] ?? ''),
                    $isActive,
                    $sortOrder,
                    $data['id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '作品更新成功',
                    'data' => [
                        'id' => $data['id'],
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'image' => $data['image'] ?? '',
                        'tags' => json_decode($tags, true),
                        'link' => $data['link'] ?? '',
                        'github' => $data['github'] ?? '',
                        'is_active' => $isActive,
                        'sort_order' => $sortOrder
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'delete_item':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：id'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM portfolio_items WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => '删除成功'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => '未找到该作品'], JSON_UNESCAPED_UNICODE);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '删除失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'toggle_item':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少作品 ID'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->prepare("UPDATE portfolio_items SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => '状态已更新'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该作品'], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update_sort':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['items']) || !is_array($data['items'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少作品列表'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE portfolio_items SET sort_order = ? WHERE id = ?");
                foreach ($data['items'] as $sortItem) {
                    if (!empty($sortItem['id']) && isset($sortItem['sort_order'])) {
                        $stmt->execute([intval($sortItem['sort_order']), $sortItem['id']]);
                    }
                }
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => '排序更新成功'], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 action 参数'], JSON_UNESCAPED_UNICODE);
    }
}
