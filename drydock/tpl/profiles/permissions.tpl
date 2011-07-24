{include file=head.tpl}
<title>{$THname} &#8212; Editing permissions of {$user.username}</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">User permissions: {$user.username}</div><div>
                {if $userlevelerror}
                    <span style="color:#ff0000; font-weight:bolder;">You cannot raise the userlevel to one higher than your own!</span><br />
                {/if}

                <form action="{$THurl}profiles.php?action=permissions&amp;user={$user.username}" method="post">
                    <input type="checkbox" name="admin" value="1" {if $user.mod_admin}checked="checked"{/if} /> Admin
                    <input type="checkbox" name="moderator" value="1" {if $user.mod_global}checked="checked"{/if} /> Global moderator
                    <br />                

                    <u>Individual boards:</u><br />
                    {counter name="boardcount" assign="boardcount" start="0"}
                    {foreach from=$boards item=board}
                        <input type="checkbox" name="mod_board_{$board.id}" value="1" {if listcontains item=$board.id list=$user.mod_array}checked="checked"{/if} /> /{$board.folder}/ moderator        
                    {if $boardcount mod 5 == 4 }<br />{/if}
                    {counter name="boardcount" assign="boardcount" print="false"}
                {/foreach}
                <br />

                <u>Userlevel:</u><br />
                <input type="text" name="userlevel" value="{$user.userlevel}" /><br />

                <u>User's capcode hash:</u><br />
                <input type="text" name="capcode" value="{$user.capcode|escape}" />
                <input type="checkbox" name="remove_capcode" value="1" /> Remove

                <input type="hidden" name="permsub" value="1" />
                <br /><input type="submit" value="Submit" />
            </form>


            [<a href="{$THurl}profiles.php?action=viewprofile&amp;user={$user.username}">User profile</a>]
        </div>
        {include file=bottombar.tpl}