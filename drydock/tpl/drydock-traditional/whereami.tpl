{include_php file="banners.php"}
<div class="pgtitle">
<div style="width: 100%; text-align: center;">
{$binfo.name}
<br />
<font color="red" size="-2">Last post: {if $binfo.lasttime>0}{$binfo.lasttime|date_format:$THdatetimestring}{else}unavailable{/if}</font><br />
</div>
</div>
