DROP TABLE IF EXISTS ezxgis_position;
CREATE TABLE  `ezxgis_position` (
  `contentobject_attribute_id` int(11) NOT NULL default '0',
  `contentobject_attribute_version` int(11) NOT NULL default '0',
  `latitude` float NOT NULL default '0',
  `longitude` float NOT NULL default '0',
  `street` varchar(255) default NULL,
  `zip` varchar(20) default NULL,
  `district` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `state` varchar(255) default NULL,
  `country` varchar(255) default NULL,
   PRIMARY KEY (contentobject_attribute_id,contentobject_attribute_version)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ezxgis_position` 
ADD INDEX `country` (`country` ASC),
ADD INDEX `state` (`state` ASC),
ADD INDEX `city` (`city` ASC),
ADD INDEX `zip` (`zip` ASC),
ADD INDEX `district` (`district` ASC);
