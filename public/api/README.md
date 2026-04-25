# 下载管理 API 使用说明

## 📁 文件结构

```
public/
└── api/
    ├── db_config.php          # 数据库配置文件（已添加到 .gitignore）
    ├── downloads.php          # 下载管理 API 主文件
    └── init.sql               # 数据库初始化脚本
```

## 🗄️ 数据库配置

### 1. 执行数据库初始化

在 MySQL 数据库中执行 `public/api/init.sql` 文件：

```bash
mysql -u www_gldhn_top -p www_gldhn_top < public/api/init.sql
```

或手动在 MySQL 客户端中执行 SQL 文件内容。

### 2. 修改数据库配置（可选）

如果需要修改数据库连接信息，编辑 `public/api/db_config.php`：

```php
return [
    'host' => 'localhost',
    'dbname' => 'your_database_name',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
];
```

**注意**: `db_config.php` 已添加到 `.gitignore`，不会被提交到 Git 仓库。

## 🔌 API 接口说明

### 基础 URL
```
/api/downloads.php
```

### 1. 获取下载列表

#### 获取默认展示的数据
```http
GET /api/downloads.php
```

响应示例：
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "老下载站",
      "size": "密码：gldxz",
      "url": "http://guailoudou.ysepan.com/",
      "is_default": "1"
    }
  ]
}
```

#### 获取指定 ID 的数据
```http
GET /api/downloads.php?id=1
```

#### 获取所有数据（包括非默认展示）
```http
GET /api/downloads.php?all=1
```

### 2. 创建下载项

```http
POST /api/downloads.php
Content-Type: application/json

{
  "name": "资源名称",
  "size": "文件大小或来源",
  "url": "https://example.com/file.zip",
  "is_default": true
}
```

响应示例：
```json
{
  "success": true,
  "message": "创建成功",
  "data": {
    "id": "abc123def456",
    "name": "资源名称",
    "size": "文件大小或来源",
    "url": "https://example.com/file.zip",
    "is_default": "1"
  }
}
```

### 3. 更新下载项

```http
PUT /api/downloads.php
Content-Type: application/json

{
  "id": "abc123def456",
  "name": "新资源名称",
  "size": "新文件大小",
  "url": "https://example.com/newfile.zip",
  "is_default": false
}
```

### 4. 删除下载项

```http
DELETE /api/downloads.php
Content-Type: application/json

{
  "id": "abc123def456"
}
```

## 🎨 前端使用

### 下载页面自动加载 API 数据

下载页面 (`/download`) 会自动从 API 获取数据：

- **无参数访问** (`/download`): 只显示 `is_default=1` 的数据
- **带 ID 访问** (`/download?id=xxx`): 只显示指定 ID 的数据

### 管理页面

访问 `/admin/downloads` 可以管理下载资源：

- ✅ 添加新下载项
- ✅ 编辑现有下载项
- ✅ 删除下载项
- ✅ 设置是否默认展示

## 🔐 安全建议

1. **生产环境配置**：
   - 为管理页面添加身份验证
   - 限制 API 访问权限
   - 使用 HTTPS

2. **数据库权限**：
   - 为应用创建专用的数据库用户
   - 只授予必要的权限

3. **CORS 配置**：
   - 当前 API 允许跨域访问（`Access-Control-Allow-Origin: *`）
   - 生产环境应限制为特定域名

## 📝 数据表结构

```sql
CREATE TABLE `downloads` (
  `id` VARCHAR(32) NOT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT '下载项名称',
  `size` VARCHAR(50) NOT NULL COMMENT '文件大小或来源',
  `url` VARCHAR(512) NOT NULL COMMENT '下载链接',
  `is_default` CHAR(1) DEFAULT '0' COMMENT '是否默认展示：1-是，0-否',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 🛠️ 故障排查

### 1. API 返回 500 错误
- 检查数据库连接配置是否正确
- 确认数据库用户权限
- 查看 PHP 错误日志

### 2. API 返回 404
- 确认文件路径是否正确
- 检查 Web 服务器配置（Apache/Nginx）

### 3. 跨域问题
- 检查 CORS 头配置
- 确认请求方法是否正确

## 📞 技术支持

如有问题，请提交 Issue 或联系开发者。
