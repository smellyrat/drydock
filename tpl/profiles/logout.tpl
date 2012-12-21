{include file='head.tpl'}
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
            [<a href="{$THurl}drydock.php">Board index</a>]</div>
            {include file='bottombar.tpl'}