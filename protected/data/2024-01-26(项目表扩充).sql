
-- ----------------------------
-- Table structure for fed_project
-- ----------------------------
ALTER TABLE fed_project ADD COLUMN urgency int(11) NULL DEFAULT NULL COMMENT '紧急程度 1:低 2：中 3：高' AFTER end_date;
