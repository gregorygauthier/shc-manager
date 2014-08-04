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

try
{
    /* Step 1: Add the category */
    $name = strip_tags($_POST["categoryname"], '<b><i><u>');
    $explanatory_text = strip_tags($_POST["explanatory"], '<a><b><i><u>');
    if(is_null($_POST["dayid"]) or $_POST["dayid"] == 0)
    {
        $day_id = null;
    }
    else
    {
        $day_id = $_POST["dayid"];
    }
    $cat_id = Database::add_category($name, $explanatory_text, null, $day_id);
    
    /* Step 2.  Add the clues */
    $clue_ids = array();

    $multiplier = ($_POST["point_scheme"] == "second" ? 3 : 2);
    
    for($i = 1; $i <= 5; $i++)
    {
        $clue_text = strip_tags($_POST["clue$i"], '<a><b><i><u><img><br>');
        $point_value = $multiplier * $i;
        $wrong_point_value = -$i;
        $clue_ids[$i] = Database::add_clue(
            $clue_text, $cat_id, $point_value, $wrong_point_value);
    }
    
    /* Step 3.  Add the responses */
    for($i = 1; $i <= 5; $i++)
    {
        if (is_null($_POST["response$i"]))
        {
            continue;
        }
        $raw_response = $_POST["response$i"];
        if(!trim($raw_response))
        {
            continue;
        }
        $responses = explode("\n", $_POST["response$i"]);
        foreach($responses as $response)
        {
            $trimmed_response = trim($response);
            $correct = 1;
            Database::add_response($clue_ids[$i], $trimmed_response, $correct);
        }
    }
    
    $title = "Category added successfully";
    $message = "<p>Successfully added the category, clues, and responses!</p>";
}
catch(Exception $e)
{
    $title = "Error adding category";
    $message = sprintf("<p class=\"error\">Error adding category: %s</p>",
        $e->getMessage());
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
echo $message;
footer();
?>
</body>
</html>
