{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Moderator Window</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
        	Currently editing 
        	{if $THuserewrite}
            <a href="{$THurl}{$boardname}/thread/{$threadid}#{$postid}">
            {else}
            <a href="{$THurl}drydock.php?b={$boardname}&i={$threadid}#{$postid}">
            {/if}
            p.{$postid}</a> in thread {$threadid} in /{$boardname}/
        </div>
        
    <form action="{$THurl}editpost.php?board={$boardname}&post={$postid}" method="POST">
	<table style="width: 80%;"><tbody>
	
		<tr>
			<td><b>Public ID:</b>{$postid}</td>
			<td><b>Poster time:</b>{$postarray.time|date_format:$THdatetimestring}</td>
		</tr>
		
		<tr>
			<td><b>Private ID:</b>{$postarray.id}</td>
			<td><b>Poster IP:</b><a href="lookups.php?action=iplookup&ip={$ipstring}" target="_blank">{$ipstring}</a></td>
		</tr>
	
		<tr>
			<td><b>Poster name:</b><br /> 
			<input name="name"
			{if $adminpowers == 0} disabled {/if}
			type="text" value="{$postarray.name}"></td>
			
			<td><b>Poster trip:</b><br /> <input name="trip"
			{if $adminpowers == 0} disabled {/if}
			type="text" value="{$postarray.trip}"></td>
		</tr>
		
		<tr>
			<td><b>Poster link:</b><br /> <input name="link"
			{if $adminpowers == 0} disabled {/if}
			type="text" value="{$postarray.link}"></td>
			
			<td><b>Post subject (if OP):</b><br /> <input name="title"
			{if $adminpowers == 0} disabled {/if}
			type="text" value="{$postarray.title}"></td>
		</tr>
	
	</tbody></table>

	<table style="width: 90%;"><tbody>
		<tr>
			<td><b>Post body:</b></td>
			<td><textarea
			{if $adminpowers == 0} disabled {/if}
			name="body" cols="48" rows="6" >{$postarray.body}</textarea></td>
		</tr>
	</tbody></table>
	
	<table style="width: 90%;"><tbody>
		<tr>
			<td>
				<table><tbody>
					<tr>	
						<td><b>Modify thread:</b><br /></td>
						<td><b>Visibility:</b><br /></td>
					</tr>

					<tr>
						<td>
						{if $postarray.lawk}
							<input type="checkbox" name="lawk" checked="checked" value="1">Locked
						{else}
							<input type="checkbox" name="lawk" value="1">Locked
						{/if}
						</td>
						<td>
						{if $postarray.visible}
							<input type="radio" name="visible" checked="1" value="1"> Visible
						{else}
							<input type="radio" name="visible" value="1"> Visible
						{/if}
						</td>
					</tr>
	
					<tr>
						<td>
						{if $postarray.pin}
							<input type="checkbox" name="pin" checked="checked" value="1">Stickied
						{else}
							<input type="checkbox" name="pin" value="1">Stickied
						{/if}
						</td>
	
						<td>
						{if $postarray.visible}
							<input type="radio" name="visible" value="0"> Invisible
						{else}
							<input type="radio" name="visible" checked="1" value="0"> Invisible
						{/if}
						</td>
					</tr>
	
					<tr>
						<td>
						{if $postarray.permasage}
							<input type="checkbox" name="permasage" checked="checked" value="1">Permasage
						{else}
							<input type="checkbox" name="permasage" value="1">Permasage
						{/if}
						</td>
						<td>
						<b>Moderation last performed:</b>
						{if $postarray.unvisibletime != 0}
							{$postarray.unvisibletime|date_format:$THdatetimestring}
						{else}
							Never.
						{/if}
						</td>
					</tr>
				</tbody></table>
			</td>
	
			<td>
				<b>Delete images:</b><br />
	
				{counter name="imgcount" assign="imgcount" start="0"} {* start a new table row after every 5th picture *}
				{foreach from=$postarray.images item=thisimage}
				
					{if $thisimage.hash != "deleted" }
						<input type="checkbox" name="remimage{$thisimage.hash}" value="1">
					{/if}
					
					<a class=info href="images/{$thisimage.id}/{$thisimage.name}">
						{if $thisimage.hash != "deleted" }
							<img src="images/{$thisimage.id}/{$thisimage.tname}" border=0>
						{else}
							<img src="{$THurl}static/file_deleted.png" alt="File Deleted" border=0 />
						{/if}
					</a>
						
					{counter name="imgcount"}
					
					{if $imgcount mod 2 == 0}
						<br />
					{/if}
				{/foreach}
				{if $imgcount == 0}
					No images<br />
				{/if}
			</td>
		</tr>
		
		<tr>
			<td>
				{if $adminpowers > 0}
					<b>Delete:</b><br />
					<select name="moddo">
						<option value="nil" selected="selected">&#8212;</option>
						<option value="killpost">Delete this post</option>
						<option value="killip">Delete all posts from this poster's IP</option>
						<option value="killsub">Delete all posts from this poster's subnet</option>
					</select><br />
				{else} 
					Deletion functions are not available at your access level.
					Please use the hide functions instead.<br />
				{/if}
		
				<b>Ban:</b><br />
				<select name="modban">
				<option value="nil" selected="selected">&#8212;</option>
				<option value="banip">Ban this poster's IP</option>
				<option value="bansub">Ban this poster's subnet</option>;
				{* If this is a thread and we're admin we can threadban *}
				{if $postarray.thread == 0 && $adminpowers > 0}
					<option value="banthread">Ban this thread</option>
				{/if}
				</select>
	
				{* Is this a thread and are we admin? *}
				{if $postarray.thread == 0 && $adminpowers > 0} 
					<br /><b>Move thread:</b><br />
					<select name="movethread">
					<option value="nil" selected="selected">&#8212;</option>
					{foreach from=$boards item=board}
						{if $board.id != $postarray.board } {* Can't move it to the same board *}
						<option value="{$board.id}">
							Move to /{$board.folder}/
						</option>
						{/if}
					{/foreach}
					</select>
					<br /><b>Warning:</b> hiding/deleting this post will hide/delete all replies.
				{/if}	
			</td>
	
			<td>
				<b>Ban options:</b><br />
				<table><tbody><tr>
					<td>Public ban reason:</td>
					<td><input type="text" name="publicbanreason" size="20" /></td></tr>
					
					<tr><td>Private ban reason:</td>
					<td><input type="text" name="privatebanreason" size="20" /></td></tr>
					
					<tr><td>Admin ban reason:
					<td><input type="text" name="adminbanreason" size="20" /></td></tr>
					
					<tr><td>Duration:
					<td><input type="text" name="banduration" size="20" /> hrs (-1 for perma, 0 for warning)</td>
				</tr></tbody></table>
			</td>
		</tr>
	</tbody></table>
	
	<input type="hidden" name="editsub" value="1">
	<INPUT type="submit" value="Edit"> <INPUT type="reset">
	</form>
    </div>
</div>
{include file=admin-foot.tpl}