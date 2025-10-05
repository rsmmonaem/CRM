-- MySQL Migration SQL
-- Generated on: 2025-10-05 10:58:41

-- Data for table: migrations
INSERT INTO migrations VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO migrations VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO migrations VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO migrations VALUES ('4', '2025_10_04_062755_create_services_table', '1');
INSERT INTO migrations VALUES ('5', '2025_10_04_062757_create_statuses_table', '1');
INSERT INTO migrations VALUES ('6', '2025_10_04_062759_create_leads_table', '1');
INSERT INTO migrations VALUES ('7', '2025_10_04_062802_create_lead_details_table', '1');
INSERT INTO migrations VALUES ('10', '2025_10_04_081438_create_permissions_table', '2');
INSERT INTO migrations VALUES ('11', '2025_10_04_081441_create_user_permissions_table', '2');
INSERT INTO migrations VALUES ('12', '2025_10_04_123632_update_leads_table_phone_email_nullable', '3');
INSERT INTO migrations VALUES ('13', '2025_10_05_064406_add_call_tracking_columns_to_lead_details_table', '4');
INSERT INTO migrations VALUES ('14', '2025_10_05_072304_make_name_nullable_in_leads_table', '5');
INSERT INTO migrations VALUES ('15', '2025_10_05_072430_add_location_to_leads_table', '6');

-- Data for table: users
INSERT INTO users VALUES ('1', 'Admin User', 'admin@example.com', '2025-10-04 07:10:43', '$2y$12$9dClPmeYSBd37NdRLiG.YeQBDwlFEDA1yNXa/JGNNmKFFNVSQgxUG', 'admin', 'KrgauXPEbDNZJ0ac0KithZEyj9jInUjqi1Rn0x21swIZOfIpqoGlbugsJxx9', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO users VALUES ('2', 'Regular User', 'user@example.com', '2025-10-04 07:10:44', '$2y$12$9dClPmeYSBd37NdRLiG.YeQBDwlFEDA1yNXa/JGNNmKFFNVSQgxUG', 'user', 'G3WCpdkXsti0K90JiswGcisyU1e00CZ4VZpcxZz7LL83YLFwPOkx7zmpknUv', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO users VALUES ('4', 'Updated Name', 'updated@example.com', NULL, '$2y$12$1Nq9vht0q9aByKkWDS/jV.aiv1fo1Htjs0RUp6JFgVhDFTZ8rBr62', 'user', NULL, '2025-10-04 08:56:19', '2025-10-04 12:09:03');
INSERT INTO users VALUES ('5', 'Test User', 'test@example.com', NULL, '$2y$12$HXkaKbdV2FyJqgs37lDHA.s8BmbdD0yojwqZHF9Evb9.h740wo3n6', 'user', NULL, '2025-10-04 12:01:53', '2025-10-04 12:01:53');
INSERT INTO users VALUES ('6', 'cr_asia', 'cr_asia@gmail.com', NULL, '$2y$12$Z3DN0GkqOECh.RcfoMsEpujKH8OjBzWzv9bQG8KBDsHMC3obIrWXa', 'user', NULL, '2025-10-04 12:59:49', '2025-10-04 12:59:49');

-- Data for table: password_reset_tokens

-- Data for table: sessions
INSERT INTO sessions VALUES ('hRY5pBiOihEkduYE6REtJL3rcsHJMggO232zF7pO', '1', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidGhRemQ4dnhrMkpMaXgzU3QxdWJkZ0t1dENLVGI3eGZEalN4enRKayI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', '1759655050');
INSERT INTO sessions VALUES ('TWf5mPHrUm1XdSJwkjUQAYHszhzyibN6At8qRyTB', '2', '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYlh6QTlYc2pUVThRaFpzRXFlSlhpRkdTbm5HTHlzRkFNV3c5amNHQiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', '1759661823');

-- Data for table: cache
INSERT INTO cache VALUES ('laravel-cache-user@gmai.com|127.0.0.1:timer', 'i:1759642457;', '1759642457');
INSERT INTO cache VALUES ('laravel-cache-user@gmai.com|127.0.0.1', 'i:1;', '1759642457');
INSERT INTO cache VALUES ('laravel-cache-admin@gmai.com|127.0.0.1:timer', 'i:1759643467;', '1759643467');
INSERT INTO cache VALUES ('laravel-cache-admin@gmai.com|127.0.0.1', 'i:2;', '1759643467');

-- Data for table: cache_locks

-- Data for table: jobs

-- Data for table: job_batches

-- Data for table: failed_jobs

-- Data for table: services
INSERT INTO services VALUES ('1', 'Web Development', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO services VALUES ('2', 'Mobile App Development', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO services VALUES ('3', 'Digital Marketing', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO services VALUES ('5', 'Travel Doctor - Visit', '2', '2025-10-05 07:38:58', '2025-10-05 07:38:58');
INSERT INTO services VALUES ('6', 'Travel Doctor - Work Permit', '2', '2025-10-05 07:39:15', '2025-10-05 07:39:15');
INSERT INTO services VALUES ('7', 'Ecommerce', '2', '2025-10-05 07:39:46', '2025-10-05 07:39:46');
INSERT INTO services VALUES ('8', 'Real Estate Erp', '2', '2025-10-05 07:40:03', '2025-10-05 07:40:03');
INSERT INTO services VALUES ('9', 'Custom Soulutions', '2', '2025-10-05 07:40:20', '2025-10-05 07:40:20');

-- Data for table: statuses
INSERT INTO statuses VALUES ('1', 'New', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO statuses VALUES ('2', 'Contacted', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO statuses VALUES ('3', 'Qualified', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO statuses VALUES ('4', 'Proposal Sent', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO statuses VALUES ('5', 'Negotiation', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');
INSERT INTO statuses VALUES ('6', 'Closed Win', '1', '2025-10-04 07:10:44', '2025-10-05 07:37:25');
INSERT INTO statuses VALUES ('7', 'Closed Lost', '1', '2025-10-04 07:10:44', '2025-10-04 07:10:44');

-- Data for table: permissions
INSERT INTO permissions VALUES ('1', 'dashboard', 'view', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('2', 'leads', 'view', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('3', 'leads', 'create', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('4', 'leads', 'edit', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('5', 'leads', 'delete', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('6', 'services', 'view', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('7', 'services', 'create', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('8', 'services', 'edit', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('9', 'services', 'delete', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('10', 'statuses', 'view', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('11', 'statuses', 'create', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('12', 'statuses', 'edit', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('13', 'statuses', 'delete', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('14', 'users', 'view', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('15', 'users', 'create', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('16', 'users', 'edit', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('17', 'users', 'delete', '2025-10-04 08:48:54', '2025-10-04 08:48:54');
INSERT INTO permissions VALUES ('18', 'lead_details', 'view', '2025-10-04 12:56:59', '2025-10-04 12:56:59');
INSERT INTO permissions VALUES ('19', 'lead_details', 'create', '2025-10-04 12:56:59', '2025-10-04 12:56:59');
INSERT INTO permissions VALUES ('20', 'lead_details', 'edit', '2025-10-04 12:56:59', '2025-10-04 12:56:59');
INSERT INTO permissions VALUES ('21', 'lead_details', 'delete', '2025-10-04 12:56:59', '2025-10-04 12:56:59');

-- Data for table: user_permissions
INSERT INTO user_permissions VALUES ('2', '4', '2', NULL, NULL);
INSERT INTO user_permissions VALUES ('3', '4', '3', NULL, NULL);
INSERT INTO user_permissions VALUES ('4', '4', '4', NULL, NULL);
INSERT INTO user_permissions VALUES ('5', '4', '5', NULL, NULL);
INSERT INTO user_permissions VALUES ('14', '2', '14', NULL, NULL);
INSERT INTO user_permissions VALUES ('15', '2', '3', NULL, NULL);
INSERT INTO user_permissions VALUES ('16', '2', '5', NULL, NULL);
INSERT INTO user_permissions VALUES ('17', '2', '4', NULL, NULL);
INSERT INTO user_permissions VALUES ('18', '2', '2', NULL, NULL);
INSERT INTO user_permissions VALUES ('19', '2', '7', NULL, NULL);
INSERT INTO user_permissions VALUES ('20', '2', '9', NULL, NULL);
INSERT INTO user_permissions VALUES ('21', '2', '8', NULL, NULL);
INSERT INTO user_permissions VALUES ('22', '2', '6', NULL, NULL);
INSERT INTO user_permissions VALUES ('23', '2', '11', NULL, NULL);
INSERT INTO user_permissions VALUES ('24', '2', '13', NULL, NULL);
INSERT INTO user_permissions VALUES ('25', '2', '12', NULL, NULL);
INSERT INTO user_permissions VALUES ('26', '2', '10', NULL, NULL);
INSERT INTO user_permissions VALUES ('27', '2', '1', NULL, NULL);
INSERT INTO user_permissions VALUES ('29', '5', '3', NULL, NULL);
INSERT INTO user_permissions VALUES ('30', '5', '5', NULL, NULL);
INSERT INTO user_permissions VALUES ('31', '5', '4', NULL, NULL);
INSERT INTO user_permissions VALUES ('32', '5', '2', NULL, NULL);
INSERT INTO user_permissions VALUES ('33', '5', '1', NULL, NULL);
INSERT INTO user_permissions VALUES ('34', '6', '1', NULL, NULL);
INSERT INTO user_permissions VALUES ('35', '6', '2', NULL, NULL);
INSERT INTO user_permissions VALUES ('36', '6', '3', NULL, NULL);
INSERT INTO user_permissions VALUES ('37', '6', '4', NULL, NULL);
INSERT INTO user_permissions VALUES ('38', '6', '5', NULL, NULL);
INSERT INTO user_permissions VALUES ('39', '6', '9', NULL, NULL);
INSERT INTO user_permissions VALUES ('40', '6', '8', NULL, NULL);
INSERT INTO user_permissions VALUES ('41', '6', '7', NULL, NULL);
INSERT INTO user_permissions VALUES ('42', '6', '6', NULL, NULL);
INSERT INTO user_permissions VALUES ('43', '6', '10', NULL, NULL);
INSERT INTO user_permissions VALUES ('44', '6', '11', NULL, NULL);
INSERT INTO user_permissions VALUES ('45', '6', '12', NULL, NULL);
INSERT INTO user_permissions VALUES ('46', '6', '13', NULL, NULL);
INSERT INTO user_permissions VALUES ('47', '6', '17', NULL, NULL);
INSERT INTO user_permissions VALUES ('48', '6', '16', NULL, NULL);
INSERT INTO user_permissions VALUES ('49', '6', '15', NULL, NULL);
INSERT INTO user_permissions VALUES ('50', '6', '14', NULL, NULL);
INSERT INTO user_permissions VALUES ('51', '6', '18', NULL, NULL);
INSERT INTO user_permissions VALUES ('52', '6', '19', NULL, NULL);
INSERT INTO user_permissions VALUES ('53', '6', '20', NULL, NULL);
INSERT INTO user_permissions VALUES ('54', '6', '21', NULL, NULL);

-- Data for table: lead_details
INSERT INTO lead_details VALUES ('22', '9', '2025-10-05 00:00:00', 'esfsfsd', '2025-10-16 00:00:00', '2', '2025-10-05 05:52:37', '2025-10-05 05:52:37', NULL, NULL);
INSERT INTO lead_details VALUES ('24', '13', '2025-10-01 00:00:00', 'fdgdgdsfsf', '2025-10-08 00:00:00', '2', '2025-10-05 05:53:42', '2025-10-05 07:00:03', '2025-10-05 07:00:03', NULL);
INSERT INTO lead_details VALUES ('27', '13', '2025-10-05 00:00:00', 'sdfdx', '2025-10-31 00:00:00', '2', '2025-10-05 06:31:20', '2025-10-05 06:31:20', NULL, NULL);
INSERT INTO lead_details VALUES ('30', '13', '2025-10-05 00:00:00', '223', '2025-10-11 00:00:00', '2', '2025-10-05 06:50:51', '2025-10-05 06:50:51', NULL, NULL);
INSERT INTO lead_details VALUES ('38', '16', '2025-10-05 00:00:00', 'ddd', NULL, '2', '2025-10-05 07:17:43', '2025-10-05 07:17:43', '2025-10-05 07:17:43', NULL);
INSERT INTO lead_details VALUES ('39', '16', '2025-10-16 00:00:00', 'sws', '2025-10-24 00:00:00', '2', '2025-10-05 07:18:01', '2025-10-05 07:18:01', '2025-10-05 07:18:01', NULL);
INSERT INTO lead_details VALUES ('40', '16', '2025-07-01 00:00:00', 'dfsdf', '2025-10-01 00:00:00', '2', '2025-10-05 07:18:19', '2025-10-05 07:18:19', '2025-10-05 07:18:19', NULL);
INSERT INTO lead_details VALUES ('41', '17', '2025-10-17 00:00:00', 'Previous call completed. Follow-up needed today.dgdgf', '2025-11-28 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 09:28:37', '2025-10-05 07:21:12', NULL);
INSERT INTO lead_details VALUES ('42', '17', '2025-10-02 00:00:00', 'Overdue call that needs to be made.', '2025-10-04 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL, NULL);
INSERT INTO lead_details VALUES ('43', '17', '2025-10-05 00:00:00', 'Call scheduled for next week.', '2025-10-12 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL, NULL);
INSERT INTO lead_details VALUES ('44', '17', '2025-10-03 00:00:00', 'This call was already completed.', '2025-10-08 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL);
INSERT INTO lead_details VALUES ('45', '18', '2025-10-04 00:00:00', 'Previous call completed. Follow-up needed today.', '2025-10-05 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL, NULL);
INSERT INTO lead_details VALUES ('46', '18', '2025-10-02 00:00:00', 'Overdue call that needs to be made.', '2025-10-04 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL, NULL);
INSERT INTO lead_details VALUES ('47', '18', '2025-10-05 00:00:00', 'Call scheduled for next week.', '2025-10-12 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL, NULL);
INSERT INTO lead_details VALUES ('48', '18', '2025-10-03 00:00:00', 'This call was already completed.', '2025-10-08 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL);
INSERT INTO lead_details VALUES ('49', '19', '2025-10-04 00:00:00', 'Previous call completed. Follow-up needed today.', '2025-11-07 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:21:21', '2025-10-05 07:21:21', NULL);
INSERT INTO lead_details VALUES ('50', '19', '2025-10-02 00:00:00', 'Overdue call that needs to be made.', '2025-10-29 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:21:33', '2025-10-05 07:21:33', NULL);
INSERT INTO lead_details VALUES ('51', '19', '2025-10-05 00:00:00', 'Call scheduled for next week.', '2025-10-12 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL, NULL);
INSERT INTO lead_details VALUES ('52', '19', '2025-10-03 00:00:00', 'This call was already completed.', '2025-10-08 00:00:00', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL);
INSERT INTO lead_details VALUES ('53', '10', '2025-10-05 00:00:00', 'dgdg', '2025-10-30 00:00:00', '2', '2025-10-05 09:02:30', '2025-10-05 09:02:30', '2025-10-05 09:02:30', NULL);

-- Data for table: leads
INSERT INTO leads VALUES ('9', 'MOHAMMADMUNAYAM SOWDAGOR', 'NIBIZ SOFT', '01968422925', 'rsmme33dia66@gmail.com', '2', '5', '2', '2', '2025-10-05 05:34:33', '2025-10-05 05:48:30', NULL);
INSERT INTO leads VALUES ('10', 'Clinton Willis', 'Murphy Sweet LLC', '+1 (473) 773-5675', 'xarelypi@mailinator.com', '3', '1', '2', '2', '2025-10-05 05:49:02', '2025-10-05 05:49:02', NULL);
INSERT INTO leads VALUES ('11', 'Jorden Delgado', 'Wiley Mcfarland Inc', '+1 (178) 489-5658', 'momy@mailinator.com', '3', '1', '2', '2', '2025-10-05 05:49:05', '2025-10-05 05:49:05', NULL);
INSERT INTO leads VALUES ('12', 'Fleur Harding', 'Parks Briggs Plc', '+1 (235) 103-1457', 'lilovycem@mailinator.com', '1', '5', '5', '2', '2025-10-05 05:49:09', '2025-10-05 05:49:09', NULL);
INSERT INTO leads VALUES ('13', 'Robin Lloyd', 'Patterson and Justice Trading', '+1 (557) 782-2095', 'wazufe@mailinator.com', '2', '4', '2', '2', '2025-10-05 05:49:13', '2025-10-05 05:49:13', NULL);
INSERT INTO leads VALUES ('14', 'Ashely Coleman', 'Waters and Carter Traders', '+1 (284) 951-5088', 'wocute@mailinator.com', '3', '6', '6', '2', '2025-10-05 05:49:17', '2025-10-05 05:49:17', NULL);
INSERT INTO leads VALUES ('15', 'Dahlia Taylor', 'Howard Curry Traders', '+1 (546) 698-5762', 'nimi@mailinator.com', '1', '2', '4', '2', '2025-10-05 05:49:34', '2025-10-05 05:49:34', NULL);
INSERT INTO leads VALUES ('16', 'Wayne Goff', 'Lucas and Rowland Co', '+1 (671) 626-6635', 'saryqad@mailinator.com', '1', '6', '2', '2', '2025-10-05 05:49:48', '2025-10-05 05:49:48', NULL);
INSERT INTO leads VALUES ('17', 'Test Lead 1', 'Test Company 1', '+1234567890', 'test1@example.com', '1', '1', '2', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL);
INSERT INTO leads VALUES ('18', 'Test Lead 2', 'Test Company 2', '+1234567891', 'test2@example.com', '1', '1', '2', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL);
INSERT INTO leads VALUES ('19', 'Test Lead 3', 'Test Company 3', '+1234567892', 'test3@example.com', '1', '1', '2', '2', '2025-10-05 07:20:38', '2025-10-05 07:20:38', NULL);
INSERT INTO leads VALUES ('21', 'MOHAMMADMUNAYAM SOWDAGOR', 'NIBIZ SOFT', NULL, 'rsmmedwwia66@gmail.com', '1', '1', '2', '2', '2025-10-05 10:18:08', '2025-10-05 10:18:08', 'Dhaka Dhanmondi');
INSERT INTO leads VALUES ('22', 'MOHAMMADMUNAYAM SOWDAGOR', NULL, NULL, 'qqrswwmmedia66@gmail.com', '1', '4', '2', '2', '2025-10-05 10:18:50', '2025-10-05 10:18:50', 'Dhaka Dhanmondi');

