{include file=heady.tpl comingfrom=$comingfrom}
{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{*include_php file="linkbar.php"*} {* tyam - this way we have a list of boards to quicklink to *}
{* no pages for thread view *}
<br clear="all" />
{it->head assign="thread"}{* Workaround *}
{it->getreplies assign="posts"}
{include file=whereami.tpl comingfrom=$comingfrom}
[<a href="{$THurl}{if !$THuserewrite}drydock.php?b={/if}{$binfo.folder}">Return</a>]
{include file=postblock.tpl comingfrom=$comingfrom}
<hr />

{* Beginning of form for post deletion/reporting/whatever else we might want in the future *}
<form target="_blank" action="misc.php" method="POST" id="delform">
<input type="hidden" name="board" value="{$binfo.folder}" />

<table style="width: 100%;">
{*		we don't need to get each thread here so we don't need a 
		for each for it here, but here is a place keeper for numbering *}
{include file=viewblock.tpl comingfrom=$comingfrom}
</table><br style="clear: both;"><hr>
{* we're out on this out, so let's clean it
	we don't need the loop for multithread	*}
{literal}
<script type="text/javascript" defer="defer">
	<!--
		function visfile(thisone)
		{
			if (document.getElementById("file"+(thisone+1)))
			{
				document.getElementById("file"+(thisone+1)).style.display="block";
			}
		}
	-->
</script>
{/literal}

{* End of form for post deletion/reporting/whatever else *}
<div style="text-align:right">
Password: <input type="password" name="password" value=""><br />
<input type="submit" name="report" value="Report"><input type="submit" name="delete" value="Delete">
</div>
</form>


{literal}
<script type="text/javascript">
	<!--
		var n=readCookie("{/literal}{$THcookieid}{literal}-name");
		var t=readCookie("{/literal}{$THcookieid}{literal}-tpass");
		var l=readCookie("{/literal}{$THcookieid}{literal}-link");
		var p=readCookie("{/literal}{$THcookieid}{literal}-password");
		
		if (n!=null)
		{
			document.forms['postform'].elements['nombre'].value=unescape(n).replace(/\+/g," ");
        }
		if (t!=null)
		{
			document.forms['postform'].elements['tpass'].value=unescape(t).replace(/\+/g," ");
        }
		if (l!=null)
		{
			document.forms['postform'].elements['link'].value=unescape(l).replace(/\+/g," ");
		}
		
		if (p!= null)
		{
			document.forms['postform'].elements['password'].value=unescape(p).replace(/\+/g," ");
			document.forms['delform'].elements['password'].value=unescape(p).replace(/\+/g," ");
		}
		else
		{
			var chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			var pass='';

			for(var i=0;i<8;i++)
			{
				var rnd=Math.floor(Math.random()*chars.length);
				pass+=chars.substring(rnd,rnd+1);
			}

			document.forms['postform'].elements['password'].value=pass;
			document.forms['delform'].elements['password'].value=pass;			
		}
	//-->
</script>
{/literal}

{*include_php file="linkbar.php"*} {* tyam - gives us quicklinks *}

