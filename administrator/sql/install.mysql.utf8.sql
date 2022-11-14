CREATE TABLE IF NOT EXISTS `#__blogg_posts` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NOT NULL  DEFAULT 1,
`ordering` INT(11)  DEFAULT 0,
`checked_out` INT(11)  NOT NULL,
`checked_out_time` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
`created_by` INT(11)  NOT NULL,
`modified_by` INT(11)  DEFAULT 0,
`post_date` DATETIME NOT NULL,
`post_update` DATETIME DEFAULT NULL,
`post_ip` VARCHAR(15) DEFAULT NULL,
`post_hits` INT(11) DEFAULT 0,
`post_title` VARCHAR(500)  NOT NULL,
`post_desc` TEXT NOT NULL ,
`post_image` VARCHAR(255) DEFAULT NULL,
`ext_gallery` VARCHAR(255)  DEFAULT NULL,
`ext_gallery_text` VARCHAR(255)  DEFAULT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__blogg_comments` (
`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,

`state` TINYINT(1)  NOT NULL  DEFAULT 1,
`ordering` INT(11)  DEFAULT 0,
`checked_out` INT(11)  NOT NULL,
`checked_out_time` DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
`created_by` INT(11)  NOT NULL,
`modified_by` INT(11)  DEFAULT 0,
`post_id` INT(11)  NOT NULL,
`comment_date` DATETIME NOT NULL,
`comment_update` DATETIME DEFAULT NULL,
`comment_ip` VARCHAR(15)  DEFAULT NULL,
`comment_hit` INT(11)  DEFAULT 0,
`comment_title` VARCHAR(150)  NOT NULL,
`comment_desc` TEXT NOT NULL ,
`comment_gallery` VARCHAR(255) DEFAULT NULL,
`comment_gallery_text` VARCHAR(255) DEFAULT NULL,
PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

