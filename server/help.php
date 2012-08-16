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

/* USAGE OF THIS PAGE: id is a get parameter that represents
the id number of the category to be displayed.  If id is not specified,
it is an error.  Format is optional; if format=bbcode, the clues
are displayed in a text area for copypasting.  If format=normal
or is not given, then the usual HTML format is used.*/

require_once('common.inc');
startpage(UNRESTRICTED);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/> 
<link rel="stylesheet" type="text/css" href="theme.css" />
<link rel="icon" type="image/png" href="shcicon.png" />
<title>Help</title>
</head>
<body>
<h1>Help</h1>
<h2 id="grading">Grading</h2>
<h3 id="how-grading-works">How grading works</h3>
<p>Each question has a list of pre-defined responses.  Each response is
a <a href="http://en.wikipedia.org/wiki/Regular_expression">
regular expression</a>.  The main points you need to keep in mind for
writing responses are the following:</p>
<ul>
<li>Alphabetic characters have their normal meaning.</li>
<li>Parentheses group multiple characters or elements as a single element</li>
<li>A question mark (<kbd>?</kbd>) indicates that the preceding character or
parenthesized group may appear zero or one times.  That is, it is optional.
Use this to indicate additional correct information that is not required for
a correct answer but may be provided.</li>
<li>A vertical bar (<kbd>|</kbd>) separates consecutive elements in a
list of elements of which exactly one must appear.</li>
<li>Do not include the <kbd>^</kbd> or <kbd>$</kbd> symbols to mark
the beginning and end of regular expressions; SHC manager automatically
requires the entire player response to match the entire given response pattern
for an answer to be considered correct.</li>
<li>The period <kbd>.</kbd> is a wildcard character that matches any
character; to match a period, you must use the <kbd>\.</kbd> escape sequence.
<li>All regular expressions are case-insensitive.</li>
<li>Note that all punctuation marks (except for periods preceding a digit)
and leading and trailing whitespace are stripped from player responses,
and multiple spaces and other whitespace characters between words of a response
are replaced with a single space.  Therefore, do not include such stripped
punctuation marks as part of an response.</li>
</ul>
<p>Here are some examples of regular expressions:</p>
<ul>
<li><kbd>(G(eorge)? )?Washington</kbd> is a regular expression that matches
responses of <kbd>Washington</kbd>, <kbd>G Washington</kbd>, and
<kbd>George Washington</kbd>.  It does not match <kbd>Denzel Washington</kbd>,
as <kbd>Denzel</kbd> is not an allowed optional part of the answer.</li>
<li><kbd>Zero|0</kbd> matches <kbd>Zero</kbd> or <kbd>0</kbd></li>
</ul>
<p>For each player response, SHC manager tries to match the player
response against each response for the corresponding clue.  If it matches
at least one correct response, the player response is counted as correct.
If it does not match at least one correct response, but it matches at least
one incorrect response, the player response is counted as incorrect.
If the player response does not match any given responses for that clue,
the response is not scored and is flagged as ungraded.</p>
<p>Completely blank player responses and non-existent player responses
are treated as "CLAM"s:
they are not scored and they are not flagged as ungraded responses.</p>
<h3 id="How to enter responses">How to enter responses</h3>
Click on "View/edit responses", and then select the player and category whose
responses you want to edit.  Enter each player response, as given on the forum,
beside the corresponding clue.  Leave out extraneous text that is not part
of a response (such as editorializing, phrasing in question form, etc.).
If the player chooses to "CLAM" in response to a clue,
leave the response field blank.
<h3 id="How to grade responses">How to grade responses</h3>
<p>To grade the responses to a clue, click on the (grade) link next to
any clue in the category view, or click on (edit responses) in the
clue editor for that clue.</p>
<p>The grading window contains all responses, right or wrong, that are listed
with the question.  Additionally, each ungraded response appears below
the given responses.</p>
<p>Use the radio buttons to change whether each response is to be considered
right or wrong.</p>
<?php footer();?>
</body>
</html>
