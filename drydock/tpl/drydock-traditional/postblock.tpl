<div class="theader">

{if $comingfrom=="board"}
	{if $binfo.tlock}
		(Board is locked, no more posts allowed)
	{else}
		Posting mode: New thread
	{/if}
{elseif $comingfrom=="thread"}
	{if $thread.lawk}
		(Thread is locked, no more posts allowed)
	{else}
		Posting mode: Reply
	{/if}
{else}
	huh
{/if}
</div>

<div class="postarea">
	<form id="postform" action="{$THurl}{if $comingfrom=="thread"}reply{else if $comingfrom == "board"}thread{/if}.php" method="post" enctype="multipart/form-data">
		<table>
			<tbody>
{				if $binfo.forced_anon != "1"} {* begin forced_anon *}
{					if $binfo.requireregistration != "1"}
					<tr>
						<td class="postblock">Name</td>
						<td><input type="text" name="nombre" size="40" maxlength="40"/><span style="font-size:x-small;"><input type="checkbox" name="mem" style="font-size:x-small;" value="on" />Remember</span></td>
					</tr>
{					else}
						<input type="hidden" name="nombre" value="{$username}"/>
{					/if} {* end registration *}
{				/if} {* end forced_anon *}
{				if $binfo.forced_anon!="1" and $comingfrom=="board"} {* begin forced_anon / boardorthread check*}
				<tr>
					<td class="postblock">Subject</td>
					<td><input type="text" name="subj" size="40" maxlength="40"/></td>
					</tr>
{				/if} {* end forced_anon / boardorthread check *}
				<tr>
					<td class="postblock">Link</td>
					<td><input type="text" name="link" size="40" maxlength="40"/>
						<input type="submit" value="Submit" id="subbtn" /></td>
				</tr>
		{if $THvc==1}
				<tr>
					<td class="postblock">Verification Code</td>
					<td>
						{literal}<script type="text/javascript">var RecaptchaOptions = { theme : 'clean' };</script>{/literal}
						{$captcha}
					</td>
				</tr>
		{elseif $THvc==2}
				<tr>
					<td class="postblock">LEAVE BLANK IF HUMAN</td>
					<td><input type=text" name="email" /></td>
				</tr>
		{/if}
				<tr>
					<td class="postblock">Comment</td>
					<td><textarea name="body" cols="48" rows="4" id="cont"></textarea></td>
				</tr>
{			if (($binfo.tpix > 0 and $comingfrom == "board") or ($binfo.rpix > 0 and $comingfrom == "thread"))} {* are there images? *}
				<tr>
					<td class="postblock">File</td>
					<td>
				<script type="text/javascript">
					<!--
						document.write('\
{section name=filelist loop=$binfo.pixperpost}
<div id="file{$smarty.section.filelist.index}"{if $smarty.section.filelist.index!=0} style="display:none;"{/if}><input type="file" name="file{$smarty.section.filelist.index}" onchange="visfile({$smarty.section.filelist.index})" /><br /></div>\
{/section}');
					// /-->
				</script>
				<noscript>
{section name=filelistnojs loop=$binfo.pixperpost}
<div id="file{$smarty.section.filelistnojs.index}"><input type="file" name="file{$smarty.section.filelistnojs.index}" /><br /></div>
{/section}
				</noscript>        
					</td>
				</tr>
			{/if} {* if pix>0*}
				<tr>
					<td class="postblock">Password</td>
					<td><input type="password" name="password" size="8" /> (for post deletion)</td>{* New password field for deletion *}
				</tr>
				<tr>
					<td class="postblock">Rules</td>
					<td><div class="rules">{include file=rules.tpl}</div></td>
				</tr>
<input type="hidden" name="board" value="{$binfo.folder}" />
{if $comingfrom == "thread"}<input type="hidden" name="thread" value="{$thread.id}" />{/if}
{if $blotter}
<tr>
<td class="postblock">Blotter</td>
<td><div class="rules">
{foreach from=$blotter item=blot}
<span class="timedate">{$blot.time|date_format:$THdatetimestring}</span> - {$blot.entry}<br>
{/foreach}
</div></td>
</tr>
{/if}
			</tbody>
		</table>
	</form>
</div>
