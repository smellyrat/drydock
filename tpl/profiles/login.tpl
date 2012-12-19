{include file=head.tpl}
<title>{$THname} &#8212; Login</title>
</head>
<body>
    <div id="main">
        <div class="box">
            {if $loginerror}
                <div class="pgtitle">Login error</div>
                <strong>There was an error processing your request.</strong><br />
                <u>Possible causes:</u>
                <ul>
                    <li>Invalid username</li>
                    <li>Invalid password</li>
                    <li>Your account has not been approved</li>
                    <li>Your account has been disabled</li>
                </ul>   
            {/if}

            {if $loggedin}
                <div class="pgtitle">Logged in as {$username}</div>
                <div>You are logged in as <strong>{$username}</strong>.<br /><br />
                    [<a href="{$THurl}profiles.php?action=logout">Logout if this is not you</a>]</div>
                {else}
                <div class="pgtitle">Login</div><br />
                <table><form action="{$THurl}profiles.php?action=login" method="post">
                        <tr><td>Username:</td><td><input type="text" name="name" maxlength="30" ></td></tr>
                        <tr><td>Password:</td><td><input type="password" name="password" maxlength="30" ></td></tr>
                        <tr><td><input type="checkbox" name="remember" ><font size="2">Remember me</td><td style="text-align: right;">
                                {if $showreset}
                                    <a href="{$THurl}profiles.php?action=forgotpass"><font size="2">Forgot password?</a>
                                    {else}
                                    &nbsp;
                                {/if}</td></tr>
                        <tr><td><input type="submit" value="Login"></td></tr>
                    </form></table>    
            {/if}
            {include file=bottombar.tpl}