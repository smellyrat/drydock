{include file='heady.tpl' comingfrom=$comingfrom}
{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{*phplinkbar*} {* tyam - this way we have a list of boards to quicklink to - take the asterisks out if you want them*}
{include file='pages.tpl'}
<br clear="all" />
{* no workaround *}
{* we don't get replies here *}
{include file='whereami.tpl' comingfrom=$comingfrom}
{* we're at top, no return possible *}
{include file='postblock.tpl' comingfrom=$comingfrom}
<hr />

<table style="width: 100%;">
<tr style="width: 100%;">
<th style="width: 55%; text-align: left;">Subject</th>
<th style="width: 20%; text-align: left;">Poster</th>
<th style="width: 15%; text-align: center;">Timestamp</th>
<th style="width: 10%; text-align: center;">Posts</th>
</tr>
{it->getsthreads assign="sthreads"}
{foreach from=$sthreads item=thread}
{include file=viewblock.tpl comingfrom=$comingfrom}
{/foreach}{* multiple threads *}
</table><br clear=all><hr>
{include file=pages.tpl}
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
