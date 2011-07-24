{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Filters</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
	  Filters Settings
	</div>
	<br />
        <div class="sslarge">
    {if $filters==null}
            There are currently no filters.
    {else}
            <form method="post" enctype="multipart/form-data" action="admin.php?t=ew">
                <div>
                    <table>
                        <tr>
                            <td>
                                Remove?
                            </td>						
                            <td>
								Filter From
                            </td>
                            <td>
                                Filter To
                            </td>
                            <td>
                                Notes
                            </td>    
                       </tr>
    {foreach from=$filters item=filters}
                        <tr>
						    <td>
                                <input type="checkbox" name='del{$filters.id}' />
								<input type="hidden" name="id{$filters.id}" size="15" value='{$filters.id}' />
                            </td>
                            <td>
								<input type="text" name="from{$filters.id}" size="15" value='{$filters.filterfrom|escape:'quotes'}' />
                            </td>
                            <td>
                                <input type="text" name="to{$filters.id}" size="40" value='{$filters.filterto|escape:'quotes'}' />
                            </td>
                            <td>
                                <input type="text" name="notes{$filters.id}" size="25" value='{$filters.notes|escape:'quotes'}' />
                            </td>
                        </tr>
    {/foreach}
                    </table>
                    <input type="submit" value="Save filters" />
                </div>
            </form>
    {/if}
            <form method="post" enctype="multipart/form-data" action="admin.php?t=aw">
                <div>
        <div class="pgtitle">
	  Filter Creation
	</div>
	<br />
		Filters from: <input type="text" name="filterfrom" size="10" />
		Filters to: <input type="text" name="filterto"/> 
		Notes: <input type="text" name="notes"/> <input type="submit" value="Submit" />
	</div>
            </form>
        </div>
    </div>
{include file=admin-foot.tpl}