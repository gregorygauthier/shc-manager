<?php
/** Copyright (c) 2012-2014 Gregory Gauthier

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
$isloggedin = startpage(UNRESTRICTED);
try
{
    if(!isset($_GET['id']) or !$_GET['id'])
    {
        throw new Exception("No category id was specified");
    }
    $id = $_GET['id'];
    if(isset($_GET['format']) and $_GET['format'] == 'bbcode')
    {
        $format = 'bbcode';
    }
    else
    {
        $format = 'normal';
    }
    
    $category = Database::get_category_by_id($id);
    $clues = Database::get_clues_for_category($id, true);
    
    $title = strip_tags($category->name);
}
catch(Exception $e)
{
    $title = "Error viewing category.";
    $errortext = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title><?php echo $title;?></title>
</head>
<body>
<?php
if(!isset($errortext))
{
    echo("<h1>{$category->name}</h1>");
    echo("<p class=\"explanatory\">{$category->explanatory_text}</p>");
    if($format == 'bbcode')
    {
        echo "You can copy and paste the text below onto a forum:";
        $modified_catname = strtoupper($category->name);
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
        
        $modified_expl = $category->explanatory_text;
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
            
        foreach($clues as $clue)
        {
            $modified_cluetext = $clue->clue_text;
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
            printf ('%d. %s', $clue->point_value, $modified_cluetext);
            echo "\n";
        }
        echo '</textarea>';
    }
    else
    {
        echo "<table>";
        echo '<tr>';
        if($isloggedin)
        {
            echo '<th>Links</th>';
        }
        echo '<th>Clue</th>';
        echo '<th>Right</th>';
        echo '<th>Wrong</th>';
        echo '<th>Clam</th>';
        echo '<th>Ungraded</th>';
        echo '</tr>';
        $clue_number = 0;
        foreach($clues as $clue)
        {
            $clue_number++;
            printf('<tr class="%s">', $clue_number % 2 ? 'even': 'odd');
            if($isloggedin)
            {
                echo '<td>';
                printf ('(<a href="edit_clue.php?id=%d">edit</a>) ',
                    $clue->id);
                printf ('(<a href="edit_responses.php?id=%d">grade</a>) ',
                    $clue->id);
                echo '</td>';
            }
            printf ('<td>(%d/%d) %s</td>',
                $clue->point_value, $clue->wrong_point_value,
                $clue->clue_text);
            printf ('<td class="count">%d</td>',
                $clue->clue_statistics->num_right);
            printf ('<td class="count">%d</td>',
                $clue->clue_statistics->num_wrong);
            printf ('<td class="count">%d</td>',
                $clue->clue_statistics->num_clam);
            if ($clue->clue_statistics->num_ungraded)
            {
                printf ('<td class="count">%d</td>',
                    $clue->clue_statistics->num_clam);
            }
            else
            {
                echo '<td class="count"></td>';
            }
            echo '</tr>';
        }
        echo "</table>";
    }
}
if(isset($errortext))
{
    displayError($errortext);
}

?>
<p>View the <?php
if($format == 'bbcode')
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
