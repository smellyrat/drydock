<?php
/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-thread.php
	Description:    Abstract interface for a ThornThreadDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
	
*/

/**
 * ThornThreadDBI is a class which has a very specific (but necessary)
 * role.  It contains an entire thread's data as part of its extra
 * members (the thread entry is stored as $head, the relevant board
 * information is stored as the typical $binfo array, and the
 * blotter entries are also stored as $blotterentries).  However,
 * what makes it particularly important is that all post data within
 * it has IP data stripped from it, making it particularly suitable
 * for passing into Smarty.
 */
interface absThornThreadDBI
{
	/**
	 * Returns the replies for this thread. $p is an array which has various
	 * values used for altering the produced output:
	 * 
	 * If $p['sortmethod'] == "id", posts are sorted by ID number. 
	 * If "time", they will be sorted by post time. Defaults to "time".
	 * (Theoretically, each of these will yield the same result.) 
	 * 
	 * If $p['desc'] == true, posts will be sorted in descending order. Defaults to false.
	 * Note that this has other effects when combined with $p['withhead'] == true.
	 * 
	 * If $p['withhead'] == true, The thread head will be included in results and put at the 
	 * beginning of the array if $p['desc'] is false and at the end if $p['desc'] is true.
	 * 
	 * If $p['full'] == true, all post information will be fetched, including images.  If false,
	 * only the ID, name, trip, link, globalid, and time will be returned.  Defaults to false.
	 * 
	 * @param array $p The array containing the previously mentioned values.
	 * @param reference $sm A reference to a Smarty object
	 * 
	 * @return array An array of post data in assoc-array format, with the IP field stripped
	 * from each entry
	 */	
	function getreplies($p, & $sm);
	
}
?>
