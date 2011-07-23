{it->binfo assign=binfo}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>

{$THname} - /{$binfo.folder}/ - thread list

</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
{* tyam - load per-board stylesheets *}
{if $binfo.customcss}
<link rel="stylesheet" type="text/css" href="{$THpath}tpl/{$binfo.folder}.css" title="{$binfo.folder} theme" />
<link rel="alternate stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />
{else}
<link rel="stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />
{/if}
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="{$THurl}js.js"></script>
</head>
<body>
<div id="main">
	<div class="box">
		<div class="pgtitle">
			{$THname} - /{$binfo.folder}/ - thread list
		</div>
		<div>

[<a href="{$THurl}{if !$THuserewrite}drydock.php?b={/if}{$binfo.folder}">Return</a>]<br/>
<table width=50%>
<thead>
<tr>
<th align=right>Num</th>
<th>Title</th>
<th align=right>Posts</th>
<th>Last post</th>
</tr>
</thead>
<tbody>
<tr>
{it->getallthreads assign="bthreads"}
{counter name="upto" assign="upto" start="0"}
{foreach from=$bthreads item=th}
{counter name="upto"}
<tr>
<td align=right>{$th.globalid}:</td>
<td><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$th.globalid}">{if $th.title}{$th.title|escape:'html':'UTF-8'}{else}No Subject{/if}</a></td>
<td align=right><a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$th.globalid}">{$th.rcount+1}</a></td>
<td align=right>
<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/thread/{else}drydock.php?b={$binfo.folder}&i={/if}{$th.globalid}">
{if $th.rcount>0}
{$th.lastrep|date_format:$THdatetimestring}
{else}
{$th.time|date_format:$THdatetimestring}
{/if}
</a>
</td>
</tr>
{foreachelse}
(no threads)
{/foreach}
</tbody></table>
	</div>
</div>
</div>
