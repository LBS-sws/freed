
-- ----------------------------
-- Table structure for fed_project
-- ----------------------------
ALTER TABLE fed_project ADD COLUMN plan_date date NULL DEFAULT NULL COMMENT '计划完成日期' AFTER menu_id;
