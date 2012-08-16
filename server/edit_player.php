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
startpage(RESTRICTED);

do
{
    $mysqli = connect_mysql();
    if(!$mysqli)
    {
        $errortext = "Could not connect to database.";
        break;
    }
    $mysqli->query("USE $mysql_dbname;");

    $query = "SELECT id, username FROM players ORDER BY username ASC";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement";
        $mysqli->close();
        break;
    }
    $stmt->execute();
    $stmt->bind_result($id, $username);
    $users = array();
    while($stmt->fetch())
    {
        $users[$id] = $username;
    }
    $stmt->close();
    $mysqli->close();
} while (false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>
<?php
if(isset($errortext))
{
    echo "Error editing players";
}
else
{
    echo "Edit player";
}
?>
</title>
<?php
if(!isset($errortext))
{
    echo <<<SCRIPT
<script>
var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
{
    if (xmlhttp.readyState==4 && xmlhttp.status == 200)
    {
        document.getElementById("player").innerHTML =
            xmlhttp.responseText;
    }
};
function update_player()
{
    var idx = document.getElementById("playerselector").selectedIndex;
    var playerId = document.getElementById("playerselector").
        getElementsByTagName("option")[idx].getAttribute("value");
    if(playerId == 0)
    {
        document.getElementById("player").innerHTML = '';
    }
    else
    {
        // AJAX time!
        
        xmlhttp.open("GET","get_player.php?id="+playerId, true);
        xmlhttp.send();
    }
};
</script>
SCRIPT;
}
?>
</head>
<body<?php if(!isset($errortext))
{
    echo ' onload="update_player()"';
}?>
>
<?
if(isset($errortext))
{
    displayError($errortext);
}
else
{
    echo '<h1>Edit user</h1>';
    echo '<form action="submit_edited_player.php" method="post">';
    echo '<select id="playerselector" name="playerid"'.
        ' onchange="update_player()">';
    echo '<option value="0" selected="selected">Select a player...</option>';
    foreach($users as $id => $username)
    {
        printf('<option value="%d">%s</option>', $id, $username);
    }
    echo '</select>';
    echo '<div id="player"></div>';
    echo '</form>';
}
footer();
?>
</body>
</html>
