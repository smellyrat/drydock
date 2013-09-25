{include file='admin-head.tpl'}
<title>{$THname} &#8212; Administration &#8212; Lookup Tool</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Lookup Tool
        </div>
	<br />
	
{* Image URL lookup form *}
<div class="sslarge">
<div class="pgtitle">Find post from image:</div>
<form action="lookups.php" method="get">
Image URL: <input type="text" name="url"><br />
<input type="submit" name="submit">
<input type="hidden" name="action" value="imglookup">
</form>
</div>

{* IP lookup form *}
<div class="sslarge">
<div class="pgtitle">Look up history from IP:</div>
<form action="lookups.php" method="get">
IP:
{if $single_ip != ""}
<input type="text" name="ip" value="{$single_ip}">
{else}
<input type="text" name="ip">
{/if}<br />
<input type="submit" name="submit">
<input type="hidden" name="action" value="iplookup">
</form>
</div>

{* IP history *}
{if $single_ip != ""}
    <div class="sslarge">
	<div class="pgtitle">History for IP {$single_ip}:</div>

	{* Show post history *}
	<div class="pgtitle">Recent posts:</div>
	{if $posthistory}
		{foreach from=$posthistory item=post}
			<table>
			{assign var=boardz value=$boards[$post.board].folder} {* for brevity's sake *}
						
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
					Post {$post.globalid} in [<a href="{$THurl}drydock.php?t=$post.thread">Thread</a>]				
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
			
			{* Mark if a post has already been moderated *}
			{if $post.unvisibletime != 0}
			&nbsp;<i><b>Previously moderated</b></i>
			{/if}
			
			<br />
			
			{* Show stuff like name, link field, etc *}
			
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
			
			{* Show images *}
			{if $post.images}
				<table><tbody>
				{foreach from=$post.images item=image}
					<tr><td>
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
					<br />
					</td></tr>
				{/foreach}
				</tbody></table>
			{/if}
			</td></tr>
			<tr><td> {* Split rest of post from post body *}
				<blockquote><p>
					{$post.body|nl2br}
				</p></blockquote>
			</td></tr>
			</table><hr>
		{/foreach}
	{else}
	No recent posts found.
	{/if}
	
	{* Show report history *}
	<div class="pgtitle">Recently reviewed reports:</div>
	{if $reports}
		<table>
			<tr>
				<td>
				Post ID
				</td>
				<td>
				Board ID
				</td>
				<td>
				Category
				</td>
				<td>
				Time made
				</td>
				<td>
				Status
				</td>
			</tr>
		{foreach from=$reports item=report}
			<tr>
				<td>
					{$report.postid}
				</td>
				<td>
					{$boards[$report.board].folder}
				</td>
				<td>
					{if $report.category == 1}
					Illegal content
					{elseif $report.category == 2}
					Rule violation
					{else}
					Low-quality posting
					{/if}
				</td>
				<td>
					{$report.time|date_format:$THdatetimestring}
				</td>
				<td>
					{if $report.status == 1}
					Valid
					{elseif $report.status == 2}
					Invalid
					{else}
					Reviewed
					{/if}
				</td>
			</tr>
		{/foreach}
		</table>
	{else}
	No recent reports found.
	{/if}
	
	{* Show current ban(s) *}
	<div class="pgtitle">Current ban(s):</div>
	{if $currentban}
		<div>
			{* We might have overlapping bans *}
			{foreach from=$currentban item=singlecurban}
	        <table>
	            <tr>
	                <tr><td>
	                    Ban is for: </td><td>{$singlecurban.ip_octet1}.{$singlecurban.ip_octet2}.{if $ban.ip_octet3=="-1"}*{else}{$singlecurban.ip_octet3}{/if}.{if $ban.ip_octet4=="-1"}*{else}{$singlecurban.ip_octet4}{/if}
	                </td></tr>
	                <tr><td>
	                    Public Reason</td><td>{$singlecurban.publicreason}
	                </td></tr>
	                <tr><td>
	                    Private Reason</td><td>{$singlecurban.privatereason}
	                </td></tr>
	                <tr><td>
	                    Admin Reason</td><td>{$singlecurban.adminreason}
	                </td></tr>
	                <tr><td>
	                    Post data</td><td>{$singlecurban.postdata|escape:'html':'UTF-8'}
	                </td></tr>
					<tr><td>
	                    Duration</td><td>{if $singlecurban.duration=="-1"}Permanent{else}{$singlecurban.duration}{/if}
	                </td></tr>
	                <tr><td>
	                    Ban set</td><td> {$singlecurban.bantime|date_format:$THdatetimestring}
	                </td></tr>
	                <tr><td>
	                    Banned by</td><td>{$singlecurban.bannedby}
	                </td></tr>
	        </table>
	        {/foreach}
		</div>
	{else}
	No current bans for this IP.
	{/if}
	
	{* Show past history *}
	<div class="pgtitle">Previous associated ban history:</div>
	{if $banhistory==null}
	There were no prior bans found.
	{else}
	        <div>
	                <table>
	                    <tr>
	                        <td>
	                            Banned IP
	                        </td>
	                        <td>
	                            Private<br />Reason
	                        </td>
	                        <td>
	                            Admin<br />Reason
	                        </td>
							<td>
	                            Duration
	                        </td>
	                        <td>
	                            Ban set
	                        </td>
	                        <td>
	                            Banned by
	                        </td>
	                        <td>
	                            Unbannning reason
	                        </td>
	                   </tr>
		{foreach from=$banhistory item=ban}
					<tr>
						<td>
							{$ban.ip_octet1}.{$ban.ip_octet2}.{if $ban.ip_octet3=="-1"}*{else}{$ban.ip_octet3}{/if}.{if $ban.ip_octet4=="-1"}*{else}{$ban.ip_octet4}{/if}
						</td>
						<td>
							{$ban.privatereason}
						</td>
						<td>
							{$ban.adminreason}
						</td>
						<td>
							{if $ban.duration=="-1"}Permanent{elseif $ban.duration=="0"}Warning{else}{$ban.duration}{/if}
						</td>
						<td>
							{$ban.bantime|date_format:$THdatetimestring}
						</td>
						<td>
							{$ban.bannedby}
						</td>
						<td>
							{$ban.unbaninfo}
						</td>
					</tr>
		{/foreach}
				</table>
			</div>
	{/if}
	</div> {* sslarge *}
{/if}

</div> {* box *}

{* this closes up main div *}
{include file='admin-foot.tpl'} 