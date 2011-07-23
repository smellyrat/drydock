{include file=head.tpl}
{it->binfo assign="binfo"}
{it->head assign="thread"}{* Workaround *}
{it->getreplies assign="posts"}
<div id="main">
[<a href="{$THurl}{if !$THuserewrite}drydock.php?b={/if}{$binfo.folder}">Return</a>]
{include file="viewblock.tpl"}
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
