{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Ban Settings</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Ban Settings
        </div>
	<br />
        <div class="sslarge">
{if !$banselect}
    {if $bans==null}
            There are currently no banned IPs.
    {else}
            <form method="post" enctype="multipart/form-data" action="admin.php?t=ux">
                <div>
                    <table>
                        <tr>
                            <td>
                                Unban?
                            </td>
                            <td>
                                Banned IP
                            </td>
                            <td>
                                Private<br />Reason
                            </td>
							<td>
                                Duration
                            </td>
                            <td>
                                Ban set
                            </td>
                            <td>
                                Banned by
                            </td>
                       </tr>
    {foreach from=$bans item=ban}
                        <tr>
                            <td>
                                <input type="checkbox" name="del{$ban.id}" />
                            </td>
                            <td>
                                <a href="{$THurl}admin.php?a=x&banselect={$ban.id}">{$ban.ip1}.{$ban.ip2}.
								{if $ban.ip3=="-1"}*{else}{$ban.ip3}{/if}.
								{if $ban.ip4=="-1"}*{else}{$ban.ip4}{/if}</a>
                            </td>
                            <td>
                                {$ban.privatereason}
                            </td>
                            <td>
								{if $ban.duration=="-1"}Permanent{elseif $ban.duration=="0"}Warning{else}{$ban.duration}{/if}
                            </td>
                            <td>
                                {$ban.bantime|date_format:$THdatetimestring}
                            </td>
                            <td>
                                {$ban.bannedby}
                            </td>
                        </tr>
			
    {/foreach}
                         </table>
					<br />Unban rationale: <input type="text" name="reason"><br />
                    <input type="submit" value="Unban checked IPs" />
                </div>
            </form>

    {/if}
{else}
	<a href="{$THurl}admin.php?a=x">back to ban list</a>
	            <form method="post" enctype="multipart/form-data" action="admin.php?t=ux">
                <div>
				<div>
                    <table>
                        <tr>
                            <tr><td>
                                Unban?</td><td><input type="checkbox" name="del{$ban.id}" />
                            </td></tr>
                            <tr><td>
                                Banned IP</td><td>{$ban.ip_octet1}.{$ban.ip_octet2}.
								{if $ban.ip_octet3=="-1"}*{else}{$ban.ip_octet3}{/if}.
								{if $ban.ip_octet4=="-1"}*{else}{$ban.ip_octet4}{/if}
                            </td></tr>
                            <tr><td>
                                Public Reason</td><td>{$ban.publicreason}
                            </td></tr>
                            <tr><td>
                                Private Reason</td><td>{$ban.privatereason}
                            </td></tr>
                            <tr><td>
                                Admin Reason</td><td>{$ban.adminreason}
                            </td></tr>
                            <tr><td>
                                Post data</td><td>{$ban.postdata|escape:'html':'UTF-8'}
                            </td></tr>
							<tr><td>
                                Duration</td><td>{if $ban.duration=="-1"}Permanent{else}{$ban.duration}{/if}
                            </td></tr>
                            <tr><td>
                                Ban set</td><td> {$ban.bantime|date_format:$THdatetimestring}
                            </td></tr>
                            <tr><td>
                                Banned by</td><td>{$ban.bannedby}
                            </td></tr>
			        </table>
				</div>
<div class="pgtitle">Previous associated ban history:</div>
    {if $banhistory==null}
            There were no prior bans found.
    {else}
            <div>
                    <table>
                        <tr>
                            <td>
                                Banned IP
                            </td>
                            <td>
                                Private<br />Reason
                            </td>
                            <td>
                                Admin<br />Reason
                            </td>
							<td>
                                Duration
                            </td>
                            <td>
                                Ban set
                            </td>
                            <td>
                                Banned by
                            </td>
                            <td>
                                Unbannning reason
                            </td>
                       </tr>
		{foreach from=$banhistory item=ban}
					<tr>
						<td>
							<a href="{$THurl}admin.php?a=x&banselect={$ban.id}">{$ban.ip_octet1}.{$ban.ip_octet2}.
							{if $ban.ip_octet3=="-1"}*{else}{$ban.ip_octet3}{/if}.
							{if $ban.ip_octet4=="-1"}*{else}{$ban.ip_octet4}{/if}</a>
						</td>
						<td>
							{$ban.privatereason}
						</td>
						<td>
							{$ban.adminreason}
						</td>
						<td>
							{if $ban.duration=="-1"}Permanent{elseif $ban.duration=="0"}Warning{else}{$ban.duration}{/if}
						</td>
						<td>
							{$ban.bantime|date_format:$THdatetimestring}
						</td>
						<td>
							{$ban.bannedby}
						</td>
						<td>
							{$ban.unbaninfo}
						</td>
					</tr>
		{/foreach}
				</table>
			</div>
	{/if}
			<br />Unban rationale: <input type="text" name="reason"><br />
			<input type="submit" value="Unban checked IPs" />
        </div>
     </form>

{/if}
        <div class="pgtitle">
	  Add New Ban
	</div>
	<br />
            <form method="post" enctype="multipart/form-data" action="admin.php?t=ax">
                <div>
                    IP address: <input type="text" name="ip1" size="3" />.<input type="text" name="ip2" size="3" />.<input type="text" name="ip3" size="3" />.<input type="text" name="ip4" size="3" /> <select name="ipsub">
  <option value ="0">Ban IP</option>
  <option value ="1">Ban subnet (xxx.xxx.xxx.*)</option>
  <option value ="2">Ban class C subnet (xxx.xxx.*.*)</option>
</select><br />
                    Reason: <input type="text" name="adminreason" size="20" /> Duration: <input type="text" name="duration" size="3" />hrs<input type="submit" value="Ban" />
                </div>
            </form>
<div class="pgtitle">
	  Lookup Existing Ban
</div>
<br />
<form method="post" enctype="multipart/form-data" action="admin.php?t=lx">
<div>
IP address: <input type="text" name="ip" /><br />
<input type="submit" value="Lookup" />
</div>
</form>
        </div>
    </div>
{include file=admin-foot.tpl}