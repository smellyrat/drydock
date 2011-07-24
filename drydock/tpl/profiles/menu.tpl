{include file=head.tpl}
<title>{$THname} &#8212; Profiles System</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">Profiles System</div><div>
                Profile system options:
                <ul>
                    {if $sessUsername}
                        <li><a href="{$THurl}profiles.php?action=logout">Logout</a></li>
                        <li><a href="{$THurl}profiles.php?action=viewprofile&amp;username={$sessUsername}">Your profile</a></li>
                    {else}
                        <li><a href="{$THurl}profiles.php?action=login">Login</a></li>
                    {if $regpolicy > 0}<li><a href="{$THurl}profiles.php?action=register">Register</a></li>{/if}
                {/if}
                {if $canSeeMemberlist}
                    <li><a href="{$THurl}profiles.php?action=memberlist">Member list</a></li>
                {/if}
            </ul>
            [<a href="drydock.php">Board index</a>]
            {include file=bottombar.tpl}