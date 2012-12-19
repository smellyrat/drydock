<?php

/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-mod.php
	Description:    Abstract interface for a ThornModDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
	
*/

/**
 * A DBI with a lot of moderation functions, mostly focused on
 * post deletion, banning, and blotter/capcode/wordfilter
 * manipulation.  No special member variables appear to be
 * necessary.
 */
interface absThornModDBI
{
	/**
	 * Add a banned IP to the databse.  If the ban is for a specific IP (not a subnet), it will
	 * check to see if the IP is already banned first.
	 * 
	 * @param mixed $id The ip2long'd IP to ban.  Additionally, a string is acceptable.  If is_int($ip) is 
	 * true it will be converted back into a string.
	 * @param int $subnet 0 to ban no subnet, 1 to ban the subnet, 2 to ban the class C subnet (be careful with this)
	 * @param string $privatereason The reason shown to the user (privately) why they were banned
	 * @param string $publicreason The reason shown publically ("(USER WAS BANNED FOR THIS POST)")
	 * @param string $adminreason The reason shown to admins (used for notes)
	 * @param string $postdata What post was the user banned for? (if any)
	 * @param int $duration The duration of the ban. 0 for warning, -1 for perma, anything else is in hours
	 * @param string $bannedby Who performed the banning
	 * 
	 * @return bool True if the ban succeeded, false if it failed (if it already exists in the DB or something)
	 */
	function banip($ip, $subnet, $privatereason, $publicreason, $adminreason, $postdata, $duration, $bannedby);

	/**
	 * Append a public ban message to the end of a post, with HTML formatting and the like.
	 * 
	 * @param int $id The (unique) ID of the post
	 * @param bool $isthread Whether the post is a thread or not
	 * @param string $publicbanreason The publically displayed reason.  
	 * Defaults to "USER HAS BEEN BANNED FOR THIS POST"
	 */
	function banbody($id, $isthread, $publicbanreason = "USER HAS BEEN BANNED FOR THIS POST");

	/**
	 * Bans an IP or subnet by fetching the IP from a post or thread.  Calls banip(), touchpost(),
	 * and touchreports() to mark all reports for this post as valid.
	 * 
	 * @param int $id The (unique) ID number of the thread/post
	 * @param bool $isthread Whether $id refers to a thread (if true) or a reply (if false)
	 * @param bool $subnet  If true, ban the IP's subnet. banip() has more information on banning subnets
	 * @param string $privatereason The reason shown to the user (privately) why they were banned
	 * @param string $publicreason The reason shown publically ("(USER WAS BANNED FOR THIS POST)")
	 * @param string $adminreason The reason shown to admins (used for notes)
	 * @param int $duration The duration of the ban. 0 for warning, -1 for perma, anything else is in hours
	 * @param string $bannedby Who performed the banning
	 * 
	 * @return bool True if the ban succeeded, false if it failed
	 */
	function banipfrompost($id, $isthread, $subnet, $privatereason, $publicreason, $adminreason, $duration, $bannedby);

	/**
	 * Ban an entire thread.  Calls banipfrompost().
	 * 
	 * @param int $id The (unique) ID number of the thread/post
	 * @param string $privatereason The reason shown to the user (privately) why they were banned
	 * @param string $publicreason The reason shown publically ("(USER WAS BANNED FOR THIS POST)")
	 * @param string $adminreason The reason shown to admins (used for notes)
	 * @param int $duration The duration of the ban. 0 for warning, -1 for perma, anything else is in hours
	 * @param string $bannedby Who performed the banning
	 */
	function banipfromthread($id, $privatereason, $publicreason, $adminreason, $duration, $bannedby);

	/**
	 * Deletes a ban from the active bans table, and moves it to the ban history table.
	 * 
	 * @param int $id The ID of the ban to delete
	 * @param string $reason Why the ban is getting lifted (defaults to "None provided")
	 */
	function delban($id, $reason = "None provided");

	/**
	 * Retrieve a ban from the database based on ID
	 * 
	 * @param int $id The ID of the ban
	 * 
	 * @return array An assoc-array with the ban data
	 */
	function getbanfromid($id);

	/**
	 * Get ban history information for a particular IP.  Note that this does not include
	 * active bans.
	 * 
	 * @param int $ip The IP address.  long2ip will be used on it.
	 * 
	 * @return array An array of assoc-arrays containing ban history information
	 */
	function getiphistory($ip);

	/**
	 * Return all ban information.  Intended for rendering the ban management
	 * admin page.
	 * 
	 * @return array An array of assoc-arrays containing ban data
	 */
	function getallbans();

	/**
	 * Deletes a post or thread.  If $isthread, all posts in the thread
	 * will be deleted as well.  If the deleted post(s) have images,
	 * their database information is deleted and their image indexes are 
	 * stored in an array and returned.  Calls touchreports() to mark
	 * all associated reports with this post to be deleted as valid.
	 * 
	 * @param int $id The (unique) ID number of the offending post
	 * @param bool $isthread Whether the target post is a thread or not
	 * 
	 * @return array A one-dimensional array of affected images
	 */
	function delpost($id, $isthread);

	/**
	 * Deletes all posts from an IP. If the IP created threads, all posts from those threads are 
	 * deleted too. If $delsub, all posts from the subnet are fragged. If any if the fragged 
	 * posts contain images, that image info is removed from the database, and the image indexes
	 * are stored in an array to be passed to Thorn's image deletion function.  Calls touchreports()
	 * to mark all associated reports with this IP or IP range as valid, as well as to mark all replies
	 * in threads started by this IP or IP range as indeterminate (status 3).
	 * 
	 * @param int $ip If the post is a thread (false if it is a reply)
	 * @param bool $delsub Delete from the entire subnet or just the one IP.  Defaults to false.
	 * 
	 * @return array A one-dimensional array of affected images
	 */
	function delip($ip, $delsub = false);

	/**
	 * Gets the IP of a post for which we want to delete all posts from that IP,
	 * and then deletes all posts from that IP through the use of the delip function.
	 * 
	 * @param int $id The (unique) ID for the post
	 * @param bool $isthread If the post is a thread (false if it is a reply)
	 * @param bool $subnet Delete from the entire subnet or just the one IP.  Defaults to false.
	 * 
	 * @return array A one-dimensional array of affected images (like delip)
	 */
	function delipfrompost($id, $isthread, $subnet = false);

	/**
	 * Set up a new board, assigning default values for most of the
	 * board settings.
	 * 
	 * @param string $name The name of the board
	 * @param string $folder The folder for the board (should be unique)
	 * @param string $about A description for the board
	 * @param string $rules Some initial rules for the board
	 * 
	 * @return int The insertion ID for the board
	 */
	function makeboard($name, $folder, $about, $rules);

	/**
	 * Update board information based on the values passed in as the array.
	 * Change the board field for posts associated with a particular board 
	 * if that board's ID has changed, for whatever reason.  If there is a
	 * change in a board's ID, it should be stored in $board['id'] with the
	 * previous ID stored in $board['oldid'] or the results will be unexpected.
	 * 
	 * @param array $boards An array of assoc-arrays with a board's information in each assoc
	 */
	function updateboards($boards);

	/**
	 * Delete all the threads, posts, and image information from a certain board.
	 * It will return a list of the image indexes so that they can be deleted
	 * via some other function.
	 * 
	 * @param name $board The ID of the board to frag
	 * 
	 * @return array A one-dimensional array of image indices
	 */
	function fragboard($board);

	/**
	 * Delete the board entirely.  Note that this is different from fragboard, and
	 * it will assume you have already called fragboard. Obviously, you should
	 * be very careful with this function.
	 * 
	 * @param int $board The ID of the board to delete
	 */
	function removeboard($board);

	/**
	 * Insert either a blotter post (type 1), capcode (type 2), or wordfilter (type 3),
	 * based upon the passed type parameter.
	 * 
	 * @param int $type What type of item to insert (1 for blotter post, 2 for capcode, 3 for wordfilter).
	 * Defaults to -1
	 * 
	 * @param string $field1 Its meaning is based upon the type.
	 * For blotter posts: the entry
	 * For capcodes: capcodefrom
	 * For wordfilter: filterfrom
	 * 
	 * @param mixed $field2 Its meaning is based upon the type.
	 * For blotter posts: the target board (integer)
	 * For capcodes: capcodeto (string)
	 * For wordfilters: filterto (string)
	 * 
	 * @param string $field3 Its meaning is based upon the type.
	 * For blotter posts: nothing
	 * For capcodes: notes
	 * For wordfilters: notes
	 * 
	 * @return int The resulting insertion ID
	 */
	function insertBCW($type = -1, $field1 = "", $field2 = "", $field3 = "");

	/**
	 * Update either a blotter post (type 1), capcode (type 2), or wordfilter (type 3),
	 * based upon the passed type parameter and selected by the particular ID.
	 * 
	 * @param int $type What type of item to update (1 for blotter post, 2 for capcode, 3 for wordfilter).
	 * Defaults to -1
	 * @param int $id The ID of the item to update
	 * 
	 * @param string $field1 Its meaning is based upon the type.
	 * For blotter posts: the entry
	 * For capcodes: capcodefrom
	 * For wordfilter: filterfrom
	 * 
	 * @param mixed $field2 Its meaning is based upon the type.
	 * For blotter posts: the target board (integer)
	 * For capcodes: capcodeto (string)
	 * For wordfilters: filterto (string)
	 * 
	 * @param string $field3 Its meaning is based upon the type.
	 * For blotter posts: nothing
	 * For capcodes: notes
	 * For wordfilters: notes
	 */
	function updateBCW($type = -1, $id, $field1 = "", $field2 = "", $field3 = "");

	/**
	 * Delete either a blotter post (type 1), capcode (type 2), or wordfilter (type 3),
	 * based upon the passed type parameter and selected by the particular ID.
	 * 
	 * @param int $type What type of item to delete (1 for blotter post, 2 for capcode, 3 for wordfilter).
	 * Defaults to -1
	 * @param int $id The ID of the item to delete
	 */
	function deleteBCW($type = -1, $id);

	/**
	 * Retrieve either all blotter posts (type 1), capcodes (type 2), or wordfilters (type 3),
	 * based upon the passed type parameter.
	 * 
	 * @param int $type What type of items to select (1 for blotter posts, 2 for capcodes, 3 for wordfilters).
	 * Defaults to -1
	 * 
	 * @return array An array of assoc-arrays
	 */
	function fetchBCW($type = -1);

	/**
	 * Attempt to delete all the specified posts in the array
	 * using a specified password.  It will clear the caches for
	 * matching threads and delete images for all posts as well.
	 * Additionally, it will set the status of all relevant
	 * reports to 3 via touchreports().
	 * 
	 * @param array $posts An array of post global IDs
	 * @param int $board The board ID
	 * @param string $password The password to search for
	 * 
	 * @return int The number of posts deleted
	 */
	function userdelpost($posts, $board, $password);

	/**
	 * Update the specified post's unvisibletime to indicate
	 * that some moderation action has already been performed on it.
	 * 
	 * @param int $id The (unique) ID of the post
	 * @param bool $isthread If the post is a thread or not
	 * @param int $time The time (optional). If null, will be set to the
	 * current time.
	 */
	function touchpost($id, $isthread, $time = null);

	/**
	 * Retrieve the 20 "top" reports (technically aggregates of reports) in assoc-array form.
	 * Only one report will be retrieved for each post, with two extra fields returned for each
	 * record: "reporter_count", an int indicating the number of people who have reported
	 * a particular post, "earliest_report", another int indicating the time it was first
	 * reported, and "avg_category", another int indicating the average category that a post
	 * was reported for.  Optionally this will filter by a board.
	 * 
	 * The sorting methodology to determine "top":
	 * 1) Category (ascending because illegal content is 1)
	 * 2) Reporter count (descending)
	 * 3) Earliest report (ascending)
	 * 
	 * @param int $board The optional board ID to filter by
	 * 
	 * @return array An array of assoc-arrays containing report information
	 */
	function gettopreports($board = 0);

	/**
	 * Alter all reports in the table for a particular post to have a certain
	 * status (1 for found valid, 2 for found entirely invalid, 3 as a catchall
	 * for neither outright valid or invalid, or when the user deletes their own
	 * post)
	 * 
	 * @param int $post The post globalid
	 * @param int $board The board ID
	 * @param int $status The new status for all relevant reports (defaults to 3)
	 */
	function touchreports($post, $board, $status = 3);

	/**
	 * Fetch the 15 most recently-reviewed reports for a particular IP.
	 * 
	 * @param int $ip The (ip2longed) IP to perform a lookup for.  If $ip is null, it will
	 * use $_SERVER['REMOTE_ADDR']
	 * 
	 * @return array An array of assoc-arrays containing report data
	 */
	function recentreportsfromip($ip = null);

	/**
	 * Fetch the 10 most recent posts (both threads and replies) for a particular IP.
	 * 
	 * @param int $ip The (ip2longed) IP to perform a lookup for.  If $ip is null, it will
	 * use $_SERVER['REMOTE_ADDR']
	 * 
	 * @return array An array of assoc-arrays containing post data, which will also contain
	 * the element "thread_globalid" for each reply
	 */
	function recentpostsfromip($ip = null);

	/**
	 * This function retrieves the location of a post based on a given image index. It will
	 * retrieve the thread location, the board ID, and, if the post is a reply, the specific
	 * post location (in the event of it being a thread this would be redundant).
	 * 
	 * @param int $imgidx The image index by which to search
	 * 
	 * @return array If the post is a reply, this returns an array with the elements 'post_loc', 
	 * 'thread_loc', and 'board'. If it is a reply, it returns an array with the elements 
	 * 'thread_loc' and 'board'.  All of these elements correspond to global IDs.  If no match
	 * was found, the array will return null.
	 */
	function getpostfromimgidx($imgidx);
	
	/**
	 * Add a page with the given name and title into the static
	 * pages table.  The publishing status will default to
	 * admin-only.
	 * 
	 * @param string $name The name of the page, used as an identifier, must be unique
	 * @param string $title The title of the page, shown in the HTML
	 * 
	 * @return int The page ID
	 */
	function addstaticpage($name, $title);
	
	/**
	 * Check if a page name exists, optionally if it exists with a certain ID.
	 * If you want to check before making a new page, only use $name. Use $id
	 * for editing when you want to verify that you're not changing the name
	 * of a page to something that belongs to another page.
	 * 
	 * @param string $name The name to check for
	 * @param int $id The ID to optionally check for
	 * 
	 * @return bool If $id is null, this will return true if a page has a name
	 * equal to $name.  If $id is not null, this will return true if a page has
	 * a name equal to $name and NOT equal to $id.
	 */
	function checkstaticpagename($name, $id=null);
	
	/**
	 * Delete a static page with the given ID.  Make sure to clean
	 * the cached version afterwards.
	 * 
	 * @param int $id The ID of the page to delete
	 */
	function delstaticpage($id);
	
	/**
	 * Edit a static page with the given ID.  Make sure to clean
	 * the cached version afterwards.
	 * 
	 * @param int $id The ID of the page to edit
	 * @param string $name The name of the page
	 * @param string $title The title of the page to show
	 * @param string $content The body of the page
	 * @param int $publish The publishing status of the page
	 */
	function editstaticpage($id, $name, $title, $content, $publish);
	
	/**
	 * Retrieve all static pages in an array of assoc-arrays.
	 * 
	 * @return array An array of assoc-arrays containing static page info
	 */
	function getstaticpages();
	
}
?>
