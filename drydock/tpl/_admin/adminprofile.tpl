{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Profile Settings</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Profile Settings
        </div>
	<br />
	Profile settings can be changed under configuration options.
        <div class="pgtitle">
	  Pending Registrations
	</div>
	<br />
    {if $pend_regs!=null}

	<table>
						<tr>
							<td>
							<b>Proposed Username</b>
							</td>
							<td>
							<b>Email</b>
							</td>
							<td>
							<b>Action</b>
							</td>
						</tr>
    {foreach from=$pend_regs item=pend_reg}
                        <tr>
                            <td>
                                {$pend_reg.username}
                            </td>
                            <td>
                                {$pend_reg.email}
                            </td>
							<td>
							<a href="admin.php?a=p&action=regyes&username={$pend_reg.username}">Approve</a>&nbsp;
							<a href="admin.php?a=p&action=regno&username={$pend_reg.username}">Deny</a>
							</td>
                        </tr>
    {/foreach}
	</table>
	{else}
	There are currently no pending registrations.<br />
	{/if}
        <div class="pgtitle">
	  Pending Pictures
	</div>
	<br />
    {if $pend_pics!=null}
	<table>
						<tr>
							<td>
							<b>Username</b>
							</td>
							<td>
							<b>Picture</b>
							</td>
							<td>
							<b>Action</b>
							</td>
						</tr>
    {foreach from=$pend_pics item=pend_pic}
                        <tr>
                            <td>
                                {$pend_pic.username}
                            </td>
                            <td>
                            <a href="admin.php?profilepic&filename={$pend_pic.username}.{$pend_pic.pic_pending}">Picture</a>
                            </td>
							<td>
							<a href="admin.php?a=p&action=picyes&username={$pend_pic.username}">Approve</a>&nbsp;
							<a href="admin.php?a=p&action=picno&username={$pend_pic.username}">Deny</a>
							</td>
                        </tr>
    {/foreach}
	</table>
		{else}
	There are currently no pending picture requests.<br />
	{/if}
	        <div class="pgtitle">
	  Pending Capcodes
	</div>
	<br />
{if $pend_caps!=null}

	<table>
						<tr>
							<td>
							<b>Username</b>
							</td>
							<td>
							<b>Capcode</b>
							</td>
							<td>
							<b>Action</b>
							</td>
						</tr>
    {foreach from=$pend_caps item=pend_cap}
                        <tr>
                            <td>
                                {$pend_cap.username}
                            </td>
                            <td>
                                {$pend_cap.proposed_capcode|escape:"htmlall":'UTF-8'}
                            </td>
							<td>
							<a href="admin.php?a=p&action=capyes&username={$pend_cap.username}">Approve</a>&nbsp;
							<a href="admin.php?a=p&action=capno&username={$pend_cap.username}">Deny</a>
							</td>
                        </tr>
    {/foreach}
	</table>
		{else}
	There are currently no pending capcode requests.<br />
	{/if}
	<form action="admin.php?t=au" method="post">
		<div>
        <div class="pgtitle">
	  Manually Add (and auto-approve) User Account
	</div>
	<br />
		Name: <input type="text" name="user"/> Password:<input type="password" name="password"> 
		Email address: <input type="text" name="email" /> <input type="submit" value="Submit" /><br />
		</div>
	</form>
</div>
{include file=admin-foot.tpl}
