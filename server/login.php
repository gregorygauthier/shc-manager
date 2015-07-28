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
$isloggedin = startpage(UNRESTRICTED);
if($isloggedin)
{
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Login</title>
</head>
<body>
<h1>Already logged in</h1>
HTML;
printf('<p>You are already logged in as <span class="username">%s</span>. ',
    $logged_in_username);
echo 'If you want to log in with a different account, '.
    'please <a href="logout.php">log out</a>.</p>';
}
else
{
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Login</title>
</head>
<body>
<h1>Login</h1>
<h2>Log in to existing account</h2>
<form action="auth.php" method="POST">
<label for="usernamefield">Username:</label>
<input type="text" name="username" maxlength="30" id="usernamefield" />
<label for="passwordfield">Password:</label>
<input type="password" name="password" id="passwordfield" />
<button type="submit">Log in</button>
</form>
<h2>Forgotten password</h2>
<p>If you forgot your password, enter your username below and you will be
given a new password by e-mail.</p>
<form action="forgotten.php" method="POST">
<label for="forgottenusernamefield">Username:</label>
<input type="text" name="username" maxlength="255" id="forgottenusernamefield"
/>
<button type="submit">Send new password</button>
</form>
<h2>Don't have an account?</h2>
<p>You can <a href="new_player.php">create an account</a>.
You do not need an account to view revealed Summer Hiatus Challenge clues and
up-to-date standings on this website.</p>
HTML;
}
footer();?>
</body>
</html>
