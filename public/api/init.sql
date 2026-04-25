-- 数据库初始化脚本
-- 创建下载信息表

CREATE TABLE IF NOT EXISTS `downloads` (
  `id` VARCHAR(32) NOT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT '下载项名称',
  `size` VARCHAR(50) NOT NULL COMMENT '文件大小或来源',
  `url` VARCHAR(512) NOT NULL COMMENT '下载链接',
  `is_default` CHAR(1) DEFAULT '0' COMMENT '是否默认展示：1-是，0-否',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_is_default` (`is_default`)
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

-- 插入初始数据（3 条示例数据）
INSERT INTO `downloads` (`id`, `name`, `size`, `url`, `is_default`) VALUES
('1', '老下载站', '密码：gldxz', 'http://guailoudou.ysepan.com/', '1'),
('2', 'opl 联机工具', '夸克网盘', 'https://pan.quark.cn/s/8537690fd74b', '1'),
('3', '示例资源', '示例大小', 'https://example.com', '0');
