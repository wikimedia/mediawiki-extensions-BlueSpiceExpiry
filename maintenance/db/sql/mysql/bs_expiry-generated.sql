-- This file is automatically generated using maintenance/generateSchemaSql.php.
-- Source: extensions/BlueSpiceExpiry/maintenance/db/sql/bs_expiry.json
-- Do not modify this file directly.
-- See https://www.mediawiki.org/wiki/Manual:Schema_changes
CREATE TABLE /*_*/bs_expiry (
  exp_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
  exp_page_id INT NOT NULL,
  exp_date DATETIME NOT NULL,
  exp_comment VARBINARY(255) NOT NULL,
  INDEX exp_page_id_idx (exp_page_id),
  INDEX exp_date_idx (exp_date),
  PRIMARY KEY(exp_id)
) /*$wgDBTableOptions*/;