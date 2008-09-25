--
-- Table structure for table 'Classes'
--
CREATE TABLE Classes (
  ID int(10) unsigned NOT NULL auto_increment,
  FileID int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  Description text,
  Abstract tinyint(3) unsigned NOT NULL COMMENT '1 for true, 0 for false',
  Extends varchar(255) default NULL COMMENT 'Name of the extended class',
  Visibility varchar(20) NOT NULL,
  PRIMARY KEY  (ID)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Table structure for table 'Files'
--
CREATE TABLE Files (
  ID int(11) unsigned NOT NULL auto_increment,
  `Name` varchar(255) collate utf8_unicode_ci NOT NULL,
  Description text collate utf8_unicode_ci,
  Source text collate utf8_unicode_ci NOT NULL,
  PackageID int(10) unsigned default NULL,
  PRIMARY KEY  (ID),
  UNIQUE KEY Filename (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


--
-- Table structure for table 'Functions'
--
CREATE TABLE Functions (
  ID int(10) unsigned NOT NULL auto_increment,
  FileID int(10) unsigned NOT NULL,
  ClassID int(10) unsigned default NULL,
  InterfaceID int(10) unsigned default NULL,
  `Name` varchar(255) NOT NULL,
  Description text,
  Parameters varchar(255) NOT NULL,
  Visibility varchar(20) default NULL,
  ReturnType varchar(255) default NULL,
  ReturnDescription text,
  PRIMARY KEY  (ID),
  KEY `Name` (`Name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Table structure for table 'Interfaces'
--
CREATE TABLE Interfaces (
  ID int(10) unsigned NOT NULL auto_increment,
  FileID int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  Description text,
  Extends varchar(255) default NULL COMMENT 'Name of the extended class',
  Visibility varchar(20) NOT NULL,
  PRIMARY KEY  (ID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Table structure for table 'Packages'
--
CREATE TABLE Packages (
  ID int(10) unsigned NOT NULL auto_increment,
  `Name` varchar(50) NOT NULL,
  PRIMARY KEY  (ID)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Table structure for table 'Parameters'
--
CREATE TABLE Parameters (
  ID int(10) unsigned NOT NULL auto_increment,
  FunctionID int(10) unsigned NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Type` varchar(255) default NULL,
  Description text,
  PRIMARY KEY  (ID)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Table structure for table 'Variables'
--
CREATE TABLE `Variables` (
  ID int(10) unsigned NOT NULL auto_increment,
  ClassID int(10) unsigned default NULL,
  InterfaceID int(10) unsigned default NULL,
  `Name` varchar(255) NOT NULL,
  `Type` varchar(255) default NULL,
  Description text,
  PRIMARY KEY  (ID)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



--
-- Table structure for table 'Projects'
--
CREATE TABLE Projects (
  ID int(10) unsigned NOT NULL auto_increment,
  Name varchar (255) NOT NULL,
  PRIMARY KEY  (ID)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


