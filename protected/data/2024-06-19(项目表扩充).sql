
-- ----------------------------
-- Table structure for fed_project
-- ----------------------------
ALTER TABLE fed_project ADD COLUMN status_type int(1) NOT NULL DEFAULT 1 COMMENT '0:草稿 1:发布' AFTER urgency;
