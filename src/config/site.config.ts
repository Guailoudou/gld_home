// 网站配置文件 - 所有链接和内容均可在此修改

export interface LinkItem {
  name: string;
  url: string;
  description?: string;
}

export interface SiteConfig {
  siteName: string;
  background?: {
    image: string;
    overlay?: string;
  };
  navigation: LinkItem[];
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
  is_featured?: string;
  priority?: number;
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
        { name: 'GitHub', icon: 'code', url: 'https://github.com/Guailoudou' },
        { name: 'B站', icon: 'star', url: 'https://space.bilibili.com/496960407' },
        { name: '邮箱', icon: 'mail', url: 'mailto:guailoudou@163.com' },
        { name: 'QQ', icon: 'chat', url: 'https://qm.qq.com/q/tVdS3KHYxG' }
      ]
    },
    navCards: [
      {
        title: '作品展示',
        icon: 'atom',
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
      {
        name: 'mcping',
        description: 'Minecraft 服务器状态查询',
        icon: 'search',
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
        icon: 'download',
        items: [
          { name: '老下载站', size: '密码：gldxz', url: 'http://guailoudou.ysepan.com/' },
          { name: 'opl联机工具', size: '夸克网盘', url: 'https://pan.quark.cn/s/8537690fd74b' }
        ]
      }
    ]
  },
  portfolio: {
    title: '作品展示',
    description: '这里展示我的个人项目和作品',
    items: [
      {
        title: 'OPL联机工具',
        description: '基于 OpenP2P 的便捷联机工具',
        image: 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=800&q=80',
        tags: ['WPF', 'C#', 'Golang'],
        link: 'https://blog.gldhn.top/2024/04/19/opl_ui/',
        github: 'https://github.com/Guailoudou/OPL-WpfApp'
      }
    ]
  }
};
