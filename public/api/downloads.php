<?php
/**
 * 下载信息和下载包 API
 * 
 * GET 请求：
 * - 无参数：获取所有默认展示的数据
 * - ?id=xxx：获取指定 ID 的数据（支持多个 id，用逗号分隔）
 * - ?package_code=xxx：获取下载包数据
 * 
 * POST 请求：
 * - 获取所有数据（需要密码哈希）：action=get_all, password_hash=xxx
 * - 创建新的下载项（需要密码哈希）：action=create, data={...}, password_hash=xxx
 * - 更新下载项（需要密码哈希）：action=update, data={...}, password_hash=xxx
 * - 删除下载项（需要密码哈希）：action=delete, data={...}, password_hash=xxx
 * - 获取所有下载包（需要密码哈希）：action=get_packages, password_hash=xxx
 * - 创建下载包（需要密码哈希）：action=create_package, data={...}, password_hash=xxx
 * - 更新下载包（需要密码哈希）：action=update_package, data={...}, password_hash=xxx
 * - 删除下载包（需要密码哈希）：action=delete_package, data={...}, password_hash=xxx
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

// 生成密码的哈希值（用于前端计算）
// 前端应该计算：SHA256(密码 + 盐值)
// 这里我们使用简单的哈希：SHA256(密码)
// 生产环境建议使用更安全的方案，如 HMAC
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
    echo json_encode(['error' => '数据库连接失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * 验证密码哈希
 */
function verifyPasswordHash($passwordHash, $expectedHash) {
    return isset($passwordHash) && $passwordHash === $expectedHash;
}

/**
 * 生成随机 ID
 */
function generateId() {
    return bin2hex(random_bytes(8));
}

/**
 * 验证请求方法
 */
function validateMethod($method) {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        http_response_code(405);
        echo json_encode(['error' => '方法不允许'], JSON_UNESCAPED_UNICODE);
        exit();
    }
}

/**
 * 获取 JSON 输入
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
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
        echo json_encode(['error' => '不支持的请求方法'], JSON_UNESCAPED_UNICODE);
}

/**
 * 处理 GET 请求
 */
function handleGet($pdo) {
    // 检查是否有 package_code 参数（下载码）
    if (isset($_GET['package_code'])) {
        $stmt = $pdo->prepare("SELECT * FROM download_packages WHERE code = ? AND is_active = '1'");
        $stmt->execute([$_GET['package_code']]);
        $package = $stmt->fetch();
        
        if ($package) {
            // 解析下载 ID 列表
            $downloadIds = json_decode($package['download_ids'], true);
            if (is_array($downloadIds) && !empty($downloadIds)) {
                $placeholders = str_repeat('?,', count($downloadIds) - 1) . '?';
                $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id IN ($placeholders)");
                $stmt->execute($downloadIds);
                $downloads = $stmt->fetchAll();
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'package' => [
                            'id' => $package['id'],
                            'code' => $package['code'],
                            'name' => $package['name'],
                            'description' => $package['description']
                        ],
                        'downloads' => $downloads
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => false, 'error' => '下载包为空'], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(['success' => false, 'error' => '下载码不存在或已禁用'], JSON_UNESCAPED_UNICODE);
        }
        return;
    }
    
    // 检查是否有 id 参数（支持下载包 ID、下载码或普通下载项 ID）
    if (isset($_GET['id'])) {
        $ids = explode(',', $_GET['id']);
        
        // 优先在下载包中搜索（支持 ID 和下载码 code）
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        // 同时匹配 id 字段和 code 字段
        $stmt = $pdo->prepare("SELECT * FROM download_packages WHERE (id IN ($placeholders) OR code IN ($placeholders)) AND is_active = '1'");
        $stmt->execute(array_merge($ids, $ids));
        $packages = $stmt->fetchAll();
        
        if (!empty($packages)) {
            // 找到了下载包，合并所有下载包中的资源
            $allDownloadIds = [];
            foreach ($packages as $pkg) {
                $downloadIds = json_decode($pkg['download_ids'], true);
                if (is_array($downloadIds)) {
                    $allDownloadIds = array_merge($allDownloadIds, $downloadIds);
                }
            }
            
            // 去重
            $allDownloadIds = array_unique($allDownloadIds);
            
            if (!empty($allDownloadIds)) {
                $placeholders = str_repeat('?,', count($allDownloadIds) - 1) . '?';
                $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id IN ($placeholders)");
                $stmt->execute($allDownloadIds);
                $downloads = $stmt->fetchAll();
                
                // 如果只有一个下载包，返回 package 信息
                if (count($packages) === 1) {
                    $pkg = $packages[0];
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'package' => [
                                'id' => $pkg['id'],
                                'code' => $pkg['code'],
                                'name' => $pkg['name'],
                                'description' => $pkg['description']
                            ],
                            'downloads' => $downloads
                        ]
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    // 多个下载包，只返回下载列表
                    echo json_encode(['success' => true, 'data' => $downloads], JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo json_encode(['success' => false, 'error' => '下载包为空'], JSON_UNESCAPED_UNICODE);
            }
            return;
        }
        
        // 下载包中没有找到，在普通下载项中搜索
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $data = $stmt->fetchAll();
        
        if ($data) {
            // 如果只有一个 ID，返回单个对象；否则返回数组
            if (count($ids) === 1) {
                echo json_encode(['success' => true, 'data' => $data[0]], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(['success' => false, 'error' => '未找到该下载项'], JSON_UNESCAPED_UNICODE);
        }
        return;
    }
    
    // 默认：只获取默认展示的数据（不需要密码）
    $stmt = $pdo->query("SELECT * FROM downloads WHERE is_default = '1' ORDER BY created_at DESC");
    $data = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
}

/**
 * 生成随机下载码（5 位字母数字组合）
 */
function generateDownloadCode() {
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $code = '';
    for ($i = 0; $i < 5; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $code;
}

/**
 * 处理 POST 请求（统一处理所有需要密码的操作）
 */
function handlePost($pdo, $expectedPasswordHash) {
    $data = getJsonInput();
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => '无效的请求数据'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 验证密码哈希
    if (!verifyPasswordHash($data['password_hash'] ?? null, $expectedPasswordHash)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => '密码错误'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    // 根据 action 执行不同操作
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'get_all':
            // 获取所有数据
            $stmt = $pdo->query("SELECT * FROM downloads ORDER BY created_at DESC");
            $result = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
            break;
        
        case 'create':
            // 创建新数据
            if (empty($data['name']) || empty($data['size']) || empty($data['url'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：name, size, url'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $id = generateId();
            $isDefault = isset($data['is_default']) && $data['is_default'] ? '1' : '0';
            
            try {
                $stmt = $pdo->prepare("INSERT INTO downloads (id, name, size, url, is_default) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id, $data['name'], $data['size'], $data['url'], $isDefault]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '创建成功',
                    'data' => [
                        'id' => $id,
                        'name' => $data['name'],
                        'size' => $data['size'],
                        'url' => $data['url'],
                        'is_default' => $isDefault
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '创建失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update':
            // 更新数据
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：id'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 检查记录是否存在
            $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该下载项'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 更新数据
            $isDefault = isset($data['is_default']) && $data['is_default'] ? '1' : '0';
            
            try {
                $stmt = $pdo->prepare("UPDATE downloads SET name = ?, size = ?, url = ?, is_default = ? WHERE id = ?");
                $stmt->execute([$data['name'], $data['size'], $data['url'], $isDefault, $data['id']]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '更新成功',
                    'data' => [
                        'id' => $data['id'],
                        'name' => $data['name'],
                        'size' => $data['size'],
                        'url' => $data['url'],
                        'is_default' => $isDefault
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'delete':
            // 删除数据
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：id'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM downloads WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => '删除成功'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => '未找到该下载项'], JSON_UNESCAPED_UNICODE);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '删除失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'get_packages':
            // 获取所有下载包
            $stmt = $pdo->query("SELECT * FROM download_packages ORDER BY created_at DESC");
            $result = $stmt->fetchAll();
            
            // 解析每个下载包的 download_ids
            $packages = array_map(function($pkg) {
                $pkg['download_ids'] = json_decode($pkg['download_ids'], true) ?: [];
                return $pkg;
            }, $result);
            
            echo json_encode(['success' => true, 'data' => $packages], JSON_UNESCAPED_UNICODE);
            break;
        
        case 'create_package':
            // 创建下载包
            if (empty($data['name']) || empty($data['download_ids'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：name, download_ids'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 生成唯一的 5 位下载码
            $code = generateDownloadCode();
            // 确保下载码唯一
            while (true) {
                $stmt = $pdo->prepare("SELECT id FROM download_packages WHERE code = ?");
                $stmt->execute([$code]);
                if (!$stmt->fetch()) {
                    break;
                }
                $code = generateDownloadCode();
            }
            
            // 生成 5 位 ID（和下载码一样长度）
            $id = generateDownloadCode();
            $isActive = isset($data['is_active']) && $data['is_active'] ? '1' : '0';
            $downloadIdsJson = json_encode($data['download_ids'], JSON_UNESCAPED_UNICODE);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO download_packages (id, code, name, description, download_ids, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $code, $data['name'], $data['description'] ?? '', $downloadIdsJson, $isActive]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '下载包创建成功',
                    'data' => [
                        'id' => $id,
                        'code' => $code,
                        'name' => $data['name'],
                        'description' => $data['description'] ?? '',
                        'download_ids' => $data['download_ids'],
                        'is_active' => $isActive
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '创建失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'update_package':
            // 更新下载包
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：id'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 检查记录是否存在
            $stmt = $pdo->prepare("SELECT * FROM download_packages WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => '未找到该下载包'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            // 更新数据
            $isActive = isset($data['is_active']) && $data['is_active'] ? '1' : '0';
            $downloadIdsJson = json_encode($data['download_ids'], JSON_UNESCAPED_UNICODE);
            
            try {
                $stmt = $pdo->prepare("UPDATE download_packages SET code = ?, name = ?, description = ?, download_ids = ?, is_active = ? WHERE id = ?");
                $stmt->execute([$data['code'], $data['name'], $data['description'] ?? '', $downloadIdsJson, $isActive, $data['id']]);
                
                echo json_encode([
                    'success' => true,
                    'message' => '下载包更新成功',
                    'data' => [
                        'id' => $data['id'],
                        'code' => $data['code'],
                        'name' => $data['name'],
                        'description' => $data['description'] ?? '',
                        'download_ids' => $data['download_ids'],
                        'is_active' => $isActive
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '更新失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        case 'delete_package':
            // 删除下载包
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '缺少必填字段：id'], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM download_packages WHERE id = ?");
                $stmt->execute([$data['id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => '删除成功'], JSON_UNESCAPED_UNICODE);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => '未找到该下载包'], JSON_UNESCAPED_UNICODE);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => '删除失败', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            }
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => '无效的 action 参数'], JSON_UNESCAPED_UNICODE);
    }
}
