# GLD 的小站 - 个人主页

一个基于 Vue 3 + TypeScript + Vite 构建的现代化个人主页，采用毛玻璃设计风格，支持完全配置化。

![Vue](https://img.shields.io/badge/Vue-3.5.32-4FC08D?logo=vue.js)
![TypeScript](https://img.shields.io/badge/TypeScript-6.0.2-3178C6?logo=typescript)
![Vite](https://img.shields.io/badge/Vite-8.0.4-646CFF?logo=vite)
![License](https://img.shields.io/badge/license-MIT-blue.svg)

## ✨ 特性

- 🎨 **毛玻璃设计风格** - 现代化的 UI 设计，类似 macOS 的毛玻璃效果
- 📱 **完全响应式** - 完美适配桌面、平板、手机等多种设备
- ⚙️ **完全配置化** - 所有内容可通过配置文件修改，无需更改代码
- 🚀 **高性能** - 基于 Vite 5，开发秒开，构建快速
- 🎯 **TypeScript** - 完整的类型支持，开发更安全
- 🌈 **动画效果** - 流畅的过渡动画和交互效果
- 🔗 **路由系统** - 基于 Vue Router 4 的单页应用

## 📸 页面展示

### 主要页面

- **首页** - 个人信息展示 + 快捷导航卡片
- **链接导航** - 常用链接和工具集合
- **作品展示** - 个人项目和作品展示
- **下载中心** - 软件和资源下载
- **小工具** - 各种实用小工具集合
- **留言板** - 访客留言互动

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

启动后访问：<http://localhost:5174/>

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
├── public/              # 静态资源
│   └── logo.svg        # 网站 Logo
├── src/
│   ├── assets/         # 项目资源文件
│   ├── components/     # 公共组件
│   │   └── Layout.vue  # 主布局组件（导航栏 + 页脚）
│   ├── config/         # 配置文件
│   │   └── site.config.ts  # ⭐ 网站配置（所有可配置内容）
│   ├── router/         # 路由配置
│   │   └── index.ts
│   ├── views/          # 页面组件
│   │   ├── Home.vue    # 首页
│   │   ├── Links.vue   # 链接导航
│   │   ├── Portfolio.vue # 作品展示
│   │   ├── Download.vue  # 下载中心
│   │   ├── Tools.vue     # 小工具
│   │   └── Guestbook.vue # 留言板
│   ├── App.vue         # 根组件
│   ├── main.ts         # 入口文件
│   └── style.css       # 全局样式
├── index.html          # HTML 模板
├── package.json        # 项目配置
├── tsconfig.json       # TypeScript 配置
└── vite.config.ts      # Vite 配置
```

## ⚙️ 配置说明

所有可配置内容都在 [`src/config/site.config.ts`](src/config/site.config.ts) 文件中。

### 网站基本信息

```typescript
{
  siteName: 'GLD 的小站',  // 网站名称
  logo: '/logo.svg',      // Logo 路径
  background: {
    image: '背景图片 URL',
    overlay: '遮罩颜色'
  }
}
```

### 导航菜单

```typescript
navigation: [
  { name: '链接', url: '/links' },
  { name: '下载', url: '/download' },
  { name: '小工具', url: '/tools' },
  { name: '留言', url: '/guestbook' }
]
```

### 首页个人信息

```typescript
home: {
  personalInfo: {
    name: '你的名字',
    title: '职业/头衔',
    description: '个人简介',
    avatar: '头像 URL',
    socialLinks: [
      { name: 'GitHub', icon: '🐙', url: 'https://github.com' },
      { name: '微博', icon: '📱', url: 'https://weibo.com' }
    ]
  },
  navCards: [
    {
      title: '作品展示',
      icon: '⚛',
      description: '查看我的个人项目',
      url: '/portfolio',
      featured: true  // 是否突出显示
    }
  ]
}
```

### 小工具配置

```typescript
tools: {
  title: '小工具',
  description: '各种实用有趣的小工具',
  items: [
    {
      name: '2048',
      description: '经典数字益智游戏',
      icon: '🎮',
      color: '#FF6B6B',  // 卡片背景色
      url: '/tools/2048'
    }
  ]
}
```

### 链接导航板块

```typescript
sections: [
  {
    title: '链接',
    icon: '↓',
    links: [
      { name: 'flash', url: 'https://example.com/flash' },
      { name: '下载中心', url: '/download' }
    ]
  }
]
```

### 作品展示

```typescript
portfolio: {
  title: '作品展示',
  description: '这里展示我的个人项目和作品',
  items: [
    {
      title: '个人博客系统',
      description: '基于 Vue3 + Node.js 的全栈博客系统',
      image: '封面图片 URL',
      tags: ['Vue3', 'Node.js', 'MongoDB'],
      link: 'https://example.com/blog',
      github: 'https://github.com/example/blog'
    }
  ]
}
```

### 页脚配置

```typescript
footer: {
  copyright: '{{ siteName }}. All rights reserved.',
  links: [
    { name: 'GitHub', url: 'https://github.com' },
    { name: '关于', url: '/about' }
  ]
}
```

## 🎨 自定义样式

### 修改背景

编辑 [`src/style.css`](src/style.css)：

```css
body {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-attachment: fixed;
}
```

### 修改背景图片

在 `site.config.ts` 中修改：

```typescript
background: {
  image: '你的背景图片 URL',
  overlay: 'rgba(255, 255, 255, 0.3)'
}
```

## 🛠️ 技术栈

### 前端技术
- **前端框架**: Vue 3.5.32 (Composition API)
- **开发语言**: TypeScript 6.0.2
- **构建工具**: Vite 8.0.4
- **路由管理**: Vue Router 4.6.4
- **CSS 预处理器**: Sass 1.99.0
- **UI 设计**: 自定义毛玻璃风格 + HarmonyOS 图标

### 后端技术
- **后端语言**: PHP 8+
- **数据库**: MySQL / MariaDB
- **数据库操作**: PDO (预处理语句)
- **安全机制**: SHA256 密码哈希、SQL 注入防护、XSS 防护

## 🔧 PHP 后端说明

本项目包含完整的 PHP 后端 API 系统，用于支持下载管理、下载包管理和留言板功能。

### 后端目录结构

```
public/
└── api/
    ├── auth_config.php      # 数据库和管理密码配置（需添加到 .gitignore）
    ├── init.sql             # 数据库初始化脚本
    ├── downloads.php        # 下载管理 API
    └── messages.php         # 留言板 API
```

### 数据库配置

1. **创建数据库**

   使用提供的初始化脚本创建数据表：
   ```bash
   mysql -u 用户名 -p 数据库名 < public/api/init.sql
   ```

2. **配置文件**

   编辑 `public/api/auth_config.php` 配置数据库连接信息：
   ```php
   return [
       'database' => [
           'host' => 'localhost',
           'dbname' => '你的数据库名',
           'username' => '你的用户名',
           'password' => '你的密码',
           'charset' => 'utf8mb4'
       ],
       'auth' => [
           'admin_password' => '你的管理密码'
       ]
   ];
   ```

3. **安全注意事项**
   - `auth_config.php` 包含敏感信息，**必须**添加到 `.gitignore` 中
   - 默认管理密码为 `gld2026admin`，请修改为强密码
   - 建议使用 HTTPS 部署以保护数据传输安全

### 数据库表结构

#### 下载信息表 (downloads)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | VARCHAR(32) | 下载项 ID |
| name | VARCHAR(255) | 下载项名称 |
| size | VARCHAR(50) | 文件大小或来源 |
| url | VARCHAR(512) | 下载链接 |
| is_default | CHAR(1) | 是否默认展示（1-是，0-否） |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

#### 下载包表 (download_packages)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | VARCHAR(32) | 下载包 ID（5 位随机字符） |
| code | VARCHAR(10) | 下载码（5 位随机字符） |
| name | VARCHAR(255) | 下载包名称 |
| description | TEXT | 下载包描述 |
| download_ids | TEXT | 包含的下载项 ID 列表（JSON 数组） |
| is_active | CHAR(1) | 是否启用（1-是，0-否） |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

#### 留言板表 (messages)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INT | 留言 ID（自增主键） |
| nickname | VARCHAR(50) | 昵称（必填） |
| email | VARCHAR(100) | 邮箱（可选） |
| content | TEXT | 留言内容（必填） |
| ip_address | VARCHAR(45) | 提交者 IP |
| is_displayed | TINYINT(1) | 是否展示（1-是，0-否，默认 0） |
| created_at | DATETIME | 创建时间 |
| updated_at | DATETIME | 更新时间 |

### API 接口说明

#### 下载 API (`/api/downloads.php`)

**GET 请求**:
- `/api/downloads.php` - 获取默认展示的下载项
- `/api/downloads.php?id=2` - 获取指定 ID 的下载项（支持多个 ID，用逗号分隔）
- `/api/downloads.php?id=abc12` - 通过下载码或下载包 ID 获取下载包内容
- `/api/downloads.php?package_code=abc12` - 通过下载码获取下载包

**POST 请求**:
| action | 说明 | 所需参数 |
|--------|------|----------|
| `get_all` | 获取所有下载项 | `password_hash` |
| `create` | 创建下载项 | `password_hash`, `name`, `size`, `url`, `is_default` |
| `update` | 更新下载项 | `password_hash`, `id`, `name`, `size`, `url`, `is_default` |
| `delete` | 删除下载项 | `password_hash`, `id` |
| `get_packages` | 获取所有下载包 | `password_hash` |
| `create_package` | 创建下载包 | `password_hash`, `name`, `description`, `download_ids` |
| `update_package` | 更新下载包 | `password_hash`, `id`, `name`, `description`, `download_ids`, `is_active` |
| `delete_package` | 删除下载包 | `password_hash`, `id` |

**下载包访问流程**:
1. 管理员在后台选择多个资源创建下载包
2. 系统生成 5 位随机下载码（如 `abi3r`）
3. 用户访问 `/download/abi3r` 查看下载包内容
4. 前端通过 API 的 `id` 参数获取下载包中的资源列表

#### 留言板 API (`/api/messages.php`)

**GET 请求**:
- `/api/messages.php` - 获取已展示的留言（公开）
- `/api/messages.php?page=1&limit=10` - 分页获取留言

**POST 请求**:
| action | 说明 | 所需参数 |
|--------|------|----------|
| `submit` | 提交留言（公开） | `nickname`, `email`(可选), `content` |
| `get_all` | 获取所有留言（需密码） | `password_hash`, `page`, `limit`, `search_nickname`(可选), `filter_displayed`(可选), `filter_date_from`(可选), `filter_date_to`(可选) |
| `toggle_display` | 切换展示状态 | `password_hash`, `id` |
| `delete` | 删除留言 | `password_hash`, `id` |
| `batch_toggle` | 批量切换展示状态 | `password_hash`, `ids[]`, `display` |
| `batch_delete` | 批量删除留言 | `password_hash`, `ids[]` |

**留言提交流程**:
1. 用户填写昵称和留言内容（邮箱可选）
2. 前端进行表单验证
3. 提交到 API，后端再次验证数据
4. 留言默认状态为"未展示"（`is_displayed = 0`）
5. 管理员在后台审核后切换为"展示"状态

### 安全机制

1. **密码验证**
   - 管理操作需要传递密码的 SHA256 哈希值
   - 前端使用 Web Crypto API 计算哈希：
     ```typescript
     const hash = await crypto.subtle.digest('SHA-256', encoder.encode(password))
     ```
   - 后端比较哈希值验证身份，密码本身不在网络中传输

2. **SQL 注入防护**
   - 所有数据库查询使用 PDO 预处理语句
   - 参数绑定防止 SQL 注入攻击

3. **XSS 防护**
   - 用户输入使用 `htmlspecialchars()` 进行转义
   - 移除 HTML 标签，只保留纯文本

4. **输入验证**
   - 昵称：必填，最多 50 字符
   - 邮箱：可选，但填写后必须为有效格式
   - 内容：必填，5-2000 字符

### 管理后台

管理后台提供以下管理功能：

| 模块 | 路径 | 功能 |
|------|------|------|
| 下载管理 | `/admin/downloads` | 添加、编辑、删除下载项 |
| 下载包管理 | `/admin/packages` | 创建下载包、生成下载码 |
| 留言管理 | `/admin/messages` | 审核留言、批量操作、搜索筛选 |

**首次访问管理页面**需要输入管理密码进行身份验证，密码会保存在浏览器 localStorage 中以便后续访问。

### 部署注意事项

1. **PHP 版本要求**: PHP 8.0 或更高版本
2. **PHP 扩展要求**: PDO, PDO_MySQL, JSON
3. **MySQL 版本**: MySQL 5.7+ 或 MariaDB 10.2+
4. **服务器配置**: 确保 Web 服务器（Nginx/Apache）支持 PHP 和 PHP 文件解析
5. **文件权限**: 确保 PHP 进程有读取 `api/` 目录的权限
6. **HTTPS**: 建议使用 HTTPS 部署以保护密码和数据传输安全

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

## 📦 构建和部署

### 本地构建

```bash
npm run build
```

### 部署到服务器

将 `dist/` 目录上传到 Web 服务器即可。

### 部署到 Vercel

```bash
# 安装 Vercel CLI
npm i -g vercel

# 部署
vercel
```

### 部署到 GitHub Pages

1. 修改 `vite.config.ts`，设置 `base: '/仓库名/'`
2. 构建项目：`npm run build`
3. 将 `dist/` 目录推送到 `gh-pages` 分支

## 📝 添加新页面

1. 在 `src/views/` 下创建新的 Vue 组件
2. 在 `src/router/index.ts` 中添加路由
3. 在 `src/config/site.config.ts` 的 `navigation` 中添加导航链接
4. 在 `src/components/Layout.vue` 中会自动显示（如果使用配置）

示例路由：

```typescript
{
  path: '/new-page',
  name: 'newPage',
  component: () => import('../views/NewPage.vue')
}
```

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📄 许可证

MIT License

## 👤 作者

GLD

## 🔗 链接

- [Vue.js 文档](https://vuejs.org/)
- [Vite 文档](https://vitejs.dev/)
- [TypeScript 文档](https://www.typescriptlang.org/)
- [Vue Router 文档](https://router.vuejs.org/)

***

Made with ❤️ by NHD
