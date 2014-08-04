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

$responses = array();

foreach($_POST as $key => $value)
{
    if(preg_match('/^response(\d+)/', $key, $matches))
    {
        $tmp = $value;
        // Here are the rules for storing answers:
        // All non-alphanumeric characters are removed.
        // All whitespace is converted to a single space.
        // All leading and trailing whitespace is removed
        // The only exceptions is as follows:
        // A decimal point in a numerical expression is preserved
        // To be precise, this refers to a decimal point that is
        // NOT followed by a numerical character
        //
        // Examples:
        // Mr. Jones -> Mr Jones
        // $1000. -> 1000
        // .14159 -> .14159
        $tmp = preg_replace('/-/', ' ', $tmp);
        $tmp = preg_replace('/[^A-Za-z0-9.\/\s]/', '', $tmp);
        $tmp = preg_replace('/\.([^0-9]|$)/', '$1', $tmp);
        $tmp = preg_replace('/\s+/', ' ', $tmp);
        $tmp = trim($tmp);
        $responses[$matches[1]] = $tmp;
    }
    else if($key == 'playerid')
    {
        $player_id = $value;
    }
}

try
{
    if(is_null($player_id))
    {
        throw new Exception("No player id specified");
    }
    foreach($responses as $id => $resp)
    {
        Database::add_player_response($player_id, $id, $resp);
    }
    $title = "Responses successfully updated";
    $message = "<p>The responses were successfully updated!</p>";
}
catch (Exception $e)
{
    $title = "Error adding player response";
    $message = sprintf("<p class=\"error\">Error adding player response: %s</p>",
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


