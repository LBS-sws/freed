/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : freeddev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2024-01-31 15:47:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for fed_min
-- ----------------------------
DROP TABLE IF EXISTS `fed_min`;
CREATE TABLE `fed_min` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL COMMENT '大项目id',
  `menu_id` varchar(2) NOT NULL COMMENT '菜单编号',
  `plan_date` date DEFAULT NULL COMMENT '计划完成日期',
  `plan_start_date` date DEFAULT NULL COMMENT '计划开始日期',
  `project_code` varchar(255) DEFAULT NULL COMMENT '项目编号',
  `project_type` int(2) NOT NULL DEFAULT '1' COMMENT '项目类型 1：新功能 2：问题修复 3：功能延伸 4：其它',
  `project_name` varchar(255) NOT NULL COMMENT '项目名称',
  `project_text` text NOT NULL COMMENT '项目描述',
  `project_status` int(2) NOT NULL DEFAULT '0' COMMENT '项目状态 0：未进展 1：进展中 9：已完成',
  `assign_user` varchar(255) DEFAULT NULL COMMENT '修复人员（多选）',
  `assign_str_user` varchar(255) DEFAULT NULL,
  `assign_plan` int(3) NOT NULL DEFAULT '0' COMMENT '进度',
  `current_user` varchar(255) DEFAULT NULL COMMENT '当前跟进人员',
  `start_date` datetime DEFAULT NULL COMMENT '开始日期',
  `end_date` datetime DEFAULT NULL COMMENT '结束日期',
  `urgency` int(11) DEFAULT NULL COMMENT '紧急程度 1:低 2：中 3：高',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小项目管理表';

-- ----------------------------
-- Table structure for fed_min_assign
-- ----------------------------
DROP TABLE IF EXISTS `fed_min_assign`;
CREATE TABLE `fed_min_assign` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `min_id` int(10) NOT NULL COMMENT '小项目id',
  `username` varchar(255) NOT NULL COMMENT '日报表系统的username',
  `prev_id` int(11) NOT NULL DEFAULT '0' COMMENT '前一条记录的id',
  `prev_plan` int(3) NOT NULL DEFAULT '0' COMMENT '前一条记录的进度',
  `assign_plan` int(3) NOT NULL COMMENT '进度',
  `assign_text` text NOT NULL COMMENT '跟进内容',
  `assign_day` int(4) NOT NULL DEFAULT '0' COMMENT '跟进的天数（对比上一条记录）',
  `assign_hour` int(2) NOT NULL DEFAULT '0' COMMENT '跟进的小时（对比上一条记录）',
  `assign_minute` int(2) NOT NULL DEFAULT '0' COMMENT '跟进的分钟（对比上一条记录）',
  `diff_timer` int(10) NOT NULL COMMENT '时间差（对比上一条记录）',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小项目跟进表';

-- ----------------------------
-- Table structure for fed_min_email
-- ----------------------------
DROP TABLE IF EXISTS `fed_min_email`;
CREATE TABLE `fed_min_email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `min_id` int(10) NOT NULL COMMENT '菜单配置id',
  `username` varchar(255) NOT NULL COMMENT '日报表系统的username',
  `email_type` int(2) NOT NULL DEFAULT '1' COMMENT '邮件通知类型0：不接受邮件 1:项目所有变动  2：初始及完成邮件  3：仅完成邮件',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小项目电邮收件人';

-- ----------------------------
-- Table structure for fed_min_history
-- ----------------------------
DROP TABLE IF EXISTS `fed_min_history`;
CREATE TABLE `fed_min_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `min_id` int(11) NOT NULL,
  `update_type` int(11) NOT NULL DEFAULT '1' COMMENT '修改類型 1：修改 2:新增',
  `update_html` text NOT NULL COMMENT '修改內容',
  `update_json` text COMMENT '修改前的json數據',
  `espe_type` int(1) NOT NULL DEFAULT '0' COMMENT '1：特別標註的修改',
  `lcu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小项目修改記錄表';

-- ----------------------------
-- Table structure for fed_min_user
-- ----------------------------
DROP TABLE IF EXISTS `fed_min_user`;
CREATE TABLE `fed_min_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `min_id` int(10) NOT NULL COMMENT '菜单配置id',
  `username` varchar(255) NOT NULL COMMENT '日报表系统的username',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小项目跟进人员';

-- ----------------------------
-- Table structure for fed_project_user
-- ----------------------------
DROP TABLE IF EXISTS `fed_project_user`;
CREATE TABLE `fed_project_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) NOT NULL COMMENT '菜单配置id',
  `username` varchar(255) NOT NULL COMMENT '日报表系统的username',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='项目跟进人员';


-- ----------------------------
-- Table structure for fed_project
-- ----------------------------
ALTER TABLE fed_project ADD COLUMN plan_start_date date NULL DEFAULT NULL COMMENT '计划开始日期' AFTER plan_date;
ALTER TABLE fed_project ADD COLUMN assign_str_user varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER assign_user;

INSERT INTO fed_project_user (project_id,username) SELECT id,assign_user FROM fed_project WHERE id>0 and assign_str_user is NULL;

UPDATE fed_project SET
assign_str_user = (SELECT b.disp_name FROM security.sec_user b WHERE assign_user=b.username)
WHERE assign_str_user is NULL;
