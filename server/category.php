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
it is an error.  Format is optional; if format=bbcode, the clues
are displayed in a text area for copypasting.  If format=normal
or is not given, then the usual HTML format is used.*/

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
        if(isset($_GET['format']) and $_GET['format'] == 'bbcode')
        {
            echo "You can copy and paste the text below onto a forum:";
            $modified_catname = strtoupper($catname);
            // Remove <b></b> tags; change <i></i> and <u></u> tags to BBcode
            $modified_catname = preg_replace('/<\/?b>/i', '',
                $modified_catname);
            $modified_catname = preg_replace('/<i>/i', '[i]',
                $modified_catname);
            $modified_catname = preg_replace('/<\/i>/i', '[/i]',
                $modified_catname);
            $modified_catname = preg_replace('/<u>/i', '[u]',
                $modified_catname);
            $modified_catname = preg_replace('/<\/u>/i', '[/u]',
                $modified_catname);
            printf('<textarea class="fullwidthinput"'.
                'rows="%d" cols="%d" name="pasteable">',
                $pasteable_rows, $clue_cols);
            printf('[b]%s[/b]', $modified_catname);
            
            echo "\n\n";
            
            $modified_expl = $expl;
            $modified_expl = preg_replace('/<i>/i', '[/i]',
                $modified_expl);
            $modified_expl = preg_replace('/<\/i>/i', '[i]',
                $modified_expl);
            $modified_expl = preg_replace('/<b>/i', '[b]',
                $modified_expl);
            $modified_expl = preg_replace('/<\/b>/i', '[/b]',
                $modified_expl);
            $modified_expl = preg_replace('/<u>/i', '[u]',
                $modified_expl);
            $modified_expl = preg_replace('/<\/u>/i', '[/u]',
                $modified_expl);
            $modified_expl = preg_replace(
                    '/<a href="([^">]*)">(.*)<\/a>/i', 
                    '[url=$1]$2[/url]', $modified_expl);
            printf ('[i]%s[/i]', $modified_expl);
            echo "\n\n";
            
            while($stmt->fetch())
            {
                $modified_cluetext = $cluetext;
                $modified_cluetext = preg_replace('/<b>/i', '[b]',
                    $modified_cluetext);
                $modified_cluetext = preg_replace('/<\/b>/i', '[/b]',
                    $modified_cluetext);
                $modified_cluetext = preg_replace('/<i>/i', '[i]',
                    $modified_cluetext);
                $modified_cluetext = preg_replace('/<\/i>/i', '[/i]',
                    $modified_cluetext);
                $modified_cluetext = preg_replace('/<u>/i', '[u]',
                    $modified_cluetext);
                $modified_cluetext = preg_replace('/<\/u>/i', '[/u]',
                    $modified_cluetext);
                $modified_cluetext = preg_replace(
                    '/<a href="([^">]*)">(.*)<\/a>/i', 
                    '[url=$1]$2[/url]', $modified_cluetext);
                printf ('%d. %s', $pts, $modified_cluetext);
                echo "\n";
            }
            echo '</textarea>';
        }
        else
        {
            echo "<ul>";
            while($stmt->fetch())
            {
                printf ('<li>(<a href="edit_clue.php?id=%d">edit</a>) '.
                '(%d/%d) %s</li>', $clue_id, $pts, $wrong_pts, $cluetext);
            }
            echo "</ul>";
        }
        $mysqli->close();
    }
}
if(isset($errortext))
{
    displayError($errortext);
}

?>
<p>View the <?php
if(isset($_GET['format']) and $_GET['format'] == 'bbcode')
{
    printf('<a href="category.php?id=%d&format=normal">normal HTML version</a>',
        $id);
}
else
{
    printf('<a href="category.php?id=%d&format=bbcode">BBCode version</a>',
        $id);
}
?></p>
<?php footer();?>
</body>
</html>
