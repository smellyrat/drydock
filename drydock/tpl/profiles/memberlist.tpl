{*{include file=head.tpl} Commented out until everything in profiles gets switched over *}
<title>{$THname} &#8212; Members</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">Members</div><div>
{foreach from=$users item=user}
{if $user.username != "initialadmin"}
<a href="profiles.php?action=viewprofile&amp;user={$user.username}">{$user.username}</a><br />
{/if}
{/foreach}

{include file=bottombar.tpl}