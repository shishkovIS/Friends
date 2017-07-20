CREATE TABLE `const_calls` (
  `call_id` int(11) NOT NULL AUTO_INCREMENT,
  `iConstituentID` int(11) DEFAULT NULL,
  `call_date` date DEFAULT NULL,
  `call_phone` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`call_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8