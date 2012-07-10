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

$responses = array();

foreach($_POST as $key => $value)
{
    if(preg_match('/^response(\d+)/', $key, $matches))
    {
        $tmp = $value;
        preg_replace('/[^\w]/', '', $tmp);
        preg_replace('/\s+/', ' ', $tmp);
        $responses[$matches[1]] = $tmp;
    }
    else if($key == 'playerid')
    {
        $player_id = $value;
    }
}

do
{
    if(!isset($player_id))
    {
        $errortext = "Player id not specified.";
        break;
    }
    $mysqli = connect_mysql();
    
    if(!$mysqli)
    {
        $errortext = "Could not connect to database.";
        break;
    }
    
    $mysqli->query("USE $mysql_dbname;");
    
    $query_count = "SELECT COUNT(*) FROM player_responses WHERE
        player_id = ? AND clue_id = ?";
    
    $query_insert = "INSERT INTO player_responses (player_id, clue_id,
        response_text) VALUES (?, ?, ?)";
    
    $query_replace = "UPDATE player_responses SET response_text = ?
        WHERE player_id = ? AND clue_id = ?";
    
    foreach($responses as $id => $resp)
    {
        $stmt = $mysqli->prepare($query_count);
        
        if(!$stmt)
        {
            $errortext = "Could not prepare statement.";
            break;
        }
        
        $stmt->bind_param("ii", $player_id, $category_id);
        
        $stmt->execute();
        
        $stmt->bind_result($response_exists);
        $stmt->fetch();
        $stmt->close();
        if($response_exists)
        {
            $stmt = $mysqli->prepare($query_replace);
            $stmt->bind_param("sii", $resp, $player_id, $id);
        }
        else
        {
            $stmt = $mysqli->prepare($query_insert);
            $stmt->bind_param("iis", $player_id, $id, $resp);
        }
        $stmt->execute();
        if(!$stmt->affected_rows)
        {
            if(!isset($errortext))
            {
                $errortext = "";
            }
            $errortext .= "Response for question id $id could not be placed
                into the database.";
        }
        $stmt->close();
    }
}while(false);

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
    echo "Error adding responses";
}
else
{
    echo "Responses added successfully";
}
?>
</title>
</head>
<body>
<?php
if(isset($errortext))
{
    printf('<p class="error">%s</p>', $errortext);
}
else
{
    echo "<p>Responses added successfully!</p>";
}
copyright();
?>
</body>
</html>


