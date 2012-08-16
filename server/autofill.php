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
    $query = "SELECT days.id, days.name FROM days LEFT JOIN rounds
    ON days.round_id=rounds.id
    ORDER BY rounds.sequence ASC, days.sequence ASC";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement";
        $mysqli->close();
        break;
    }
    $stmt->execute();
    $stmt->bind_result($id, $name);
    $days = array();
    while($stmt->fetch())
    {
        $days[$id] = $name;
    }
    $stmt->close();
    $mysqli->close();
} while (false)
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
    echo 'Error preparing autofill page.';
}
else
{
    echo 'Autofill';
}
?>
</title>
</head>
<body>
<?php
if(isset($errortext))
{
    echo '<h1>Error preparing autofill</h1>';
    displayError($errortext);
}
else
{
    echo '<hgroup><h1>Autofill</h1>';
    echo '<h2>With great power comes great responsibility: ';
    echo 'please be careful!</h2></hgroup>';
    echo '<form>';
    echo '<select id="dayselector" name="dayid">';
    echo '<option value="0">Please select a day...</option>';
    foreach($days as $id => $name)
    {
        printf('<option value="%d">%s</option>', $id, $name);
    }
    echo '</select>';
    echo '<label for="urlfield">URL of daily thread:</label>';
    echo '<input id="urlfield" name="url" maxlength="255" />';
    echo '<label for="urlfield">Post number:</label>';
    echo '<button id="postnumberdecrement" type="button"'.
        'onclick="decrement_post_number()">-</button>';
    echo '<input id="postnumberfield" name="postnumber" maxlength="4" />';
    echo '<button id="postnumberincrement" type="button"'.
        'onclick="increment_post_number()">+</button>';
    echo '</form>';
}
?>
</body>
</html>
