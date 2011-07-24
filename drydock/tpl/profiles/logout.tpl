{*{include file=head.tpl} Commented out until everything in profiles gets switched over *}
<title>{$THname} &#8212; Logout</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">Logged out</div>
            {if $notloggedin}
                <div>You are not logged in!</div>   
            {else}
                <div>You are now logged out!</div>
            {/if}
            [<a href="drydock.php">Board index</a>]
            {include file=bottombar.tpl}