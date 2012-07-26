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

if(!isset($_POST['username']))
{
    $errortext = "Username not provided.";
}
elseif(!isset($_POST['password']))
{
    $errortext = "Password not provided.";
}
else
{
    $success = auth($_POST['username'], $_POST['password']);
    if(!$success)
    {
        $errortext = "The username/password combination is invalid.";
    }
    else
    {
        $_SESSION['username'] = $_POST['username'];
        $logged_in_username = $_SESSION['username'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title><?php
if(isset($errortext))
{
    echo "Login failed";
}
else
{
    echo "Login succeeded";
}
?></title>
</head>
<body>
<?php
if(isset($errortext))
{
    echo "<h1>Login failed</h1>";
    displayError($errortext);
}
else
{
    echo "<h1>Login successful!</h1>";
    printf('<p>You are now logged in as <span class="username">%s</span>.</p>',
        $_SESSION['username']);
}
footer();
?>
</body>
</html>
