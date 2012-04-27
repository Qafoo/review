-- Schema naming rules:
-- 
-- * Table names
--   * Always lowecase
--   * Describing word in singular
--   * Each table has a unique abreviation
--   - Example: "user" / "u"
-- 
-- * Columns
--   * All column names start with the table abbreviation and an underscore
--     * Foreign keys are the obvious exception from this rule
--     * Foreign key names are used literally from the related table
--   * The surrogate (syntethic) key always has the name "id" (with prefix)
--   - Example: "u_id", "u_name"
-- 
-- * Simple Relations
--   * Trivial relations are just implied by column names, as specified under "Columns".
--   * Relation tables a named "<abbreviation>_<abbreviation>_rel"
--     * Primary key is the combined foreign key
-- 
-- * Relations with attributes
--   * The relation gets a descriptive name (see rules for tables)
--     * The foreign keys are a unique constraint
--   
-- * Change tracking
--   * Each table *always* has a "changed" column of type "timestamp", which is
--     updated on each change. The column is always the right-most.
-- 
-- According to: http://blog.koehntopp.de/archives/3076-Namensregeln-fuer-Schemadesign.html

-- We recreate the DB entirely -- so that we do not care about violated constraints
SET foreign_key_checks = 0;

-- Table: User (u)
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `u_id` INT AUTO_INCREMENT NOT NULL,
  `u_name` VARCHAR(64) NOT NULL,
  `u_password` VARCHAR(64) NOT NULL,
  `u_password_salt` VARCHAR(64) NOT NULL,
  `u_password_method` VARCHAR(8) NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`u_id`),
  UNIQUE(`u_name`),
  KEY(`u_name`, `u_password`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Account (a)
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `a_id` INT AUTO_INCREMENT NOT NULL,
  `u_id` INT NOT NULL,
  `a_name` VARCHAR(32) NOT NULL,
  `a_type` VARCHAR(32) NOT NULL,
  `a_data` BLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`a_id`),
  FOREIGN KEY (`u_id`) REFERENCES `user`(`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Column (c)
DROP TABLE IF EXISTS `column`;
CREATE TABLE `column` (
  `c_id` INT AUTO_INCREMENT NOT NULL,
  `u_id` INT NOT NULL,
  `c_priority` INT NOT NULL,
  `c_configuration` BLOB NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`c_id`),
  FOREIGN KEY (`u_id`) REFERENCES `user`(`u_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

-- Table: Last Read (l)
DROP TABLE IF EXISTS `last_read`;
CREATE TABLE `last_read` (
  `c_id` INT NOT NULL,
  `l_last` BIGINT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`c_id`),
  FOREIGN KEY (`c_id`) REFERENCES `column`(`c_id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

