<?php
/** Copyright (c) 2013 Gregory Gauthier

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

require_once('common.inc');
$isloggedin = startpage(GRADE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Ungraded responses</title>
</head>
<body>
<h1>Ungraded responses</h1>
<?php
$mysqli = connect_mysql();


$mysqli->query("USE $mysql_dbname;");

if($mysqli->errno)
{
    die(printf("Database use failed: errno %d", $mysqli->errno));
}

$query = "CREATE TEMPORARY TABLE all_ungraded_scores
    (id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    clue_id MEDIUMINT,
    category_id SMALLINT,
    day_id SMALLINT,
    round_id SMALLINT,
    response_text TEXT,
    player_id SMALLINT,
    INDEX clue_id_idx (clue_id),
    INDEX category_id_idx (category_id),
    INDEX day_id_idx (day_id),
    INDEX round_id_idx (round_id),
    INDEX player_id_idx (player_id))";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->close();

$query = "INSERT INTO all_ungraded_scores
    (clue_id, category_id, day_id, round_id, response_text, player_id)
    SELECT s.clue_id, s.category_id, s.day_id, s.round_id, pr.response_text,
    s.player_id FROM scores AS s INNER JOIN player_responses AS pr ON
    s.clue_id = pr.clue_id AND s.player_id = pr.player_id WHERE ungraded > 0";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->close();

$query = "SELECT DISTINCT rounds.id, rounds.name, rounds.sequence
    FROM all_ungraded_scores AS aus INNER JOIN
    rounds ON aus.round_id = rounds.id
    ORDER BY sequence ASC";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($round_id, $round_name, $round_seq);

$round_ids = array();
$rounds = array();
$round_seqs = array();
$round_days = array();

while($stmt->fetch())
{
    $round_ids[] = $round_id;
    $rounds[$round_id] = $round_name;
    $round_seqs[$round_id] = $round_seq;
    $round_days[$round_id] = array();
}

$round_days[null] = array();

$stmt->close();

$query = "SELECT DISTINCT days.id, days.name, days.round_id,
    days.sequence, days.thread_url
    FROM all_ungraded_scores AS aus INNER JOIN days ON aus.day_id = days.id
    LEFT JOIN rounds ON days.round_id=rounds.id
    ORDER BY rounds.sequence ASC, days.sequence ASC";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($day_id, $day_name, $round_id,
    $day_seq, $day_url);

$days = array();
$day_seqs = array();
$day_categories = array();
$day_urls = array();

while($stmt->fetch())
{
    $round_days[$round_id][] = $day_id;
    $days[$day_id] = $day_name;
    $day_seqs[$day_id] = $day_seq;
    $day_categories[$day_id] = array();
    $day_urls[$day_id] = $day_url;
}

$stmt->close();

$query = "SELECT DISTINCT categories.id, categories.name, categories.sequence,
    categories.day_id
    FROM all_ungraded_scores AS aus
    INNER JOIN categories ON aus.category_id = categories.id
    INNER JOIN days ON categories.day_id=days.id
    LEFT JOIN rounds ON days.round_id = rounds.id
    ORDER BY rounds.sequence ASC,
    days.sequence ASC, categories.sequence ASC";

$stmt = $mysqli->prepare($query);
if (!$stmt)
{
    die($mysqli->error);
}
$stmt->execute();
$stmt->bind_result($cat_id, $cat_name, $cat_seq, $day_id);

$categories = array();
$cat_seqs = array();
$category_clues = array();

while($stmt->fetch())
{
    $day_categories[$day_id][] = $cat_id;
    $categories[$cat_id] = $cat_name;
    $cat_seqs[$cat_id] = $cat_seq;
    $category_clues[$cat_id] = array();
}

$stmt->close();

$query = "SELECT DISTINCT clues.id, clues.point_value, clues.category_id
    FROM all_ungraded_scores AS aus INNER JOIN clues ON aus.clue_id = clues.id
    INNER JOIN categories ON clues.category_id = categories.id
    INNER JOIN days ON categories.day_id = days.id
    LEFT JOIN rounds ON days.round_id = rounds.id
    ORDER BY
    rounds.sequence ASC, days.sequence ASC, categories.sequence ASC,
    clues.id ASC";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($clue_id, $clue_point_value, $cat_id);


$clue_point_values = array();
$ungradeds = array();

while($stmt->fetch())
{
    $clue_point_values[$clue_id] = $clue_point_value;
    $category_clues[$cat_id][] = $clue_id;
    $ungradeds[$clue_id] = array();
}
$stmt->close();

$query = "SELECT aus.clue_id, aus.response_text, aus.player_id,
    players.username
    FROM all_ungraded_scores AS aus
    INNER JOIN players ON aus.player_id = players.id
    ORDER BY aus.clue_id ASC, aus.response_text ASC";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($clue_id, $response_text, $player_id, $username);

while($stmt->fetch())
{
    $ungradeds[$clue_id][] = array(
        "response_text" => $response_text,
        "player_id" => $player_id,
        "username" => $username,
    );
}
$stmt->close();
$mysqli->close();

foreach($round_ids as $round_id)
{
    printf("<h2>%s</h2>", $rounds[$round_id]);
    foreach($round_days[$round_id] as $day_id)
    {
        printf('<h3>%s (<a href="%s">thread</a>)</h3>', $days[$day_id],
            $day_urls[$day_id]);
        foreach($day_categories[$day_id] as $category_id)
        {
            printf('<h4>%s</h4>', $categories[$category_id]);
            foreach($category_clues[$category_id] as $clue_id)
            {
                printf('<h5>Clue id %d for %d '.
                    '(<a href="edit_responses.php?id=%d">grade</a>)</h5>',
                    $clue_id, $clue_point_values[$clue_id], $clue_id);
                echo '<ul>';
                foreach($ungradeds[$clue_id] as $ungraded)
                {
                    printf('<li>"%s" by %s</li>', $ungraded['response_text'],
                        $ungraded['username']);
                }
                echo '</ul>';
            }
        }
    }
}
?>
<?php footer();?>
</body>
</html>
