{it->binfo assign=binfo}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>
{if $comingfrom=="board"}
{$THname} - {$binfo.name}
{else}
{$THname} - {$binfo.name} reply view {* i'd like to put globalid here if possible? *}
{/if}
</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
{* tyam - load per-board stylesheets *}
{if $binfo.customcss}
<style type="text/css">@import  url('{$THtplurl}futaba.css');</style>
<link rel="stylesheet" type="text/css" href="{$THpath}tpl/{$binfo.folder}.css" title="{$binfo.folder} theme" />
<link rel="alternate stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />
{else}
<link rel="stylesheet" type="text/css" href="{$THtplurl}futaba.css" title="Futaba-ish Stylesheet" />
{/if}
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="{$THurl}js.js"></script>
        <script type="text/javascript">{literal}
        var n=readCookie("{/literal}{$THcookieid}{literal}-name");
        var l=readCookie("{/literal}{$THcookieid}{literal}-link");
        var p=readCookie("{/literal}{$THcookieid}{literal}-password");

        if (n!=null) { $(".frmName").val(unescape(n).replace(/\+/g," ")); }
        if (l!=null) { $(".frmLink").val(unescape(l).replace(/\+/g," ")); }

        if (p!= null)
        {
           $(".frmPassword").val(unescape(p).replace(/\+/g," "));
        }
        else
        {
            var pass=randPassword();
            $(".frmPassword").val(pass);
        }            
        {/literal}</script>
</head>
{if $comingfrom=="thread"}
<body class="replypage">
{else}
<body>
{/if}
<div id="main">
	<div class="box">
		<div class="pgtitle">
			{$THname} - {$binfo.name}
		</div>
		<div>
