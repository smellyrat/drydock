<?php
/*
	handles bbcode type tags (in theory)
	
	at some point should match
	[youtube]videoid[/youtube]
	[google]googlevideoid[/google]
	[spoilers]dora has cancer[/spoilers]
*/
function smarty_modifier_vids($text)
	{
	$search= array (
		//match youtube
		'/\[youtube](\S{11})\[\/youtube]/i',
		//match google video
		'/\[google](\S{19})\[\/google]/i',
		//myspace has videos
		'/\[myspace](\S{9,10})\[\/myspace]/i'
	);
	$replace=array (
		//youtube
		'<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/'.'\\1'.'"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.'\\1'.'" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>',
		//google video
		'<embed style="width:400px; height:326px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=-'.'\\1'.'&hl=en" flashvars=""> </embed>',
		//myspace video
		'<embed src="http://lads.myspace.com/videos/vplayer.swf" flashvars="m='.'\\1'.'&type=video" type="application/x-shockwave-flash" width="430" height="346"></embed>'
	);
	return(preg_replace($search, $replace, $text));
	}
?>



