{include file='head.tpl' comingfrom=$comingfrom}

{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{*phplinkbar*} {* tyam - this way we have a list of boards to quicklink to - take the asterisks out if you want them*}
{include file='pages.tpl'}
<br style="clear: both;" />
		<div class="centered">{phpbanners}
		<div class="pgtitle">
			{$binfo.name}
		</div>
		</div>
<br />
<a name="tlist" /></a>
<div class="postbox">
<div style="padding-right: 5px; margin-bottom: 5px;">Threads on this page:
    <div class="tlist">
{it->getsthreads assign="bthreads"}
{counter name="upto" assign="upto" start="0"}
{foreach from=$bthreads item=th}
{counter name="upto"}
<a href="#{$th.globalid}">{$th.globalid}: {if $th.title}{$th.title|escape:'html':'UTF-8'}{else}No Subject{/if} ({$th.rcount+1})</a>&nbsp;&nbsp;
{foreachelse}
(no threads)
{/foreach}
	</div>
	<div style="text-align: right;">
		<a href="#newthread">New thread</a>&nbsp;&nbsp;
		<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/tlist{else}drydock.php?b={$binfo.folder}&amp;tlist{/if}">All threads</a>
	</div>
</div>
</div>
{it->getsthreads assign="sthreads"}
{foreach from=$sthreads item=thread}
<div class="box">
    {include file='viewblock.tpl' comingfrom=$comingfrom}
</div>
{foreachelse}
<div class="medtitle">(No threads on this board)</div>  
{/foreach}{*For each thread*}
<div class="box">
<a name="newthread" /></a>
<div class="postbox">
{if $binfo.tlock}Only moderators and administrators are allowed to create new threads.<br />{/if}
{include file='postblock.tpl' comingfrom=$comingfrom}
</div>
</div>
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
