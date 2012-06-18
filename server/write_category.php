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
$text_rows = 3;
$clue_cols = 60;
$response_cols = 40;
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Create category</title>
</head>
</html>
<body>
<form action="add_category.php" method="post">
<table>
<tr>
<td>Category name:</td>
<td><input type="text" maxlength="100" name="categoryname" /><br /></td>
</tr>
<tr>
<td>Optional explanatory text:</td>
<td><textarea 
rows="<?php echo $text_rows;?>" cols="<?php echo $clue_cols;?>"
name="explanatory"></textarea></td>
</tr>
</table>
<table>
<tr>
<th></th><th style="width: 60%">Clue</th><th style="width: 40%">Response</th>
</tr>
<tr>
<td>1.</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $clue_cols;?>" name="clue1">
</textarea>
</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $response_cols;?>"
name="response1">
</textarea>
</td>
</tr>
<tr>
<td>2.</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $clue_cols;?>" name="clue2">
</textarea>
</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $response_cols;?>"
name="response2">
</textarea>
</td>
</tr>
<tr>
<td>3.</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $clue_cols;?>" name="clue3">
</textarea>
</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $response_cols;?>"
name="response3">
</textarea>
</td>
</tr>
<tr>
<td>4.</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $clue_cols;?>" name="clue4">
</textarea>
</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $response_cols;?>"
name="response4">
</textarea>
</td>
</tr>
<tr>
<td>5.</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $clue_cols;?>" name="clue5">
</textarea>
</td>
<td class="inputarea">
<textarea class="fullwidthinput"
rows="<?php echo $text_rows;?>" cols="<?php echo $response_cols;?>"
name="response5">
</textarea>
</td>
</tr>
</table>
<button type="submit">Submit</button>
</form>
<?php copyright();?>
</body>
</html>
