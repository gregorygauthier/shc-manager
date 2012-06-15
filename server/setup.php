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
</head>
<body>
<?php
$mysqli = connect_mysql();

$query = "CREATE DATABASE $mysql_dbname;";

$mysqli->query($query) or die('<html>Database already created.  If the
database does not work, please delete it manually and run this script again.</html>');

$query = "USE $mysql_dbname;";

$mysqli->query($query);

$query = "CREATE TABLE rounds (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30),
  sequence SMALLINT NOT NULL);";

$mysqli->query($query);

$query = "CREATE TABLE days (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30),
  round_id SMALLINT,
  sequence SMALLINT NOT NULL,
  FOREIGN KEY (round_id) REFERENCES rounds(id));";

$mysqli->query($query);

$query = "CREATE TABLE categories (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  explanatory_text TEXT,
  day_id SMALLINT,
  sequence SMALLINT NOT NULL,
  FOREIGN KEY (day_id) REFERENCES days(id));";

$mysqli->query($query);

$query = "CREATE TABLE clues (
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  clue_text TEXT,
  category_id SMALLINT,
  point_value SMALLINT,
  wrong_point_value SMALLINT,
  FOREIGN KEY (category_id) REFERENCES categories(id));";

$mysqli->query($query);

$query = "CREATE TABLE responses (
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  clue_id MEDIUMINT,
  response_text TEXT,
  correct BOOLEAN,
  FOREIGN KEY (clue_id) REFERENCES clues(id));";

$mysqli->query($query);

$query = "CREATE TABLE players (
  id SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username CHAR(50),
  teen_eligible BOOLEAN,
  college_eligible BOOLEAN,
  atb_eligible BOOLEAN);";

$mysqli->query($query);

$query = "CREATE TABLE player_responses (
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  player_id SMALLINT,
  clue_id MEDIUMINT,
  response_text TEXT,
  FOREIGN KEY (player_id) REFERENCES players(id),
  FOREIGN KEY (clue_id) REFERENCES clues(id));";

$mysqli->query($query);

echo("<!DOCTYPE html>
<HTML><HEAD></HEAD><BODY><h1>Database successfully created!
</h1></BODY></HTML>");

$mysqli->close();
echo copyright();
?>
</body>
