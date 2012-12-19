<?php

	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			recentposts.php (aka ThornQuasiLight)
		Description:	Show the most recent posts for moderator use
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/

require_once("config.php");
require_once("common.php");

if(!$_SESSION['admin'] && !$_SESSION['moderator']) 
{ 
	THdie("Sorry, you do not have the proper permissions set to be here, or you are not logged in."); 
} 
else 
{	
	$db=new ThornToolsDBI();	

	function comp_post_ids($a, $b)
	{
		$first=$a['id'];
		$second=$b['id'];
		if ($first == $second) 
		{ //This should never happen, but whatever.
			return 0;
		}

		return ($first < $second) ? 1 : -1;
	}

	// Init some stuff
	if(isset($_GET['board']))
	{
		$board_folder = trim($_GET['board']); //trim the board name from get
	} else {
		$board_folder = "";
	}
	$isthread = true; // default to true
	$showhidden = false; // default to false
	$boardid = -1;
	$boardlink = "";
	$offset = 0;
	$offsetback = 0;
	$offsetfwd = 0;
	$count = 0;

	// We just append this to the end of all the SQL queries/links.  Makes things simpler because we only have to do it once.
	if($board_folder != "" && $db->getboardnumber($board_folder) )
	{
		$boardid = $db->getboardnumber($board_folder);
		$boardlink = "&board=".$board_folder;
	}
	else
	{
		$board_folder = ""; // Clear $board if the getboardnumber call failed
	}

	if(isset($_GET['type']) && $_GET['type'] == "posts")
	{
		$isthread = false;
	}

	if(isset($_GET['showhidden']) && $_GET['showhidden'] == true)
	{
		$showhidden = true;
		
		if($boardlink != "" || $isthread == false) // if we're showing posts the first parameter will be ?type=posts, so we're appending in that instance as well.
		{
			$boardlink .= "&showhidden=1"; // this means we'll append show hidden on the end of board links
		} 
		else // Only way this will happen if we're showing threads and not filtering by board
		{
			$boardlink = "?showhidden=1";
		}
	}

	$count = $db->getpostscount($isthread, $boardid, $showhidden);
	//print "Total Count:".$count;

	// Find our starting point.
	if(isset($_GET['offset']))
	{
		$offset = intval($_GET['offset']);
		if( $offset < 0 )
		{
			$offset = 0;
		}
	}

	// Calculate the starting point
	//Beginning should never be greater than $count, for the reason that $offset is always >= 0
	$beginning = $count - 19 - $offset;
	if( $beginning < 0 )
	{
		$beginning = 0;
	}

	// Calculate back and forward offsets for easy Smarty usage.
	$offsetback = $offset - 20;
	if($offsetback < 0)
	{
		$offsetback = 0;
	}
	$offsetfwd = $offset += 20;

	// DB abstraction is amazing.
	$posts=array();
	$posts = $db->getposts($beginning, $isthread, $boardid, $showhidden);
	
	usort($posts, 'comp_post_ids'); // THIS SHOULD WORK?
		
	// Add images to each post
	foreach($posts as &$post)
	{
		$post['images'] = $db->getimgs($post['imgidx']);
	}
	
	// Get the boards array, to show a list for filtering
	$boards = array();
	$boards = $db->getboard(); // No parameters means everything gets fetched

	$sm=sminit("adminrecentposts.tpl",null,"_admin",true); // Admin mode means NO caching. (and we provided a null id anyway)
	
	// These can be pretty big, so we're going to assign by reference.
	$sm->assign_by_ref("posts",$posts);
	$sm->assign_by_ref("boards",$boards);
	
	$sm->assign("board_folder", $board_folder); // name of the folder (for filtering)
	$sm->assign("boardlink", $boardlink); //some stuff to append for easy link creation
	
	$sm->assign("total_count", $count); // total number (for optionally showing the paging links)
	$sm->assign("offset", $offset);
	$sm->assign("offsetfwd", $offsetfwd);
	$sm->assign("offsetback", $offsetback);
	
	$sm->assign("isthread", (bool)$isthread);
	$sm->assign("showhidden", (bool)$showhidden);
	
	// Show it!
	$sm->display("adminrecentposts.tpl",null);
	die();
}
