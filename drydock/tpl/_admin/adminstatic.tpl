{include file='admin-head.tpl'}
<title>{$THname} &#8212; Administration &#8212; Static Pages</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Static Pages
        </div>
	<br />
{if $single_page == null} {* Show a list OR show the full edit listing for a single page *}
    <div class="sslarge">

	{if $pages!=null}
        <div class="pgtitle">
            Static Pages List
        </div>
	<br />
        <div class="sslarge">
            <div>
                <table>
                    <tr>
                        <td>
                            Name 
                        </td>						
                        <td>
                            Title
                        </td>
                        <td>
                        	Visibility
                        </td>
                        <td>
                        	Location
                        </td>
                        <td>
                            Edit?
                        </td>
                        <td>
                            Delete?
                        </td>    
                   </tr>
{foreach from=$pages item=page}
                    <tr>
					    <td>
                           {$page.name}
                        </td>
                        <td>
							{$page.title}
                        </td>
                        <td>
                            {if $page.publish == 0}
                            Admins
                            {elseif $page.publish == 1}
                            Mods/Admins
                            {elseif $page.publish == 2}
                            Registered Users
                            {else}
                            Public
                            {/if}
                        </td>
                        <td>
                        	<a href="misc.php?action=getpage&page={$page.name}">View</a>
                        </td>
                        <td>
							<a href="admin.php?a=spe&amp;id={$page.id}">Edit this page</a>
                        </td>
                        <td>
							<a href="admin.php?t=spx&amp;id={$page.id}">Delete this page</a>
                        </td>
                    </tr>
{/foreach}
                </table>
            </div>
	    {else}
	    There are currently no static pages.
    {/if}
		<div class="pgtitle">
            Add Static Page
        </div><br />
	<form method="post" enctype="multipart/form-data" action="admin.php?t=spa">
		<table style="width: 100%;">
			<tr>
				<td>Name:<input type="text" name="name" maxlength="50"></td>
				<td>Title:<input type="text" name="title"></td>
				<td><input type="submit" value="Submit" /></td>
			</tr>
		</table>
    </form>
    </div> {* Close sslarge *}
{else} {* Show only the single page *}
        <div class="pgtitle">
            Edit Static Page
        </div>
        <div class="sslarge">
        <form method="post" action="admin.php?t=spe"> 
        	<table>
        		<tr>
        			<td>
        			Name (must be unique):
        			</td>
        			<td>
        			<input type="text" name="name" maxlength="50" 
        			value='{$single_page.name|escape:"html":"UTF-8"}'>
        			</td>
        		</tr>
        		<tr>
        			<td>
        			Title (to display for the page):
        			</td>
        			<td>
        			<input type="text" name="title" maxlength="50" 
        			value='{$single_page.title|escape:"html":"UTF-8"}'>
        			</td>
        		</tr>
        		<tr>
        			<td>
        			Visibility:
        			</td>
        			<td>
        				<select name="publish">
        				<option value="0" {if $single_page.publish == 0}selected{/if}>Admin only</option>
        				<option value="1" {if $single_page.publish == 1}selected{/if}>Mod/admin only</option>
        				<option value="2" {if $single_page.publish == 2}selected{/if}>Registered users</option>
        				<option value="3" {if $single_page.publish == 3}selected{/if}>Public</option>
        				</select>
        			</td>
        		</tr>
        		<tr>
        			<td>
        			URL:
        			</td>
				    <td>
	                	<a href="misc.php?action=getpage&page={$single_page.name}">misc.php?action=getpage&page={$single_page.name}</a>
	                </td>
        		</tr>
        	</table>
        	Content:<br />
        	<textarea name="content" cols="48" rows="6" >{$single_page.content|escape:"html":"UTF-8"}</textarea>
        	<input type="hidden" value="{$single_page.id}" name="id">
        	<input type="submit" value="Edit" />
        </form>
        </div>
{/if}
        
    </div> {* Close box *}
{include file='admin-foot.tpl'}
