<?php
/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-tools.php
	Description:    Abstract interface for a ThornToolsDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
	
*/

/**
 * ThornToolsDBI is used for specific pages like recentposts.php
 * and recentpics.php.  If a particular tool needs a specialized kind
 * of database query, this might be an appropriate place to have
 * the function encapsulating that query.
 */
interface absThornToolsDBI
{
	/**
	 * Retrieve an image count based on the specified filtering parameters
	 * 
	 * @param int $board The ID of the board to filter images by (experimental).
	 * If $board is 0, no filtering will be performed.
	 * 
	 * @return int The number of images which fulfill this criteria
	 */
	function getpicscount($board);

	/**
	 * Retrieve a post count based on the specified filtering parameters.
	 * 
	 * @param bool $get_threads True if threads are being retrieved (false is for replies)
	 * @param int $board The ID of the board, -1 to not filter by board
	 * @param bool $showhidden Include posts that have been hidden already
	 * 
	 * @return int The number of posts which fulfill this criteria
	 */
	function getpostscount($get_threads, $board, $showhidden);
	
	/**
	 * Retrieve an array of assocs containing image data
	 * 
	 * @param int $offset The offset from which to start (for going through "pages")
	 * @param int $board The board to filter images by (experimental).
	 * If $board is 0, no filtering will be performed.
	 * 
	 * @return array An array of assoc-arrays containing image data- the arrays
	 * will have extra elements named "thread_board", "thread_id", "thread_globalid",
	 * "reply_board", "reply_id", and "reply_globalid" to eliminate the need
	 * for a second lookup query
	 */
	function getpics($offset, $board);
	
	/**
	 * Retrieve an array of assocs containing post data
	 * 
	 * @param int $offset The offset from which to start (for going through "pages")
	 * @param bool $get_threads True if threads are being retrieved (false is for replies)
	 * @param int $board The ID of the board, -1 to not filter by board
	 * @param bool $showhidden Include posts that have been hidden already
	 * 
	 * @return array An array of assoc-arrays containing post data - replies will 
	 * have an additional "thread_globalid" entry in each assoc
	 */
	function getposts($offset, $get_threads, $board, $showhidden);
	
	/**
	 * Retrieve the top 15 threads from the news board.
	 * 
	 * @return array An array of assoc-arrays containing thread data
	 */
	function getnewsthreads();
	
	/**
	 * Determine if a user can report a post
	 * (have to wait a minute between reports, can't report the same thing
	 * twice, does the post even exist, etc...)
	 * 
	 * @param int $post The global ID of the post
	 * @param int $board The board ID
	 * 
	 * @return int 0 if the user can submit,
	 * 1 if the user needs to wait before reporting another post,
	 * 2 if the user has already reported it,
	 * 3 if the post+board combo don't exist,
	 * 4 if it's already been processed
	 */
	 function checkreportpost($post, $board);
	
	/**
	 * Report a post and for a general kind of reason (illegal,
	 * rule violation, etc.)
	 * 
	 * @param int $post The global ID of the post
	 * @param int $board The board ID
	 * @param int $category The category of violation
	 */
	function reportpost($post, $board, $category);
	
	/**
	 * Retrieve a static page with a given name
	 * 
	 * @param string $name The (unique) name of the page
	 * 
	 * @return array An assoc-array with the static page information
	 */
	function getstaticpage($name);


	/**
	 * Retrieve a count for the threads posted in the specified board
	 * 
	 * @param string $board The folder id of the board
	 * 
	 * @return int count of threads on that board
	 */
	function getthreadcount($board);
}
?>
