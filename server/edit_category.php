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

/* USAGE OF THIS PAGE: id is a required parameter--the id of the
category to be edited */

require_once('common.inc');

if(isset($_GET['id']))
{
    $id = $_GET['id'];
    /* Check to see if ID matches that of a valid clue */
    $mysqli = connect_mysql();
    $mysqli->query("USE $mysql_dbname;");
    $query = "SELECT COUNT(*) FROM categories WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    if($count == 0)
    {
        $errortext = "Invalid category id specified.";
    }
    $stmt->close();
}
else
{
    $errortext = "No category id was specified.";
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>
<?php
if(isset($errortext))
{
    echo "Error editing category";
}
else
{
    echo "Editing category $id";
}
?>
</title>
</head>
<body>
<?php
if(!isset($errortext))
{
    /* Get relevant information about the category from database */
    $query = "SELECT name, explanatory_text FROM categories
        WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($category_name, $explanatory_text);
    $stmt->fetch();
    $stmt->close();
    $query = "SELECT id, clue_text, point_value, wrong_point_value FROM clues
        WHERE category_id=? ORDER BY point_value ASC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($clue_id, $clue_text, $point_value, $wrong_point_value);
    $clue_strings = array();
    while($stmt->fetch())
    {
        $clue_strings[] = sprintf('<li>(<a href="edit_clue.php?id=%d">'.
            'edit</a>) (%d/%d) %s</li>', $clue_id, $point_value,
            $wrong_point_value, $clue_text);
    }
    $stmt->close();
    echo "<h1>Editing category $id</h1>";
    echo '<form action="submit_edited_category.php" method="post">';
    echo '<table><tr><th>Category ID</th>';
    printf('<td><input type="text" size="5" name="id" value="%d"'.
        ' readonly="true"/></td></tr>', $id);
    echo '<tr><th>Category name</th>';
    printf('<td><input type="text" size="100" name="catname" value="%s"'.
        '/></td></tr>', $category_name);
    echo '<tr><th>Explanatory text</th>';
    printf('<td><textarea class="fullwidthinput"'.
        'rows="%d" cols="%d" name="explanatory">%s</textarea></td></tr>',
        $text_rows, $clue_cols, $explanatory_text);
    echo '</table>';
    echo '<h2>List of clues in this category</h2>';
    echo '<ul>';
    echo implode('', $clue_strings);
    echo '</ul>';
    echo '<button type="submit">Submit</button>';
}
else
{
    displayError($errortext);
}
footer();
?>
</body>
</html>
