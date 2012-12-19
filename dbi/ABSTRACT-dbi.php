<?php

/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-dbi.php
	Description:    Abstract interface for a base ThornDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

/**
 * The base DBI class for database queries.  All other DBIs are
 * extensions of this base class.  Ideally it should only have
 * code that's common to multiple sections.
 */
interface absThornDBI
{
	/**
	* Cleans a string completely, including trimming of trailing/leading
	* whitespace.
	* 
	* @param string $call The string to be cleaned
	* 
	* @return string The "cleaned" version of the string
	*/
	function clean($call);

	/**
	* Escapes potentially "malicious" characters in a string
	* 
	* @param string $call The string to be cleaned
	* 
	* @return string The "cleaned" version of the string
	*/
	function escape_string($call);

	/**
	 * Returns the ID for the last-inserted row
	 * 
	 * @return int The last inserted row's ID
	 */
	function lastid();
	
	/**
	 * Returns the number of affected rows from the
	 * last query
	 * 
	 * @return int The number of affected rows
	 */
	 function affectedrows();

	/**
	 * This function retrieves information about a specific board,
	 * specified by an ID
	 * 
	 * @param $id An integer ID
	 * 
	 * @return array An assoc-array containing board information
	 */
	function getbinfo($id);

	/**
	 * Execute the query and return the resulting assoc-array, or null
	 * if the call failed
	 * 
	 * @param string $call The SQL query to execute
	 * 
	 * @return array An associative array, or null if the call failed
	 */
	function myassoc($call);

	/**
	 * Execute the query and return the resulting array, or null
	 * if the call failed
	 * 
	 * @param string $call The SQL query to execute
	 * 
	 * @return array An array, or null if the call failed
	 */
	function myarray($call);

	/**
	 * Execute a query and return its result, null if the call failed
	 * or if there was no result
	 * 
	 * @param string $call The SQL query to execute
	 * 
	 * @return mixed The result (a scalar variable) or null if it failed
	 */
	function myresult($call);

	/**
	 * Execute a query and return its resource, null if the call failed.
	 * 
	 * @param string $call The SQL query to execute
	 * 
	 * @return resource The resource object (what type is implementation-dependent) or null
	 */
	function myquery($call);

	/**
	 * Encapsulate executing a query and iteratively fetching the assoc for each
	 * row, instead returning a simple array of associative arrays
	 * 
	 * @param string $call The SQL query to execute
	 * 
	 * @return array Array of associative arrays (can be size 0)
	 */
	function mymultiarray($call);

	/**
	 * Returns the number of threads between two specified times.  If this
	 * object has a corresponding board info member ($binfo) set, it will
	 * check only that board.  The check is performed inclusive.
	 * 
	 * @param int $start Starting time, in timestamp format
	 * @param int $end Ending time, in timestamp format
	 * 
	 * @return int Thread count
	 */
	function timecount($start, $end);

	/**
	 * Returns the times of all threads since $since.  If this
	 * object has a corresponding board info member ($binfo) set,
	 * it will check only in that board.
	 *
	 * @param int $since A timestamp
	 *
	 * @return array Thread IDs
	 */
	function gettimessince($since);

	/**
	 * Get the images associated with a certain post by its image index.
	 * 
	 * @param int $imgidx The image index to search for
	 * 
	 * @return array Images (blank if none), which will have an extra
	 * "exif_text" field corresponding with its associated metadata
	 * string in the extra info table
	 */
	function getimgs($imgidx);

	/**
	 * Get the latest blotter entries, perhaps associated with a certain board
	 * 
	 * @param int $board The board for which the entries are being retrieved
	 * 
	 * @return array An array of entries (blank if none)
	 */
	function getblotter($board);

	/**
	 * Returns an index of the boards. $p is an array which has various
	 * values used for altering the produced output:
	 * 
	 * If $p['full'] == true, all board information will be fetched.  If false,
	 * only the board ID, name, and description are returned.  Defaults to false.
	 * 
	 * If $p['sortmethod'] == "id", boards are sorted by ID number.  If "name", boards
	 * are sorted by name.  If "last", boards are sorted by last post time. Defaults to "id".
	 * 
	 * If $p['desc'] == false, the boards are returned in ascending order. Defaults to false.
	 * 
	 * @param array $p The array containing the previously mentioned values.
	 * @param reference $sm A reference to a Smarty object
	 * 
	 * @return array An array of assoc-arrays (blank if none)
	 */
	function getindex($p, & $sm);

	/**
	 * Check to see if an IP is banned.  Will check both the actual IP and the IP's last two
	 * subnets.
	 * 
	 * @param mixed $ip  The IP address.  If it comes in as an int, long2ip will be used.  
	 * If it comes in as a string, no additionally conversion is performed.  If it comes 
	 * in as null, it will default to $_SERVER['REMOTE_ADDR'].
	 *
	 * @return bool Whether the IP is banned or not
	 */
	function checkban($ip = null);

	/**
	 * 	Get ban information for a particular IP.  If the ban is a warning, or if the ban has 
	 * expired, the ban will additionally be moved out of the active bans table and into the 
	 * ban history table if $clear is set to true. $clear should typically be true unless
	 * you're trying to view the current ban history for another IP as an administrator or
	 * something.
	 * 
	 * @param mixed $ip  The IP address.  If it comes in as an int, long2ip will be used.  
	 * If it comes in as a string, no additionally conversion is performed.  If it comes in 
	 * as null, it will default to $_SERVER['REMOTE_ADDR'].
	 * @param bool $clear If this is set to true, warnings and expired bans will be moved
	 * to the ban history table. This defaults to true.
	 *
	 * @return array An associative array of ban information
	 */
	function getban($ip = null);

	/**
	 * Get an array of board information, will optionally filter by ID and/or folder
	 * 
	 * @param int $id The board ID to optionally filter by
	 * @param string $folder The board filter to optionally filter by
	 * 
	 * @return array An array of assoc-arrays containing board info.  The index of each
	 * element corresponds with the ID of the board, if the ID and folder are not specified.
	 */
	function getboard($id = 0, $folder = "");

	/**
	 * Get the folder name of a board from an ID
	 * 
	 * @param int $id The board ID
	 * 
	 * @return string The board folder, or null if it does not exist
	 */
	function getboardname($number);

	/**
	 * Get the ID of a board from a folder
	 * 
	 * @param string $folder The board folder
	 * 
	 * @return int The board ID, or null if it does not exist
	 */
	function getboardnumber($folder);
	
	/**
	 * Get the registration status of a board from an ID
	 * 
	 * @param int $board The board ID
	 * 
	 * @return bool If the board requires users to be registered
	 */
	function isboardreg($board);
	
	/**
	 * Retrieve the template set name for a board.
	 * 
	 * @param int $board The board ID
	 * 
	 * @param string The template set name
	 */
	function getboardtemplate($board);

	/**
	 * Insert metadata info into the extra info table, and return the ID
	 * 
	 * @param string $exif The (already HTML-escaped, but not DB-escaped) metadata string to store
	 * 
	 * @return int The insert ID, or 0 if it failed
	 */
	function addexifdata($exif);
	
	/**
	 * Find a post with a given global ID and board.  Return
	 * an int indicating its location.
	 * 
	 * @param int $globalid The global ID of the post
	 * @param int $board The board ID
	 * 
	 * @return int The post location. 1 for in the threads table,
	 * 2 for in the replies table, and 0 if not found.
	 */
	function findpost($globalid, $board);
	
	/**
	 * Get a single post's data from either the threads or the replies table
	 * (the function will find out which one is necessary)
	 * 
	 * @param int $id The global ID of the post
	 * @param int $board The board where the post may be found
	 * 
	 * @return array An assoc-array containing the post data, or null if there was no match
	 */
	function getsinglepost($id, $board);
	
	
	/**
	 * This function gets global IDs for a particular thread and possibly a particular
	 * reply.  If the post ID is not provided it gets treated as a thread lookup.
	 * 
	 * @param int $threadid The ID of the thread
	 * @param int $postid The ID of the post, defaults to -1
	 * 
	 * @return array If $postid is defined, returns an array with the elements 'post_loc' and 'thread_loc'.
	 * If $postid is not defined, returns an array with the element 'thread_loc'
	 */
	function getpostlocation($threadid, $postid = -1);
	
}
?>