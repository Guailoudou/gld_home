<?php
/**
 * 留言审核 API
 * 通过一次性令牌快速通过留言审核
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// 连接数据库
$config = require __DIR__ . '/auth_config.php';
$dbConfig = $config['database'];

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

try {
    // 自动清理过期令牌（每 5 次请求触发一次）
    if (random_int(1, 5) === 1) {
        $stmt = $pdo->prepare("DELETE FROM approve_tokens WHERE expires_at < NOW()");
        $stmt->execute();
    }
} catch (Exception $e) {
    // 清理失败不影响主流程，静默忽略
}

// 获取令牌
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => '缺少审核令牌'], JSON_UNESCAPED_UNICODE);
    exit();
}

// 验证令牌
$stmt = $pdo->prepare("SELECT * FROM approve_tokens WHERE token = ? AND used = 0 AND expires_at > NOW()");
$stmt->execute([$token]);
$tokenData = $stmt->fetch();

if (!$tokenData) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => '审核令牌无效或已过期',
        'message' => '该令牌可能已被使用或超过 24 小时有效期'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// 开始事务
$pdo->beginTransaction();

try {
    // 更新留言状态为已展示
    $stmt = $pdo->prepare("UPDATE messages SET is_displayed = 1 WHERE id = ?");
    $stmt->execute([$tokenData['message_id']]);
    
    // 标记令牌为已使用
    $stmt = $pdo->prepare("UPDATE approve_tokens SET used = 1 WHERE token = ?");
    $stmt->execute([$token]);
    
    // 提交事务
    $pdo->commit();
    
    // 如果是浏览器访问，显示成功页面
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false) {
        // 覆盖 Content-Type 为 HTML
        header('Content-Type: text/html; charset=utf-8');
        echo "
        <!DOCTYPE html>
        <html lang='zh-CN'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>审核成功</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .success-card {
                    background: white;
                    border-radius: 16px;
                    padding: 40px;
                    max-width: 500px;
                    width: 100%;
                    text-align: center;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                }
                .success-icon {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 20px;
                    font-size: 40px;
                    color: white;
                }
                h1 {
                    color: #1e1e1e;
                    font-size: 24px;
                    margin-bottom: 10px;
                }
                p {
                    color: #666;
                    font-size: 16px;
                    line-height: 1.6;
                    margin-bottom: 20px;
                }
                .info-box {
                    background: #f8f9fa;
                    border-radius: 8px;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: left;
                }
                .info-box p {
                    margin: 5px 0;
                    font-size: 14px;
                }
                .btn {
                    display: inline-block;
                    padding: 12px 30px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    margin-top: 10px;
                    transition: all 0.3s ease;
                }
                .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
                }
            </style>
        </head>
        <body>
            <div class='success-card'>
                <div class='success-icon'>✓</div>
                <h1>审核成功</h1>
                <p>该留言已通过审核，现在可以在网站中显示了。</p>
                <div class='info-box'>
                    <p><strong>留言 ID：</strong>{$tokenData['message_id']}</p>
                    <p><strong>审核时间：</strong>" . date('Y-m-d H:i:s') . "</p>
                    <p><strong>状态：</strong><span style='color: #38ef7d;'>✓ 已展示</span></p>
                </div>
                <a href='/admin/messages' class='btn'>前往留言管理</a>
            </div>
        </body>
        </html>
        ";
    } else {
        // API 请求返回 JSON
        echo json_encode([
            'success' => true,
            'message' => '留言审核成功',
            'data' => [
                'message_id' => $tokenData['message_id'],
                'approved_at' => date('Y-m-d H:i:s')
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    // 回滚事务
    $pdo->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => '审核失败',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
