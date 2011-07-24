{include file=head.tpl}
<title>{$THname} &#8212; Members</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">Members</div><div>
                {foreach from=$users item=user}
                    {if $user.username != "initialadmin"}
                        <a href="{$THurl}profiles.php?action=viewprofile&amp;user={$user.username}">{$user.username}</a><br />
                    {/if}
                {/foreach}
                [<a href="{$THurl}drydock.php">Board index</a>]</div>
                {include file=bottombar.tpl}