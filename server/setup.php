<?php
/** Copyright (c) 2012 Gregory Gauthier

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

require_once('common.inc');?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>SHC Manager setup</title>
</head>
<body>
<?php
$mysqli = connect_mysql();

echo "The database name is $mysql_dbname.";

$query = "CREATE DATABASE $mysql_dbname;";

$mysqli->query($query) or die('<html>Database already created.  If the
database does not work, please delete it manually and run this script again.</html>');

$query = "USE $mysql_dbname;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TABLE rounds (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30),
  sequence SMALLINT NOT NULL,
  is_regular BOOLEAN NOT NULL)
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TABLE toc_berths (
  id TINYINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  abbreviation VARCHAR(20),
  description TEXT)
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query);

$query = "CREATE TABLE days (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30),
  round_id SMALLINT,
  play_date DATE,
  sequence SMALLINT NOT NULL,
  thread_url VARCHAR(255),
  highest_parsed_post SMALLINT DEFAULT -1,
  FOREIGN KEY (round_id) REFERENCES rounds(id),
  INDEX round_id_idx (round_id))
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TABLE categories (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  explanatory_text TEXT,
  day_id SMALLINT,
  sequence SMALLINT NOT NULL,
  FOREIGN KEY (day_id) REFERENCES days(id),
  INDEX day_id_idx (day_id))
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TABLE clues (
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  clue_text TEXT,
  category_id SMALLINT,
  point_value SMALLINT,
  wrong_point_value SMALLINT,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  INDEX category_id_idx (category_id))
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TABLE responses (
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  clue_id MEDIUMINT,
  response_text TEXT,
  correct BOOLEAN,
  FOREIGN KEY (clue_id) REFERENCES clues(id),
  INDEX clue_id_idx (clue_id))
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TABLE players (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username CHAR(50),
  teen_eligible BOOLEAN,
  college_eligible BOOLEAN,
  atb_eligible BOOLEAN,
  rookie_eligible BOOLEAN,
  toc TINYINT,
  hashed_password CHAR(128),
  email VARCHAR(255),
  admin_privilege BOOLEAN DEFAULT FALSE,
  grade_privilege BOOLEAN DEFAULT FALSE,
  can_play BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (toc) REFERENCES toc_berths(id),
  INDEX username_idx (username))
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TABLE player_responses (
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  player_id SMALLINT,
  clue_id MEDIUMINT,
  response_text TEXT,
  grade BOOLEAN,
  FOREIGN KEY (player_id) REFERENCES players(id),
  FOREIGN KEY (clue_id) REFERENCES clues(id),
  INDEX player_id_idx (player_id),
  INDEX clue_id_idx (clue_id),
  UNIQUE player_and_clue_idx (player_id, clue_id))
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE FUNCTION grade_response(clue_id MEDIUMINT, ungraded_response_text TEXT)
RETURNS BOOLEAN
LANGUAGE SQL
COMMENT 'Grade the response against clue_id'
BEGIN
    DECLARE candidate_response TEXT;
    DECLARE done BOOLEAN;
    DECLARE grade BOOLEAN;
    DECLARE matches_incorrect BOOLEAN;
    DECLARE response_cur CURSOR FOR
        SELECT response_text, correct FROM responses AS r
        WHERE r.clue_id=clue_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    IF ungraded_response_text IS NULL THEN
        RETURN NULL;
    END IF;
    
    IF ungraded_response_text='' THEN
        RETURN NULL;
    END IF;
    
    SET done = FALSE;
    SET matches_incorrect = FALSE;
    OPEN response_cur;
    
    grade_loop: LOOP
        FETCH response_cur INTO candidate_response, grade;
        IF done THEN
            LEAVE grade_loop;
        END IF;
        IF ungraded_response_text REGEXP CONCAT('^(',candidate_response,')$') THEN
            IF grade THEN
                CLOSE response_cur;
                RETURN TRUE;
            ELSE SET matches_incorrect = TRUE;
            END IF;
        END IF;
    END LOOP;
    
    CLOSE response_cur;
    IF matches_incorrect THEN
        RETURN FALSE;
    ELSE
        RETURN NULL;
    END IF;
END;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE PROCEDURE grade_all_responses(IN clue_id MEDIUMINT)
BEGIN
    UPDATE player_responses
        SET grade=grade_response(player_responses.clue_id, response_text)
    WHERE player_responses.clue_id = clue_id;
END;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TRIGGER bi_grade_response BEFORE INSERT ON player_responses
FOR EACH ROW SET NEW.grade = grade_response(NEW.clue_id, NEW.response_text);";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TRIGGER bu_grade_response BEFORE UPDATE ON player_responses
FOR EACH ROW SET NEW.grade = grade_response(NEW.clue_id, NEW.response_text);";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TRIGGER ai_grade_all_responses AFTER INSERT ON responses
FOR EACH ROW CALL grade_all_responses(NEW.clue_id);";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TRIGGER au_grade_all_responses AFTER UPDATE ON responses
FOR EACH ROW
BEGIN
    CALL grade_all_responses(OLD.clue_id);
    IF NEW.clue_id != OLD.clue_id THEN
        CALL grade_all_responses(NEW.clue_id);
    END IF;
END;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE TRIGGER ad_grade_all_responses AFTER DELETE ON responses
FOR EACH ROW CALL grade_all_responses(OLD.clue_id);";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE VIEW scores AS SELECT player_id, clue_id,
  categories.id AS category_id, days.id AS day_id, rounds.id AS round_id,
  IF(grade IS NULL, 0, IF(grade=1, point_value, wrong_point_value)) AS score,
  IF(grade IS NULL AND response_text IS NOT NULL AND response_text != '', 1, 0)
  AS ungraded FROM player_responses
  INNER JOIN clues ON clue_id = clues.id
  LEFT JOIN categories ON clues.category_id = categories.id
  LEFT JOIN days ON categories.day_id = days.id LEFT JOIN
  rounds ON days.round_id = rounds.id;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE VIEW category_scores AS
  SELECT player_id, category_id, day_id, round_id, SUM(score) AS category_score,
  SUM(ungraded) AS category_ungraded
  FROM scores GROUP BY player_id, category_id;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE VIEW daily_scores AS
  SELECT player_id, day_id, round_id, SUM(score) AS daily_score,
  SUM(ungraded) AS daily_ungraded,
  1 AS days_played FROM scores
  GROUP BY player_id, day_id;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE VIEW round_scores AS
  SELECT player_id, round_id, SUM(score) AS round_score,
  SUM(ungraded) AS round_ungraded,
  COUNT(DISTINCT day_id) AS days_played FROM scores
  GROUP BY player_id, round_id;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));

$query = "CREATE VIEW overall_scores AS
  SELECT player_id, SUM(score) AS overall_score,
  SUM(ungraded) AS overall_ungraded,
  COUNT(DISTINCT day_id) AS days_played FROM scores
  INNER JOIN rounds ON scores.round_id = rounds.id
  WHERE rounds.is_regular = 1
  GROUP BY player_id;";

$mysqli->query($query) or die(sprintf("Error executing query $query: %s", $mysqli->error));



echo("<!DOCTYPE html>
<HTML><HEAD></HEAD><BODY><h1>Database successfully created!
</h1></BODY></HTML>");

$mysqli->close();
echo copyright();
?>
</body>
