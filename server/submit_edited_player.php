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
    if(!isset($_POST['playerid']) or is_null($_POST['playerid']))
    {
        $errortext = "No player id provided.";
        break;
    }
    $id = $_POST['playerid'];
    if(!isset($_POST['name']) or is_null($_POST['name']))
    {
        $errortext = "No username provided.";
        break;
    }
    $username = $_POST['name'];
    $mysqli = connect_mysql();
    if(!$mysqli)
    {
        $errortext = "Could not connect to database.";
        break;
    }
    $mysqli->query("USE $mysql_dbname;");
    $query = "SELECT COUNT(*) FROM players WHERE username=? AND
        id <> ?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement.";
        $mysqli->close();
        break;
    }
    $stmt->bind_param('si', $username, $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    if($count > 0)
    {
        $errortext = "Username already in use.";
        $stmt->close();
        $mysqli->close();
        break;
    }
    $stmt->close();
    $query = "UPDATE players SET teen_eligible=?, college_eligible=?,
        atb_eligible=? WHERE id=?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement.";
        $mysqli->close();
        break;
    }
    $teen = (isset($_POST['teen']) and $_POST['teen'] == 'yes');
    $college = (isset($_POST['college']) and $_POST['college'] == 'yes');
    $atb = (isset($_POST['atb']) and $_POST['atb'] == 'yes');
    $stmt->bind_param('iiii', 
        $teen, $college, $atb, $id);
    $stmt->execute();
    if($stmt->errno)
    {
        $errortext = sprintf("Error updating database (number %s).",
            $stmt->errno);
    }
    $stmt->close();
    $mysqli->close();
} while(false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title><?php
if(isset($errortext))
{
    echo 'Error editing player';
}
else
{
    echo 'Player successfully edited';
}
?>
</title>
</head>
<body>
<?php
if(isset($errortext))
{
    echo '<h1>Error editing player</h1>';
    displayError($errortext);
}
else
{
    echo '<h1>Successful player update!</h1>';
    echo '<p>Player information successfully updated!</p>';
}
footer();
?>
</body>
</html>
