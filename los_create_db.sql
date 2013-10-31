
-- just a reminder of how to truncate when using foreign keys
-- SET FOREIGN_KEY_CHECKS=0;
-- TRUNCATE Articles;
-- TRUNCATE Tags;
-- TRUNCATE Reviews;
-- TRUNCATE Articles_Tags;
-- TRUNCATE Articles_Themes;

-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE TABLE Images;
-- TRUNCATE TABLE Images_Tags;
-- TRUNCATE TABLE Images_Themes;
-- TRUNCATE TABLE Image_Reviews;

-- indicating the engine here means you don't have to do so after each table
-- but dreamhost sets db's up automatically so you have to indicate the engine after
-- each table

-- CREATE DATABASE los DEFAULT CHARSET UTF8 ENGINE = INNODB;
-- USE los_data;

CREATE TABLE Articles (

	article_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL,
	author VARCHAR(128) NOT NULL,
	location VARCHAR(128) NOT NULL,
	page_start SMALLINT UNSIGNED NOT NULL,
	page_end SMALLINT UNSIGNED NOT NULL,
	volume TINYINT UNSIGNED NOT NULL,
	issue TINYINT UNSIGNED NOT NULL,
	date_published DATE NOT NULL,
	type VARCHAR(64) NOT NULL,
	reconciled BOOLEAN NOT NULL,

	PRIMARY KEY(article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Images (

	img_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	article_id SMALLINT UNSIGNED NULL,
	img_caption VARCHAR(255) NOT NULL,
	img_volume TINYINT UNSIGNED NOT NULL,
	img_issue TINYINT UNSIGNED NOT NULL,
	img_page SMALLINT UNSIGNED NOT NULL,
	img_creator VARCHAR(128) NOT NULL,
	img_engraver VARCHAR(128) NOT NULL,
	img_date DATE NOT NULL,
	img_type VARCHAR(64) NOT NULL,
	img_rotated BOOLEAN NOT NULL,
	img_placement VARCHAR(16) NOT NULL,


	PRIMARY KEY(img_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE Reviewers (

	reviewer_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
	initials CHAR(3) NOT NULL,
	first_name VARCHAR(20) NOT NULL,
	last_name VARCHAR(20) NOT NULL,
	username VARCHAR(20) NOT NULL,
	password VARCHAR(20) NOT NULL,

	PRIMARY KEY(reviewer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Themes (

	theme_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	theme VARCHAR(128) NOT NULL,
	if_secondary BOOLEAN NOT NULL,

	PRIMARY KEY(theme_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Articles_Themes (

	article_id SMALLINT UNSIGNED,
	theme_id SMALLINT UNSIGNED,
	reviewer_id TINYINT UNSIGNED,
	if_main BOOLEAN NOT NULL,

	PRIMARY KEY(article_id, theme_id, reviewer_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	FOREIGN KEY(theme_id) REFERENCES Themes(theme_id)	
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Images_Themes (

	img_id SMALLINT UNSIGNED,
	theme_id SMALLINT UNSIGNED,
	reviewer_id TINYINT UNSIGNED,
	if_main BOOLEAN NOT NULL,

	PRIMARY KEY(img_id, theme_id, reviewer_id),
	FOREIGN KEY(img_id) REFERENCES Images(img_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	FOREIGN KEY(theme_id) REFERENCES Themes(theme_id)	
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Tags (

	tag_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	category ENUM(
		'activities',
		'commodities',
		'entities',
		'environments',
		'events',
		'florafauna',
		'groups',
		'persons',
		'places',
		'technologies',
		'works'),
	tag VARCHAR(128),

	PRIMARY KEY(tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Articles_Tags (

	article_id SMALLINT UNSIGNED,
	tag_id SMALLINT UNSIGNED,
	reviewer_id TINYINT UNSIGNED,
	if_main BOOLEAN NOT NULL,

	PRIMARY KEY(reviewer_id, article_id, tag_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	FOREIGN KEY(tag_id) REFERENCES Tags(tag_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Images_Tags (

	img_id SMALLINT UNSIGNED,
	tag_id SMALLINT UNSIGNED,
	reviewer_id TINYINT UNSIGNED,
	if_main BOOLEAN NOT NULL,

	PRIMARY KEY(reviewer_id, img_id, tag_id),
	FOREIGN KEY(img_id) REFERENCES Images(img_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	FOREIGN KEY(tag_id) REFERENCES Tags(tag_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Reviews (

	review_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	article_id SMALLINT UNSIGNED,
	reviewer_id TINYINT UNSIGNED,
	timestamp DATETIME NOT NULL ,
	summary MEDIUMTEXT NOT NULL,
	notes MEDIUMTEXT NOT NULL,
	research_notes MEDIUMTEXT NOT NULL,
	narration_pov VARCHAR(255) NOT NULL,
	narration_embedded BOOLEAN NOT NULL,
	narration_tense VARCHAR(128) NOT NULL,
	narration_tenseshift BOOLEAN NOT NULL,

	PRIMARY KEY(review_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,	
	FOREIGN KEY(reviewer_id) REFERENCES Reviewers(reviewer_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE Image_Reviews (

	img_review_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	img_id SMALLINT UNSIGNED,
	reviewer_id TINYINT UNSIGNED,
	timestamp DATETIME NOT NULL ,
	img_description MEDIUMTEXT NOT NULL,
	img_notes MEDIUMTEXT NOT NULL,
	img_research_notes MEDIUMTEXT NOT NULL,

	PRIMARY KEY(img_review_id),
	FOREIGN KEY(reviewer_id) REFERENCES Reviewers(reviewer_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	FOREIGN KEY(img_id) REFERENCES Images(img_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;