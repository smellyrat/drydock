<?php
	
	
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			editpost.php
		Description:	Edit and perform moderation actions upon posts.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	require_once ("config.php");
	require_once ("common.php");
		
	/*
		OKAY HERE COMES THE HUGE LIST OF PARAMETERS THAT CAN BE PASSED TO THIS FUNCTION (aka: documentation)
		board -			The name of the board (its folder)
		post - 			The global ID of the post (can't change this, but it's still necessary)
		name - 			The name of the poster
		trip - 			The tripcode of the poster
		link -			Sage goes in the link field :o/saggy goes in da emo
		title - 		The subject (if this post begins a thread)
		body - 			The text of the post
		visible -		Hide/unhide the post	
		pin -			STICKY DIS SHIT
		lock -			LOCK DIS SHIT	
		permasage - 	THE SAGE OF DEATH
		remimage____ - 	Remove the image with the hash matching whatever's in the blank.
		moddo -			Delete some stuff
		modban -		Ban some people
	
		Was it good for you too?
	*/
	
	/*
		What can be done with this is a touchy issue, and thus my idea is the following:
		Unrestricted access to post editing is reserved for admins only.
		Moderators have the ability to sticky, lock, permasage, ban, and hide/unhide posts.  
		No editing functions are available.
	*/
	
	// First check if we even have the params we need
	if (!isset ($_GET['post']) || !isset ($_GET['board']))
	{
		THdie("No post and/or board parameter, nothing to do!");
	}
	
	// Get the board ID.
	$board_folder = trim($_GET['board']);
	
	// Check for local mod access or global mod/admin access.
	if ((is_in_csl($board_folder, $_SESSION['mod_array']) != 1) && ($_SESSION['admin'] != 1) && ($_SESSION['mod_global'] != 1))
	{
		THdie("You are not permitted to moderate posts on this board");
	}
	
	// Initialize admin powers
	if ((isset ($_SESSION["admin"])) && ($_SESSION["admin"] == 1))
	{
		$adminpowers = 1;
	}
	else
	{
		$adminpowers = 0;
	}
	
	$db = new ThornPostDBI();
	
	// Set some stuff up.
	$board_id = $db->getboardnumber($board_folder);
	$postid = intval($_GET['post']); // SQL injection protection :]
	$threadid = 0; // set this up later once we get some post data
	$ipstring = "";
	
	// Make sure we retrieved a valid board id
	if ($board_id == null)
	{
		THdie("That board does not exist!");
	}
	
	// $postarray will hold the assoc containing post data
	$postarray = array ();
	$postarray = $db->getsinglepost($postid, $board_id);

	// Make sure it exists
	if ($postarray == null)
	{
		THdie("Post with global ID of " . $postid . " and board /" . $board_folder . "/ does not exist.");
	}
	
	// Make a string from the IP for easy use in Smarty
	$ipstring = long2ip($postarray['ip']);
	
	// If it's a thread, the thread global ID and post global ID will be one and the same.
	// Otherwise, we need to look it up.
	if ($postarray['thread'] == 0)
	{
		$threadid = $postarray['globalid'];
	}
	else
	{
		$fetched_thread = $db->gettinfo($postarray['thread']);
		$threadid = $fetched_thread['globalid'];
	}
	
	// only bother if we're receiving POST data
	if (isset ($_POST['editsub']))
	{
		// Initialize some params for the updatepost call
		$name = $postarray['name'];
		$trip = $postarray['trip'];
		$link = $postarray['link'];
		$title = $postarray['title'];
		$body = $postarray['body'];
		$pin = $postarray['pin'];
		$lock = $postarray['lawk'];
		$psage = $postarray['permasage'];
		$visible = $postarray['visible'];
	
		$_POST['pin'] = intval($_POST['pin']);
		$_POST['lawk'] = intval($_POST['lawk']);
		$_POST['permasage'] = intval($_POST['permasage']);
	
		// We'll use these to keep track of what types of log messages
		// we need to write
		$postchanged = 0; // Some actual quality of the post has changed
		$lockdelta = 0; // Lock status has changed
		$pindelta = 0; // Pin status has changed
		$psagedelta = 0; // Permasage status has changed	
		$visibledelta = 0; // Visibility has changed
	
		// First let's take care of the special stuff
		if ($adminpowers > 0)
		{
			// Name
			if (isset ($_POST['name']) && $name != $_POST['name'])
			{
				$name = $_POST['name'];
				$postchanged = 1;
			}
	
			// Tripcode hash
			if (isset ($_POST['trip']) && $name != $_POST['trip'])
			{
				$trip = $_POST['trip'];
				$postchanged = 1;
			}
	
			// Link field
			if (isset ($_POST['link']) && $name != $_POST['link'])
			{
				$link = $_POST['link'];
				$postchanged = 1;
			}
	
			// Subject
			if (isset ($_POST['title']) && $name != $_POST['title'])
			{
				$title = $_POST['title'];
				$postchanged = 1;
			}
	
			// Post body
			if (isset ($_POST['body']) && $name != $_POST['body'])
			{
				$body = $_POST['body'];
				$postchanged = 1;
			}
		}
	
		// This section covers qualities that can be changed by mods
		// but are only important for threads
		if ($postarray['thread'] == 0)
		{
			// Pin status
			if (isset ($_POST['pin']) && $pin != $_POST['pin']) // Cover an explicit change (unchecked->checked)
			{
				$pin = $_POST['pin'];
				$pindelta = 1;
			}
			elseif (isset ($_POST['pin']) == false && $pin == true) // Cover an implicit change (checked->unchecked)
			{
				$pin = false;
				$pindelta = 1;
			}
	
			// Lock status
			if (isset ($_POST['lawk']) && $lock != $_POST['lawk']) // Cover an explicit change (unchecked->checked)
			{
				$lock = $_POST['lawk'];
				$lockdelta = 1;
			}
			elseif (isset ($_POST['lawk']) == false && $lock == true) // Cover an implicit change (checked->unchecked)
			{
				$lock = false;
				$lockdelta = 1;
			}
	
			// Lock status
			if (isset ($_POST['permasage']) && $psage != $_POST['permasage']) // Cover an explicit change (unchecked->checked)
			{
				$psage = $_POST['permasage'];
				$psagedelta = 1;
			}
			elseif (isset ($_POST['permasage']) == false && $psage == true) // Cover an implicit change (checked->unchecked)
			{
				$psage = false;
				$psagedelta = 1;
			}
		}
	
		// You know, this is the one thing that can be consistently changed.
		if (isset ($_POST['visible']) && $visible != $_POST['visible'])
		{
			$visible = $_POST['visible'];
			$visibledelta = 1;
		}
	
		// If we changed the post data, we're going to report the full edit.
		// If we changed the visibility status, we're going to report that as well.
		// In either of these two cases, updatepost will be called.
	
		if ($postchanged > 0)
		{
			// Update the post data
			$db->updatepost($postarray['globalid'], $postarray['board'], $name, $trip, $link, $title, $body, $visible, $pin, $lock, $psage);
	
			$actionstring = "edit\tgid:" . $postarray['globalid'] . "\tb:" . $postarray['board'];
			writelog($actionstring, "moderator");
		}
		elseif ($visibledelta > 0)
		{
			// Update the post data
			$db->updatepost($postarray['globalid'], $postarray['board'], $name, $trip, $link, $title, $body, $visible, $pin, $lock, $psage);
	
			$actionstring = "vis\tgid:" . $postarray['globalid'] . "\tb:" . $postarray['board'];
			writelog($actionstring, "moderator");
		}
	
		// Write lock/pin/permasage logs
		if ($lockdelta > 0)
		{
			$actionstring = "lock\tt:" . $postarray['id'] . "\tb:" . $postarray['board'] . "\tv:" . $lock;
			writelog($actionstring, "moderator");
		}
	
		if ($pindelta > 0)
		{
			$actionstring = "pin\tt:" . $postarray['id'] . "\tb:" . $postarray['board'] . "\tv:" . $pin;
			writelog($actionstring, "moderator");
		}
	
		if ($psagedelta > 0)
		{
			$actionstring = "psage\tt:" . $postarray['id'] . "\tb:" . $postarray['board'] . "\tv:" . $psage;
			writelog($actionstring, "moderator");
		}
	
		// Since we know it exists, let's fetch its images.
		$postarray['images'] = $db->getimgs($postarray['imgidx']);
		foreach ($postarray['images'] as $img)
		{
			if ($_POST['remimage' . strval($img['hash'])] != 0 && isset ($_POST['remimage' . strval($img['hash'])]))
			{
				// Make the DB call to delete the image
				$db->deleteimage($postarray['imgidx'], strval($img['hash']), $img['extra_info']);
	
				// And delete the physical file
				$path = THpath . "images/" . $postarray['imgidx'] . "/";
				unlink($path . $img['name']);
				unlink($path . $img['tname']);
	
				// Log this action
				$actionstring = "Delete img\timgidx:" . $postarray['imgidx'] . "\tn:" . $img['name'];
				writelog($actionstring, "moderator");
			}
		}
	
	}
	
	if (isset ($_POST['modban']) || isset ($_POST['moddo']))
	{
		if ($_POST['modban'] != "nil" || $_POST['moddo'] != "nil")
		{
			$moddb = new ThornModDBI();
	
			//Get post
			$targetid = $postarray['id'];
	
			// Find out if this is a thread
			if ($postarray['thread'] != 0) // Reply
			{
				$targetisthread = false;
			}
			else // Thread
				{
				$targetisthread = true;
			}
	
			if ($_POST['modban'] == "banip") // Ban an IP
			{
				$moddb->banipfrompost($targetid, $targetisthread, 0, $_POST['privatebanreason'], $_POST['publicbanreason'], $_POST['adminbanreason'], $_POST['banduration'], $_SESSION['username'] . " via mod panel");
			}
			elseif ($_POST['modban'] == "bansub") // Ban a subnet
			{
				$moddb->banipfrompost($targetid, $targetisthread, 1, $_POST['privatebanreason'], $_POST['publicbanreason'], $_POST['adminbanreason'], $_POST['banduration'], $_SESSION['username'] . " via mod panel");
			}
			elseif ($_POST['modban'] == "banthread" && $adminpowers > 0) // Ban a whole thread (requires admin powers)
			{
				$moddb->banipfromthread($targetid, $_POST['privatebanreason'], $_POST['publicbanreason'], $_POST['adminbanreason'], $_POST['banduration'], $_SESSION['username'] . " via mod panel (threadban)");
			}
	
			// Post deletion, if they have access
			if ($adminpowers > 0 && $_POST['moddo'] != "nil")
			{
				if ($targetisthread)
				{
					$actionstring = "delete\tt:" . $postarray['globalid'] . "\tb:" . $postarray['board'];
	
					if (THuserewrite)
					{
						$diereturn = 'Post(s) deleted.<br /><a href="' . THurl . $boardname . '">Return to board</a>';
					}
					else
					{
						$diereturn = 'Post(s) deleted.<br /><a href="' . THurl . 'drydock.php?b=' . $boardname . '">Return to board</a>';
					}
				}
				else
				{
					$actionstring = "delete:\tp:" . $threadid . "\tb:" . $postarray['board'] . "\tp:" . $postarray['globalid'];
	
					if (THuserewrite)
					{
						$diereturn = 'Post(s) deleted.<br /><a href="' . THurl . $boardname . '/thread/' . $threadop . '">Return to thread</a>';
					}
					else
					{
						$diereturn = 'Post(s) deleted.<br /><a href="' . THurl . 'drydock.php?b=' . $boardname . '&amp;i=' . $threadop . '">Return to thread</a>';
					}
				}
	
				if ($_POST['moddo'] == "killpost")
				{
					smclearcache($board, -1, $thread); // clear the associated cache for this thread
					smclearcache($board, -1, -1); // AND the board
					delimgs($moddb->delpost($targetid, $targetisthread));
				}
				elseif ($_POST['moddo'] == "killip")
				{
					delimgs($moddb->delipfrompost($targetid, $targetisthread, false));
					
					// Indicate that an entire IP is getting its posts deleted
					$actionstring = $actionstring . "\tip:" . $ipstring;
				}
				elseif ($_POST['moddo'] == "killsub")
				{
					delimgs($moddb->delipfrompost($targetid, $targetisthread, true));
					
					// Indicate that an entire subnet is getting its posts deleted
					// We do this by writing "sub" instead of "ip" and calling ipsub so that
					// the last octet will be a 0
					$actionstring = $actionstring . "\tsub:" . long2ip(ipsub($postarray['ip']));
				}
	
				// Write to the log
				writelog($actionstring, "moderator");
	
				//Display our link back.
				THdie($diereturn);
	
			}
			elseif ($_POST['moddo'] != "nil")
			{
				THdie("You lack sufficient ability to delete this post!");
			}
		}
	}
	
	// Attempt to move a thread (we can if we're an admin)
	if (isset ($_POST['movethread']) && $_POST['movethread'] != "nil")
	{
		if ($adminpowers > 0 && $postarray['thread'] == 0) // this last bit makes sure that it's a thread
		{
			$destboard = intval($_POST['movethread']);
			$destboard_name = $db->getboardname($destboard);
	
			if ($destboard_name == null)
			{
				THdie("You can't move a thread to a board that doesn't exist!");
			}
	
			// Pass the rest off to the DB
			$newthreadspot = $db->movethread($id, $destboard);
	
			// Check if it failed
			if ($newthreadspot == null)
			{
				THdie("Move failed!");
			}
	
			// Clear the relevant caches
			smclearcache($board_id, -1, $threadid); // clear the associated cache for this thread
			smclearcache($board_id, -1, -1); // clear the associated cache for the original board
			smclearcache($destboard, -1, -1); // clear the associated cache for the target board
	
			// Write to the log
			$actionstring = "Move thread\t(t:" . $thread . ",ob:" . $postarray['board'] . ") => (tid:" . $newthreadspot . ",b:" . $destboard . ")";
			writelog($actionstring, "moderator");
	
			if (THuserewrite)
			{
				THdie('Thread moved.<br /><a href="' . THurl . $destboard_name . '/thread/' . $newthreadspot . '">Return to thread</a>');
			}
			else
			{
				THdie('Thread moved.<br /><a href="' . THurl . 'drydock.php?b=' . $destboard_name . '&amp;i=' . $newthreadspot . '">Return to thread</a>');
			}
		}
		else
		{
			THdie("Invalid move thread attempt!");
		}
	}
	
	// Some stuff might have changed after all that, so let's refetch the data
	$postarray = $db->getsinglepost($postid, $board_id);
	$postarray['images'] = $db->getimgs($postarray['imgidx']);
	
	// Get the boards array, to possibly show a list for moving
	$boards = array ();
	$boards = $db->getboard(); // No parameters means everything gets fetched
	
	$sm = sminit("adminedit.tpl", null, "_admin", true); // Admin mode means NO caching. (and we provided a null id anyway)
	
//	$sm->debugging = true; // debug for now
//	$sm->debug_tpl = THpath."_Smarty/debug.tpl";
	
	// These can be pretty big, so we're going to assign by reference.
	$sm->assign_by_ref("boards", $boards);
	$sm->assign_by_ref("postarray", $postarray); // Contains all the post data
	
	// Board information
	$sm->assign("boardname", $board_folder);
	$sm->assign("boardid", $board_id);
	
	// Specific post location information
	$sm->assign("threadid", $threadid);
	$sm->assign("postid", $postid);
	
	// Misc
	$sm->assign("adminpowers", $adminpowers); // Administrative abiities
	$sm->assign("ipstring", $ipstring);
	
	// Show it!
	$sm->display("adminedit.tpl", null);
	die();
?>
