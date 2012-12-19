<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			modifier_filters_new.php
		Description:	Wordfilter Smarty modifier based on preg_replace.
					Plus, it does spoiler tags!
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/

// Here's something interesting.  If we declare these here, we make sure
// that $from and $to will always be defined as SOMETHING (avoiding possible
// issues if someone does something stupid like define register_globals, and has
// the include statement fail).  Since the include statement is inside of the function,
// the $from and $to in the cached file are the values that will be used because
// of variable scoping.
$from=array(); 
$to=array();
srand(time());

/**
 * Convert spoiler tags to something that'll show up when moused over
 * 
 * @param string $text The post body
 * 
 * @return string The text with spoiler spans substituted for tags
 */
function checkspoilers($text)
{
	/*
	(11:19:34 PM) Ordog163: you need to make your quantifiers nongreedy
	(11:19:53 PM) Ordog163: because if you do
	(11:20:21 PM) Ordog163: [spoiler]blashaskdjhsdfkjsdf[/spoiler]
	this text shouldn't be under spoiler tags
	[spoiler]as.djhsdlksgafd[/spoiler]
	(11:20:26 PM) Ordog163: all of it will show up as spoiler text
	*/
	$search= '/\[spoiler](.*)\[\/spoiler]/iU';
	$replace='<span class="spoiler" style="background-color:#000;color:#000;" onmouseover="this.style.color=\'#FFF\';" onmouseout="this.style.color=this.style.backgroundColor=\'#000\'">'
			.$text.'</span>';
	return(preg_replace($search, $replace, $text));
}

/**
 * Convert wordfilters and spoiler tags in text to what they're supposed
 * to be
 * 
 * @param string $filt The post body
 * 
 * @return string The nl2br'd text with wordfilters/spoiler tags applied
 */
function smarty_modifier_filters_new($filt)
{
	
	$filt=checkspoilers($filt);
	//WOOHOO WE'RE CACHED
	@include(THpath.'unlinked/filters.php');
	return @nl2br(preg_replace($from, $to, $filt));

}
?>
