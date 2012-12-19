{include file=admin-head.tpl}
<title>{$THname} &#8212; Administration &#8212; Manager Post to /{$binfo.folder}/</title></head>
<body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Manager Post
        </div>
<form action="{$THurl}thread.php" method="post" enctype="multipart/form-data">
<table>
	<tbody>
		<tr>
					<td class="postblock">Name</td>
					<td><input type="text" name="nombre" size="28" />
					</td>
			<td>
				<input type="submit" value="Submit" id="subbtn" />
			</td>
		</tr>

				<tr>
					<td class="postblock">Subject</td>
					<td colspan="2"><input type="text" name="subj" size="35" /></td>
				</tr>
			<tr>
				<td class="postblock">Comment</td>
				<td colspan="2"><textarea name="body" cols="48" rows="4"></textarea></td>
			</tr>
				<tr><td class="postblock">Files</td><td colspan="2">
{*
				<script type="text/javascript">
					<!--
						document.write('\
{section name=filelist loop=$binfo.pixperpost}
<div id="file{$smarty.section.filelist.index}"{if $smarty.section.filelist.index!=0} style="display:none;"{/if}><input type="file" name="file{$smarty.section.filelist.index}" onchange="visfile({$smarty.section.filelist.index})" /><br /></div>\
{/section}');
					// /-->
				</script>
				<noscript>
*}
{section name=filelistnojs loop=$binfo.pixperpost}
<div id="file{$smarty.section.filelistnojs.index}"><input type="file" name="file{$smarty.section.filelistnojs.index}" /><br /></div>
{/section}
{*				</noscript>     *}
				</td></tr>
		<tr>
			<td class="postblock">
				<input type="checkbox" name="pin" checked="checked" value="on"/>Pin 
				<input type="checkbox" name="lock" checked="checked" value="on"/>Lock
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="board" value="{$binfo.folder}" />
</form>
{include file=admin-foot.tpl}