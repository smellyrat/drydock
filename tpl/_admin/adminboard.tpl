{include file='admin-head.tpl'}
<title>{$THname} &#8212; Administration &#8212; Board Settings</title></head>
<body>
{literal}
	<script type="text/javascript"><!--
		function CalcAllowed(form)
		{
			var total = 0;
			var max = 5;
			for (var idx = 0; idx < max; idx++)
			{
				if (eval("document.boardedit.fileformat[" + idx + "].checked") == true)
				{
					total += Math.pow(2,idx);
				}
				{/literal}
				document.boardedit.allowedformats{$board.id}.value = total;
				{literal}
			}
		}
	--></script>
{/literal}
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Board Settings
        </div>
	<br />
	{if !$boardselect}
<table>	
	{foreach from=$boards item=board}
<tr><td>{$board.name} - /{$board.folder}/</td>
<td>[<a href="admin.php?a=b&boardselect={$board.folder}">Edit this board</a>]</td>
<td>[<a href="admin.php?a=mp&board={$board.folder}">Make a manager post</a>]</td>
<td>[<a href="{$THurl}{if !$THuserewrite}drydock.php?b={/if}{$board.folder}">Visit</a>]</td></tr>
	{/foreach}
</table>	
{if $boards==null}
There are currently no boards set up.
{/if}
        <div class="pgtitle">
            Board Creation
        </div>
	<br />
	More options are available after submitting this form. After creating this board you must edit these options or the board will not be usable.<br />
			<div class="sslarge">
            <form method="post" name="boardedit" enctype="multipart/form-data" action="admin.php?t=b">
                <div>
                    <table>
<tr><td>Board name</td><td><input type="text" name="namenew" size="60" /></td></tr>
<tr><td>Board folder</td><td><input type="text" name="foldernew" size="5" /></td></tr>
<tr><td>Board description</td><td><input type="text" name="aboutnew" size="60" /></td></tr>
<tr><td>Board rules</td><td><input type="text" name="rulesnew" size="60" /></td></tr>
<tr><td>
Edit this board now or continue to add more boards (and edit later)</td><td><input type="radio" name="nextaction" value="edit" checked/>Edit now <input type="radio" name="nextaction" value="create"/>Create more</td></tr>
</table>

	{else}
	
			<div class="sslarge">
            <form method="post" name="boardedit" enctype="multipart/form-data" action="admin.php?t=b">
                <div>
                    <table>
<input type="hidden" name="boardselect" value="{$boardselect}" />
<input type="hidden" name="id{$board.id}" value="{$board.id}" />
{*DON'T MESS WITH THIS <tr><td>Global thread index</td><td><input type="text" name="globalid{$board.id}" size="3" value="{$board.globalid}" /></td></tr> I'M SERIOUS, DON'T*}
<tr><td>Board name</td><td><input type="text" name="name{$board.id}" size="60" value="{$board.name}" /></td></tr>
<tr><td>Board folder</td><td><input type="text" name="folder{$board.id}" size="5" value="{$board.folder}" /></td></tr>
<tr><td>Board description</td><td><input type="text" name="about{$board.id}" size="60" value="{$board.about}" /><br /> <i>Supports these HTML tags:
&lt;i>&lt;b>&lt;u>&lt;strike>&lt;br>&lt;font>&lt;a>&lt;ul>&lt;ol>&lt;li></i></td></tr>
<tr><td>Board rules</td><td><input type="text" name="rules{$board.id}" size="60" value="{$board.rules}" /></td></tr>
<tr><td>Hide from index/linkbar</td><td><input type="checkbox" name="hidden{$board.id}"{if $board.hidden==1} checked="checked"{/if} /></td></tr>
<tr><td>forced_anon</td><td><input type="checkbox" name="forced_anon{$board.id}"{if $board.forced_anon==1} checked="checked"{/if} /></td></tr>
<tr><td>Apply wordfilters</td><td><input type="checkbox" name="filter{$board.id}"{if $board.filter==1} checked="checked"{/if} /></td></tr>
<tr><td>Allow embedded videos<br />(MySpace, YouTube, Google)</td><td><input type="checkbox" name="allowvids{$board.id}"{if $board.allowvids==1} checked="checked"{/if} /></td></tr>
<tr><td>Require registration</td><td><input type="checkbox" name="requireregistration{$board.id}"{if $board.requireregistration==1} checked="checked"{/if} /></td></tr>
<tr><td>Board layout</td><td>
                    <select name="boardlayout{$board.id}">
   {foreach from=$tplsets item=set}
                       <option value="{$set}"{if $set==$board.boardlayout} selected="selected"{/if}>{$set}</option>
   {/foreach}
	</select>
</tr></td>
<tr><td>Use custom css<br />(Place css file named "{$board.folder}.css" in template directory)</td><td><input type="checkbox" name="customcss{$board.id}"{if $board.customcss==1} checked="checked"{/if} /></td></tr>

<tr><td>Thread lock</td><td><input type="checkbox" name="tlock{$board.id}"{if $board.tlock==1} checked="checked"{/if} /></td></tr>
<tr><td>Reply lock</td><td><input type="checkbox" name="rlock{$board.id}"{if $board.rlock==1} checked="checked"{/if} /></td></tr>
<tr><td>Max threads</td><td><input type="text" name="tmax{$board.id}" size="5" value="{$board.tmax}" /></td></tr>
<tr><td>Threads per page</td><td><input type="text" name="perpg{$board.id}" size="3" value="{$board.perpg}" /></td></tr>
<tr><td>Replies per thread</td><td><input type="text" name="perth{$board.id}" size="3" value="{$board.perth}" /></td></tr>
<tr><td>Maximum images per post:</td><td><input type="text" name="pixperpost{$board.id}" size="4" value="{$board.pixperpost}" /></td></tr>
<tr><td>Images in threads</td><td><select name="tpix{$board.id}">
	<option value="0"{if $board.tpix==0} selected="selected"{/if}>Not allowed</option>
	<option value="1"{if $board.tpix==1} selected="selected"{/if}>Allowed</option>
	<option value="2"{if $board.tpix==2} selected="selected"{/if}>Required</option>
</select>
</td></tr>
<tr><td>Images in replies</td><td><select name="rpix{$board.id}">
	<option value="0"{if $board.rpix==0} selected="selected"{/if}>Not allowed</option>
	<option value="1"{if $board.rpix==1} selected="selected"{/if}>Allowed</option>
	<option value="2"{if $board.rpix==2} selected="selected"{/if}>Required</option>
</select>
</td></tr>
<tr><td>Max image file size</td><td><input type="text" name="maxfilesize{$board.id}" size="12" value="{$board.maxfilesize}" />bytes</td></tr>
<tr><td>Max image resolution</td><td><input type="text" name="maxres{$board.id}" size="5" value="{$board.maxres}" />pixels</td></tr>
<tr><td>Thumbnail resolution</td><td><input type="text" name="thumbres{$board.id}" size="5" value="{$board.thumbres}" />pixels</td></tr>
<tr><td>Allowed image formats</td><td>
<input type="checkbox" name="fileformat"{if $board.allowedformats &  1} checked="checked"{/if} onclick="CalcAllowed(this.form)"/> JPG 
<input type="checkbox" name="fileformat"{if $board.allowedformats &  2} checked="checked"{/if} onclick="CalcAllowed(this.form)"/> GIF 
<input type="checkbox" name="fileformat"{if $board.allowedformats &  4} checked="checked"{/if} onclick="CalcAllowed(this.form)"/> PNG 
<input type="checkbox" name="fileformat"{if $board.allowedformats &  8} checked="checked"{/if} {if !$THuseSVG}disabled {/if} onclick="CalcAllowed(this.form)"/> SVG 
<input type="checkbox" name="fileformat"{if $board.allowedformats & 16} checked="checked"{/if} onclick="CalcAllowed(this.form)"/> SWF
<input type="checkbox" name="fileformat"{if $board.allowedformats & 32} checked="checked"{/if} {if !$THusePDF}disabled {/if} onclick="CalcAllowed(this.form)"/> PDF
<br />Raw: <input type="text" name="allowedformats{$board.id}" size="3" value="{$board.allowedformats}" /></td></tr>
<tr><td>Delete</td><td><input type="checkbox" name="delete{$board.id}" /></td></tr>
</table>
{/if}
		    Submitting this form will cause the cache to be deleted.<br />
                    <input type="submit" value="Submit" /><br />
                </div>
            </form>
        </div>
    </div>
{if $boardselect}<a href="admin.php?a=b">Return to board list</a><br />{/if}
{include file='admin-foot.tpl'}
