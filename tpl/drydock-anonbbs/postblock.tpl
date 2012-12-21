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
        {if $thread.lawk}
		Thread is locked, no more posts allowed.
        {else}
		Reply
        {/if}
    {/if}
</div>
<div class="sslarge showit">
    <form method="post" enctype="multipart/form-data" action="{$THurl}{if $comingfrom=="thread"}reply{else if $comingfrom == "board"}thread{/if}.php">
        <div>
            {if $comingfrom == "board" && $binfo.forced_anon!=1} {* begin forced_anon *}
                Subject: <input type="text" name="subj" size="45" /><br />
            {/if} {* end forced_anon *}
            {if $binfo.forced_anon!=1} {* begin forced_anon *}
                Name: <input type="text" class="frmName" name="nombre" size="20" /> 
            {/if}{* end forced_anon *}

                Link: <input type="text" class="frmLink" name="link" size="20" /><br />

                {if $THvc==1}
                    {include file='recaptcha.tpl'}
                {elseif $THvc==2}
                    LEAVE BLANK IF HUMAN: <input type="text" name="email" /><br />
                {/if}

                <textarea name="body" cols="51" rows="8"></textarea><br />
                Password: <input type="password" class="frmPassword" name="password" size="8" /> {* New password field for deletion *}
            {if $THvc==1}
                <input type="button" id="recaptcha_required_{$pb}"
                onclick="showRecaptcha('recaptcha_div_{$pb}', 
                'submit_{$pb}',
                'recaptcha_required_{$pb}');" 
                value="Post" 
                class="recaptcha_required" />
                <input type="submit" id="submit_{$pb}" class="post_submit" value="Post" style="display: none;" />
             {else}
                <input type="submit" value="Post" />
              {/if}  
                <br /> 
                <input type="hidden" name="board" value="{$binfo.folder}" />
            {if $comingfrom == "thread"}<input type="hidden" name="thread" value="{$thread.id}" />{/if}
        </div>
    </form>
</div>
<div class="ssmed">
    <a href="{$THurl}{if $THuserewrite}{$binfo.folder}{else}drydock.php?b={$binfo.folder}{/if}#tlist">Thread List</a>
</div>
