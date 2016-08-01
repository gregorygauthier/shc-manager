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
} while(false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Current Standings</title>
<script>
// Do a test for Internet Explorer 6, against my better judgment
var xmlhttp = null;
if(window.XMLHttpRequest)
{
    xmlhttp = new XMLHttpRequest();
}
else
{
    xmlhttp = new ActiveXObject('MSXML2.XMLHTTP.3.0');
}
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
function change_button()
{
    var x = document.getElementById('reloadbutton');
    x.setAttribute("type", "button");
    x.setAttribute("onclick", "update_standings()");
    x.innerHTML = "Refresh";
};
</script>
</head>
<body onload="change_button()">
<h1>Standings</h1>
<form action="standings.php" method="get">
<select id="selector" name="selector" onchange="update_standings()">
<?php
if(isset($_GET['selector']))
{
    if(preg_match('/^([ord])(\d*)$/', $_GET['selector'], $matches))
    {
        if($matches[1] == 'r')
        {
            $day_get = null;
            $round_get = $matches[2];
        }
        elseif($matches[1] == 'd')
        {
            $day_get = $matches[2];
            $round_get = null;
        }
        else
        {
            $day_get = null;
            $round_get = null;
        }
    }
    else
    {
        $day_get = null;
        $round_get = null;
    }
}
else
{
    $day_get = (isset($_GET['dayid']) ? intval($_GET['dayid']) : null);
    if(is_null($day_get))
    {
        $round_get =
            (isset($_GET['roundid']) ? intval($_GET['roundid']) : null);
    }
    else
    {
        $round_get = null;
    }
}
printf('<option value="o" %s>Overall standings</option>',
    (is_null($day_get) and is_null($round_get)) ? 'selected="selected"' : '');
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
    printf('<option value="r%d" %s> &gt; %s</option>', $roundid,
        (!is_null($round_get) and $round_get == $roundid) ?
        'selected="selected"' : '', $roundname);
    foreach($round_days[$roundid] as $dayid)
    {
        printf('<option value="d%d" %s> &gt; &gt; %s</option>',
            $dayid,
            (!is_null($day_get) and $day_get == $dayid) ?
            'selected="selected"' : '',
            $days[$dayid]);
    }
}
foreach($roundless_days as $dayid)
{
    printf('<option value="d%d" %s> &gt; &gt; %s</option>',
        $dayid,
        (!is_null($day_get) and $day_get == $dayid) ?
        'selected="selected"' : '',
        $days[$dayid]);
}
$mysqli->close();
?>
</select>
<button id="reloadbutton" type="submit">Reload</button>
</form>
<div id="standings">
<?php
echo get_standings($day_get, $round_get);
?>
</div>
<p class="footnote">Note that scores are tentative and unofficial.
Responses that do not match the regular expression pattern for any given
response, correct or incorrect, are neither given credit nor penalized.</p>
<?php footer(); ?>
</body>
</html>
