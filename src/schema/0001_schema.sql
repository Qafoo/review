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

-- Table: Annotation (a)
DROP TABLE IF EXISTS `annotation`;
CREATE TABLE `annotation` (
  `a_id` INT AUTO_INCREMENT NOT NULL,
  `a_file` VARCHAR(255) NOT NULL,
  `a_line` INT NOT NULL,
  `a_character` INT NOT NULL,
  `a_type` VARCHAR(16) NOT NULL,
  `a_class` VARCHAR(16) NOT NULL,
  `a_message` TEXT NOT NULL,
  `changed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`a_id`),
  KEY(`a_file`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

