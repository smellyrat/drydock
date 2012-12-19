<?php

	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			lookups.php
		Description:	Some lookup utilities for moderator use
		
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
	
	// Image lookup!
	if(isset($_GET['action']) && $_GET['action'] == "imglookup")
	{
		$url = trim($_GET['url']);
		
		// Generate a regex pattern that'll capture the image index
		// from the directory name
		$pattern = "/^".preg_quote(THurl, '/')."images\/(\d+)\/.*$/i";
		$matches = array(); // use this as a preg_match param
		
		echo $pattern;
		
		// Die if we can't parse this properly
		if( preg_match($pattern, $url, $matches) == 0)
		{
			THdie("Invalid image URL '".$url."' specified.");
		}
		
		// $matches[1] should have the captured imgidx
		$post_location = $db->getpostfromimgidx($matches[1]);
		
		if( $post_location == null )
		{
			THdie("Post with imgidx ".$matches[1]." not found.");
		}
		
		// If we got this far, then we have a valid result.
		$board_folder = $db->getboardname($post_location['board']); // get the board folder name
		
		// And now we redirect.
		if( THuserewrite )
		{
			header("Location: ".THurl.$board_folder."/thread/".$post_location['thread_loc'].
					"#".$post_location['post_loc']);			
		}
		else
		{
			header("Location: ".THurl."drydock.php?b=".$board_folder."&i=".$post_location['thread_loc'].
					"#".$post_location['post_loc']);
		}
		die();
	}
	else
	{
		// Imglookup is the only one that actually uses a redirect, the rest uses the adminlookup template
		
		// Init some things that MIGHT get set later if we're doing an IP lookup
		$single_ip = "";
		$posthistory=array();
		$banhistory=array();
		$banselect=array();
		$reports=array();
		$boards=array();
				
		// Perform some IP lookup things if requested
		if( isset ($_GET['action']) && $_GET['action'] == "iplookup")
		{
			$single_ip = trim($_GET['ip']);
			
			if( $single_ip != "" )
			{
				$longip = ip2long($single_ip);
				
				// Make sure it's valid
				if( $longip === false )
				{
					THdie("Invalid IP of '".$single_ip."' provided.");
				} 
				
				// Get recent reports
				$reports = $db->recentreportsfromip($longip);
				
				// Get ban history
				$banhistory = $db->getiphistory($longip);
				
				// Get current ban information, if any
				$banselect = $db->getban($longip, false); // don't clear bans (hence the 2nd parameter)
				
				// Get recent posts
				$posthistory = $db->recentpostsfromip($longip);
				
				// Set images for each post
				foreach($posthistory as $post)
				{
					$post['images'] = $db->getimgs($post['imgidx']);
				}	
				
				// Get the boards array to show folders
				$boards = $db->getboard(); // No parameters means everything gets fetched						
			}
		}
		
		$sm=sminit("adminlookup.tpl",null,"_admin",true); // Admin mode means NO caching. (and we provided a null id anyway)
		
		// These can be pretty big, so we're going to assign by reference.
		$sm->assign_by_ref("posthistory",$posthistory);
		$sm->assign_by_ref("banhistory",$banhistory);
		$sm->assign_by_ref("banselect",$banselect);
		$sm->assign_by_ref("reports",$reports);
		$sm->assign_by_ref("boards",$boards);

		$sm->assign("single_ip", $single_ip); // IP string
		
		// Show it!
		$sm->display("adminlookup.tpl",null);
		die();
	}
}
