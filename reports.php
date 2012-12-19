<?php

	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			reports.php
		Description:	Show the most recent reports for moderator use
		
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
	$db=new ThornModDBI();	

	// Init some stuff

	if(isset($_GET['board']))
	{
		$board_folder = trim($_GET['board']); //trim the board name from get
	} else {
		$board_folder = "";
	}
	$boardid = 0;
	$reports = array();
	$processed_reports = array();

	if($board_folder && $db->getboardnumber($board_folder) )
	{
		$boardid = $db->getboardnumber($board_folder);
		// Filter by this boardid
		$reports = $db->gettopreports($boardid);
	}
	else
	{
		$board_folder = ""; // Clear $board if the getboardnumber call failed
		$reports = $db->gettopreports();
	}
	
	// Populate each report with post and image information as well
	foreach( $reports as $report )
	{
		// Basic post information
		$report['post'] = $db->getsinglepost($report['postid'], $report['board']);
		
		// Add in the thread location (globalid)
		if ( $report['post']['thread'] == 0)
		{
			$report['post']['thread_globalid'] = $report['post']['globalid'];
		}
		else
		{
			 // Get the thread globalid
			$loc_array = $db->getpostlocation($report['post']['thread']);
			$report['post']['thread_globalid'] = $loc_array['thread_loc'];
		}
		
		// Add in images
		$report['post']['images'] = $db->getimgs($report['post']['imgidx']);
		
		// Round off report information
		$report['category'] = round($report['avg_category']);
		
		$processed_reports[] = $report;
	}
	
	// Get the boards array, to show a list for filtering
	$boards = array();
	$boards = $db->getboard(); // No parameters means everything gets fetched

	$sm=sminit("adminreports.tpl",null,"_admin",true); // Admin mode means NO caching. (and we provided a null id anyway)
	
	// These can be pretty big, so we're going to assign by reference.
	$sm->assign_by_ref("reports",$processed_reports);
	$sm->assign_by_ref("boards",$boards);
	
	$sm->assign("board_folder", $board_folder); // name of the folder (for filtering)
		
	// Show it!
	$sm->display("adminreports.tpl",null);
	die();
}
