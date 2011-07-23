{include file=head.tpl}
{it->binfo assign="binfo"}
{it->head assign="thread"}{* Workaround *}
{it->getreplies assign="posts"}
<div id="main">
[<a href="{$THurl}{if !$THuserewrite}drydock.php?b={/if}{$binfo.folder}">Return</a>]
{include file="viewblock.tpl"}
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