<div class="centered">{include_php file="banners.php"}</div>
<div class="pgtitle">
<div class="centered">
{$binfo.name}
<br />
<font color="red" size="-2">Last post: {if $binfo.lasttime>0}{$binfo.lasttime|date_format:$THdatetimestring}{else}unavailable{/if}</font><br />
</div>
</div>
