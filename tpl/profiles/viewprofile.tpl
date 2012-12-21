{include file='head.tpl'}
<title>{$THname} &#8212; Viewing profile of {$user.username}</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">User profile: {$user.username}</div><div>

                {if $caneditprofile}
                    <a href="{$THurl}profiles.php?action=edit&amp;user={$user.username}">
                        <img src="{$THurl}static/edit.png" alt="Edit profile" border="0" />Edit profile</a>
                    {/if}

                {if $isadmin}
                    <a href="{$THurl}profiles.php?action=permissions&amp;user={$user.username}">
                        <img src="{$THurl}static/shield.png" alt="Edit permissions" border="0" />Edit permissions</a>
                    <a href="{$THurl}profiles.php?action=remove&amp;user={$user.username}">
                        <img src="{$THurl}static/disable.png" alt="Disable user" border="0" />Disable user</a>
                    {/if}
                <br />

                {if $user.has_picture}
                    <img src="{$THurl}images/profiles/{$user.username}.{$user.has_picture}"
                         align="left" alt="User profile picture" />
                {else}
                    <img src="{$THurl}static/nopicture.png" align="left" alt="No picture provided" />
                {/if}
                <br />

                {if $capcode}
                    <strong>Posts as:</strong> {$capcode}<br />
                {/if}

                <strong>Gender:</strong> 
                {if $user.gender == "M" || $user.gender == "F"}
                    {$user.gender}
                {else}
                    Unspecified
                {/if}
                <br />

                <strong>Age:</strong> 
                {if $user.age}
                    {$user.age}
                {else}
                    Unspecified
                {/if}
                <br />

                <strong>Location:</strong> 
                {if $user.location}
                    {$user.location}
                {else}
                    Unspecified
                {/if}
                <br />

                {if $user.mod_admin}
                    <strong>Position:</strong> Administrator<br />
                {elseif $user.mod_global || $user.mod_array}
                    <strong>Position:</strong> Moderator<br /> 
                {/if}

                <strong>Contact information:</strong> 
                {if $user.contact}
                    {$user.contact}
                {else}
                    Unspecified
                {/if}
                <br />

                <strong>Description:</strong> 
                {if $user.description}
                    {$user.description}
                {else}
                    None
                {/if}
                <br />

                [<a href="{$THurl}profiles.php?action=memberlist">Return to member list</a>]
            </div>
{include file='bottombar.tpl'}