ALTER TABLE documents DROP COLUMN subject;
ALTER TABLE  `documents` CHANGE  `identifier`  `identifier` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE  `documents` CHANGE  `name`  `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE  `documents` CHANGE  `priority`  `priority` INT( 11 ) NULL DEFAULT  '1'


ALTER TABLE  `documents` ADD  `entity` VARCHAR( 128 ) NULL AFTER  `priority`;
ALTER TABLE  `documents` ADD  `proposer_service` VARCHAR( 128 ) NULL AFTER  `entity`;
ALTER TABLE  `documents` ADD  `act_number` VARCHAR( 128 ) NULL AFTER  `proposer_service`;
ALTER TABLE  `documents` ADD  `act_date` TIMESTAMP NULL AFTER  `act_number`;
ALTER TABLE  `documents` ADD  `publication_date_from` TIMESTAMP NULL AFTER  `act_date`;    
ALTER TABLE  `documents` ADD  `publication_date_to` TIMESTAMP NULL AFTER  `publication_date_from`;    
ALTER TABLE  `documents` ADD  `publication_status` TINYINT NOT NULL DEFAULT 0 AFTER  `status`;
ALTER TABLE  `documents` ADD  `main_document_type` TINYINT NOT NULL DEFAULT 0 AFTER  `document_type`;

ALTER TABLE  `documents` ADD  `publication_requested` TINYINT NOT NULL DEFAULT 0 AFTER  `publication_status`;
 