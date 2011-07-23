{* Due to Smarty annoyance, must include entire header here, without the menu part. *}
{* This is just a thing for small little popups and intermediate pages,
   thus with heavily stripped-down HTML which can have four things set - $title, 
   $text, $redirectURL, and $timeout.  If $redirectURL is set and $timeout is > 0,
   it will redirect to the provided URL after $timeout seconds.  If the redirect
   URL is not set, It will close after $timeout seconds if $timeout is > 0.  
   Caching is also disabled for it. *}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
{if $title == ""}
<title>{$THname}</title>
{else}
<title>{$title}</title>
{/if}
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

{if $timeout > 0}
	{if $redirectURL != ""} {* Possibly redirect *}
		<meta http-equiv="refresh" content="{$timeout};URL={$redirectURL}"> 
	{else} {* No redirect, just close *}
		<script language="javascript">
		<!--
		setTimeout("self.close();",{$timeout*1000})
		//-->
		</script> 
	{/if}
{/if}

<style type="text/css">{literal}
body { margin: 0; padding: 8px; margin-bottom: auto; }
{/literal}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />
</head>

{$text}

{if $timeout > 0}
	{if $redirectURL != ""} {* Possibly redirect *}
	<br>This window will redirect in {$timeout} seconds.  Click <a href="{$redirectURL}">here</a> to immediately go to your destination.
	{else}
	<br>This window will close in {$timeout} seconds.
	{/if}
{/if}

</body>
</html>