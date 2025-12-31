
-- ----------------------------
-- Table structure for bl_ticket_replies
-- ----------------------------
DROP TABLE IF EXISTS `bl_ticket_replies`;
CREATE TABLE `bl_ticket_replies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `ticket_id` bigint unsigned NOT NULL COMMENT '工单ID',
  `user_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '回复人ID（用户ID或管理员ID）',
  `user_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '回复人类型：1用户 2管理员 3系统',
  `content` text COLLATE utf8mb4_general_ci NOT NULL COMMENT '回复内容',
  `attachments` json DEFAULT NULL COMMENT '附件URL列表（JSON数组）',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_ticket` (`ticket_id`,`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='工单回复表';

-- ----------------------------
-- Alter table bl_tickets
-- ----------------------------
ALTER TABLE `bl_tickets` 
ADD COLUMN `score` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '评分: 1-5' AFTER `reply_at`,
ADD COLUMN `evaluation` text COLLATE utf8mb4_general_ci COMMENT '评价内容' AFTER `score`,
ADD COLUMN `evaluation_at` timestamp NULL DEFAULT NULL COMMENT '评价时间' AFTER `evaluation`;
