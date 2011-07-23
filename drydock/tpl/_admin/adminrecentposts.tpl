{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Recent Posts</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Recent Posts
{if $board_folder != null}
&#8212; showing only posts in {$board_folder}
{/if}
        </div>
	<br />
	
{* Show links to forward/back pages if necessary *}
{if $total_count > 20}
	<table style="width: 100%;"><tr>
		{if $offset > 0}
			{if $isthread == 1}
				<td class="lefthalf"><a href="recentposts.php?offset={$offsetback}{$boardlink}">&lt;&lt;</a></td>	
			{else}
				<td class="lefthalf"><a href="recentposts.php?type=posts&offset={$offsetback}{$boardlink}">&lt;&lt;</a></td>
			{/if}
		{else} 
			<td class="lefthalf">&lt;&lt;</td>
		{/if}
	
		{if $beginning > 0}
			{if $isthread == 1}
				<td class="righthalf"><a href="recentposts.php?offset={$offsetfwd}{$boardlink}">&gt;&gt;</a></td>
			{else}
				<td class="righthalf"><a href="recentposts.php?type=posts&offset={$offsetfwd}{$boardlink}">&gt;&gt;</a></td>
			{/if}		
		{else} 
			<td class="righthalf">&gt;&gt;</td>
		{/if}
	</tr></table><hr>
{/if}

{* Show filtering links *}
{if $showhidden == true}
	{if $isthread == true}
		- <a href="recentposts.php?type=posts&showhidden=1">Pull posts</a> + Pull threads + <a href="recentposts.php">Show all posts</a> - Filter by board: 
		<select name="board">
		<option value="" onclick="window.location='recentposts.php?showhidden=1'">All boards</option>
		{foreach from=$boards item=board}
			<option value="{$board.folder}" {if $board_folder==$board.folder}selected{/if} 
				onclick="window.location='recentposts.php?showhidden=1&board={$board.folder}'">
				/{$board.folder}/
			</option>
		{/foreach}
	{else}
		- Pull posts + <a href="recentposts.php?showhidden=1">Pull threads</a> + <a href="recentposts.php?type=posts">Show all posts</a> - Filter by board: 
		<select name="board">
		<option value="" onclick="window.location='recentposts.php?type=posts&showhidden=1'">All boards</option>
		{foreach from=$boards item=board}
			<option value="{$board.folder}" {if $board_folder==$board.folder}selected{/if} 
				onclick="window.location='recentposts.php?type=posts&showhidden=1&board={$board.folder}'">
				/{$board.folder}/
			</option>
		{/foreach}
	{/if}
{else}
	{if $isthread == true}
		- <a href="recentposts.php?type=posts">Pull posts</a> + Pull threads + <a href="recentposts.php?showhidden=1">Show only hidden</a> - Filter by board: 
		<select name="board">
		<option value="" onclick="window.location='recentposts.php'">All boards</option>
		{foreach from=$boards item=board}
			<option value="{$board.folder}" {if $board_folder==$board.folder}selected{/if} 
				onclick="window.location='recentposts.php?board={$board.folder}'">
				/{$board.folder}/
			</option>
		{/foreach}
	{else}
		- Pull posts + <a href="recentposts.php">Pull threads</a> + <a href="recentposts.php?type=posts&showhidden=1">Show only hidden</a> - Filter by board: 
		<select name="board">
		<option value="" onclick="window.location='recentposts.php?type=posts'">All boards</option>
		{foreach from=$boards item=board}
			<option value="{$board.folder}" {if $board_folder==$board.folder}selected{/if} 
				onclick="window.location='recentposts.php?type=posts&board={$board.folder}'">
				/{$board.folder}/
			</option>
		{/foreach}		
	{/if}
{/if}

</select> {* Close off the select tag.  Heh.*}
<hr>
	
{* Show posts *}
    <div>
    {if $posts!=null}
		{foreach from=$posts item=post}
			{assign var=boardz value=$boards[$post.board].folder} {* for brevity's sake *}
			
			<div>			
			{* Link to thread *}
			{if $post.thread != 0} {* This is a reply *}	
				{if $boardz != false }				
					Post {$post.globalid} in thread {$post.thread_globalid} on /{$boardz}/
					
					{if $THuserewrite == true}
						[<a href="{$THurl}{$boardz}/thread/{$post.thread_globalid}#{$post.globalid}">Thread</a>]
					{else} 
						[<a href="{$THurl}drydock.php?b={$boardz}&amp;i={$post.thread_globalid}#{$post.globalid}">Thread</a>]
					{/if}
				{else} {* No board found, weird *}		
					Post {$post.globalid} in [<a href="{$THurl}drydock.php?t={$post.thread}">Thread</a>]				
				{/if}			
			{else} 
				{* thread *}
				{if $post.id != 0 } 
					Post {$post.globalid} in /{$boardz}/ 
					{if $THuserewrite == true}
						[<a href="{$THurl}{$boardz}/thread/{$post.globalid}">Thread</a>]
					{else} 
						[<a href="{$THurl}drydock.php?b={$boardz}&amp;i={$post.globalid}">Thread</a>]
					{/if}
				{else}
					[Thread]
				{/if}			
			{/if}
			
			
			{* Show edit link *}

			{if $THuserewrite == true}
				[<a href="{$THurl}{$boardz}/edit/{$post.globalid}">Edit</a>]
			{else} 
				[<a href="{$THurl}editpost.php?board={$boardz}&post={$post.globalid}">Edit</a>]
			{/if}
		
			
			{* Show quick-moderation panel *}

			<form target="_blank" action="misc.php" method="POST">
			[<a onclick="$('#quickmod{$post.id}').toggle();">Quickmod</a>]
			<span id="quickmod{$post.id}" style="display: none;" class="modblock">
					<input type="hidden" name="board" value="{$boardz}" />
					<input type="hidden" name="post" value="{$post.globalid}" />
					<input type="checkbox" name="doban" value="1"> Ban poster<br />
					Reason: <input type="text" name="banreason"> <br />
					Duration: <input type="text" name="duration" value="0"><br />
					<input type="checkbox" name="del" value="1"> Delete this post (requires admin)<br />
					<input type="submit" name="quickmod" value="quickmod">
			</span>
			</form>

			{* Show quicker-mod panel *}
			
			<form target="_blank" action="misc.php" method="POST">
			[<a href="misc.php?quicker=1&board={$boardz}&post={$post.globalid}">X</a>]
			
			{* Mark if a post has already been moderated *}
			{if $post.unvisibletime != 0}
			<i><b>Previously moderated</b></i>
			{/if}
			
			</div> {* end of ID, board, moderation links *}
				
			
			{* Show stuff like name, link field, etc *}
			<div>
			
			{if $post.link}<a href="{$post.link}">{/if}

			{if !$post.trip}
				{if !$post.name}
						<span class="postername">Anonymous</span>
				{else}
						<span class="postername">{$post.name|escape:'html':'UTF-8'}</span>
				{/if}
			{else}
				<span class="postername">{$post.name|escape:'html':'UTF-8'|default:""}</span><span class="postertrip">!{$post.trip}</span>
			{/if}
			<span class="timedate">{$post.time|date_format:$THdatetimestring}</label></span>
			
			{if $post.link}</a>{/if}
			
			{if $post.visible == 0}
				<img src="{$THurl}static/invisible.png" alt="INVISIBLE" border="0">
			{/if}
						
			{if $post.title != ''}<br /><span class="filetitle">{$post.title}</span>{/if}
			
			</div> {* end of name, link field, etc *}
			
			{* Show images *}
			{if $post.images}	
				<table>
				
				{counter name="imgcount" assign="imgcount" start="0"}
				<tr>
				{foreach from=$post.images item=image}
					<td>
					<div class="filesize">
						File: <a href="images/{$post.imgidx}/{$image.name}" target="_blank">{$image.name}</a><br />
						{* Display file size, dimensions, and possible an a (for animated) *}
						(<em>{$image.fsize} K, {$image.width}x{$image.height} {if $image.anim}a{/if}</em>)
					</div>
					File: <a class="info" href="images/{$post.imgidx}/{$image.name}" target="_blank">
						{if $image.hash != "deleted"}
							<img src="images/{$post.imgidx}/{$image.tname}" width="{$image.twidth}" 
								height="{$image.theight}" alt="{$image.name}" class="thumb" />
						{else}
							<img src="{$THurl}static/file_deleted.png" alt="File deleted" width="100" height="16" class="thumb" />
						{/if}
						</a>
						</td>
				{if ($imgcount mod 4 == 3)}</tr><tr>{/if}
				{counter name="imgcount"}
				{/foreach}
				
				</tr></table>
			{/if}
			
			{* Split rest of post from post body *}
			<div>
				<blockquote>
					{$post.body|nl2br}
				</blockquote>
			</div>
						
		{/foreach}
	{else}
	No posts match these filters.<br />
	{/if}
	</div>

{* Show links to forward/back pages if necessary (again) *}
{if $total_count > 20}
	<table style="width: 100%;"><tr>
		{if $offset > 0}
			{if $isthread == 1}
				<td class="lefthalf"><a href="recentposts.php?offset={$offsetback}{$boardlink}">&lt;&lt;</a></td>	
			{else}
				<td class="lefthalf"><a href="recentposts.php?type=posts&offset={$offsetback}{$boardlink}">&lt;&lt;</a></td>
			{/if}
		{else} 
			<td class="lefthalf">&lt;&lt;</td>
		{/if}
	
		{if $beginning > 0}
			{if $isthread == 1}
				<td class="righthalf"><a href="recentposts.php?offset={$offsetfwd}{$boardlink}">&gt;&gt;</a></td>
			{else}
				<td class="righthalf"><a href="recentposts.php?type=posts&offset={$offsetfwd}{$boardlink}">&gt;&gt;</a></td>
			{/if}		
		{else} 
			<td class="righthalf">&gt;&gt;</td>
		{/if}
	</tr></table><hr>
{/if}	

</div>
{include file=admin-foot.tpl}
