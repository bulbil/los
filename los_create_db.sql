CREATE DATABASE los DEFAULT CHARSET UTF8;

USE los;

GRANT ALL ON los.* TO 'lummis'@'localhost' IDENTIFIED BY 'pQaD9oF';

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
);

CREATE TABLE Reviewers (

	reviewer_id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
	initials CHAR(3) NOT NULL,
	first_name VARCHAR(20) NOT NULL,
	last_name VARCHAR(20) NOT NULL,
	username VARCHAR(20) NOT NULL,
	password VARCHAR(20) NOT NULL,

	PRIMARY KEY(reviewer_id)
);

CREATE TABLE Article_Reviewers (

	article_id SMALLINT UNSIGNED NOT NULL,
	reviewer_id TINYINT UNSIGNED NOT NULL,

	PRIMARY KEY(article_id, reviewer_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id),
	FOREIGN KEY(reviewer_id) REFERENCES Reviewers(reviewer_id)	
);

CREATE TABLE Themes (

	theme_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	theme VARCHAR(128) NOT NULL,

	PRIMARY KEY(theme_id)
);

CREATE TABLE Article_Themes (

	article_id SMALLINT UNSIGNED NOT NULL,
	theme_id SMALLINT UNSIGNED NOT NULL,
	reviewer_id TINYINT UNSIGNED NOT NULL,
	if_main BOOLEAN NOT NULL,

	PRIMARY KEY(article_id, theme_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id),
	FOREIGN KEY(theme_id) REFERENCES Themes(theme_id)	
);

CREATE TABLE Tags (

	tag_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	category ENUM(
		'groups',
		'persons',
		'entities',
		'places',
		'activities',
		'flora_fauna',
		'commodities',
		'events',
		'works',
		'technologies',
		'environments'),
	tag VARCHAR(128),

	PRIMARY KEY(tag_id)
);

CREATE TABLE Article_Tags (

	article_id SMALLINT UNSIGNED NOT NULL,
	tag_id SMALLINT UNSIGNED NOT NULL,
	reviewer_id TINYINT UNSIGNED NOT NULL,
	if_main BOOLEAN NOT NULL,

	PRIMARY KEY(article_id, tag_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id),
	FOREIGN KEY(tag_id) REFERENCES Tags(tag_id)		
);

CREATE TABLE Reviews (

	review_id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	article_id SMALLINT UNSIGNED NOT NULL,
	reviewer_id TINYINT UNSIGNED NOT NULL,
	timestamp DATETIME NOT NULL ,
	main VARCHAR(255) NOT NULL,
	summary MEDIUMTEXT NOT NULL,
	notes MEDIUMTEXT NOT NULL,
	research_notes MEDIUMTEXT NOT NULL,
	narration_pov VARCHAR(255) NOT NULL,
	narration_embedded BOOLEAN NOT NULL,
	narration_tense VARCHAR(128) NOT NULL,
	narration_tenseshift BOOLEAN NOT NULL,

	PRIMARY KEY(review_id),
	FOREIGN KEY(article_id) REFERENCES Articles(article_id),
	FOREIGN KEY(reviewer_id) REFERENCES Reviewers(reviewer_id)
);