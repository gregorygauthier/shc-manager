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

require_once('common.inc');
$isloggedin = startpage(UNRESTRICTED);

$mysqli = connect_mysql();


$mysqli->query("USE $mysql_dbname;");

if($mysqli->errno)
{
    die(printf("Database use failed: errno %d", $mysqli->errno));
}

$query = "SELECT id, name, sequence FROM rounds ORDER BY sequence ASC";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($round_id, $round_name, $round_seq);

$rounds = array();
$round_seqs = array();
$round_days = array();

while($stmt->fetch())
{
    $rounds[$round_id] = $round_name;
    $round_seqs[$round_id] = $round_seq;
    $round_days[$round_id] = array();
}

$round_days[null] = array();

$stmt->close();

$query = "SELECT days.id, days.name, days.round_id, days.play_date,
    days.sequence, days.thread_url
    FROM days LEFT JOIN rounds ON days.round_id=rounds.id
    ORDER BY rounds.sequence ASC, days.sequence ASC";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($day_id, $day_name, $round_id,
    $play_date, $day_seq, $day_url);

$days = array();
$play_dates = array();
$day_seqs = array();
$day_categories = array();
$day_urls = array();

while($stmt->fetch())
{
    $round_days[$round_id][] = $day_id;
    $days[$day_id] = $day_name;
    $play_dates[$day_id] = $play_date;
    $day_seqs[$day_id] = $day_seq;
    $day_categories[$day_id] = array();
    $day_urls[$day_id] = $day_url;
}

$stmt->close();

$query = "SELECT categories.id, categories.name, categories.sequence,
    categories.day_id
    FROM categories INNER JOIN days ON day_id=days.id
    LEFT JOIN rounds ON round_id = rounds.id ORDER BY rounds.sequence ASC,
    days.sequence ASC, categories.sequence ASC";

$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($cat_id, $cat_name, $cat_seq, $day_id);

$categories = array();
$cat_seqs = array();

while($stmt->fetch())
{
    $day_categories[$day_id][] = $cat_id;
    $categories[$cat_id] = $cat_name;
    $cat_seqs[$cat_id] = $cat_seq;
}

$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Calendar</title>
</head>
<body>
<h1>Calendar</h1>
<?php
foreach($rounds as $round_id => $round_name)
{
    printf('<h2 id="round%d">%s</h2>', $round_id, $round_name);
    if(empty($round_days[$round_id]))
    {
        echo '<p>No days found in this round.</p>';
    }
    else
    {
        echo '<table>';
        echo '<tr><th>Day</th><th>Play date</th><th>Categories</th>'.
            '<th>Thread</th></tr>';
        $rows_written = 0;
        foreach($round_days[$round_id] as $day_id)
        {
            $day_name = $days[$day_id];
            $play_date = $play_dates[$day_id];
            $day_seq = $day_seqs[$day_id];
            $day_url = $day_urls[$day_id];
            $todays_cats = array();
            foreach($day_categories[$day_id] as $cat_id)
            {
                $todays_cats[$cat_id] = sprintf(
                    '<a href="category.php?id=%d">%s</a>',
                    $cat_id, $categories[$cat_id]);
            }
            $catlist = implode(", ", $todays_cats);
            $rows_written++;
            printf('<tr id="day%d" class="%s">'.
                '<td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $day_id, $rows_written % 2 ? "odd" : "even",
                $day_name, date("F j, Y", strtotime($play_date)), $catlist,
                is_null($day_url) ? '' : sprintf(
                '<a href="%s">%s</a>', $day_url, $day_url));
        }
        echo '</table>';
    }
}
if($isloggedin)
{
    echo <<<HTML
<h2 id="newroundheader">Add a new round</h2>
<form action="add_round.php" method="post">
<p>
<label for="newroundnamefield">Name: </label>
<input type="text" maxlength="30" name="newroundname" id="newroundnamefield"/>
</p>
<button type="submit">Create round</button></p>
</form>
HTML;
}
if($isloggedin and !empty($rounds))
{
    echo <<<HTML
<h3>Add a new day</h3>
<form action="add_day.php" method="post">
<p><label for="newdayroundselector">Round: </label>
<select name="newdayround" id="newdayroundselector" required="required"/>
HTML;
    $options_written = 0;
    foreach($rounds as $round_id => $round_name)
    {
        $options_written++;
        printf('<option value="%d" %s>%s</option>', $round_id,
            $options_written == 1 ? 'selected="selected"' : '', $round_name);
    }
    echo <<<HTML
</select></p>
<p><label for="newdaynamefield">Name: </label>
<input type="text" name="newdayname" id="newdaynamefield" maxlength="30" /></p>
<p><label for="newdaydatefield">Date (YYYY-MM-DD): </label>
<input type="date" name="newdaydate" id="newdaydatefield" /></p>
<p><label for="newdayurlfield">Thread URL: </label>
<input type="text" name="newdayurl" id="newdayurlfield" maxlength="255" /></p>
<button type="submit">Add day</button>
</form>
HTML;
}
footer();
?>
</body>
</html>
