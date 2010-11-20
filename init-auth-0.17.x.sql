INSERT INTO `UserAccount` (`user_id`, `user_name`, `user_password_hash`, `user_realname`)
VALUES (1, 'admin', sha1('admin'), 'RackTables Administrator');
DELETE FROM Script;
INSERT INTO Script VALUES ('RackCode', '# Keep admin password immutable by means of special (and also immutable) RackCode.\ndeny {$op_updateUser} or {$op_saveRackCode}\nallow {$userid_1}\n');
