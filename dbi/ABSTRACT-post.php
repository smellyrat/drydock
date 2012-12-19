<?php

/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/ABSTRACT-post.php
	Description:    Abstract interface for a ThornPostDBI class.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
	
*/

/**
 * ThornPostDBI is a class which does a lot of functions related to
 * posting.  More specifically, it is used to add new threads,
 * replies, and images to the database, as well as retrieve various
 * pieces of information such as post location.  It has no
 * special internals.
 */
interface absThornPostDBI
{
	/**
	 * Get the thread head, with no images necessary.
	 * 
	 * @param int $t The (unique) ID of the thread
	 * 
	 * @return array An assoc-array with the post data
	 */
	function gettinfo($t);

	/**
	 * Posts a reply to a thread, updates the "bump" column of the relevant thread, 
	 * and updates the last post time of the relevant board. Note that, as with 
	 * putthread, images are stored using the separate putimgs() function. The 
	 * relevant caches are cleared by this function.
	 * 
	 * @param string $name The poster's name
	 * @param string $tpass The poster's encoded tripcode
	 * @param int $board The board to which this post's thread belongs
	 * @param string $title The title for this thread
	 * @param string $body The body text of the post
		 * @param string $link The poster's link (could be mailto, could be sage, who knows!)
	 * @param int $ip The ip2long()'d IP address of the poster
	 * @param bool $mod Is the poster a mod/admin (for future maintainability)
	 * @param bool $pin Should the thread be auto-pinned?
	 * @param bool $lock Should the thread be auto-locked?
	 * @param bool $permasage Should the thread be auto-permasaged?
	 * @param string $password The password for post deletion (can be an empty string)
	 * @param int $tyme The timestamp at which to post, defaults to the current system time
	 *
	 * @return int The unique ID of the thread
	 */
	function putthread($name, $tpass, $board, $title, $body, $link, $ip, $mod, $pin, $lock, $permasage, $password, $tyme = false);

	/**
	 * Posts a reply to a thread, updates the "bump" column of the relevant thread, 
	 * and updates the last post time of the relevant board. Note that, as with 
	 * putthread, images are stored using the separate putimgs() function. The 
	 * relevant caches are cleared by this function.
	 * 
	 * @param string $name The poster's name
	 * @param string $tpass The poster's encoded tripcode
	 * @param string $link The poster's link (could be mailto, could be sage, who knows!)
	 * @param int $board The board to which this post's thread belongs
	 * @param int $thread The thread for which this post is a reply (unique ID, not globalid)
	 * @param string $body The body text of the post
	 * @param int $ip The ip2long()'d IP address of the poster
	 * @param bool $mod Is the poster a mod/admin (for future maintainability)
	 * @param string $password The password for post deletion (can be an empty string)
	 * @param int $tyme The timestamp at which to post, defaults to the current system time
	 *
	 * @return int The unique ID of the reply
	 */
	function putpost($name, $tpass, $link, $board, $thread, $body, $ip, $mod, $password, $tyme = false);

	/**
	 * Puts image information into the database, and then updates the relevant thread
	 * or post with the image data's image index.
	 * 
	 * @param int $num The (unique) ID number of the post to which we are putting images
	 * @param bool $isthread If $num refers to a thread or not
	 * @param array $files An array containing information about the files to be uploaded (in assoc-format).
	 * Each element in $files will have:
	 *			string $file['hash']
	 *		The SHA-1 hash of the image file.
	 *			string $file['name']
	 *		The image's filename.
	 *			int $file['width']
	 *		The width in pixels of the image.
	 *			int $file['height']
	 *		The height in pixels of the image
	 * 			string $file['tname']
	 *		The name of the image's thumbnail.
	 *			int $file['twidth']
	 *		The thumbnail's width in pixels.
	 *			int $file['theight']
	 *		The thumbnail's height in pixels
	 *			int $file['fsize']
	 *		The image's filesize in K, rounded up.
	 *			bool $file['anim']
	 *		Is the image animated?
	 *
	 * @return int The image index associated with these images
	 */
	function putimgs($num, $isthread, $files);

	/**
	 * Purges a board after a new thread is posted.
	 * 
	 * @param int $boardid The ID of the board to purge
	 * 
	 * @return array A one-dimensional array of image indices to be deleted from disk (as they
	 * have just been deleted from the database)
	 */
	function purge($boardid);

	/**
	 * A function to check to see if any of the SHA-1 hashes in $hashes are
	 * already in the images database.  Note that this will only check within
	 * the given board ID.
	 * 
	 * @param array $hashes A one-dimensional string array of hashes
	 * @param int $boardid The ID of the board to check on.
	 * 
	 * @return int The number of found hashes
	 */
	function dupecheck($hashes, $boardid);

	/**
	 * This function gets a new global ID for the specified board.  It will
	 * increment the current ID for that board by one.
	 * 
	 * @param string $board The folder name of the board
	 * 
	 * @return int The new ID, or null if the specified board does not exist
	 */
	function getglobalid($board);

	/**
	 * Move a thread with a specified unique ID to a new board.
	 * 
	 * @param int $id The unique ID of the thread
	 * @param int $newboard The ID of the destination board
	 * 
	 * @return int The new global ID of the thread, or null if the move failed
	 */
	function movethread($id, $newboard);

	/**
	 * Update a pre-existing post in the DB with new information.  Note that no sort of
	 * access checking is performed so it is assumed that the user has the proper authorization
	 * for doing so. The unvisibletime field is updated to reflect that a mod has reviewed this
	 * particular post already.
	 * 
	 * @param int $id The global ID of the post
	 * @param int $board The board ID of the post
	 * @param string $name The new name field
	 * @param string $trip The new tripcode hash
	 * @param string $link THe new link field
	 * @param string $subject The new title field
	 * @param string $body The new post body
	 * @param int $visible The visible status
	 * @param int $pin The pin status (only matters for threads)
	 * @param int $lock The lock status (only matters for threads)
	 * @param int $permasage The permasage status (only matters for threads)
	 */
	function updatepost($id, $board, $name, $trip, $link, $subject, $body, $visible, $pin, $lock, $permasage);

	/**
	 * Remove a single image from the database, and optionally its corresponding
	 * metadata info, if it has one
	 * 
	 * @param int $imgidx The index of the image
	 * @param string $hash The SHA-1 hash of the image
	 * @param int $extra_info The ID for the metadata table entry, if there is one.  Defaults
	 * to -1, which signifies no such entry.
	 */
	function deleteimage($imgidx, $hash, $extra_info = -1);

	/**
	 * Check if a particular IP has posted with a certain
	 * number of seconds.
	 * 
	 * @param int $ip The (ip2longed) IP to search for
	 * @param int $timeframe The timeframe within which to check,
	 * in seconds.  Defaults to 30.
	 * 
	 * @return bool True if a post was found within the ramge
	 * of now and now minus the timeframe
	 */
	function postedwithintime($ip, $timeframe = 30);
}
?>
