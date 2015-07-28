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
startpage(UNRESTRICTED);

$username = $_POST['name'];
$teen = (isset($_POST["teen"]));
$college = (isset($_POST["college"]));
$atb = (isset($_POST["atb"]));
$rookie = (isset($_POST["rookie"]));
$email = $_POST['email'];
try
{
    if(!$username)
    {
        throw new Exception("no username provided");
    }
    if(!$email)
    {
        throw new Exception("no e-mail provided");
    }
    $return_value = Database::add_player($username, $teen, $college, $atb, $rookie, $email);
    if($return_value)
    {
        $title = "Player successfully added";
        $message = "<p>Player successfully added!</p>";
    }
    else /* adding failed since username was already in use */
    {
        $title = "Error adding player";
        $message = "<p class=\"error\">Error adding player: username already in use</p>";
    }
}
catch (Exception $e)
{
    $title = "Error adding player";
    $message = sprintf("<p class=\"error\">Error adding player: %s</p>",
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
