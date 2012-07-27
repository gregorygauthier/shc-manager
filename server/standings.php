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

    

} while(false);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Current Standings</title>
<script>
var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
{
    if (xmlhttp.readyState==4 && xmlhttp.status == 200)
    {
        document.getElementById("standings").innerHTML =
            xmlhttp.responseText;
    }
};
function update_standings()
{
    var idx = document.getElementById("selector").selectedIndex;
    var idstring = document.getElementById("selector").
        getElementsByTagName("option")[idx].getAttribute("value");
    var matches = idstring.match(/^([ord])(\d*)$/);
    var type = matches[1];
    var id = matches[2];
    var getvars = '';
    if(type == 'd')
    {
        getvars = '?dayid=' + id;
    }
    else if(type == 'r')
    {
        getvars = '?roundid=' + id;
    }
    
    xmlhttp.open("GET","get_standings.php"+getvars, true);
    xmlhttp.send();
};
</script>
</head>
<body onload="update_standings()">
<h1>Standings</h1>
<select id="selector" name="selector" onchange="update_standings()">
<option value="o" selected="selected">Overall standings</option>
<?php
$query = "SELECT id, name FROM rounds ORDER BY sequence ASC";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($roundid, $roundname);
$rounds = array();
$round_days = array();
while($stmt->fetch())
{
    $rounds[$roundid] = $roundname;
    $round_days[$roundid] = array();
}
$stmt->close();
$query = "SELECT days.id, rounds.id, days.name FROM days LEFT JOIN rounds ON
    days.round_id = rounds.id ORDER BY rounds.sequence ASC,
    days.sequence ASC";
$stmt = $mysqli->prepare($query);
$stmt->execute();
$stmt->bind_result($dayid, $roundid, $dayname);
$days = array();
$roundless_days = array();
while($stmt->fetch())
{
    if(is_null($roundid))
    {
        $roundless_days[] = $dayid;
    }
    else
    {
        $round_days[$roundid][] = $dayid;
    }
    $days[$dayid] = $dayname;
}
$stmt->close();
foreach($rounds as $roundid => $roundname)
{
    printf('<option value="r%d"> &gt; %s</option>', $roundid, $roundname);
    foreach($round_days[$roundid] as $dayid)
    {
        printf('<option value="d%d"> &gt; &gt; %s</option>',
            $dayid, $days[$dayid]);
    }
}
foreach($roundless_days as $dayid)
{
    printf('<option value="d%d"> &gt; &gt; %s</option>',
        $dayid, $days[$dayid]);
}
$mysqli->close();
?>
</select>
<div id="standings">
<?php
if(isset($errortext))
{
    displayError($errortext);
}
?>
</div>
<p class="footnote">Note that scores are tentative and unofficial.
Responses that do not match the regular expression pattern for any given
response, correct or incorrect, are neither given credit nor penalized.</p>
<?php footer(); ?>
</body>
</html>
