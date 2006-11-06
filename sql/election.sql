-- election voting sql

-- COLUMN          REFERENCES
--
-- election_id     elections(id)
-- vote            election_choices(choice)

CREATE TABLE election_votes_single (
  election_id INT NOT NULL,
  vote TINYINT NOT NULL,
  vote_hash CHAR(32) NOT NULL,
  PRIMARY KEY (election_id, vote_hash)
);

CREATE TABLE election_votes_multiple (
  election_id INT NOT NULL,
  vote TINYINT NOT NULL,
  vote_hash CHAR(32) NOT NULL,
  PRIMARY KEY (election_id, vote, vote_hash)
);

CREATE TABLE election_votes_abstain (
  election_id INT NOT NULL ,
  vote_hash CHAR(32) NOT NULL ,
  PRIMARY KEY (election_id, vote_hash)
);

CREATE TABLE elections (
  id INT NOT NULL AUTO_INCREMENT,
  purpose VARCHAR(100) NOT NULL,
  detail TEXT NOT NULL,
  votestart DATE NOT NULL,
  voteend DATE NOT NULL,
  creator VARCHAR(20) NOT NULL,
  createdate DATETIME NOT NULL,
  minimum_choices TINYINT DEFAULT '1' NOT NULL,
  maximum_choicse TINYINT DEFAULT '1' NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE election_choices (
  election_id INT NOT NULL,
  choice TINYINT NOT NULL,
  summary VARCHAR(100) NOT NULL,
  summary_link VARCHAR(255) NOT NULL,
  PRIMARY KEY (election_id, choice)
);

CREATE TABLE election_handle_votes (
  election_id INT NOT NULL,
  handle VARCHAR(20) NOT NULL,
  PRIMARY KEY (election_id, handle)
);

CREATE TABLE election_results (
  election_id INT NOT NULL,
  choice TINYINT NOT NULL,
  votepercent FLOAT NOT NULL,
  PRIMARY KEY (election_id, choice)
);