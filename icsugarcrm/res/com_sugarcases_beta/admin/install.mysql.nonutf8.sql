CREATE TABLE IF NOT EXISTS `#__sugar_portal_configuration` (
  `id` int(11) NOT NULL auto_increment,
  `component` varchar(254)  NOT NULL default 'GLOBAL',
  `name` varchar(50)  NOT NULL default '',
  `value` text ,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) AUTO_INCREMENT=10 ;
            
CREATE TABLE IF NOT EXISTS `#__sugar_case_portal_fields` (
  `id` int(11) NOT NULL auto_increment,
  `field` varchar(255) NOT NULL default '',
  `name` varchar(255) default NULL,
  `type` varchar(255) NOT NULL default 'text',
  `show` tinyint(1) NOT NULL default '0',
  `inlist` tinyint(4) NOT NULL default '0',
  `size` varchar(10) default '10',
  `canedit` tinyint(1) default '0',
  `default` varchar(255) default NULL,
  `searchable` tinyint(4) NOT NULL default '0',
  `advanced` tinyint(4) NOT NULL default '0',
  `ordering` int(11) NOT NULL,
  `parameters` text,
  PRIMARY KEY  (`id`)
) AUTO_INCREMENT=70 ;
            
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (70, 'id', 'Id', 'id', 0, 0, '0', 0, '', 0, 0, 1, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (71, 'case_number', 'Number', 'int', 1, 1, '0', 0, '', 1, 1, 2, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (72, 'date_entered', 'Date Entered', 'datetime', 1, 0, '0', 0, '', 0, 0, 3, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (73, 'date_modified', 'Date Modified', 'datetime', 1, 1, '0', 0, '', 0, 0, 4, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (74, 'name', 'Subject', 'varchar', 1, 1, '40', 1, '', 1, 1, 5, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (75, 'account_name', 'Account Name', 'relate', 1, 0, '0', 0, '', 0, 0, 6, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (76, 'account_id', 'Account Id', 'id', 0, 0, '0', 0, '', 0, 0, 7, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (77, 'status', 'Status', 'enum', 1, 1, '0', 0, 'New', 1, 1, 8, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (78, 'priority', 'Priority', 'enum', 1, 1, '0', 0, 'High', 1, 1, 9, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (79, 'description', 'Description', 'text', 1, 0, '40', 1, '', 0, 1, 10, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (80, 'resolution', 'Resolution', 'text', 1, 1, '40', 0, '', 0, 1, 11, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (81, 'assigned_user_name', 'Assigned To', 'assigned_user_name', 1, 1, '0', 0, '', 0, 0, 12, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (82, 'assigned_user_id', 'Assigned User Id', 'assigned_user_name', 0, 0, '0', 0, '', 0, 0, 13, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (83, 'modified_by_name', 'Modified By Name', 'assigned_user_name', 1, 0, '0', 0, '', 0, 0, 14, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (84, 'modified_user_id', 'Modified User Id', 'assigned_user_name', 0, 0, '0', 0, '', 0, 0, 15, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (85, 'created_by_name', 'Created By Name', 'assigned_user_name', 1, 0, '0', 0, '', 0, 0, 16, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (86, 'created_by', 'Created By', 'assigned_user_name', 0, 0, '0', 0, '', 0, 0, 17, NULL);
INSERT IGNORE INTO `#__sugar_case_portal_fields` VALUES (87, 'deleted', 'Deleted', 'bool', 0, 0, '0', 0, '', 0, 0, 18, NULL);
