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
startpage(ADMIN);

do
{
    if(!isset($_POST['username']))
    {
        $errortext = "No username specified.";
        break;
    }
    $username = $_POST['username'];
    if(!isset($_POST['email']))
    {
        $errortext = "No e-mail specified.";
        break;
    }
    $email = $_POST['email'];
    $mysqli = connect_mysql();
    if(!$mysqli)
    {
        $errortext = "Could not connect to database.";
        break;
    }
    $mysqli->query("USE $mysql_dbname;");
    $query = "SELECT COUNT(*) FROM users WHERE username=?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement.";
        break;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if($count > 0)
    {
        $errortext = "Username already exists.";
        break;
    }
    $query = "SELECT COUNT(*) FROM users WHERE email=?";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement.";
        break;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    if($count > 0)
    {
        $errortext = "An account already exists for this e-mail address.";
        break;
    }
    $password = '';
    $password_length = 15;
    for($i = 0; $i < $password_length; $i++)
    {
        $random = rand(0, 61);
        if($random < 10)
        {
            $password .= chr(48 + $random);
        }
        elseif($random < 36)
        {
            $password .= chr((65 - 10) + $random);
        }
        else
        {
            $password .= chr((97 - 36) + $random);
        }
    }
    $query = "INSERT INTO users (username, hashed_password, email)
        VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    if(!$stmt)
    {
        $errortext = "Could not prepare statement";
        break;
    }
    $hash = hash('sha512', $password.$salt);
    $stmt->bind_param('sss', $username, $hash, $email);
    $stmt->execute();
    if($stmt->errno)
    {
        $errortext = "There was an error inserting the new user into the
            database.";
        break;
    }
    $message = <<<MESSAGE
An account has been created for you at SHC Manager.

Your username is %s
Your password is %s

You can log in to your account at %s

Please keep this e-mail in a safe place.  Because we do not store
unencrypted passwords, we cannot retrieve your password if you
forget it; however, should you forget your password, you can
request that a new password be created for you.

Thank you for helping run the Summer Hiatus Challenge.

Sincerely,

The SHC Manager team
MESSAGE;
    $message = sprintf($message, $username, $password, $site_url.'/login.php');
    mail($email, "Account information at SHC Manager", $message,
            "From:$admin_address\r\n");
} while (false);
?>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>
<?php
if(isset($errortext))
{
    echo 'Error creating account.';
}
else
{
    echo 'Account created successfully';
}
?></title>
</head>
<body>
<?php
if(isset($errortext))
{
    echo '<h1>Error creating account.</h1>';
    displayError($errortext);
}
else
{
    echo '<h1>Account created successfully</h1>';
    printf('<p>Account <span class="username">%s</span> has been '.
        'successfully created!</p>', $username);
}
footer();
?>
</body>
</html>
