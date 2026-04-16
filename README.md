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

- **前端框架**: Vue 3.5.32 (Composition API)
- **开发语言**: TypeScript 6.0.2
- **构建工具**: Vite 8.0.4
- **路由管理**: Vue Router 4.6.4
- **CSS 预处理器**: Sass 1.99.0
- **UI 设计**: 自定义毛玻璃风格

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
