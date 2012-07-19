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

/* USAGE OF THIS PAGE: player is an optional parameter that autoselects
the player whose responses are to be filled in.  Category is an
optional parameter that autoselects a category.  */

require_once('common.inc');

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
        $errortext = "Could not create prepared statement.";
        break;
    }

    $stmt->execute();

    $stmt->bind_result($id, $name);

    $players = array(); // map of ids to player names

    while($stmt->fetch())
    {
        $players[$id] = $name;
    }

    $stmt->close();
    
    $query = "SELECT id, name FROM categories ORDER BY id ASC";
    
    $stmt = $mysqli->prepare($query);
    
    if(!$stmt)
    {
        $errortext = "Could not create prepared statement.";
        break;
    }
    
    $stmt->execute();
    
    $stmt->bind_result($id, $name);
    
    $categories = array();
    
    while($stmt->fetch())
    {
        $categories[$id] = $name;
    }
    
    $stmt->close();
}while(false);
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Edit/add responses</title>
<script>
var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange=function()
{
    if (xmlhttp.readyState==4 && xmlhttp.status == 200)
    {
        document.getElementById("responses").innerHTML =
            xmlhttp.responseText;
    }
};
function update_responses()
{
    var idx = document.getElementById("playerselector").selectedIndex;
    var playerId = document.getElementById("playerselector").
        getElementsByTagName("option")[idx].getAttribute("value");
    idx = document.getElementById("categoryselector").selectedIndex;
    var categoryId = document.getElementById("categoryselector").
        getElementsByTagName("option")[idx].getAttribute("value");
    if(playerId == 0 || categoryId == 0)
    {
        document.getElementById("responses").innerHTML = '';
    }
    else
    {
        // AJAX time!
        
        xmlhttp.open("GET","get_responses.php?player="+playerId+
            "&category="+categoryId, true);
        xmlhttp.send();
    }
}
</script>
</head>
<body onload="update_responses()">
<?php
if(isset($errortext))
{
    displayError($errortext);
    copyright();
    die();
}
?>
<form action="add_responses.php" method="post">
<p>Player:
<select id="playerselector" name="playerid" onchange="update_responses()">
<?php
if(!array_key_exists($_GET["player"], $players))
{
    echo '<option value="0" selected="selected">'.
        'Please select a player...</option>';
}
foreach($players as $id => $name)
{
    if($id == $_GET["player"])
    {
        printf('<option value="%d" selected="selected">%s</option>',
            $id, $name);
    }
    else
    {
        printf('<option value="%d">%s</option>', $id, $name);
    }
}
?>
</select>
</p>
<p>Category:
<select id="categoryselector" name="categoryid" onchange="update_responses()">
<?php
if(!array_key_exists($_GET["category"], $categories))
{
    echo '<option value="0" selected="selected">'.
        'Please select a category...</option>';
}
foreach($categories as $id => $name)
{
    if($id == $_GET["category"])
    {
        printf('<option value="%d" selected="selected">%s</option>',
            $id, $name);
    }
    else
    {
        printf('<option value="%d">%s</option>', $id, $name);
    }
}
?>
</select>
</p>
<div id="responses">
</div>
</form>
<?php
footer();
?>
</body>
</html>
