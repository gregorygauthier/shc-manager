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

/* USAGE OF THIS PAGE: This is an internal page used only by standings.php.

dayid is an optional parameter; if specified, results count only the
scores of the day with the given id.
roundid is an optional parameter; if specified and dayid is not
specified, results count only the scores of the round with the given id
With no parameters, this returns the overall standings.

The result is not well-formed html, as it is intended for inclusion in
the standings.php page  */

require_once('common.inc');
startpage(UNRESTRICTED);

$mysqli = connect_mysql();
$mysqli->query("USE $mysql_dbname;");

$overall_query = "SELECT username, teen_eligible, college_eligible,
    atb_eligible, overall_score, overall_ungraded FROM overall_scores
    INNER JOIN players ON overall_scores.player_id = players.id
    ORDER BY overall_score DESC, username ASC";

$round_query = "SELECT username, teen_eligible, college_eligible,
    atb_eligible, round_score, round_ungraded FROM round_scores
    INNER JOIN players ON round_scores.player_id = players.id
    WHERE round_id = ? ORDER BY round_score DESC, username ASC";

$day_query = "SELECT username, teen_eligible, college_eligible,
    atb_eligible, daily_score, daily_ungraded FROM daily_scores
    INNER JOIN players ON daily_scores.player_id = players.id
    WHERE day_id = ?
    ORDER BY daily_score DESC, username ASC";

if(isset($_GET['dayid']))
{
    $stmt = $mysqli->prepare($day_query);
    $stmt->bind_param('i', $_GET['dayid']);
}
elseif(isset($_GET['roundid']))
{
    $stmt = $mysqli->prepare($round_query);
    $stmt->bind_param('i', $_GET['roundid']);
}
else // overall standings
{
    $stmt = $mysqli->prepare($overall_query);
}
$stmt->execute();
$stmt->bind_result($username, $teen_eligible, $college_eligible,
    $atb_eligible, $score, $num_ungraded);

$usernames = array();
$teen_eligible_stats = array();
$college_eligible_stats = array();
$atb_eligible_stats = array();
$scores = array();
$ungradeds = array();

while($stmt->fetch())
{
    $usernames[] = $username;
    $teen_eligible_stats[] = $teen_eligible;
    $college_eligible_stats[] = $college_eligible;
    $atb_eligible_stats[] = $atb_eligible;
    $scores[] = $score;
    $ungradeds[] = $num_ungraded;
}

$stmt->close();
if(isset($_GET['dayid']))
{
    $query = "SELECT name FROM days WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $_GET['dayid']);
    $stmt->execute();
    $stmt->bind_result($dayname);
    $stmt->fetch();
    printf ('<h2>Day results for %s</h2>', $dayname);
    $stmt->close();
}
elseif(isset($_GET['roundid']))
{
    $query = "SELECT name FROM rounds WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $_GET['roundid']);
    $stmt->execute();
    $stmt->bind_result($roundname);
    $stmt->fetch();
    printf ('<h2>Round results for %s</h2>', $roundname);
    $stmt->close();
}
else // overall standings
{
    echo '<h2>Overall results</h2>';
}
if(!isset($errortext))
{
    echo <<<HTML
<table>
<tr><th>Rank</th><th>Player</th><th>Teen</th><th>College</th>
<th>ATB</th><th>Score</th><th>Ungraded</th></tr>
HTML;
    $records_printed = 0;
    foreach($usernames as $key => $value)
    {
        $records_printed++;
        printf('<tr class="%s"><td class="rank">%d</td>'.
            '<td>%s</td><td class="marker">%s</td>'.
            '<td class="marker">%s</td><td class="marker">%s</td>'.
            '<td class="score">%d</td><td class="score">%s</td></tr>',
            $records_printed % 2 ? "odd" : "even", $records_printed,
            $value, $teen_eligible_stats[$key] ? 'T' : '',
            $college_eligible_stats[$key] ? 'C' : '',
            $atb_eligible_stats[$key] ? 'A' : '',
            $scores[$key],
            $ungradeds[$key] ? $ungradeds[$key] : '');
    }
    echo '</table>';
}
else
{
    displayError($errortext);
}
?>

