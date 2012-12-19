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
<form target="_blank" action="misc.php" method="post">
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
