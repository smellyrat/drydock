{include file='head.tpl'}
<title>{$THname} &#8212; Removing {$username}</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">Successful removal of {$username}</div><div>
                The specified user has been successfully deleted.
                <a href="{$THurl}profiles.php?action=memberlist">Return to member list</a>
            </div>
            {include file='bottombar.tpl'}