<?php
// I'm doing it this way to avoid a possible security hole.  Look at the filters_new modifier
// comments for more information.
$capcodes=array();

/**
 * Return a new capcode based on a given tripcode hash
 * 
 * @param string $trip The tripcode hash
 * 
 * @return string The equivalent capcodeto in the capcodes array
 */
function smarty_modifier_capcode($trip)
{
	@include(THpath.'unlinked/capcodes.php');
	return(html_entity_decode($capcodes[$trip]));
}
?>
