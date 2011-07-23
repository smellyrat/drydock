<div class="pgtitle">
{if $comingfrom=="board"}
	{if $binfo.tlock}
		Board is locked, no more posts allowed.
	{else}
		New thread
	{/if}
{elseif $comingfrom=="thread"}
	{if $thread.lawk}
		Thread is locked, no more posts allowed.
	{else}
		Reply
	{/if}
{/if}
</div>
        <div id="showit" class="sslarge">
			<form method="post" enctype="multipart/form-data" action="{$THurl}{if $comingfrom=="thread"}reply{else if $comingfrom == "board"}thread{/if}.php" id="postform">
                <div>
                    {if $comingfrom == "board"}Subject: <input type="text" name="subj" size="45" /><br />{/if}
                    Name: <input type="text" name="nombre" size="20" /> Link: <input type="text" name="link" size="20" /><br />
                    {if $THvc==1}
						Verification Code:
						{literal}<script type="text/javascript">var RecaptchaOptions = { theme : 'clean' };</script>{/literal}
						{$captcha}
                    {elseif $THvc==2}
						LEAVE BLANK IF HUMAN: <input type=text" name="email" />
                    {/if}
               	    <input type="submit" value="Post" />
                    <textarea name="body" cols="51" rows="8" id="cont"></textarea><br />
{			if (($binfo.tpix > 0 and $comingfrom == "board") or ($binfo.rpix > 0 and $comingfrom == "thread"))} {* are there images? *}
<table><tr><td class="postblock">File</td><td>
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
</td></tr></table>        
			{/if} {* if pix>0*}
Password: <input type="password" name="password" size="8" /><br> {* New password field for deletion *}
                    After submission, go to the:
                    <select name="todo">
                        <option value="board">Return to board</option>
                        <option value="thread">Go to the new thread</option>
                    </select>

<input type="hidden" name="board" value="{$binfo.folder}" />
{if $comingfrom == "thread"}<input type="hidden" name="thread" value="{$thread.id}" />{/if}
                </div>
            </form>
        </div>
    <div class="ssmed">
        <span class="name">
<a href="{$THurl}{if $THuserewrite}{$binfo.folder}{else}drydock.php?b={$binfo.folder}{/if}#tlist">Thread List</a>

