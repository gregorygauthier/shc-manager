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
</head>
<body>
<?php
if(!isset($_GET['id']))
{
    die("<p class=\"error\">No category id was specified.</p>");
}
$id = $_GET['id'];
$mysqli = connect_mysql();
$mysqli->query("USE $mysql_dbname;");
$query = "SELECT name, explanatory_text FROM categories WHERE id=?";
$stmt = $mysqli->prepare($query);
if(!$stmt)
{
    die("<p class=\"error\">Could not create a statement to query the".
    " categories table.</p>");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($catname, $expl);
if($stmt->fetch())
{
    echo("<h1>$catname</h1>");
    echo("<p class=\"explanatory\">$expl</p>");
}
else
{
    die("<p class=\"error\">Invalid category id specified.</p>");
}
$stmt->close();
$query = "SELECT clue_text, point_value FROM clues WHERE category_id=?".
    " ORDER BY point_value ASC";
$stmt = $mysqli->prepare($query);
if(!$stmt)
{
    die("<p class=\"error\">Could not create a statement to query the".
    " clues table.</p>");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($cluetext, $pts);
echo "<ul>";
while($stmt->fetch())
{
    echo "<li>($pts) $cluetext</li>";
}
echo "</ul>";
$mysqli->close();
?>
<?php copyright();?>
</body>
</html>
