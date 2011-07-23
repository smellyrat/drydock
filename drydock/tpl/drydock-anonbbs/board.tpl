{include file=head.tpl comingfrom=$comingfrom}

{it->binfo assign=binfo}
{it->blotterentries assign=blotter}
{* include_php file="linkbar.php" *} {* tyam - this way we have a list of boards to quicklink to - take the asterisks out if you want them*}
{include file=pages.tpl}
<br style="clear: both;" />
		<div class="centered">{include_php file="banners.php"}
		<div class="pgtitle">
			{$binfo.name}
		</div>
		</div>
<br />{if $binfo.tlock}Only moderators and administrators are allowed to create new threads.<br />{/if}
<a name="tlist" />
Threads on this page:
    <div class="medtitle">
{it->getsthreads assign="bthreads"}
{counter name="upto" assign="upto" start="0"}
{foreach from=$bthreads item=th}
{counter name="upto"}
<a href="#{$th.globalid}">{$th.globalid}: {if $th.title}{$th.title|escape:'html':'UTF-8'}{else}No Subject{/if} ({$th.rcount+1})</a>&nbsp;&nbsp;
{foreachelse}
(no threads)
{/foreach}
	</div>
	<div style="text-align: right;">
		<a href="#newthread">New thread</a>&nbsp;&nbsp;
		<a href="{$THurl}{if $THuserewrite}{$binfo.folder}/tlist{else}drydock.php?b={$binfo.folder}&amp;tlist{/if}">All threads</a>
	</div>
</div>

{it->getsthreads assign="sthreads"}
{foreach from=$sthreads item=thread}
</div><div class="box">
{include file="viewblock.tpl" comingfrom=$comingfrom}
{foreachelse}
<div class="medtitle">(No threads on this board)</div></div>
{/foreach}{*For each thread*}
</div>
<div class="box">
<a name=newthread />
{include file=postblock.tpl comingfrom=$comingfrom}
 </div>
</div>
{literal}
<script type="text/javascript" defer="defer">
	<!--
		function visfile(thisone)
		{
			if (document.getElementById("file"+(thisone+1)))
			{
				document.getElementById("file"+(thisone+1)).style.display="block";
			}
		}
	-->
</script>
{/literal}

{literal}
<script type="text/javascript">
	<!--
		var n=readCookie("{/literal}{$THcookieid}{literal}-name");
		var t=readCookie("{/literal}{$THcookieid}{literal}-tpass");
		var l=readCookie("{/literal}{$THcookieid}{literal}-link");
		var p=readCookie("{/literal}{$THcookieid}{literal}-password");
		
		if (n!=null)
		{
			document.forms['postform'].elements['nombre'].value=unescape(n).replace(/\+/g," ");
        }
		if (t!=null)
		{
			document.forms['postform'].elements['tpass'].value=unescape(t).replace(/\+/g," ");
        }
		if (l!=null)
		{
			document.forms['postform'].elements['link'].value=unescape(l).replace(/\+/g," ");
		}
		
		if (p!= null)
		{
			document.forms['postform'].elements['password'].value=unescape(p).replace(/\+/g," ");
		}
		else
		{
			var chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			var pass='';

			for(var i=0;i<8;i++)
			{
				var rnd=Math.floor(Math.random()*chars.length);
				pass+=chars.substring(rnd,rnd+1);
			}

			document.forms['postform'].elements['password'].value=pass;	
		}
	//-->
</script>
{/literal}