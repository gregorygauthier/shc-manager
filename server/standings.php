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
startpage(UNRESTRICTED);

do
{
    $mysqli = connect_mysql();
    
    if(!$mysqli)
    {
        $errortext = "Could not connect to database.";
        break;
    }

    $mysqli->query("USE $mysql_dbname;");

    $query = "SELECT username, teen_eligible, college_eligible, atb_eligible,
        overall_score, overall_ungraded FROM overall_scores INNER JOIN players
        ON overall_scores.player_id = players.id ORDER BY overall_score DESC";

    $stmt = $mysqli->prepare($query);
    
    if(!$stmt)
    {
        $errortext = "Could not prepare statement.";
        break;
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
} while(false);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Current Standings</title>
</head>
<body>
<?php
if(isset($errortext))
{
    displayError($errortext);
}
?>
<h1>Current Standings</h1>
<table>
<tr><th>Rank</th><th>Player</th><th>Teen</th><th>College</th>
<th>ATB</th><th>Score</th><th>Ungraded resps.</th></tr>
<?php
if(!isset($errortext))
{
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
}
?>
</table>
<p class="footnote">Note that scores are tentative and unofficial.
Responses that do not match the regular expression pattern for any given
response, correct or incorrect, are neither given credit nor penalized.</p>
<?php footer(); ?>
</body>
</html>
