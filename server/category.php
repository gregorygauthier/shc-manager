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

/* USAGE OF THIS PAGE: id is a get parameter that represents
the id number of the category to be displayed.  If id is not specified,
it is an error.*/

require_once('common.inc');
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<?php
if(!isset($_GET['id']))
{
    $errortext="No category id was specified.";
}
else
{
    $id = $_GET['id'];
    $mysqli = connect_mysql();
    $mysqli->query("USE $mysql_dbname;");
    $query = "SELECT name, explanatory_text FROM categories WHERE id=?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext="Could not create a statement to query the".
        " categories table.";
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($catname, $expl);
    if(!$stmt->fetch())
    {
        $errortext="Invalid category id specified.";
    }
    $stmt->close();
}
?>
<title>
<?php
if(isset($errortext))
{
    echo "Error displaying category";
}
else
{
    echo strip_tags($catname);
}
?>
</title>
</head>
<body>
<?php
if(!isset($errortext))
{
    echo("<h1>$catname</h1>");
    echo("<p class=\"explanatory\">$expl</p>");
    $query = "SELECT id, clue_text, point_value, wrong_point_value
        FROM clues WHERE category_id=? ORDER BY point_value ASC";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext="Could not create a statement to query the".
        " clues table.";
    }
    else
    {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($clue_id, $cluetext, $pts, $wrong_pts);
        echo "<ul>";
        while($stmt->fetch())
        {
            printf ('<li>(<a href="edit_clue.php?id=%d">edit</a>) '.
            '(%d/%d) %s</li>', $clue_id, $pts, $wrong_pts, $cluetext);
        }
        echo "</ul>";
        $mysqli->close();
    }
}
if(isset($errortext))
{
    displayError($errortext);
}

?>
<?php copyright();?>
</body>
</html>
