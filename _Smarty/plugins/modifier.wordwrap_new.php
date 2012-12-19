<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty wordwrap modifier plugin
 *
 * Type:     modifier<br />
 * Name:     wordwrap_new<br />
 * Purpose:  wrap a string of text at a given length, hopefully in a way that doesn't break things!  Call this before nl2br.
 * @author   ordog (lol)
 * @param string $string The source string
 * @param integer $length The length to wrap to, defaults to 80
 * @param string $break The string to break up lines by, defaults to " "
 * @param boolean $cut If $cut is true, the string is always wrapped at the specified width
 * @return string The wordwrapped string
 */
function smarty_modifier_wordwrap_new($string,$length=80,$break=" ",$cut=false)
{
	// We'll use this to do the cutting for us.
	$string = preg_replace("/(\B{".$length."})(\B+)/Su", "\\1".$break."\\2", $string);
    return utf8_wordwrap($string,$length,$break,$cut);
}

// I found this at http://milianw.de/section:Snippets/content:UTF-8-Wordwrap.
/**
* wordwrap for utf8 encoded strings
*
* @param string $str The source string
* @param integer $len The length to wrap to, defaults to 80
* @param string $what The string to break up lines by, defaults to " "
* @param boolean $cut If $cut is true, the string is always wrapped at the specified width
* @return string The wordwrapped string
* @author Milian Wolff <mail@milianw.de>
*/
function utf8_wordwrap($str, $width, $break,$cut = false)
{
	if(!$cut)
	{
		$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.',}\b#U';
	} 
	else 
	{
		$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.'}#';
	}
	
	if(function_exists('mb_strlen'))
	{
		$str_len = mb_strlen($str,'UTF-8');
	} 
	else 
	{
		$str_len = preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $var_empty);
	}
	
	$while_what = ceil($str_len / $width);
	$i = 1;
	$return = '';
	
	while ($i < $while_what)
	{
		preg_match($regexp, $str,$matches);
		$string = $matches[0];
		$return .= $string . $break;
		$str = substr($str,strlen($string));
		$i++;
	}
	return $return.$str;
}

?>
