{* Increment variable *}
{counter assign="pb" name="postblock_increment"}
<div class="pgtitle">
    {if $comingfrom=="board"}
        {if $binfo.tlock}
		Board is locked, no more posts allowed.
        {else}
		New thread
        {/if}
    {elseif $comingfrom=="thread"}
        {if $thread.lawk||$binfo.rlock}
		Thread is locked, no more posts allowed.
        {else}
		Reply
        {/if}
    {/if}
</div>
<br />
{if (($comingfrom=="board" && $binfo.tlock) || ($comingfrom=="thread" && ($thread.lawk||$binfo.rlock)))} 
	{if $modvar==1}	
	    {include file="postform.tpl" comingfrom=$comingfrom}
	{/if}
{else}
    {include file="postform.tpl" comingfrom=$comingfrom}
{/if}







<div class="ssmed">
    <a href="{$THurl}{if $THuserewrite}{$binfo.folder}{else}drydock.php?b={$binfo.folder}{/if}#tlist">Thread List</a>
</div>
<br />