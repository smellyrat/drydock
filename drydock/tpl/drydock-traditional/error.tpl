{*Due to Smarty annoyance, must include entire header here, without the menu part.*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>{$THname} &#8212; Error!</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

<style type="text/css">{literal}
body { margin: 0; padding: 8px; margin-bottom: auto; }
blockquote blockquote { margin-left: 0em }
form { margin-bottom: 0px }
.postarea { text-align: center }
.postarea table { margin: 0px auto; text-align: left }
.thumb { border: none; float: left; margin: 2px 20px }
.nothumb { float: left; background: #eee; border: 2px dashed #aaa; text-align: center; margin: 2px 20px; padding: 1em 0.5em 1em 0.5em; }
.reply blockquote, blockquote :last-child { margin-bottom: 0em }
.reflink a { color: inherit; text-decoration: none }
.reply .filesize { margin-left: 20px }
.userdelete { float: right; text-align: center; white-space: nowrap }
.replypage .replylink { display: none }{/literal}
</style>

<link rel="stylesheet" type="text/css" href="{$THtplurl}Futaba.css" title="Futaba-ish Stylesheet" />

<script type="text/javascript">var style_cookie="Futaba-ish Stylesheet";</script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="{$THtplurl}js.js"></script>
</head>


<h1 style="text-align: center">
{if $error=="ADbanned"}
b&
{elseif $error=="ADbadpass"}
Incorrect admin pass.
{elseif $error=="ADdeadsess"}
Admin session expired
{elseif $error=="ADdupeboardids"}
Multiple ID numbers assigned
{elseif $error=="ADbanbadip"}
Invalid IP
{elseif $error=="ADnowrconfig"}
chmod the config files
{elseif $error=="DBcode"}
db error
{elseif $error=="DBsel"}
db connect okay, no such db
{elseif $error=="POnosubj"}
Please fill in the subject line when you post a new thread.
{elseif $error=="PObanned"}
b&
{elseif $error=="POnonewth"}
Thread Lock is enabled on this board. Only mods and admins can post new threads.
{elseif $error=="POthnopix"}
New threads on this board may not have images.
{elseif $error=="POthmustpix"}
New threads on this board must have at least one image.
{elseif $error=="POthrlocked"}
This thread is locked; you may not reply to it.
{elseif $error=="POboardreplocked"}
Reply Lock is enabled on this board. Only mods and admins can reply to threads.
{elseif $error=="POrepnopix"}
Replies on this board may not have images.
{elseif $error=="POrepmustpix"}
Replies on this board must have at least one image.
{elseif $error=="VCbad"}
You entered an incorrect verification code.
{elseif $error=="POdupeimg"}
You cannot upload an image that has already been uploaded.
{elseif $error=="POmakeimgdir"}
Could not make a directory on the server to store your uploaded images in. This is probably a permissions problem. Please tell an admin about this error.
{elseif $error=="POmoveimg"}
Could not move your uploaded images into an image directory. This is probably a permissions problem. Please tell an admin about this error.
{elseif $error=="ADdbfirst"}
As an admin, you are trying to configure something that first requires the proper configuration of the database.
{elseif $error=="DBcxn"}
A connection to the database could not be made. If the settings were changed recently, they may be incorrect; if they haven't been changed recently, then perhaps the database is down. Please tell an admin about this error.
{else}
{* An error of some sort happened! Sorry, I don't know enough about it to be more specific at this point… *}
{$error}
{/if}
<br /><br />

</h1></body></html>