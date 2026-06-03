# GLD 小站 - 个人主页

一个基于 Vue 3 + TypeScript + Vite 构建的现代化个人主页，采用毛玻璃设计风格，支持动态数据管理和后台管理。

![Vue](https://img.shields.io/badge/Vue-3.5.32-4FC08D?logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-6.0.2-3178C6?logo=typescript)
![Vite](https://img.shields.io/badge/Vite-8.0.8-646CFF?logo=vite)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

## ✨ 特性

- 🎨 **毛玻璃设计风格** - 现代化的 UI 设计，类似 macOS 的毛玻璃效果
- 📱 **完全响应式** - 完美适配桌面、平板、手机等多种设备
- ⚙️ **配置化管理** - 网站基础信息通过配置文件管理，无需更改代码
- 🗄️ **动态数据管理** - 下载、留言、链接等内容支持后台动态管理
- 🚀 **高性能** - 基于 Vite，开发秒开，构建快速
- 🎯 **TypeScript** - 完整的类型支持，开发更安全
- 🌈 **动画效果** - 流畅的过渡动画和交互效果
- 🔐 **统一登录验证** - 管理后台统一密码验证，安全便捷

## 📸 页面展示

### 前台页面

- **首页** - 个人信息展示 + 快捷导航卡片
- **链接导航** - 常用链接和工具集合
- **作品展示** - 个人项目和作品展示
- **下载中心** - 软件和资源下载（支持优先级排序、强调显示、下载码访问）
- **小工具** - 各种实用小工具集合
- **留言板** - 访客留言互动

### 管理后台

- **管理主页** - 统一登录入口 + 各模块导航
- **下载管理** - 下载资源增删改查
- **下载包管理** - 创建下载包、生成下载码
- **留言管理** - 留言审核、搜索筛选、批量操作
- **链接管理** - 链接分组和链接的增删改查

## 🚀 快速开始

### 环境要求

- Node.js >= 18.0.0
- npm >= 9.0.0

### 安装依赖

```bash
npm install
```

### 开发模式

```bash
npm run dev
```

### 构建生产版本

```bash
npm run build
```

构建文件将输出到 `dist/` 目录

### 预览生产构建

```bash
npm run preview
```

## 📁 项目结构

```
gld_home/
├── public/
│   ├── api/                         # PHP 后端 API
│   │   ├── auth_config.php          # 数据库和认证配置（敏感文件）
│   │   ├── downloads.php            # 下载管理 API
│   │   ├── messages.php             # 留言管理 API
│   │   ├── links.php                # 链接管理 API
│   │   ├── approve.php              # 留言快速审核 API
│   │   └── init.sql                 # 数据库初始化脚本
│   └── favicon.svg                  # 网站图标
├── src/
│   ├── main.ts                      # 入口文件
│   ├── App.vue                      # 根组件
│   ├── style.css                    # 全局样式
│   ├── router/
│   │   └── index.ts                 # 路由配置
│   ├── config/
│   │   └── site.config.ts           # 网站配置
│   ├── components/
│   │   ├── Layout.vue               # 布局组件（导航栏 + 页脚）
│   │   └── HIcon.vue                # HarmonyOS 风格图标组件
│   ├── views/
│   │   ├── Home.vue                 # 首页
│   │   ├── Links.vue                # 链接页
│   │   ├── Download.vue             # 下载页
│   │   ├── DownloadPackages.vue     # 下载包管理页
│   │   ├── Tools.vue                # 小工具页
│   │   ├── Guestbook.vue            # 留言页
│   │   ├── Portfolio.vue            # 作品展示页
│   │   ├── NotFound.vue             # 404 页面
│   │   ├── AdminDashboard.vue       # 管理后台首页
│   │   ├── AdminDownloads.vue       # 下载管理页
│   │   ├── AdminMessages.vue        # 留言管理页
│   │   └── AdminLinks.vue           # 链接管理页
│   └── utils/
│       └── icons.ts                 # 图标定义工具
├── construction.md                  # 项目结构详细文档
├── index.html                       # HTML 入口
├── vite.config.ts                   # Vite 配置
├── tsconfig.json                    # TypeScript 配置
└── package.json                     # 项目依赖
```

## ⚙️ 配置说明

网站基础信息在 [`src/config/site.config.ts`](src/config/site.config.ts) 中配置：

- `siteName` - 网站名称
- `background` - 背景图片和遮罩
- `navigation` - 导航栏链接
- `home` - 首页个人信息和导航卡片
- `tools` - 小工具列表
- `download` - 下载项配置（API 为空时的回退数据）
- `portfolio` - 作品展示列表
- `footer` - 页脚配置

## 🛠️ 技术栈

### 前端
- **框架**: Vue 3 (Composition API)
- **语言**: TypeScript
- **构建工具**: Vite
- **路由**: Vue Router 4
- **CSS**: Sass/SCSS
- **图标**: HarmonyOS 风格自定义图标

### 后端
- **语言**: PHP 8+
- **数据库**: MySQL / MariaDB
- **数据库操作**: PDO
- **安全**: SHA256 密码哈希、SQL 注入防护、XSS 防护

## 🔧 PHP 后端配置

### 1. 初始化数据库

```bash
mysql -u 用户名 -p 数据库名 < public/api/init.sql
```

### 2. 配置数据库连接

编辑 `public/api/auth_config.php`：

```php
return [
    'database' => [
        'host' => 'localhost',
        'dbname' => '数据库名',
        'username' => '用户名',
        'password' => '密码',
        'charset' => 'utf8mb4'
    ],
    'auth' => [
        'admin_password' => '管理密码'
    ],
    'email' => [
        'admin_email' => '管理员邮箱',
        'mail_api_url' => '/beta/mail/sendemail.php'
    ]
];
```

> ⚠️ `auth_config.php` 包含敏感信息，请确保已添加到 `.gitignore` 中。

### 数据库表结构

#### downloads（下载项）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | VARCHAR(32) | 下载项 ID |
| name | VARCHAR(255) | 名称 |
| size | VARCHAR(50) | 大小/来源 |
| url | VARCHAR(512) | 下载链接 |
| is_default | CHAR(1) | 是否默认展示 |
| is_featured | CHAR(1) | 是否强调显示 |
| priority | INT | 优先级（越小越靠前） |

#### download_packages（下载包）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | VARCHAR(32) | 下载包 ID |
| code | VARCHAR(10) | 下载码（5 位随机字符） |
| name | VARCHAR(255) | 名称 |
| description | TEXT | 描述 |
| download_ids | TEXT | 包含的下载项 ID（JSON） |
| is_active | CHAR(1) | 是否启用 |

#### messages（留言）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT | 留言 ID |
| nickname | VARCHAR(50) | 昵称 |
| email | VARCHAR(100) | 邮箱 |
| content | TEXT | 留言内容 |
| ip_address | VARCHAR(45) | IP 地址 |
| is_displayed | TINYINT(1) | 是否展示 |

#### link_sections（链接分组）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | VARCHAR(32) | 分组 ID |
| title | VARCHAR(100) | 分组标题 |
| icon | VARCHAR(50) | 图标 |
| is_active | CHAR(1) | 是否启用 |
| sort_order | INT | 排序 |

#### links（链接）
| 字段 | 类型 | 说明 |
|------|------|------|
| id | VARCHAR(32) | 链接 ID |
| section_id | VARCHAR(32) | 所属分组 ID |
| name | VARCHAR(100) | 链接名称 |
| url | TEXT | 链接地址 |
| description | VARCHAR(255) | 描述 |
| is_active | CHAR(1) | 是否启用 |
| sort_order | INT | 排序 |

## 🔐 管理后台

### 登录验证

所有管理页面统一在 `/admin` 进行密码验证：
- 密码通过 SHA-256 哈希计算后发送到后端验证
- 验证成功后保存到 `localStorage`
- 管理主页提供"退出登录"按钮清除凭证

### 管理模块

| 模块 | 路径 | 功能 |
|------|------|------|
| 管理主页 | `/admin` | 统一登录入口 |
| 下载管理 | `/admin/downloads` | 下载资源增删改查、设置优先级和强调显示 |
| 下载包管理 | `/admin/packages` | 创建下载包、生成下载码 |
| 留言管理 | `/admin/messages` | 留言审核、搜索筛选、批量操作 |
| 链接管理 | `/admin/links` | 链接分组和链接管理 |

## 📦 部署

### 前端

将 `dist/` 目录上传到 Web 服务器即可。

### Nginx 配置示例

```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    root /path/to/dist;
    index index.html index.php;
    
    # 前端 SPA 路由
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # PHP API
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 📝 添加新页面

1. 在 `src/views/` 下创建新的 Vue 组件
2. 在 `src/router/index.ts` 中添加路由
3. 在 `src/config/site.config.ts` 的 `navigation` 中添加导航链接

## 📄 许可证

MIT License

## 👤 作者

GLD (乖漏斗)

## 🔗 链接

- [Vue.js 文档](https://vuejs.org/)
- [Vite 文档](https://vitejs.dev/)
- [TypeScript 文档](https://www.typescriptlang.org/)
- [Vue Router 文档](https://router.vuejs.org/)

***

Made with ❤️ by 乖漏斗
