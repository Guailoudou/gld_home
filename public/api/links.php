<?php
/**
 * 链接导航 API
 * 
 * GET 请求：
 * - 无参数：获取所有已启用的链接分组和链接（公开）
 * - ?admin=1：提示使用 POST 请求获取所有数据
 * 
 * POST 请求：
 * - 获取所有数据（需要密码）：action=get_all, password_hash=xxx
 * - 创建链接分组（需要密码）：action=create_section, name=xxx, icon=xxx, links=[{name, url, description}], password_hash=xxx
 * - 更新链接分组（需要密码）：action=update_section, id=xxx, name=xxx, icon=xxx, password_hash=xxx
 * - 删除链接分组（需要密码）：action=delete_section, id=xxx, password_hash=xxx
 * - 切换分组状态（需要密码）：action=toggle_section, id=xxx, password_hash=xxx
 * - 创建链接（需要密码）：action=create_link, section_id=xxx, name=xxx, url=xxx, description=xxx, password_hash=xxx
 * - 更新链接（需要密码）：action=update_link, id=xxx, section_id=xxx, name=xxx, url=xxx, description=xxx, password_hash=xxx
 * - 删除链接（需要密码）：action=delete_link, id=xxx, password_hash=xxx
 * - 切换链接状态（需要密码）：action=toggle_link, id=xxx, password_hash=xxx
 * - 批量更新分组排序/拖拽排序（需要密码）：action=update_section_sort, sections=[{id, sort_order}], password_hash=xxx
 * - 批量更新链接排序/拖拽排序（需要密码）：action=update_link_sort, section_id=xxx, links=[{id, sort_order}], password_hash=xxx
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
    // 获取所有已启用的分组（按排序）
    $stmt = $pdo->query("SELECT * FROM link_sections WHERE is_active = '1' ORDER BY sort_order ASC, created_at DESC");
    $sections = $stmt->fetchAll();
    
    // 获取所有已启用的链接（按排序）
    $stmt = $pdo->query("SELECT * FROM links WHERE is_active = '1' ORDER BY sort_order ASC, created_at DESC");
    $links = $stmt->fetchAll();
    
    // 组装数据结构
    $result = [];
    foreach ($sections as $section) {
        $sectionLinks = array_filter($links, function($link) use ($section) {
            return $link['section_id'] === $section['id'];
        });
        
        $result[] = [
            'id' => $section['id'],
            'title' => $section['title'],
            'icon' => $section['icon'],
            'links' => array_values($sectionLinks)
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
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
            
            $stmt = $pdo->query("SELECT * FROM link_sections ORDER BY sort_order ASC, created_at DESC");
            $sections = $stmt->fetchAll();
            
            $stmt = $pdo->query("SELECT * FROM links ORDER BY sort_order ASC, created_at DESC");
            $links = $stmt->fetchAll();
            
            // 组装数据结构
            $result = [];
            foreach ($sections as $section) {
                $sectionLinks = array_filter($links, function($link) use ($section) {
                    return $link['section_id'] === $section['id'];
                });
                
                $result[] = [
                    'id' => $section['id'],
                    'title' => $section['title'],
                    'icon' => $section['icon'],
                    'is_active' => $section['is_active'],
                    'sort_order' => $section['sort_order'],
                    'links' => array_values($sectionLinks)
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
            break;
        
        case 'create_section':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：name'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = generateId();
            $isActive = isset($data['is_active']) && $data['is_active'] ? '1' : '0';
            $sortOrder = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
            
            try {
                $stmt = $pdo->prepare("INSERT INTO link_sections (id, title, icon, is_active, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $id,
                    sanitizeInput($data['name']),
                    $data['icon'] ?? '',
                    $isActive,
                    $sortOrder
                ]);
                
                // 如果有链接数据，同时创建链接
                if (!empty($data['links']) && is_array($data['links'])) {
                    $linkStmt = $pdo->prepare("INSERT INTO links (id, section_id, name, url, description, is_active, sort_order) VALUES (?, ?, ?, ?, ?, '1', 0)");
                    foreach ($data['links'] as $linkData) {
                        if (!empty($linkData['name']) && !empty($linkData['url'])) {
                            $linkId = generateId();
                            $linkStmt->execute([
                                $linkId,
                                $id,
                                sanitizeInput($linkData['name']),
                                sanitizeInput($linkData['url']),
                                sanitizeInput($linkData['description'] ?? '')
                            ]);
                        }
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => '分组创建成功',
                    'data' => [
                        'id' => $id,
                        'title' => $data['name'],
                        'icon' => $data['icon'] ?? '',
                        'is_active' => $isActive,
                        'sort_order' => $sortOrder
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '创建失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update_section':
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
            
            $stmt = $pdo->prepare("SELECT * FROM link_sections WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该分组'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $isActive = isset($data['is_active']) && $data['is_active'] ? '1' : '0';
            $sortOrder = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE link_sections SET title = ?, icon = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([
                    sanitizeInput($data['name']),
                    $data['icon'] ?? '',
                    $isActive,
                    $sortOrder,
                    $data['id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '更新成功',
                    'data' => [
                        'id' => $data['id'],
                        'title' => $data['name'],
                        'icon' => $data['icon'] ?? '',
                        'is_active' => $isActive,
                        'sort_order' => $sortOrder
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'delete_section':
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
                // 事务：同时删除分组和其下的链接
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("DELETE FROM links WHERE section_id = ?");
                $stmt->execute([$data['id']]);
                
                $stmt = $pdo->prepare("DELETE FROM link_sections WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => '删除成功'], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '删除失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'toggle_section':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少分组 ID'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->prepare("UPDATE link_sections SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => '状态已更新'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该分组'], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'create_link':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['section_id']) || empty($data['name']) || empty($data['url'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：section_id, name, url'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = generateId();
            $sortOrder = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
            
            try {
                $stmt = $pdo->prepare("INSERT INTO links (id, section_id, name, url, description, is_active, sort_order) VALUES (?, ?, ?, ?, ?, '1', ?)");
                $stmt->execute([
                    $id,
                    $data['section_id'],
                    sanitizeInput($data['name']),
                    sanitizeInput($data['url']),
                    sanitizeInput($data['description'] ?? ''),
                    $sortOrder
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '链接创建成功',
                    'data' => [
                        'id' => $id,
                        'section_id' => $data['section_id'],
                        'name' => $data['name'],
                        'url' => $data['url'],
                        'description' => $data['description'] ?? '',
                        'is_active' => '1',
                        'sort_order' => $sortOrder
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '创建失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update_link':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id']) || empty($data['name']) || empty($data['url'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：id, name, url'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->prepare("SELECT * FROM links WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该链接'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $sortOrder = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
            
            try {
                $stmt = $pdo->prepare("UPDATE links SET section_id = ?, name = ?, url = ?, description = ?, is_active = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([
                    $data['section_id'],
                    sanitizeInput($data['name']),
                    sanitizeInput($data['url']),
                    sanitizeInput($data['description'] ?? ''),
                    isset($data['is_active']) && $data['is_active'] ? '1' : '0',
                    $sortOrder,
                    $data['id']
                ]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '链接更新成功',
                    'data' => [
                        'id' => $data['id'],
                        'section_id' => $data['section_id'],
                        'name' => $data['name'],
                        'url' => $data['url'],
                        'description' => $data['description'] ?? '',
                        'is_active' => isset($data['is_active']) && $data['is_active'] ? '1' : '0',
                        'sort_order' => $sortOrder
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'delete_link':
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
                $stmt = $pdo->prepare("DELETE FROM links WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => '删除成功'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => '未找到该链接'], JSON_UNESCAPED_UNICODE);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '删除失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'toggle_link':
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少链接 ID'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $stmt = $pdo->prepare("UPDATE links SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => '状态已更新'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该链接'], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update_section_sort':
            // 批量更新分组排序（拖拽排序）
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['sections']) || !is_array($data['sections'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：sections'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE link_sections SET sort_order = ? WHERE id = ?");
                
                foreach ($data['sections'] as $section) {
                    if (isset($section['id']) && isset($section['sort_order'])) {
                        $stmt->execute([intval($section['sort_order']), $section['id']]);
                    }
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => '分组排序已更新'], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新排序失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update_link_sort':
            // 批量更新链接排序（拖拽排序）
            if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            if (empty($data['section_id']) || empty($data['links']) || !is_array($data['links'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：section_id, links'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE links SET sort_order = ? WHERE id = ?");
                
                foreach ($data['links'] as $link) {
                    if (isset($link['id']) && isset($link['sort_order'])) {
                        $stmt->execute([intval($link['sort_order']), $link['id']]);
                    }
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => '链接排序已更新'], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                $pdo->rollBack();
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新排序失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 action 参数'], JSON_UNESCAPED_UNICODE);
    }
}