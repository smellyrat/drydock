<?php

	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			recentpics.php (aka ThornLight)
		Description:	Show the most recent posted pictures for moderator use
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/

require_once ("config.php");
require_once ("common.php");
if (!$_SESSION['admin'] && !$_SESSION['moderator'])
{
	THdie("Sorry, you do not have the proper permissions set to be here, or you are not logged in.");
}
else
{
	$db = new ThornToolsDBI();

	// Init some stuff
	if(isset($_GET['board']))
	{
		$board_folder = trim($_GET['board']); //trim the board name from get
	} else {
		$board_folder = "";
	}
	$boardid = 0;
	$boardlink = "";
	$offset = 0;
	$offsetback = 0;
	$offsetfwd = 0;
	$count = 0;

	// We just append this to the end of all the SQL queries/links.  Makes things simpler because we only have to do it once.
	if ($board_folder != "" && $db->getboardnumber($board_folder))
	{
		$boardid = $db->getboardnumber($board_folder);
		$boardlink = "&board=" . $board_folder;
	}
	else
	{
		$board_folder = ""; // Clear $board if the getboardnumber call failed
	}

	$count = $db->getpicscount($boardid);

	if (isset ($_GET['offset']))
	{
		$offset = intval($_GET['offset']);

		if ($offset < 0)
		{
			$offset = 0;
		}
	}

	$beginning = $count -40 - $offset;

	if ($beginning < 0)
	{
		$beginning = 0;
	}
	//Beginning should never be greater than $count, for the reason that $offset is always >= 0

	// Get the images
	$imgs = array ();
	$imgs = $db->getpics($offset, $boardid);

	// Get the boards array, to show a list for filtering
	$boards = array ();
	$boards = $db->getboard(); // No parameters means everything gets fetched

	$sm = sminit("adminrecentpics.tpl", null, "_admin", true); // Admin mode means NO caching. (and we provided a null id anyway)

	// These can be pretty big, so we're going to assign by reference.
	$sm->assignbyref("imgs", $imgs);
	$sm->assignbyref("boards", $boards);

	$sm->assign("board_folder", $board_folder); // name of the folder (for filtering)
	$sm->assign("boardlink", $boardlink); //some stuff to append for easy link creation

	$sm->assign("total_count", $count); // total number (for optionally showing the paging links)
	$sm->assign("offset", $offset);
	$sm->assign("offsetfwd", $offsetfwd);
	$sm->assign("offsetback", $offsetback);

	// Show it!
	$sm->display("adminrecentpics.tpl", null);
	die();
}
?>