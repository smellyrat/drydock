{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212;Housekeeping</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
Housekeeping Functions
        </div>
	<br />
        <div class="sslarge">
			The following buttons can be used to force updates to certain script built pages.  They can be called manually if there is some error in the build process or if changes are made outisde of the scope of the update scripts (such as editing a post in your news board).
        <div class="pgtitle">
	  Rebuild Functions
	</div>
	<br />			
            <form method="post" enctype="multipart/form-data" action="admin.php?a=hkc">
			<table><tr>
			<td><input type="submit" name="fc" style="width:20em;" value="Rebuild cached files"></td>
			<td><input type="submit" name="lb" style="width:20em;" value="Rebuild top/bottom link bars"></td>
			</tr><tr>
				<td><input type="submit" name="ht" style="width:20em;" value="Rebuild .htaccess"></td>
				<td><input type="submit" name="sl" style="width:20em;" value="Rebuild side menu"></td>
			</tr><tr>
				<td><input type="submit" name="sp" style="width:20em;" value="Rebuild spam list"></td>
				<td><input type="submit" name="fl" style="width:20em;" value="Rebuild word filters"></td>
			</tr><tr>
				<td><input type="submit" name="cp" style="width:20em;" value="Rebuild capcodes"></td>
{if THnewsboard!=0}<td><input type="submit" name="rs" style="width:20em;" value="Rebuild news page RSS feed"></td>{/if}
			</tr><tr>
				<td><input type="submit" name="al" style="width:20em;" value="Rebuild EVERYTHING"></td>
            </tr></table>
			</form>
{*  //we need to support dumping of both dbis here
{if $THdbtype=="MySQL"}
{php} if(file_exists("dbi/MySQL-dump.php")) { include("dbi/MySQL-dump.php"); } {/php}
{elseif $THdbtype=="SQLite"}
{php} if(file_exists("dbi/SQLite-dump.php")) { include("dbi/SQLite-dump.php"); } {/php}
{/if}
*}
        </div>
    </div>
{include file=admin-foot.tpl}
