{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Recent Images</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Recent Images
{if $board_folder != null}
&#8212; showing only images in {$board_folder}
{/if}
        </div>
	<br />
	
{* Show links to forward/back pages if necessary *}
{if $total_count > 40}
	<table width=100%><tr>
		{if $offset > 0}
			<td align=left width=50%><a href="recentpics.php?offset={$offsetback}{$boardlink}">&lt;&lt;</a></td>
		{else} 
			echo '<td align=left width=50%>&lt;&lt;</td>';
		{/if}
	
		{if $beginning > 0}
			<td align=right width=50%><a href="recentpics.php?offset={$offsetfwd}{$boardlink}">&gt;&gt;</a></td>
		{else} 
			<td align=right width=50%>&gt;&gt;</td>
		{/if}
	</tr></table><hr>
{/if}

{* Show filtering links *}

{if $isthread == true}
Filter by board: <select name="board">
<option value="" onclick="window.location='window.location='recentposts.php'">All boards</option>
{foreach from=$boards item=board}
	<option value="{$board.folder}" 
		onclick="window.location='recentpics.php?board={$board.folder}'">
		/{$board.folder}/
	</option>
{/foreach}
</select>
<hr>
	
{* Show pictures *}
<div align="center">
{if $imgs!=null}
	<table BORDER="0" CELLPADDING="5"><tr>
	{counter name="imgcount" assign="imgcount" start="0"} {* start a new table row after every 5th picture *}
	{foreach from=$imgs item=thisimg}
		{if ($imgcount mod 5 == 4)}</tr><tr>{/if}
		<td>
			<a class=info href="images/{$thisimage.id}/{$thisimage.name}">
			{if $thisimage.hash != "deleted" }
				<img src="images/{$thisimage.id}/{$thisimage.tname}" border=0>
			{else}
				<img src="{$THurl}static/file_deleted.png" alt="File Deleted" border=0 />
			{/if}
			</a><br />

			{ * Check if this matches an ID *}
			{if $thisimage.reply_globalid != 0}
			
				{if $thisimage.reply_board > 0}

					{assign var=boardz value=`{$boards[$thisimage.reply_board].folder}`} {* for brevity's sake *}

					{if $boardz != false}
						/{$boardz}/
					{/if}

					<a 
					{if $THuserewrite == true}
						href="{$THurl}board={$boardz}/edit/$thisimage.reply_globalid"
					{else} 
						href="{$THurl}editpost.php?board={$boardz}&post=$thisimage.reply_globalid"
					{/if}
					>edit</a>]
					
				{else} {* No board found.  Weird. *}
					No board (UID {$thisimage.reply_id})
				{/if}
			
			{elseif $thisimage.thread_globalid != 0}
			
				{if $thisimage.thread_board > 0}

					{assign var=boardz value=`{$boards[$thisimage.thread_board].folder}`} {* for brevity's sake *}

					{if $boardz != false}
						/{$boardz}/
					{/if}

					<a 
					{if $THuserewrite == true}
						href="{$THurl}board={$boardz}/edit/{$thisimage.thread_globalid}"
					{else} 
						href="{$THurl}editpost.php?board={$boardz}&post={$thisimage.thread_globalid}"
					{/if}
					>edit</a>]
					
				{else} {* No board found.  Weird. *}
					No board (UID {$thisimage.thread_id})
				{/if}

			{else}
				[edit]
			{/if}
			
			<br />
			(<i>{$thisimage.fsize} K, {$thisimage.width}x{$thisimage.height}</i>)
			
			{if $thisimage.anim > 0} {* Animated flag *}
				(<i>A</i>)
			{/if}
		</td>
	{counter name="imgcount"}
	{/foreach}
	</tr></table>
{else}
No images found!<br />
{/if}
</div>

{* Show links to forward/back pages if necessary (again) *}
{if $total_count > 40}
	<table width=100%><tr>
		{if $offset > 0}
			<td align=left width=50%><a href="recentpics.php?offset={$offsetback}{$boardlink}">&lt;&lt;</a></td>
		{else} 
			echo '<td align=left width=50%>&lt;&lt;</td>';
		{/if}
	
		{if $beginning > 0}
			<td align=right width=50%><a href="recentpics.php?offset={$offsetfwd}{$boardlink}">&gt;&gt;</a></td>
		{else} 
			<td align=right width=50%>&gt;&gt;</td>
		{/if}
	</tr></table><hr>
{/if}

</div>
{include file=admin-foot.tpl}