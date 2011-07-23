{it->binfo assign=binfo}
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>
{if $comingfrom=="board"}
{$THname} - /{$binfo.folder}/
{else}
{$THname} - /{$binfo.folder}/ reply view {* i'd like to put globalid here if possible? *}
{/if}
</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
{* tyam - load per-board stylesheets *}
{if $binfo.customcss}
<style type="text/css">@import  url('{$THtplurl}futaba.css');</style>
<link rel="stylesheet" type="text/css" href="{$THpath}tpl/{$binfo.folder}.css" title="{$binfo.folder} theme" />
<link rel="alternate stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />
{else}
<link rel="stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />
{/if}
<script type="text/javascript" src="{$THurl}js.js"></script>
</head>
{if $comingfrom=="thread"}
<body class="replypage">
{else}
<body>
{/if}
<div id="main">
	<div class="box">
		<div class="pgtitle">
			{$THname} - /{$binfo.folder}/
		</div>
		<div>