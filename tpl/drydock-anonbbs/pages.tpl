{it->binfo assign=binfo}
{* tyam - page links for top and bottom of page *}
{it->getallthreads assign="bthreads"}
{assign value=$GET.g|default:0 var=thispg}
<table border="0"><tbody><tr><td>
        {if $thispg!=0}<a href="{$THurl}{$binfo.folder}/{$thispg-1}">Previous</a>{else}Previous{/if}
    </td><td>
        {counter name="upto" assign="upto" start="0"}{counter name="page" assign="page" start="0"}
        {foreach from=$bthreads item=th}
            {counter name="upto"}{if $upto==$binfo.perpg}{counter name="page"}{counter name="upto" assign="upto" start="0"}
{if $page-1!=$thispg}[<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/{else}drydock.php?b={$binfo.folder}&amp;g={/if}{$page-1}">{$page-1}</a>]{else}[{$page-1}]{/if}
{/if}{/foreach}{if $page!=$thispg}[<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/{else}drydock.php?b={$binfo.folder}&amp;g={/if}{$page}">{$page}</a>]{else}[{$page}]{/if}
</td><td>
{if $thispg!=$page}<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/{else}drydock.php?b={$binfo.folder}&amp;g={/if}{$thispg+1}">Next</a>{else}Next{/if}
</td></tr></tbody></table>
