<?php
/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-board.php
	Description:    Abstract interface for a ThornBoardDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
	
*/

/**
 * An abstract interface for ThornBoardDBI - which should be used
 * for board-specific functionality.
 * 
 * Instances of this should probably have board information in an
 * assoc-array stored as the $binfo member.  It might also have a 
 * scalar $payj variable to indicate the current page.  It might also 
 * have date-specific data stored in $on as an array (with year,
 * month, and/or day as possible entries), or possibly even blotter 
 * items stored in $blotterentries.
 */
interface absThornBoardDBI
{

	/**
	 * Get all threads on this board. $p is an array which has various
	 * values used for altering the produced output:
	 * 
	 * If $p['full'] == true, all thread information will be fetched.  If false,
	 * only basic information will be returned.  Defaults to false.
	 * 
	 * If $p['sortmethod'] == "id", threads are sorted by ID number.  If "title", threads
	 * are sorted by title alphabetically.  If "bump", threads are sorted by last bumped time. 
	 * If "time", they will be sorted by date. Defaults to "bump".
	 * 
	 * If $p['desc'] == false, the threads are returned in ascending order. Defaults to true.
	 * 
	 * If $p['date'] == true, it will perform a getdate() call on each entry and put the result 
	 * in a 'date' field with each entry. Defaults to false.
	 * 
	 * @param array $p The array containing the previously mentioned values.
	 * @param reference $sm A reference to a Smarty object
	 * 
	 * @return array An array of assoc-arrays (blank if none)
	 */	
	function getallthreads($p, & $sm);

	/**
	 * Get sample threads on this board - Get sample threads for this board - the ones 
	 * intended to be shown in the main area of the page, with the last $binfo['perth'] 
	 * replies per thread. $p is an array which has various values used for altering 
	 * the produced output:
	 * 
	 * If $p['full'] == true, all thread information will be fetched.  If false,
	 * only basic information will be returned.  Defaults to false.
	 * 
	 * If $p['sortmethod'] == "id", threads are sorted by ID number.  If "title", threads
	 * are sorted by title alphabetically.  If "bump", threads are sorted by last bumped time. 
	 * If "time", they will be sorted by date. Defaults to "bump".
	 * 
	 * If $p['tdesc'] == false, the threads are returned in ascending order. Defaults to true.
	 * 
	 * If $p['rdesc'] == true, it will sort all replies in descending order (Replies are always
	 * sorted by time).  Defaults to false.
	 * 
	 * @param array $p The array containing the previously mentioned values.
	 * @param reference $sm A reference to a Smarty object
	 * 
	 * @return array An array of assoc-arrays (blank if none)
	 */		
	function getsthreads($p, & $sm);
	
}
?>
