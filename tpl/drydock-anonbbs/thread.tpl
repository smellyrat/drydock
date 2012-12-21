{include file='head.tpl'}
{it->binfo assign="binfo"}
{it->getreplies assign="posts"}
{include file="viewblock.tpl"}
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
