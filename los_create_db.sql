SET FOREIGN_KEY_CHECKS=0;
TRUNCATE Articles;
TRUNCATE Tags;
TRUNCATE Reviews;
TRUNCATE Articles_Tags;
TRUNCATE Articles_Themes;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Images;
TRUNCATE TABLE Images_Tags;
TRUNCATE TABLE Images_Themes;
TRUNCATE TABLE Image_Reviews;

-- indicating the engine here means you don't have to do so after each table
-- but dreamhost sets db's up automatically so you have to indicate the engine after
-- each table

-- CREATE DATABASE los DEFAULT CHARSET UTF8 ENGINE = INNODB;
-- USE los_data;

-- GRANT ALL ON los_data.* TO 'lummis'@'localhost' IDENTIFIED BY 'pQaD9oF';

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

-- just for shits, for testing table with test values

INSERT INTO Reviewers (`initials`, `first_name`, `last_name`, `username`, `password`) VALUES
('JT', 'John', 'Toe', 'jtoe', 'what'),
('JH', 'Joshua', 'Hubbard', 'jhub', 'what'),
('AW', 'Ari', 'Weinberg', 'awein', 'what'),
('RM', 'Rachel', 'Miller', 'rmill', 'what'),
('MB', 'Michael', 'Barera', 'mbar', 'what'),
('VMR', 'Valentina', 'Montero-Roe', 'vroe', 'what'),
('SH', 'Steven', 'Hoelscher', 'shoe', 'what'),
('jj', 'Justin', 'Joque', 'jjoe', 'what'),
('rec','rec' , 'rec', 'rec','rec');

UPDATE Reviewers SET username = , password = 'lummisexpandsthesoul' WHERE reviewer_id = 2;

-- INSERT INTO Articles (title, author, location, page_start, page_end, volume, issue, date_published, type, reconciled) VALUES
-- ('what', 'waht', 'what', '1', '2', '1','2','2000-10-10','poetry','0'),
-- ('two', 'two', 'two', '1', '3', '4','5','2000-10-10','poetry','0');

-- INSERT INTO Themes (theme,if_secondary) VALUES
-- ('chunk','0'),
-- ('greasy','1');

-- INSERT INTO Articles_Themes (article_id, reviewer_id, theme_id, if_main) VALUES
-- ('1', '1', '1', '0'),
-- ('2', '2', '2', '0');

-- INSERT INTO Tags (tag, category) VALUES
-- ('activities','activities'),
-- ('groups','groups'),
-- ('works','works'),
-- ('entities','entities'),
-- ('events','events');

-- INSERT INTO Articles_Tags (article_id, tag_id, reviewer_id, if_main) VALUES
-- ('1','1','1','0'),
-- ('1','2','2','0'),
-- ('1','3','1','0'),
-- ('1','3','2','0'),
-- ('2','1','1','0'),
-- ('2','2','2','0'),
-- ('2','3','	1','0'),
-- ('2','4','2','0');

-- INSERT INTO Reviews (article_id,reviewer_id,summary,notes) VALUES
-- ('1','1','summary article 1 reviewer 1', 'notes one article 1 reviewer 1'),
-- ('1','2','summary article 1 reviewer 2', 'notes article 1 reviewer 2'),
-- ('2','1','summary article 2 reviewer 1', 'notes one article 2 reviewer 1'),
-- ('2','2','summary article 2 reviewer 2', 'notes article 2 reviewer 2');