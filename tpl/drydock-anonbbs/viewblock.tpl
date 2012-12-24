<div class="postbox">
<a name="{$thread.globalid}" />
<div class="medtitle">
    {if $thread.pin}
        <img src="{$THurl}static/sticky.png" alt="HOLY CRAP STICKY">
    {/if}
    {if $thread.lawk}
        <img src="{$THurl}static/locked.png" alt="LOCKED">
    {/if}
    {if $thread.permasage}
        <img src="{$THurl}static/permasage.png" alt="THIS THREAD SUCKS">
    {/if}

    <a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}">
        {if $binfo.forced_anon != 1}
            {$thread.title|escape:'html':'UTF-8'|default:"No Subject"}
        {else}
            No Subject
        {/if}
    </a> ({$thread.rcount+1})



</div>
<div><span class="medtitle">{$thread.globalid}</span> 

    {if $binfo.forced_anon!=1} {* begin forced_anon *}

        Name: 
    {if $thread.link}<a href="{$thread.link}">{/if}
        {if $thread.name == "CAPCODE"}
            <span class="postername">{$thread.trip|capcode}</span>
    {/if}{* name = capcode? *}
    {if $thread.name != "CAPCODE"}
        {if !$thread.trip}
            {if !$thread.name}
                <span class="postername">{$THdefaultname}</span>
            {else}
                <span class="postername">{$thread.name|escape:'html':'UTF-8'}</span>
            {/if} {* name used? *}
        {else}
            <span class="postername">{$thread.name|escape:'html':'UTF-8'|default:""}</span><span class="postertrip">!{$thread.trip}</span>
        {/if} {* trip used? *}
    {/if} {* name not capcode? *}

    {/if} {* end forced_anon *}
        :
        <span class="timedate">{$thread.time|date_format:$THdatetimestring}</span>
    {if $thread.link}</a>{/if}

[<a href="misc.php?action=report&amp;board={$binfo.folder}&amp;postid={$thread.globalid}" target="_blank">Report</a>]

<a class="jsmod" style="display:none;">{$binfo.folder},{$thread.globalid}</a>

</div>
{if $thread.images}
    <table>
        <tr>
            {counter name="imgcount" assign="imgcount" start="0"}
            {foreach from=$thread.images item=it}
                <td style="text-align: center;">
                    <div class="filesize">File: <a href="{$THurl}images/{$thread.imgidx}/{$it.name}" target="_blank">{$it.name|filetrunc}</a></div>
                    <a class="info" href="{$THurl}images/{$thread.imgidx}/{$it.name}" target="_blank">
                        <img src="{$THurl}images/{$thread.imgidx}/{$it.tname}" width="{$it.twidth}" height="{$it.theight}" alt="{$it.name}" class="thumb" />
                        <span>{$it.fsize} K, {$it.width}x{$it.height}{if $it.anim}, animated{/if}<br />{$it.exif_text}</span>
                    </a>
                </td>
            {if ($imgcount mod 4 == 3)}</tr><tr>{/if}{* tyam - let's avoid more template breaking *}
                {counter name="imgcount"}
                {/foreach}
                </tr>
            </table>
{/if}
{if $comingfrom=="board"} {$post = "" } {/if}
                <div class="postbody"><blockquote><p>
                            {assign value=$thread.body|THtrunc:2000 var=bodey}
                            {if $binfo.id == THnewsboard or $binfo.id == THmodboard or $binfo.filter!=1}
                                {$bodey.text|nl2br|wrapper|quotereply:"$binfo":"$post":"$thread"}
                            {else}
                                {$bodey.text|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
                            {/if}
                {if $bodey.wastruncated}<em><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}#{$post.globalid}" class="ssmed">[more...]</a></em>{/if}
            </p></blockquote>
    </div>
    {if $comingfrom=="board"}
        {if $thread.rcount>$binfo.perth}
            <div class="ssmed"><span class="name">Showing only last {$binfo.perth} {if $binfo.perth==1}reply{else}replies{/if}&rarr;</span></div>
        {/if}
    {/if}

    {if $comingfrom=="board"}
        {assign value=$thread.reps var="location"}
    {else}
        {assign value=$posts var="location"}
    {/if}
    
    {foreach from=$location item=post}
        <div class="sslarge"><span class="medtitle">{$post.globalid}</span> 

            {if $binfo.forced_anon != 1} {* begin forced_anon *}
                Name: 
            {if $post.link}<a href="{$post.link}">{/if}
                {if $post.name == "CAPCODE"}
                    <span class="postername">{$post.trip|capcode}</span>
            {/if}{* name = capcode? *}
            {if $post.name != "CAPCODE"}
                {if !$post.trip}
                    {if !$post.name}
                        <span class="postername">{$THdefaultname}</span>
                    {else}
                        <span class="postername">{$post.name|escape:'html':'UTF-8'}</span>
                    {/if} {* name used? *}
                {else}
                    <span class="postername">{$post.name|escape:'html':'UTF-8'|default:""}</span><span class="postertrip">!{$post.trip}</span>
                {/if} {* trip used? *}
            {/if} {* name not capcode? *}

            {/if} {* end forced_anon *}
                :
                <span class="timedate">{$post.time|date_format:$THdatetimestring}</span>
            {if $post.link}</a>{/if}

        [<a href="misc.php?action=report&amp;board={$binfo.folder}&amp;postid={$post.globalid}" target="_blank">Report</a>]

        <a class="jsmod" style="display:none;">{$binfo.folder},{$post.globalid}</a>

    </div>
{if $post.images}
				<table>
					<tr>
{counter name="imgcount" assign="imgcount" start="0"}
{foreach from=$post.images item=it} {* each image *}
						<td align=center>
							<div class="filesize">File: <a href="{$THurl}images/{$post.imgidx}/{$it.name}" target="_blank">{$it.name|filetrunc}</a><br /></div>
							<a class="info" href="{$THurl}images/{$post.imgidx}/{$it.name}" target="_blank">
								<img src="{$THurl}images/{$post.imgidx}/{$it.tname}" width="{$it.twidth}" height="{$it.theight}" alt="{$it.name}" class="thumb" />
								<span>{$it.fsize} K, {$it.width}x{$it.height}{if $it.anim}, animated{/if}<br />{$it.exif_text}</span>
							</a>
						</td>
{if ($imgcount == 3)}</tr><tr>{/if} {* tyam - let's avoid more template breaking *}
{counter name="imgcount"}
{/foreach}
					</tr>
				</table>
{/if}

    <div class="postbody">
        <blockquote><p>
                {assign value=$post.body|THtrunc:2000 var=bodey}

                {if $binfo.id == THnewsboard or $binfo.id == THmodboard or $binfo.filter!=1}
                    {$bodey.text|nl2br|wrapper|quotereply:"$binfo":"$post":"$thread"}
                {else}
                    {$bodey.text|filters_new|wrapper|quotereply:"$binfo":"$post":"$thread"}
                {/if}
    {if $bodey.wastruncated}<em><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}#{$post.globalid}" class="ssmed">[more...]</a></em>{/if}
</p></blockquote>
</div>
    {/foreach}{*For each reply*}

        {include file="postblock.tpl" comingfrom="thread"}
        {if $comingfrom=="board"}
            &nbsp;&bull;&nbsp;<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&amp;i={/if}{$thread.globalid}">View thread</a>&nbsp;&bull;&nbsp;
{$thread.rcount+1} total post{if $thread.rcount==1}{else}s{/if}.{if $thread.scount>0} Last <span class="name">{$thread.scount}</span> {if $thread.scount==1}reply{else}replies{/if} shown.{/if}
{/if}
</div>