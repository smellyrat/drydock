{include file='head.tpl'}
<title>{$THname} &#8212; Register</title>
</head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">Register a new account</div>
            {if $errorstring}
                <div><strong>The following errors were encountered:</strong> {$errorstring}   
                {/if}
                {if $success == 0}
                    <form action="profiles.php?action=register" method="post">
                        <strong>Username:</strong><input type="text" name="user" maxlength="30" /><br />
                        <strong>Password:</strong><input type="password" name="password" maxlength="30" /><br />
                        <strong>Email:</strong><input type="text" name="email" maxlength="50" /><br />
                        <input type="submit" value="Register" />
                    </form>
                {else}
                    You have successfully registered an account with username <strong>{$username}</strong>.<br />

                    {if $regpolicy == 1}
                        However, you must be manually approved by a moderator before logging in.<br />
                        {if $emailwelcome}
                            You will receive notification of your approval through email.<br />
                        {/if}
                    {else}
                        You may log in as soon as desired.<br />
                        {if $emailwelcome}
                            An email containing your account information has been sent to your specified email address.<br />
                        {/if}
                    {/if}

                    <table><form action="profiles.php?action=login" method="post">
                            <tr><td>Username:</td><td><input type="text" name="name" maxlength="30" value="{$username}" /></td></tr>
                            <tr><td>Password:</td><td><input type="password" name="password" maxlength="30" /></td></tr>
                            <tr><td><input type="checkbox" name="remember" /><font size="2">Remember me</td>
                            <tr><td><input type="submit" value="Login" /></td></tr>
                        </form></table>
                    {/if}
                [<a href="drydock.php">Board index</a>]
                {include file='bottombar.tpl'}