{include file='head.tpl'}
<title>{$THname} &#8212; Lost password</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">Forgot password</div><div>
                {if $submitting}
                    {if $mismatch}
                        The email address you provided did not match the one associated with your account.
                    {elseif $error}
                        There was an error resetting your password.  Please try again later.
                    {else}
                        Your password has been reset and emailed to your specified address.
                    {/if}
                {/if}
                {if !$submitting || $mismatch}
                    <strong>Note:</strong> submitting this form will reset your password.<br />
                    <form action="profiles.php?action=forgotpass" method="post">
                        Username: <input type="text" name="user" maxlength="30" /><br />
                        Email: <input type="text" name="email" /><br />
                        <input type="submit" value="Submit"/>
                    </form>    
                {/if}
                [<a href="{$THurl}drydock.php">Board index</a>]</div>
            {include file='bottombar.tpl'}