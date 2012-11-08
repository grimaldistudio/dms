ALTER TABLE  `documents` ADD  `is_visible_to_all` TINYINT(1) NOT NULL DEFAULT b'0' AFTER  `publication_date_to` ,
ADD  `sync_file` TINYINT(1) NOT NULL DEFAULT b'1' AFTER  `is_visible_to_all` ,
ADD  `is_inbound` TINYINT(1) NOT NULL DEFAULT b'1' AFTER  `sync_file` ,
ADD	 `publication_number` VARCHAR(128) NULL AFTER  `is_inbound`;

ALTER TABLE  `documents` CHANGE  `main_document_type`  `main_document_type` TINYINT( 4 ) NOT NULL DEFAULT  '3';

INSERT INTO  `rights` (`id`, `key`, `name`) VALUES (NULL ,  'document/public',  'Documenti visibili a tutti');

INSERT IGNORE INTO `roles_rights` SELECT roles.id, rights.id FROM roles, rights WHERE rights.`key`='document/public'; 
INSERT IGNORE INTO `roles_rights` SELECT roles.id, rights.id FROM roles, rights WHERE rights.`key` = 'document/index';
