<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			misc.php
		Description:	Perform miscellaneous actions.
		This will be used typically for short AJAX calls or special
		kinds of POST form submission.
		
		Things that take $_GET:
			- handle report (for moderators)
				Handle report params:
					- string $_GET['board'] - The board name
					- int $_GET['post'] - The ID of the post
					- int $_GET['status'] - The category to assign to these reports
					- string $_GET['action'] = "handlereport"
			- view static page
				View static page params:
					- string $_GET['page'] - The page name
					- string $_GET['action'] = "getpage"
			- report (gets set to the relevant POST parameters)
				Report params:
					- string $_GET['action'] = "report"
					- string $_GET['board'] - board folder
					- int $_GET['postid'] - post globalid			
			-quicker mod
				Delete a post:
					- string $_GET['quickb'] - board folder
					- int $_GET['quickp'] - post globalid			
								
		Things that take $_POST:
			- delete (delete posts)
				Delete params:
				    - string $_POST['delete'] - just means the delete button was successful
					- string $_POST['password'] - post password
					- bool $_POST['chkpost_____'] - post globalid (could be multiple)
			- report (report a post)
				Report params:
					- string $_POST['report'] - "report" just means the report button was successful
					- string $_POST['board'] - board folder
					- bool $_POST['chkpost_____'] - post globalid
			- report2 (report a post, with category)
				Report params:
					- string $_POST['report'] - "report2" signifies second stage
					- string $_POST['board'] - board folder
					- int $_POST['post'] - post globalid
					- int $_POST['category'] - classification
			- quickmod (quick moderation action)	
				Quickmod params:
				    - string $_POST['quickmod'] - indicate that this is a quickmod action
					- string $_POST['board'] - board folder
					- int $_POST['post'] - post globalid
					- bool $_POST['doban'] - ban (privately) or no ban
					- string $_POST['banreason'] - private/admin reason
					- int $_POST['duration'] - ban duration
					- bool $_POST['del'] - delete post or not
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	require_once("config.php");
	require_once("common.php");
	
	// If we are attempting to report a post via $_GET:
	// Map some $_GET parameters to $_POST parameters, so we don't
	// have to duplicate the reporting code
	if ($_GET['action'] == "report")
	{
		$_POST['report'] = "report";
		$_POST['board'] = $_GET['board'];
		$_POST['chkpost'.$_GET['postid']] = true;
	}
	
	if ($_GET['action'] == "handlereport")
	{
		// First check if we even have the params we need
		if (!isset ($_GET['post']) || !isset ($_GET['board']))
		{
			$message = "No post and/or board parameter, nothing to do!";
		}
		else
		{
			$db = new ThornModDBI();
			
			if ($db->checkban())
			{
				THdie("ADbanned");
			} 
			
			// Get the board name.
			$board_folder = trim($_GET['board']);
			
			// Check for local mod access or global mod/admin access.
			if ((is_in_csl($board_folder, $_SESSION['mod_array']) != 1) 
			&& ($_SESSION['admin'] != 1) && ($_SESSION['mod_global'] != 1))
			{
				$message = "You are not permitted to moderate posts on this board!";
			}
			elseif ( $board_folder == null )
			{
				$message = "Invalid board specified!";	
			}
			else
			{
				// Set some stuff up.
				$board_id = $db->getboardnumber($board_folder);
				$status = intval($_GET['status']);
				$postid = intval($_GET['post']);
				
				if( $status < 1 || $status > 3)
				{
					$message = "Invalid status given!";
				}
				else
				{
					// Handle the reports, we're ok here
					$db->touchreports($postid, $board_id, $status);
					$message = "Reports for post ".$postid." in /".$board_folder."/ successfully handled.";
				}
			}
		}
		
		$sm=sminit("popup.tpl");
		$sm->assign("text",$message);
		$sm->assign("timeout", 5); // 5s
		$sm->assign("title", "Report handler");
		$sm->display("popup.tpl");
		die();	
	}
	elseif ($_GET['action'] == "getpage") // View a static page
	{
		if(!isset($_GET['page']) || trim($_GET['page']) == "")
		{
			THdie("No page provided!");
		}
		else
		{
			$db = new ThornToolsDBI();
			$page = trim($_GET['page']);
			
			$pagedata = $db->getstaticpage($page);
			
			if( $pagedata == null )
			{
				THdie("A page by that name was not found!");
			}
			else
			{
				// Found a valid page.  Do access checking if necessary.
				switch( $pagedata['publish'] )
				{
					case 0: // Admin-only
						if( $_SESSION['admin'] != 1 )
						{
							THdie("You are not permitted to access that page.");
						}
						break;
						
					case 1: // Admin or mod-only
						if( $_SESSION['admin'] != 1 && $_SESSION['mod_global'] != 1 )
						{
							THdie("You are not permitted to access that page.");
						}
						break;
						
					case 2: // Registered user only
						if( !$_SESSION['username'] )
						{
							THdie("You must be registered to that page.");
						}
						
					// $pagedata['publish'] == 3 is publically viewable
					// so we need no checks for that
				}	
			
				// Since we haven't died yet, we can go ahead and display this.
				
				// Caching ID format: p<pageid>
				$cid="p".$pagedata['id'];
				
				// Initialize the Smarty object, display the object, and go
				$sm=sminit("staticpage.tpl",$cid,THtplset);
				$sm->assign_by_ref("page",$pagedata);
				$sm->display("staticpage.tpl",$cid);
				$sm->display("bottombar.tpl", null);
				die();
			}
		}
	}
	elseif ($_POST['delete'] == "Delete" ) // Delete a post/posts
	{
		$message = "what";
		
		if( !isset($_POST['password']) || $_POST['password'] == ""
		||  !isset($_POST['board']) || $_POST['board'] == "")
		{
			$message = "No password or board provided!";
		}
		else
		{
			$db = new ThornModDBI();
			
			if ($db->checkban())
			{
				THdie("PObanned");
			} 
			
			$board = $db->getboardnumber($_POST['board']);
			
			// verify that the board is correct
			if( $board == null )
			{
				$message = "Invalid board provided!";
			}
			else
			{
				// This will be an array of global IDs
				$posts_to_delete = array();
				
				foreach(array_keys($_POST) as $entry)
				{
					if(preg_match('/^chkpost\d+$/', $entry ) && $_POST[$entry] != 0 )
					{
						// strip out the leading "chkpost" and add the id to the posts_to_delete array
						$posts_to_delete[] = intval(preg_replace('/^chkpost/', '', $entry ));
					}
				}
				
				if( count($posts_to_delete) <= 0 )
				{
					$message = "No valid posts to delete!";
				}
				else
				{
					$delcount = $db->userdelpost($posts_to_delete, $board, $_POST['password']);
					$message = $delcount." posts deleted.";
				}
			}
		}
		
		$sm=sminit("popup.tpl");
		$sm->assign("text",$message);
		$sm->assign("timeout", "5"); // 5s
		$sm->assign("title", "Post deletion form");
		$sm->display("popup.tpl");
		die();
	}
	elseif($_POST['report'] == "Report" ) // Report a post
	{
		$message = "what";
		$timeout = 5; // we'll change it to something else if the report is ok so far
		
		if( !isset($_POST['board']) || $_POST['board'] == "")
		{
			$message = "No board provided!";
		}
		else
		{
			$db = new ThornToolsDBI();
			
			if ($db->checkban())
			{
				THdie("PObanned");
			} 
			
			$board = $db->getboardnumber($_POST['board']);
			
			// verify that the board is correct
			if( $board == null )
			{
				$message = "Invalid board provided!";
			}
			else
			{			
				$found_post = -1;

				foreach(array_keys($_POST) as $entry)
				{
					if(preg_match('/^chkpost\d+$/', $entry ) && $_POST[$entry] != 0 )
					{
						// strip out the leading "chkpost" and set the integer value to $found_post
						$found_post = intval(preg_replace('/^chkpost/', '', $entry ));
						break;
					}
				}
				
				if( $found_post <= 0 )
				{
					$message = "No valid post to report!";
				}
				else
				{	
					$status = $db->checkreportpost( $found_post, $board);
					
					switch( $status )
					{
						case 0: // valid report attempt
							$message = 'Reporting post #'.$found_post.' in /'.$_POST['board'].'/.<br />
										<form action="misc.php" method="post">Report category:<br />
										<input type="radio" name="category" value="1"> Illegal content<br />
										<input type="radio" name="category" value="2"> Rule violation<br />
										<input type="radio" name="category" value="3" checked="checked"> Low-quality posting<br />
										<input type="hidden" name="report" value="report2">
										<input type="hidden" name="post" value="'.$found_post.'">
										<input type="hidden" name="board" value="'.$_POST['board'].'">
										<input type="submit"></form>';
							$timeout = -1; // disable auto-closing of the popup
							
							break;
						case 1:
							$message = "You must wait before reporting another post.";
							break;
						case 2:
							$message = "Someone with your IP has already reported this post.";
							break;
						case 3:
							$message = "This post does not exist!";
							break;
						case 4:
							$message = "This post has already been reviewed.";
							break;
						default:
							$message = "Unknown error!";
					}
					
				}
			}
		}
		
		$sm=sminit("popup.tpl");
		$sm->assign("text",$message);
		$sm->assign("timeout", $timeout);
		$sm->assign("title", "Post reporting form");
		$sm->display("popup.tpl");
		die();
	}
	elseif( $_POST['report'] == "report2") // Second stage, with category submission
	{
		$message = "what";
		
		if( !isset($_POST['board']) || $_POST['board'] == "")
		{
			$message = "No board provided!";
		}
		else
		{
			$db = new ThornToolsDBI();
			
			if ($db->checkban())
			{
				THdie("PObanned");
			} 
			
			$board = $db->getboardnumber($_POST['board']);
			
			// verify that the board is correct
			if( $board == null )
			{
				$message = "Invalid board provided!";
			}
			else
			{			
				if( $_POST['post'] <= 0 || isset($_POST['post']) == false )
				{
					$message = "No valid post to report!";
				}
				else
				{	
					if( isset($_POST['category']))
					{
						$category = intval($_POST['category']);
						
						if( $category > 0 && $category < 4)
						{
							if( $db->checkreportpost( $_POST['post'], $board) == 0)
							{
								$db->reportpost($_POST['post'], $board, $category);
							}
							
							// We'll just tell them they got this far even if
							// it was invalid for whatever reason.
							$message = "Your report has been submitted.";
						}
						else
						{
							$message = "Invalid category provided!";
						}
					}
					else
					{
						$message = "No category provided!";
					}
				}
			}
		}
		
		$sm=sminit("popup.tpl");
		$sm->assign("text",$message);
		$sm->assign("timeout", 5); // 5 s
		$sm->assign("title", "Post reporting form");
		$sm->display("popup.tpl");
		die();	
	}
	elseif( $_POST['quickmod'] == "quickmod")
	{
		// First check if we even have the params we need
		if (!isset ($_POST['post']) || !isset ($_POST['board']))
		{
			$message = "No post and/or board parameter, nothing to do!";
		}
		else
		{
			$db = new ThornModDBI();
			
			if ($db->checkban())
			{
				THdie("ADbanned");
			} 
			
			// Get the board name.
			$board_folder = trim($_POST['board']);
			
			// Check for local mod access or global mod/admin access.
			if ((is_in_csl($board_folder, $_SESSION['mod_array']) != 1) 
			&& ($_SESSION['admin'] != 1) && ($_SESSION['mod_global'] != 1))
			{
				$message = "You are not permitted to moderate posts on this board";
			}
			else
			{
				// Set some stuff up.
				$board_id = $db->getboardnumber($board_folder);

				// Make sure we retrieved a valid board folder
				if ($board_folder == null)
				{
					$message = "That board does not exist!";
				}
				else
				{
					$postid = intval($_POST['post']); // SQL injection protection :]
					$postarray = $db->getsinglepost($postid, $board_id);
					
					// Make sure it exists
					if ($postarray == null)
					{
						$message = "Post with global ID of " . $postid . " and board /" . $board_folder . "/ does not exist.";
					}
					else // It exists and we can mod it.  GO HOG WILD
					{				
						$message = "Moderation actions on post ".$postid." in /".$board_folder."/ performed:";
						
						// Do we ban?
						if( $_POST['doban'] == true )
						{
							$reason = "No reason given.";
							$duration = 0; // default to warning
							
							if( isset($_POST['duration']) )
							{
								$duration = intval($_POST['duration']);
							}
							
							// Fill in a reason, if we have one
							if( isset($_POST['banreason']) && trim($_POST['banreason']) != "")
							{
								$reason = trim($_POST['banreason']);
							}
							
							$isthread = ($postarray['thread'] == 0); // thread of 0 means it's a thread
							
							$db->banipfrompost($postarray['id'], $isthread, 0, $reason, "", $reason, $duration, 
								$_SESSION['username'] . " via mod panel");
							
							$message = $message . "<br />Banning";
						}
						
						// Delete, if they're an admin
						if( $_POST['del'] == true && $_SESSION['admin'] == 1)
						{
							// Let's assume this is a thread and only change if necessary
							$thread = $postarray['globalid']; // thread global ID for cache wiping
							$targetisthread = true;
							$targetid = $postarray['id']; // unique ID for post deletion
							
							if ($postarray['thread'] != 0) // Reply, so we have to look all of this up
							{
								$postdbi = new ThornPostDBI();
								$fetched_thread = $postdbi->gettinfo($postarray['thread']);
								
								$thread = $fetched_thread['globalid'];						
								$targetisthread = false;
							}

							smclearcache($board_folder, -1, $thread); // clear the associated cache for this thread
							smclearcache($board_folder, -1, -1); // AND the board
							delimgs($db->delpost($targetid, $targetisthread));				
									
							// Write to the log
							writelog("delete\tt:" . $postarray['globalid'] . "\tb:" . $postarray['board'], "moderator");
							
							$message = $message . "<br />Post deletion";
						}
						elseif ( $_POST['del'] == true ) // hrmph :[
						{
							$message = $message . "<br /><i><b>Post deletion failed (insufficient access)</i></b>";
						}
					}					
				}	
			}
		}
		
		$sm=sminit("popup.tpl");
		$sm->assign("text",$message);
		$sm->assign("timeout", 5); // 5s
		$sm->assign("title", "Moderation action");
		$sm->display("popup.tpl");
		die();	
	}
	elseif( $_GET['quicker']==1 )
	{
		// First check if we even have the params we need
		if (!isset ($_GET['board']) || !isset ($_GET['post']))
		{
			$message = "No post and/or board parameter, nothing to do!";
		}
		else
		{
			$db = new ThornModDBI();
			
			if ($db->checkban())
			{
				THdie("ADbanned");
			} 
			
			// Get the board name.
			$board_folder = trim($_GET['board']);
			
			// Check for local mod access or global mod/admin access.
			if ((is_in_csl($board_folder, $_SESSION['mod_array']) != 1) 
			&& ($_SESSION['admin'] != 1) && ($_SESSION['mod_global'] != 1))
			{
				$message = "You are not permitted to moderate posts on this board";
			}
			else
			{
				// Set some stuff up.
				$board_id = $db->getboardnumber($board_folder);

				// Make sure we retrieved a valid board folder
				if ($board_folder == null)
				{
					$message = "That board does not exist!";
				}
				else
				{
					$postid = intval($_GET['post']); // SQL injection protection :]
					$postarray = $db->getsinglepost($postid, $board_id);
					
					// Make sure it exists
					if ($postarray == null)
					{
						$message = "Post with global ID of " . $postid . " and board /" . $board_folder . "/ does not exist.";
					}
					else // It exists and we can mod it.  GO HOG WILD
					{		
					
						$message = "Moderation actions on post ".$postid." in /".$board_folder."/ performed:";
						
						// Let's ban.  This is quicker mod, so we ban with default reason
						$reason = "USER WAS BANNED FOR THIS POST";
						$duration = 0; // perma
							
						$isthread = ($postarray['thread'] == 0); // thread of 0 means it's a thread
						
						$db->banipfrompost($postarray['id'], $isthread, 0, $reason, "", $reason, $duration, 
							$_SESSION['username'] . " via mod panel");
							
						$message = $message . "<br />Banning";

						// Delete, if they're an admin
						if($_SESSION['admin'] == 1)
						{
							// Let's assume this is a thread and only change if necessary
							$thread = $postarray['globalid']; // thread global ID for cache wiping
							$targetisthread = true;
							$targetid = $postarray['id']; // unique ID for post deletion
							
							if ($postarray['thread'] != 0) // Reply, so we have to look all of this up
							{
								$postdbi = new ThornPostDBI();
								$fetched_thread = $postdbi->gettinfo($postarray['thread']);
								
								$thread = $fetched_thread['globalid'];						
								$targetisthread = false;
							}

							smclearcache($board_folder, -1, $thread); // clear the associated cache for this thread
							smclearcache($board_folder, -1, -1); // AND the board
							delimgs($db->delpost($targetid, $targetisthread));				
									
							// Write to the log
							writelog("delete\tt:" . $postarray['globalid'] . "\tb:" . $postarray['board'], "moderator");
							
							$message = $message . "<br />Post deletion";
						}
						else // hrmph :[
						{
							$message = $message . "<br /><i><b>Post deletion failed (insufficient access)</i></b>";
						}
					}
				}	
			}
		}
		
		$sm=sminit("popup.tpl");
		$sm->assign("text",$message);
		$sm->assign("timeout", 5); // 5s
		$sm->assign("title", "Moderation action");
		$sm->display("popup.tpl");
		die();	
	}

?>