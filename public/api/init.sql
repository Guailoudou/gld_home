-- 数据库初始化脚本
-- 创建下载信息表

CREATE TABLE IF NOT EXISTS `downloads` (
  `id` VARCHAR(32) NOT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT '下载项名称',
  `size` VARCHAR(50) NOT NULL COMMENT '文件大小或来源',
  `url` VARCHAR(512) NOT NULL COMMENT '下载链接',
  `is_default` CHAR(1) DEFAULT '0' COMMENT '是否默认展示：1-是，0-否',
  `is_featured` CHAR(1) DEFAULT '0' COMMENT '是否强调显示：1-是，0-否',
  `priority` INT DEFAULT 0 COMMENT '展示优先级，数字越小越靠前',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_is_default` (`is_default`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='下载信息表';

-- 创建下载包表
CREATE TABLE IF NOT EXISTS `download_packages` (
  `id` VARCHAR(32) NOT NULL,
  `code` VARCHAR(10) NOT NULL COMMENT '下载码（5 位随机字符）',
  `name` VARCHAR(255) NOT NULL COMMENT '下载包名称',
  `description` TEXT COMMENT '下载包描述',
  `download_ids` TEXT NOT NULL COMMENT '包含的下载项 ID 列表（JSON 数组）',
  `is_active` CHAR(1) DEFAULT '1' COMMENT '是否启用：1-是，0-否',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code` (`code`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='下载包表';

-- 创建留言板表
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT '留言 ID',
  `nickname` VARCHAR(50) NOT NULL COMMENT '昵称',
  `email` VARCHAR(100) DEFAULT NULL COMMENT '邮箱（可选）',
  `content` TEXT NOT NULL COMMENT '留言内容',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP 地址',
  `is_displayed` TINYINT(1) DEFAULT 0 COMMENT '是否展示：1-是，0-否',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  KEY `idx_is_displayed` (`is_displayed`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_nickname` (`nickname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='留言板表';

-- 创建链接分组表
CREATE TABLE IF NOT EXISTS `link_sections` (
  `id` VARCHAR(32) NOT NULL,
  `title` VARCHAR(100) NOT NULL COMMENT '分组标题',
  `icon` VARCHAR(50) DEFAULT '' COMMENT '图标名称',
  `is_active` CHAR(1) DEFAULT '1' COMMENT '是否启用：1-是，0-否',
  `sort_order` INT DEFAULT 0 COMMENT '排序权重',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='链接分组表';

-- 创建链接表
CREATE TABLE IF NOT EXISTS `links` (
  `id` VARCHAR(32) NOT NULL,
  `section_id` VARCHAR(32) NOT NULL COMMENT '所属分组 ID',
  `name` VARCHAR(100) NOT NULL COMMENT '链接名称',
  `url` VARCHAR(512) NOT NULL COMMENT '链接地址',
  `description` VARCHAR(255) DEFAULT '' COMMENT '链接描述',
  `is_active` CHAR(1) DEFAULT '1' COMMENT '是否启用：1-是，0-否',
  `sort_order` INT DEFAULT 0 COMMENT '排序权重',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_section_id` (`section_id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='链接表';

-- 插入初始链接数据
INSERT INTO `link_sections` (`id`, `title`, `icon`, `is_active`, `sort_order`) VALUES
('section_1', '链接', 'link', '1', 1);

INSERT INTO `links` (`id`, `section_id`, `name`, `url`, `description`, `is_active`, `sort_order`) VALUES
('link_1', 'section_1', '下载中心', '/download', '', '1', 1),
('link_2', 'section_1', 'Alist 云盘', 'https://alist.gldhn.top/', '', '1', 2),
('link_3', 'section_1', 'blog', 'https://blog.gldhn.top/', '', '1', 3),
('link_4', 'section_1', 'OPL-MC', 'https://blog.gldhn.top/2024/04/19/opl_ui/', '', '1', 4);

-- 插入初始数据（3 条示例数据）
INSERT INTO `downloads` (`id`, `name`, `size`, `url`, `is_default`) VALUES
('1', '老下载站', '密码：gldxz', 'http://guailoudou.ysepan.com/', '1'),
('2', 'opl 联机工具', '夸克网盘', 'https://pan.quark.cn/s/8537690fd74b', '1'),
('3', '示例资源', '示例大小', 'https://example.com', '0');
