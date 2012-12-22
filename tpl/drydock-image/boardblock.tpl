{it->getsthreads assign="sthreads"}
{foreach from=$sthreads item=thread}
{include file='viewblock.tpl' comingfrom="board"}
{/foreach}{* multiple threads *}
{include file='pages.tpl'}