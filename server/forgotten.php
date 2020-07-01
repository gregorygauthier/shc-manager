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

$mysqli = connect_mysql();
$mysqli->query("USE $mysql_dbname;");

if(isset($_POST['email']))
{
    // Check if the e-mail is on file.  If so, send their username to them.
    $query = "SELECT username FROM users WHERE email=? LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $_POST['email']);
    $stmt->execute();
    $stmt->bind_result($username);
    if($stmt->fetch())
    {
        $message = <<<MESSAGE
Thank you for requesting your username at SHC Manager.

Your username is %s.

Sincerely,

The SHC Manager team
MESSAGE;
        $message = sprintf($message, $username);
        mail($_POST['email'], "Username request", $message,
            "From:$admin_address\r\n");
        $result_message = "An e-mail has been sent containing your username.";
    }
    else
    {
        $result_message = sprintf("We couldn't find any account registered
            with the e-mail address %s.", $_POST['email']);
    }
    $stmt->close();
}
elseif(isset($_POST["username"]))
{
    // Check if username is on file; if so, give them new password
    $query = "SELECT email FROM users WHERE username=? LIMIT 1";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->bind_result($email);
    if($stmt->fetch())
    {
        $message = <<<MESSAGE
Thank you for requesting a new password at SHC Manager.

We have created a new password for your account.
Your new password is %s.
Please keep this information secure.

Sincerely,

The SHC Manager team
MESSAGE;
        // Generate a new password for them
        $password = '';
        for($i = 0; $i < 12; $i++)
        {
            $password .= chr(rand(ord('a'), ord('z')));
        }
        $hash = hash('sha512', $password.$salt);
        try {
            Database::reset_password($username, $hash);
            $message = sprintf($message, $password);
            mail($email, "Password reset", $message,
                "From:$admin_address\r\n");
            $result_message = "Your password has been reset.  The new password
                for your account has been sent by e-mail.";
        } catch (Exception $e) {
            $result_message = "An error occured in resetting your password.";
        }
    }
    else
    {
        $result_message = sprintf(
            "We couldn't find any user by the name of %s.",
            $_POST['username']);
    }
    $stmt->close();
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Forgotten login recovery</title>
</head>
<body>
<h1>Forgotten login recovery</h1>
<?php printf('<p>%s</p>', $result_message);
footer();?>
</body>
</html>
