{include file='admin-head.tpl'}
<title>{$THname} &#8212; Administration &#8212; Blotter Post</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Blotter Post
        </div>
	<br />
        <div class="sslarge">
	{if $blots!=null}
        <div class="pgtitle">
            Blotter Edit
        </div>
	<br />
        <div class="sslarge">
            <form method="post" enctype="multipart/form-data" action="admin.php?t=ble">
                <div>
                    <table>
                        <tr>
                            <td>
                                Remove?
                            </td>						
                            <td>
                                Time
                            </td>
                            <td>
                                Text
                            </td>
                            <td>
                                Board
                            </td>    
                       </tr>
    {foreach from=$blots item=blotter}
                        <tr>
						    <td>
                                <input type="checkbox" name='del{$blotter.id}' />
								<input type="hidden" name="id{$blotter.id}" value='{$blotter.id}' />
                            </td>
                            <td>
								{$blotter.time|date_format:$THdatetimestring}
                            </td>
                            <td>
                                <input type="text" name="post{$blotter.id}" value='{$blotter.entry}' />
                            </td>
                            <td>
								<select name="postto{$blotter.id}">
									<option value="0" {if $blotter.board == 0}selected{/if}>All</option>
									{foreach from=$boards item=board}
										<option value="{$board.id}" {if $blotter.board == $board.id}selected{/if} >/{$board.folder}/</option>
									{/foreach}
								</select><br />
                            </td>
                        </tr>
    {/foreach}
                    </table>
                    <input type="submit" value="Save blotter entries" />
                </div>
            </form>
	    {else}
	    There are currently no blotter posts.
    {/if}
<div class="pgtitle">
            Add Blotter Post
        </div><br />
<form method="post" enctype="multipart/form-data" action="admin.php?t=bl">
	<table style="width: 100%;">
		<tr>
			<td>
				Post to:
				<select name="postto">
					<option value="0">All</option>
					{foreach from=$boards item=board}
						<option value="{$board.id}">/{$board.folder}/</option>
					{/foreach}
				</select><br />
			</td>
			<td>Post contents:<input type="text" name="post"></td>
			<td><input type="submit" value="Submit" /></td>
		</tr>
	</table>
               </div>
            </form>
        </div>
    </div>
{include file='admin-foot.tpl'}
