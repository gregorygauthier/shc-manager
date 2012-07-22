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

/* USAGE OF THIS PAGE: This is an internal page used only by responses.php.

player is an required parameter that autoselects
the player whose responses are to be filled in.  Category is an
required parameter that autoselects a category.

The result is not well-formed html, as it is intended for inclusion in
the responses.php form.  */

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

    $query = "SELECT id, clue_text, point_value, wrong_point_value
        FROM clues WHERE category_id = ?";

    $stmt = $mysqli->prepare($query);
    
    if(!$stmt)
    {
        $errortext = "Could not create prepared statement.";
        break;
    }
    
    $stmt->bind_param("i", $_GET["category"]);

    $stmt->execute();

    $stmt->bind_result($id, $clue_text, $point_value, $wrong_point_value);

    $clue_paragraphs = array();

    while($stmt->fetch())
    {
        $clue_paragraphs[$id] = sprintf('(%d/%d) %s', $point_value,
            $wrong_point_value, $clue_text);
    }

    $stmt->close();
    
    $query = "SELECT clues.id, 
        response_text FROM clues LEFT JOIN
        (SELECT * FROM player_responses WHERE player_id=?) AS pr
        ON clues.id=pr.clue_id WHERE category_id=?";
    
    $stmt = $mysqli->prepare($query);
    
    if(!$stmt)
    {
        $errortext = "Could not create prepared statement.";
        break;
    }
    
    $stmt->bind_param("ii", $_GET["player"], $_GET["category"]);
    
    $stmt->execute();
    
    $stmt->bind_result($id, $response);
    
    $response_paragraphs = array();
    
    while($stmt->fetch())
    {
        if(is_null($response))
        {
            $response_paragraphs[$id] = sprintf(
                '<input type="text" maxlength="65535" '.
                'name="response%d" value=""/>',
                $id);
        }
        else
        {
            $response_paragraphs[$id] = sprintf(
                '<input type="text" maxlength="65535" '.
                'name="response%d" value="%s"/>',
                $id, $response);
        }
    }
    
    $stmt->close();
    
    // echo count($clue_paragraphs) . '<br />';
    echo '<table>';
    foreach($clue_paragraphs as $id => $clue_par)
    {
        printf('<tr><td>%s</td><td>%s</td></tr>', $clue_par,
            $response_paragraphs[$id]);
    }
    
    echo '</table><button type="submit">Submit</button>';
}while(false);
if(isset($errortext))
{
    displayError($errortext);
}
?>
