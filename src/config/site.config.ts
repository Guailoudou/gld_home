// 网站配置文件 - 所有链接和内容均可在此修改

export interface LinkItem {
  name: string;
  url: string;
  description?: string;
}

export interface Section {
  title: string;
  icon?: string;
  links: LinkItem[];
}

export interface SiteConfig {
  siteName: string;
  logo?: string;
  background?: {
    image: string;
    overlay?: string;
  };
  navigation: LinkItem[];
  sections: Section[];
  portfolio?: {
    title: string;
    description: string;
    items: PortfolioItem[];
  };
  home: HomeConfig;
  tools: ToolsConfig;
  download: DownloadConfig;
  footer: {
    copyright: string;
    links: LinkItem[];
  };
}

export interface PortfolioItem {
  title: string;
  description: string;
  image?: string;
  tags: string[];
  link?: string;
  github?: string;
}

export interface SocialLink {
  name: string;
  icon: string;
  url: string;
}

export interface HomeNavCard {
  title: string;
  icon: string;
  description: string;
  url: string;
  featured?: boolean;
}

export interface HomeConfig {
  personalInfo: {
    name: string;
    title: string;
    description: string;
    avatar: string;
    socialLinks: SocialLink[];
  };
  navCards: HomeNavCard[];
}

export interface ToolItem {
  name: string;
  description: string;
  icon: string;
  color: string;
  url: string;
}

export interface ToolsConfig {
  title: string;
  description: string;
  items: ToolItem[];
}

export interface DownloadItem {
  name: string;
  size: string;
  url: string;
}

export interface DownloadCategory {
  name: string;
  description: string;
  icon: string;
  items: DownloadItem[];
}

export interface DownloadConfig {
  title: string;
  description: string;
  categories: DownloadCategory[];
}

export const siteConfig: SiteConfig = {
  siteName: 'GLD 小站',
  logo: 'https://q1.qlogo.cn/g?b=qq&nk=1241593334&s=640', // 可选 logo
  background: {
    image: 'https://images.unsplash.com/photo-1522383225653-ed111181a951?w=1920&q=80',
    overlay: 'rgba(255, 255, 255, 0.3)'
  },
  navigation: [
    { name: '链接', url: '/links' },
    { name: '下载', url: '/download' },
    { name: '小工具', url: '/tools' },
    { name: '留言', url: '/guestbook' }
  ],
  home: {
    personalInfo: {
      name: '乖漏斗',
      title: '个人开发者',
      description: '热爱编程，喜欢探索新技术',
      avatar: 'https://q1.qlogo.cn/g?b=qq&nk=1241593334&s=640',
      socialLinks: [
        { name: 'GitHub', icon: '🐙', url: 'https://github.com/Guailoudou' },
        { name: 'B站', icon: '📺', url: 'https://space.bilibili.com/496960407' },
        { name: '邮箱', icon: '📧', url: 'mailto:guailoudou@163.com' },
        { name: 'QQ', icon: '🐧', url: 'https://qm.qq.com/q/tVdS3KHYxG' }
      ]
    },
    navCards: [
      // 通过 navigation 配置动态生成，此处可以添加额外的卡片
      {
        title: '作品展示',
        icon: '⚛',
        description: '查看我的个人项目',
        url: '/portfolio',
        featured: true
      }
    ]
  },
  tools: {
    title: '小工具',
    description: '各种实用有趣的小工具',
    items: [
      // {
      //   name: '2048',
      //   description: '经典数字益智游戏',
      //   icon: '🎮',
      //   color: '#FF6B6B',
      //   url: '/tools/2048'
      // },
      // {
      //   name: '计数器',
      //   description: '简单实用的计数工具',
      //   icon: '🔢',
      //   color: '#4ECDC4',
      //   url: '/tools/counter'
      // },
      // {
      //   name: '计算器',
      //   description: '在线科学计算器',
      //   icon: '',
      //   color: '#45B7D1',
      //   url: '/tools/calculator'
      // },
      // {
      //   name: '电子木鱼',
      //   description: '在线敲木鱼解压',
      //   icon: '',
      //   color: '#FFA07A',
      //   url: '/tools/wooden-fish'
      // },
      // {
      //   name: '新年祝福',
      //   description: '生成新年祝福卡片',
      //   icon: '🎊',
      //   color: '#DDA0DD',
      //   url: '/tools/new-year'
      // },
      // {
      //   name: '音乐解锁',
      //   description: '解锁付费音乐',
      //   icon: '🎵',
      //   color: '#98D8C8',
      //   url: '/tools/music-unlock'
      // },
      {
        name: 'mcping',
        description: 'Minecraft 服务器状态查询',
        icon: '📶',
        color: '#F7DC6F',
        url: 'https://www.gldhn.top/mc/mcping/'
      }
    ]
  },
  footer: {
    copyright: '{{ siteName }}. All rights reserved.',
    links: [
      { name: 'GitHub', url: 'https://github.com/Guailoudou' },
      { name: '关于', url: '/about' },
      { name: '鄂 ICP 备 2026017004 号', url: 'https://beian.miit.gov.cn/' }
    ]
  },
  download: {
    title: '下载中心',
    description: '提供各种软件、资源和文档的下载',
    categories: [
      {
        name: '软件下载',
        description: '各种实用软件下载',
        icon: '💾',
        items: [
          { name: '老下载站', size: '密码：gldxz', url: 'http://guailoudou.ysepan.com/' },
          { name: 'opl联机工具', size: '夸克网盘', url: 'https://pan.quark.cn/s/8537690fd74b' }
        ]
      }
    ]
  },
  sections: [
    {
      title: '链接',
      icon: '↓',
      links: [
        // { name: 'flash', url: 'https://example.com/flash' },
        { name: '下载中心', url: '/download' },
        { name: 'Alist 云盘', url: 'https://alist.gldhn.top/' },
        { name: 'blog', url: 'https://blog.gldhn.top/' },
        { name: 'OPL-MC', url: 'https://blog.gldhn.top/2024/04/19/opl_ui/' }
      ]
    },
    // {
    //   title: '小工具',
    //   icon: '',
    //   links: [
    //     // { name: '2048', url: '/tools/2048' },
    //     // { name: '计数器', url: '/tools/counter' },
    //     // { name: '计算器', url: '/tools/calculator' },
    //     // { name: '电子木鱼', url: '/tools/wooden-fish' },
    //     // { name: '新年祝福', url: '/tools/new-year' },
    //     // { name: '音乐解锁', url: '/tools/music-unlock' },
    //     { name: 'mcping', url: 'https://www.gldhn.top/mc/mcping/' }
    //   ]
    // },
    // {
    //   title: 'html 上机作业',
    //   icon: '⚛',
    //   links: [
    //     { name: 'endbigwork', url: '/homework/endbigwork' },
    //     { name: 'endwork', url: '/homework/endwork' },
    //     { name: 'work1', url: '/homework/work1' },
    //     { name: 'work2', url: '/homework/work2' },
    //     { name: 'work3', url: '/homework/work3' }
    //   ]
    // }
  ],
  portfolio: {
    title: '作品展示',
    description: '这里展示我的个人项目和作品',
    items: [
      // {
      //   title: '个人博客系统',
      //   description: '基于 Vue3 + Node.js 的全栈博客系统，支持 Markdown 编辑、评论、标签等功能',
      //   image: 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=800&q=80',
      //   tags: ['Vue3', 'Node.js', 'MongoDB'],
      //   link: 'https://example.com/blog',
      //   github: 'https://github.com/example/blog'
      // },
      // {
      //   title: '在线代码编辑器',
      //   description: '支持多种编程语言的在线 IDE，实时预览、代码高亮、智能提示',
      //   image: 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&q=80',
      //   tags: ['TypeScript', 'Monaco', 'WebAssembly'],
      //   link: 'https://example.com/editor',
      //   github: 'https://github.com/example/online-editor'
      // },
      // {
      //   title: '天气应用',
      //   description: '实时天气查询应用，支持多城市、天气预报、空气质量指数',
      //   image: 'https://images.unsplash.com/photo-1592210454359-908974d5e98b?w=800&q=80',
      //   tags: ['Vue3', 'API', 'CSS3'],
      //   link: 'https://example.com/weather'
      // },
      // {
      //   title: '任务管理工具',
      //   description: '简洁高效的待办事项管理应用，支持分类、提醒、数据统计',
      //   image: 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?w=800&q=80',
      //   tags: ['Vue3', 'Pinia', 'LocalStorage'],
      //   github: 'https://github.com/example/task-manager'
      // },
      // {
      //   title: '图片处理工具',
      //   description: '在线图片编辑工具，支持裁剪、滤镜、格式转换等功能',
      //   image: 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=800&q=80',
      //   tags: ['Canvas', 'JavaScript', 'WebGL'],
      //   link: 'https://example.com/image-tool'
      // },
      // {
      //   title: '音乐播放器',
      //   description: '精美的在线音乐播放器，支持播放列表、歌词显示、音效调节',
      //   image: 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&q=80',
      //   tags: ['Vue3', 'Audio API', 'CSS3'],
      //   link: 'https://example.com/music'
      // }
    ]
  }
};
