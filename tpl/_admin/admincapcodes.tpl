{include file='admin-head.tpl'}
<title>{$THname} &#8212; Administration &#8212; Capcodes</title></head>
<body>
    <div id="main">
        <div class="box">
            <div class="pgtitle">
                Capcode Settings
            </div>
            <br />
            <div class="sslarge">
                {if $capcodes==null}
                    There are currently no capcodes.
                {else}
                    <form method="post" enctype="multipart/form-data" action="admin.php?t=rc">
                        <div>
                            <table>
                                <tr>
                                    <td>
                                        Remove?
                                    </td>						
                                    <td>
                                        Capcode From
                                    </td>
                                    <td>
                                        Capcode To
                                    </td>
                                    <td>
                                        Notes
                                    </td>    
                                </tr>
                                {foreach from=$capcodes item='capcode'}
                                    <tr>
                                        <td>
                                            <input type="checkbox" name='del{$capcode.id}' />
                                            <input type="hidden" name="id{$capcode.id}" size="15" value='{$capcode.id}' />
                                        </td>
                                        <td>
                                            <input type="text" name="from{$capcode.id}" size="10" value='{$capcode.capcodefrom|replace:"'":"&#39;"}' />
                                        </td>
                                        <td>
                                            <input type="text" name="to{$capcode.id}" value='{$capcode.capcodeto|replace:"'":"&#39;"}' />
                                        </td>
                                        <td>
                                            <input type="text" name="notes{$capcode.id}" value='{$capcode.notes|replace:"'":"&#39;"}' />
                                        </td>
                                    </tr>
                                {/foreach}
                            </table>
                            <input type="submit" value="Save capcodes" />
                        </div>
                    </form>
                {/if}
                <div class="pgtitle">
	  Add New Capcode
                </div>
                <br />
                <form method="post" enctype="multipart/form-data" action="admin.php?t=ac">
                    <div>
                        Capcode: <input type="text" name="capcodefrom" size="10" />
                        Filters to: <input type="text" name="capcodeto"/> 
					Notes: <input type="text" name="notes"/> <input type="submit" value="Submit" />
                    </div>
                </form>
            </div>
        </div>
        {include file='admin-foot.tpl'}