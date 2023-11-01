/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50620
Source Host           : localhost:3306
Source Database       : freeddev

Target Server Type    : MYSQL
Target Server Version : 50620
File Encoding         : 65001

Date: 2023-11-01 16:23:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for fed_project
-- ----------------------------
DROP TABLE IF EXISTS `fed_project`;
CREATE TABLE `fed_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` varchar(2) NOT NULL COMMENT '菜单编号',
  `project_code` varchar(255) DEFAULT NULL COMMENT '项目编号',
  `project_type` int(2) NOT NULL DEFAULT '1' COMMENT '项目类型 1：新功能 2：问题修复 3：功能延伸 4：其它',
  `project_name` varchar(255) NOT NULL COMMENT '项目名称',
  `project_text` text NOT NULL COMMENT '项目描述',
  `project_status` int(2) NOT NULL DEFAULT '0' COMMENT '项目状态 0：未进展 1：进展中 9：已完成',
  `assign_user` varchar(255) DEFAULT NULL COMMENT '修复人员',
  `assign_plan` int(3) NOT NULL DEFAULT '0' COMMENT '进度',
  `current_user` varchar(255) DEFAULT NULL COMMENT '当前跟进人员',
  `start_date` datetime DEFAULT NULL COMMENT '开始日期',
  `end_date` datetime DEFAULT NULL COMMENT '结束日期',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='项目管理表';

-- ----------------------------
-- Table structure for fed_project_assign
-- ----------------------------
DROP TABLE IF EXISTS `fed_project_assign`;
CREATE TABLE `fed_project_assign` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) NOT NULL COMMENT '菜单配置id',
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='项目跟进表';

-- ----------------------------
-- Table structure for fed_project_email
-- ----------------------------
DROP TABLE IF EXISTS `fed_project_email`;
CREATE TABLE `fed_project_email` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) NOT NULL COMMENT '菜单配置id',
  `username` varchar(255) NOT NULL COMMENT '日报表系统的username',
  `email_type` int(2) NOT NULL DEFAULT '1' COMMENT '邮件通知类型0：不接受邮件 1:项目所有变动  2：初始及完成邮件  3：仅完成邮件',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='项目电邮收件人';

-- ----------------------------
-- Table structure for fed_project_history
-- ----------------------------
DROP TABLE IF EXISTS `fed_project_history`;
CREATE TABLE `fed_project_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `update_type` int(11) NOT NULL DEFAULT '1' COMMENT '修改類型 1：修改 2:新增',
  `update_html` text NOT NULL COMMENT '修改內容',
  `update_json` text COMMENT '修改前的json數據',
  `espe_type` int(1) NOT NULL DEFAULT '0' COMMENT '1：特別標註的修改',
  `lcu` varchar(30) DEFAULT NULL,
  `lcd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='KA客户修改記錄表';

-- ----------------------------
-- Table structure for fed_queue
-- ----------------------------
DROP TABLE IF EXISTS `fed_queue`;
CREATE TABLE `fed_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rpt_desc` varchar(250) NOT NULL,
  `req_dt` datetime DEFAULT NULL,
  `fin_dt` datetime DEFAULT NULL,
  `username` varchar(30) NOT NULL,
  `status` char(1) NOT NULL,
  `rpt_type` varchar(10) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rpt_content` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for fed_queue_param
-- ----------------------------
DROP TABLE IF EXISTS `fed_queue_param`;
CREATE TABLE `fed_queue_param` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` int(10) unsigned NOT NULL,
  `param_field` varchar(50) NOT NULL,
  `param_value` varchar(500) DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for fed_queue_user
-- ----------------------------
DROP TABLE IF EXISTS `fed_queue_user`;
CREATE TABLE `fed_queue_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` int(10) unsigned NOT NULL,
  `username` varchar(30) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for fed_setting
-- ----------------------------
DROP TABLE IF EXISTS `fed_setting`;
CREATE TABLE `fed_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `menu_code` varchar(255) DEFAULT NULL,
  `menu_name` varchar(200) NOT NULL COMMENT '菜單的名字',
  `user_str` varchar(255) NOT NULL COMMENT '加入的员工名称 逗号,分割',
  `display` int(11) NOT NULL DEFAULT '1' COMMENT '0:不顯示  1：顯示',
  `z_index` int(11) DEFAULT '0',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='菜單配置表';

-- ----------------------------
-- Table structure for fed_setting_info
-- ----------------------------
DROP TABLE IF EXISTS `fed_setting_info`;
CREATE TABLE `fed_setting_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(10) NOT NULL COMMENT '菜单配置id',
  `username` varchar(255) NOT NULL COMMENT '日报表系统的username',
  `email_type` int(2) NOT NULL DEFAULT '1' COMMENT '邮件通知类型 0：不接受任何邮件 1：只接受指定 2：全部接受',
  `user_type` int(11) NOT NULL DEFAULT '1' COMMENT '账号类型',
  `lcu` varchar(255) DEFAULT NULL,
  `luu` varchar(255) DEFAULT NULL,
  `lcd` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lud` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='菜單配置表里的用户';
