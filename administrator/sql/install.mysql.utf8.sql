CREATE TABLE IF NOT EXISTS `#__blogg_posts` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT NULL  DEFAULT 1,
`ordering` INT NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
`modified_by` INT(11)  NULL  DEFAULT 0,
`post_date` DATETIME NULL  DEFAULT NULL ,
`post_update` DATETIME NULL  DEFAULT NULL ,
`post_ip` VARCHAR(15)  NULL  DEFAULT "",
`post_hits` INT(11)  NULL  DEFAULT 0,
`post_title` VARCHAR(500)  NOT NULL ,
`post_desc` TEXT NOT NULL ,
`post_image` VARCHAR(255)  NULL  DEFAULT "",
`ext_gallery` VARCHAR(255)  NULL  DEFAULT "",
`ext_gallery_text` VARCHAR(255)  NULL  DEFAULT "",
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__blogg_comments` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NULL  DEFAULT 1,
`ordering` INT(11)  NULL  DEFAULT 0,
`checked_out` INT(11)  UNSIGNED,
`checked_out_time` DATETIME NULL  DEFAULT NULL ,
`created_by` INT(11)  NULL  DEFAULT 0,
`modified_by` INT(11)  NULL  DEFAULT 0,
`post_id` INT(11)  NULL  DEFAULT 0,
`comment_date` DATETIME NULL  DEFAULT NULL,
`comment_update` DATETIME NULL  DEFAULT NULL,
`comment_ip` VARCHAR(15)  NULL  DEFAULT "",
`comment_hit` INT(11)  NULL  DEFAULT 0,
`comment_title` VARCHAR(150)  NOT NULL,
`comment_desc` TEXT NOT NULL ,
`comment_gallery` VARCHAR(255)  NULL  DEFAULT "",
`comment_gallery_text` VARCHAR(255)  NULL  DEFAULT "",
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

