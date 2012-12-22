<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			admin.php
		Description:	Administrative functions
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	/**
	 * $_GET['a'] is typically used for displaying information as opposed
	 * to form submission.
	 * 
	 * THE BIG LIST OF $_GET['a'] POSSIBILITIES:
	 * 
	 * "b" - Board info (optionally a $_GET['boardselect'] option as well)
	 * "x" - Ban info (optionally a $_GET['banselect'] option as well)
	 * "t" - Thornlight (recent pics)
	 * "q" - Thornquasilight (recent posts)
	 * "r" - Reports (redirect)
	 * "l" - Lookups (redirect)
	 * "c" - Capcode options
	 * "w" - Wordfilter options
	 * "p" - Profile options
	 * "g" - General options
	 * "bl" - Blotter posts
	 * "mp" - Manager post
	 * "hk" - Housekeeping options
	 * "hkc" - Housekeeping options submissions
	 * "sp" - Static pages list
	 * "spe" - Edit particular static page
	 * "lv" - Log viewer (redirect)
	 * 
	 * $_GET['t'] is typically used for receiving form submissions.
	 * 
	 * THE LIST OF $_GET['t'] POSSIBILITIES:
	 * 
	 * "au" - Add user
	 * "aw" - Add wordfilter
	 * "ew" - Edit wordfilters
	 * "ac" - Add capcode
	 * "rc" - Edit capcodes
	 * "ax" - Add ban
	 * "ux" - Remove ban
	 * "lx" - Lookup ban (redirect to $_GET['a'] with $_GET['banselect'] set)
	 * "b" - Edit boards
	 * "g" - Rebuild config (gen. options edit)
	 * "bl" - Add blotter post
	 * "ble" - Edit blotter
	 * "spa" - Add static page
	 * "spx" - Delete static page
	 * "spe" - Edit static page (receiver)
 	 */
	
	require_once("config.php");
	require_once("common.php");
	require_once("rebuilds.php");
	
	checkadmin(); //make sure the person trying to access this file is allowed to
	//var_dump($_POST);
	$db=new ThornModDBI();
	if(isset($_GET['rebuild']))
	{
		//all of these could have just changed
		rebuild_hovermenu();
		rebuild_linkbars();
		rebuild_rss();
		rebuild_htaccess();
		header("Location: ".THurl."admin.php?a=g");
		die();
	}
	if(isset($_GET['profilepic']))
	{
		if(isset($_GET['filename']))
		{
			if(!file_exists('./unlinked/'.$_GET['filename']))
			{
				die();
			}
			$pinfo=pathinfo('./unlinked/'.$_GET['filename']);
			$filetype=strtolower($pinfo['extension']);
			if($filetype == "jpg" || $filetype == "jpeg") { header("Content-type: image/jpg"); }
			else if ($filetype == "png") { header("Content-type: image/png"); }
			else if($filetype == "gif") { header("Content-type: image/gif"); }
			else { die(); }
			readfile('./unlinked/'.$_GET['filename']);
		}
		
		die(); // we're done here
	}
	$sm=sminit(null,null,null,true);
	if (isset($_GET['a']))
	{
		if ($_GET['a']=="b")  //Board stuff
		{
			// We have to assign these to be able to set disabled attributes for the checkboxes for file formats
			// (i.e., greying out the SVG file type box when THuseSVG is 0)
			$sm->assign("THuseSVG",THuseSVG);
			$sm->assign("THuseSWFmeta",THuseSWFmeta);
			$sm->assign("THSVGthumbnailer", THSVGthumbnailer);
			$sm->assign("THusePDF", THusePDF);
			//Assign other template sets
			$sets=array();
			//Read template sets
			$it=opendir(THpath."tpl/");
			while (($set=readdir($it))!==false)
			{
				if (in_array($set,array(".","..","_admin",".svn","_compd","profiles"))==false && is_dir(THpath."tpl/".$set))
				{
					$sets[]=$set;
				}
			}
			$sm->assignbyref("tplsets",$sets);

			if (isset($_GET['boardselect']))
			{
				//Configure options for a specific board
				$boardselect = $db->getboard(0, $_GET['boardselect']); // Should return an array of assoc-arrays (but with only one assoc-array)
				if($boardselect)
				{
					$sm->assign("boardselect",($_GET['boardselect']));
					$sm->assign("board",$boardselect[0]);
				}
				else
				{
					$sm->assign("boardselect","-1");
					THdie("Invalid board ID provided.");
				}
			}
			else
			{
				$sm->assign("boardselect","");  //strict error fix
				//Board list
				if (THdbtype==null)
				{
					//Can't access this unless the database is set up.
					THdie("ADdbfirst");
				}
				$brds = $db->getindex(array('full'=>true),$sm);
				$sm->assign("boards",$brds);
			}
			$sm->display("adminboard.tpl");
		}
		elseif ($_GET['a']=="x")
		{
			if (THdbtype==null)
			{
				//Can't access this unless the database is set up.
				THdie("ADdbfirst");
			}
			
			//Ban config		
			if (isset($_GET['banselect']))
			{
				//Edit a specific ban
				$sm->assign("banselect",$_GET['banselect']);
				
				// Get the individual ban in question
				$single_ban_assoc = $db->getbanfromid($_GET['banselect']);
				$sm->assign("ban",$single_ban_assoc,$sm);
				
				// Next we fetch all history for the ban.
				
				// Substitute in 0 for the subnets
				$ip3 = 0;
				if( $single_ban_assoc['ip_octet3'] > -1 )
				{
					$ip3 = $single_ban_assoc['ip_octet3'];
				}
				
				$ip4 = 0;
				if( $single_ban_assoc['ip_octet4'] > -1 )
				{
					$ip4 = $single_ban_assoc['ip_octet4'];
				}
								
				$ip=ip2long($single_ban_assoc['ip_octet1'].".".$single_ban_assoc['ip_octet2'].".".$ip3.".".$ip4);
				
				// Get history for the IP
				$rawhist=$db->getiphistory($ip);
				if ($rawhist!=null)
				{
					$banhistory=array();
					foreach ($rawhist as $hist)
					{
						$banhistory[]=array(
							"ip1"=>$hist['ip_octet1'],
							"ip2"=>$hist['ip_octet2'],
							"ip3"=>$hist['ip_octet3'],
							"ip4"=>$hist['ip_octet4'],
							"id"=>$hist['id'],
							"publicreason"=>$hist['publicreason'],
							"privatereason"=>$hist['privatereason'],
							"adminreason"=>$hist['adminreason'],
							"postdata"=>$hist['postdata'],
							"duration"=>$hist['duration'],
							"bantime"=>$hist['bantime'],
							"bannedby"=>$hist['bannedby'],
							"unbaninfo"=>$hist['unbaninfo']
						);
					}
				}
				else
				{
					$banhistory=null;
				}
				$sm->assign("banhistory",$banhistory);
			}
			else
			{
				$sm->assign("banselect","");  //strict error fix
				$rawbans=$db->getallbans();
				if ($rawbans!=null)
				{
					$bans=array();
					foreach ($rawbans as $ban)
					{
						$bans[]=array(
							"ip1"=>$ban['ip_octet1'],
							"ip2"=>$ban['ip_octet2'],
							"ip3"=>$ban['ip_octet3'],
							"ip4"=>$ban['ip_octet4'],
							"id"=>$ban['id'],
							"publicreason"=>$ban['publicreason'],
							"privatereason"=>$ban['privatereason'],
							"adminreason"=>$ban['adminreason'],
							"postdata"=>$ban['postdata'],
							"duration"=>$ban['duration'],
							"bantime"=>$ban['bantime'],
							"bannedby"=>$ban['bannedby']
							);
					}
				}
				else
				{
					$bans=null;
				}
				$sm->assign("bans",$bans);
			}
			$sm->display("adminban.tpl");
		}

		//are these next two really needed?
		elseif ($_GET['a']=="t") //let's put thornlight here
		{
			include("recentpics.php");
		}
		elseif ($_GET['a']=="q") //let's put thornquasilight here
		{
			include("recentposts.php");
		}
		elseif ($_GET['a']=="r") //oh hello reports
		{
			include("reports.php");
		}
		elseif ($_GET['a']=="l") // Lookups
		{
			include("lookups.php");
		}
		elseif ($_GET['a']=="lv") // Logviewer
		{
			include("logviewer.php");
		}
		elseif ($_GET['a']=="c") //Capcodes
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}
			
			// Retrieve capcodes
			$capcodes = array();
			$capcodes = $db->fetchBCW(THbcw_capcode);
			if(count($capcodes) <= 0)
			{
				$capcodes = null;
			}
			
			$sm->assign("capcodes",$capcodes);
			$sm->display("admincapcodes.tpl");
		}
		//this block is a copy pasta from ordog's capcodes block with no changes other than a=?? ~tyam
		elseif ($_GET['a']=="w") //wordfilters
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}

			// Retrieve wordfilters
			$filters = array();
			$filters = $db->fetchBCW(THbcw_filter);
			if(count($filters) <= 0)
			{
				$filters = null;
			}

			$sm->assign("filters",$filters);
			$sm->display("adminfilters.tpl");
		}
		elseif ($_GET['a']=="p") //Profiles
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}
			
			// Use the functions provided to us by this class
			$profile_dbi = new ThornProfileDBI();
			
			//move this over to t=p eventually - tyam
			if((isset($_GET['action']) && $_GET['action']=="regyes") && isset($_GET['username']))
			{
				// Approving an account
						
				// Use the approvalaction function	
				$profile_dbi->approvalaction($_GET['username'], "account", true);
				
				if(THprofile_emailwelcome)
				{
					$email = $profile_dbi->getemail($_GET['username']);
					sendWelcome($_GET['username'], $email);
				}
			}

			if((isset($_GET['action']) && $_GET['action']=="regno") && isset($_GET['username']))
			{
				// Denying an account
				
				// Use the approvalaction function	
				$profile_dbi->approvalaction($_GET['username'], "account", false);
				
				if(THprofile_emailwelcome)
				{
					$email = $profile_dbi->getemail($_GET['username']);
					sendDenial($_GET['username'], $email);
				}
			}
			if((isset($_GET['action']) && $_GET['action']=="capyes") && isset($_GET['username']))
			{
				// Approve a proposed capcode
				
				// This abstracts the rest of the DB queries for us
				$profile_dbi->approvalaction($_GET['username'], "capcode", true);
			}
			if((isset($_GET['action']) && $_GET['action']=="capno") && isset($_GET['username']))
			{
				//this capcode isn't going to work for whatever reason, deny it
				
				$profile_dbi->approvalaction($_GET['username'], "capcode", false);
			}
			if((isset($_GET['action']) && $_GET['action']=="picyes") && isset($_GET['username']))
			{
				// Approving a requested picture
				
				// Get the file extension of the current picture (if any)
				$old_picture = $profile_dbi->getuserimage($_GET['username']);
				
				// Get the file extension of the wanted picture
				$desired_picture = $profile_dbi->getpendinguserimage($_GET['username']);
				
				// Delete the old picture, if there is one
				if($old_picture)
					unlink(THpath.'images/profiles/'.$_GET['username'].'.'.$old_picture);
				
				// Move the new picture into the profiles directory
				rename(THpath.'unlinked/'.$_GET['username'].'.'.$desired_picture, THpath.'images/profiles/'.$_GET['username'].'.'.$desired_picture);
				
				// Update the db to reflect this
				$profile_dbi->approvalaction($_GET['username'], "picture", true);
			}
			
			if((isset($_GET['action']) && $_GET['action']=="picno") && isset($_GET['username']))
			{
				// Denying a requested picture
				
				// Get the file extension of the wanted picture
				$desired_picture = $profile_dbi->getpendinguserimage($_GET['username']);
				
				// Delete the file
				unlink(THpath.'unlinked/'.$_GET['username'].'.'.$desired_picture);
				
				// Clear the db record
				$profile_dbi->approvalaction($_GET['username'], "picture", false);
			}
						
			$users = $profile_dbi->getprofilemodqueue();
			if ($users!=null)
			{
				$pend_regs=array();
				$pend_caps=array();
				$pend_pics=array();
				foreach($users as $user)
				{
					if($user['approved'] == 0)
					{
						$pend_regs[]=array("username" => $user['username'],
									   "email" => $user['email']);
					}
					
					if($user['proposed_capcode'])
					{
						$pend_caps[]=array("username" => $user['username'],
									   "proposed_capcode" => $user['proposed_capcode']);
					}
					
					if($user['pic_pending'])
					{
						$pend_pics[]=array("username" => $user['username'],
									   "pic_pending" => $user['pic_pending']);
					}
				}
			}
			else
			{
				$pend_caps=null;
				$pend_pics=null;
				$pend_regs=null;
			}
			$sm->assign('pend_regs',$pend_regs);
			$sm->assign('pend_caps',$pend_caps);
			$sm->assign('pend_pics',$pend_pics);
			$sm->display("adminprofile.tpl");
		}
		elseif ($_GET['a']=="mp") // Manager post function
		{
			if ($_GET['board'])
			{
				// Should return an array of assoc-arrays
				$boardarray = $db->getboard(0, $_GET['board']);
				//var_dump($boardarray);
				if($boardarray)
				{
					$sm->assign("binfo",$boardarray[0],$sm);
					$sm->display("adminpost.tpl");
				}
				else
				{
					THdie("Invalid board ID provided.");
				}
			}
			else
			{
				THdie("No board ID provided!");
			}
		}
		elseif ($_GET['a']=="hk")  //housekeeping~
		{
			$sm->assign("THdbtype",THdbtype);
			$sm->display("adminhouse.tpl");
		}
		elseif ($_GET['a']=="hkc") //housekeeping functions are actually called here
		{
			if (isset($_POST['fc']))
			{
				$sm->clearallcache();
				$sm->clearCompiledTemplate();
				rebuild_filters();
				rebuild_capcodes();
				rebuild_spamlist();
			}
			if(isset($_POST['rs'])) { rebuild_rss(); }
			if(isset($_POST['ht'])) { rebuild_htaccess(); }
			if(isset($_POST['sl'])) { rebuild_hovermenu(); }
			if(isset($_POST['lb'])) { rebuild_linkbars(); }
			if(isset($_POST['sp'])) { rebuild_spamlist(); }
			if(isset($_POST['fl'])) { rebuild_filters(); }
			if(isset($_POST['cp'])) { rebuild_capcodes(); }
			if(isset($_POST['al'])) {
				//Do EVERYTHING
				$sm->clearAllCache();
				$sm->clearCompiledTemplate();
				rebuild_rss();
				rebuild_htaccess();
				rebuild_hovermenu();
				rebuild_linkbars();
				rebuild_filters();
				rebuild_capcodes();				
				rebuild_spamlist();  //save this for last just in case
			}

			$actionstring = "Housekeeping";
			writelog($actionstring,"admin");		
			header("Location: ".THurl."admin.php?a=hk");
		}
		elseif ($_GET['a']=="bl") //blotter
		{
			if (THdbtype==null) //Can't access this unless the database is set up.
			{
				THdie("ADdbfirst");
			}
			
			$blotter = $db->fetchBCW(THbcw_blotter);
			
			// Perform replacequote on the blotter entries
			foreach ($blotter as $blot)
			{
				$blot = replacequote($blot);
			}

			$sm->assign("blots",$blotter);
			
			$sm->assign("boards",$db->getindex(array('full'=>true),$sm));
			$sm->display("adminblotter.tpl");
		}
		elseif ($_GET['a']=="g") //general admin - boring old TH code, can still use Smarty since it's only reading
		{
			//Assume general options
			//Assign settings
			$sm->assign("THname",THname);
			$sm->assign("THjpegqual",THjpegqual);
			$sm->assign("THtplset",THtplset);
			$sm->assign("THtpltest",THtpltest);
			$sm->assign("THdupecheck",THdupecheck);
			$sm->assign("THtimeoffset",THtimeoffset);
			$sm->assign("THvc",THvc);
			$sm->assign("THnewsboard",THnewsboard);
			$sm->assign("THmodboard",THmodboard);
			$sm->assign("THdefaulttext",THdefaulttext);
			$sm->assign("THdefaultname",THdefaultname);
			$sm->assign("THdatetimestring",THdatetimestring);
			$sm->assign("THuserewrite",THuserewrite);
			$sm->assign("THpearpath", THpearpath);
			$sm->assign("THuseSWFmeta", THuseSWFmeta);
			$sm->assign("THSVGthumbnailer", THSVGthumbnailer);
			$sm->assign("THuseSVG", THuseSVG);
			$sm->assign("THusePDF", THusePDF);
			$sm->assign("THusecURL", THusecURL);
			$sm->assign("THusePDF", THusePDF);
			$sm->assign("reCAPTCHAPrivate", reCAPTCHAPrivate);
			$sm->assign("reCAPTCHAPublic", reCAPTCHAPublic);
			$sm->assign("THprofile_adminlevel", THprofile_adminlevel);
			$sm->assign("THprofile_userlevel", THprofile_userlevel);
			$sm->assign("THprofile_emailname", THprofile_emailname);
			$sm->assign("THprofile_emailaddr", THprofile_emailaddr);
			$sm->assign("THprofile_emailwelcome", THprofile_emailwelcome);
			$sm->assign("THprofile_cookietime", THprofile_cookietime);
			$sm->assign("THprofile_cookiepath", THprofile_cookiepath);
			$sm->assign("THprofile_lcnames", THprofile_lcnames);
			$sm->assign("THprofile_maxpicsize", THprofile_maxpicsize);
			$sm->assign("THprofile_regpolicy", THprofile_regpolicy);
			$sm->assign("THprofile_viewuserpolicy", THprofile_viewuserpolicy);
			$sm->assign("DDDEBUG",DDDEBUG);
			//Assign other template sets
			$sets=array();
			//Read template sets
			$it=opendir(THpath."tpl/");
			while (($set=readdir($it))!==false)
			{
				//echo($set);
				if (in_array($set,array(".","..",".svn","_admin","_compd","profiles"))==false && is_dir(THpath."tpl/".$set)) //Should mebbe do a better test here... versions, etc
				{
					$sets[]=$set;
				}
			}
			$sm->assignbyref("tplsets",$sets);
			$sm->assign("boards",$db->getindex(array('full'=>true),$sm));
			$sm->display("admingen.tpl");
		}
		elseif ($_GET['a'] == "sp") // Static pages list
		{
			$static_pages = $db->getstaticpages();
			$single_page = null;
			$sm->assignbyref("pages",$static_pages);
			$sm->assign("single_page", $single_page);
			$sm->display("adminstatic.tpl");
		}
		elseif ($_GET['a'] == "spe") // Edit specific static page
		{
			if(!isset($_GET['id']))
			{
				THdie("No static page ID specified!");
			}
			
			$static_pages = $db->getstaticpages();
			$single_page = null;
			
			// Search through looking for a specific page
			foreach($static_pages as $static_page)
			{
				if( $static_page['id'] == $_GET['id'] )
				{
					$single_page = $static_page;
					break;
				}
			}
			
			if( $single_page == null )
			{
				THdie("Invalid static page ID specified!");
			}
			
			$sm->assignbyref("pages",$static_pages);
			$sm->assign("single_page", $single_page);
			$sm->display("adminstatic.tpl");
		}
		else
		{
			THdie("Where are you going?");
		}
		die();
	} //end GET=a

	//If still alive here, assume $_GET['t'] is set.
	if ($_GET['t']=="g")
	{
		if($_POST)
		{
			rebuild_config($_POST); //hope
			header("Location: ".THurl."admin.php?rebuild");  //fucking hack >:[
			die();
		}
		header("Location: ".THurl."admin.php?a=g");
		die();
	}
	elseif ($_GET['t']=="bl") //Update (add) blotter
	{		
		$db->insertBCW(THbcw_blotter, $_POST['post'], $_POST['postto'] );
		
		$actionstring = "Blotter post";
		writelog($actionstring,"admin");
		
		header("Location: ".THurl."admin.php?a=bl");
	}
	elseif ($_GET['t']=="ble") //Edit blotter
	{
		$blotter = $db->fetchBCW(THbcw_blotter);
		
		foreach ($blotter as $blot)
		{
			if ($_POST['del'.$blot['id']])
			{
				$db->deleteBCW(THbcw_blotter, $blot['id']);
				
				$actionstring = "Blotter delete\tid:".$blot['id'];
				writelog($actionstring,"admin");
			} 
			else 
			{
				$blotter_entry=array(
					'id'=>(int)$_POST['id'.$blot['id']],
					'text'=>$db->escape_string($_POST['post'.$blot['id']]),
					'board'=>$db->escape_string($_POST['postto'.$blot['id']])
				);
				
				$db->updateBCW(THbcw_blotter, $blotter_entry['id'], $blotter_entry['text'], $blotter_entry['board']);
			}
		}
		
		header("Location: ".THurl."admin.php?a=bl");
	}
	elseif ($_GET['t']=="b")  //edit boards
	{
		//echo '<pre>' . var_export($_POST,true).'</code></pre>';
		if(isset($_POST['boardselect']))
		{
		
			$boardnumber = $db->getboardnumber($_POST['boardselect']);
			if (isset($_POST['delete'.$boardnumber]) && $_POST['delete'.$boardnumber]==TRUE) //Delete images on that board; nuke it from db
			{	
				// Remove associated images
				delimgs($db->fragboard($boardnumber));
				
				// Remove the DB board entry
				$db->removeboard($boardnumber);
				
				$actionstring = "Board delete\tid:".$boardnumber;
				writelog($actionstring,"admin");
				$location=THurl."admin.php?a=b";
			} 
			else 
			{		
				// We're going to make an array of boards to update (with size 1) containing
				// assoc-arrays with board information
				$boards_to_update = array();
				$updated_board = array();
				
				// Get ID stuff set up
				$updated_board['oldid'] = $boardnumber;
				$updated_board['id'] = $updated_board['oldid'];
				$oldid = $updated_board['oldid'];
				//$updated_board['globalid'] = $_POST['globalid'.$oldid];
				
				// Now that the ID stuff is set up, we can do some verification.
				// Make sure we don't have a folder name conflict.
				$folder = trim($_POST['folder'.$oldid]);
				$folder_id = $db->getboardnumber($folder);
				// If we return a number on $folder_id it means there's already an id of that folder name being used, so don't let them collide
				if($_POST['boardselect']!=$folder)  //hm...
				{
					if( $folder_id )
					{
						THdie("An existing board already has a folder named \"".$folder."\"!");
					}
				}
				// String values
				$updated_board['name'] = replacequote($_POST['name'.$oldid]);
				$updated_board['folder'] = replacequote($folder); // we already did the stuff above
				$updated_board['about'] = strip_tags(replacequote($_POST['about'.$oldid]), 
					'<i><b><u><strike><p><br /><font><a><ul><ol><li><marquee>');
				$updated_board['rules'] = replacequote($_POST['rules'.$oldid]);
				$updated_board['boardlayout'] =$_POST['boardlayout'.$oldid];			
					
				// Integer values
				$updated_board['perpg'] = intval($_POST['perpg'.$oldid]);
				$updated_board['perth'] = intval($_POST['perth'.$oldid]);
				$updated_board['allowedformats'] = intval($_POST['allowedformats'.$oldid]);
				$updated_board['tpix'] = intval($_POST['tpix'.$oldid]);
				$updated_board['rpix'] = intval($_POST['rpix'.$oldid]);		
				$updated_board['tmax'] = intval($_POST['tmax'.$oldid]);
				$updated_board['thumbres'] = intval($_POST['thumbres'.$oldid]);
				$updated_board['maxfilesize'] = intval($_POST['maxfilesize'.$oldid]);
				$updated_board['maxres'] = intval($_POST['maxres'.$oldid]);
				$updated_board['pixperpost'] = intval($_POST['pixperpost'.$oldid]);
				
				// Boolean values
				if(isset($_POST['forced_anon'.$oldid])) {
					$updated_board['forced_anon'] = ($_POST['forced_anon'.$oldid]=="on");
				} else {
					$updated_board['forced_anon'] = "";
				}
				if(isset($_POST['customcss'.$oldid])) {
					$updated_board['customcss'] = ($_POST['customcss'.$oldid]=="on");
				} else {
					$updated_board['customcss'] = "";
				}
				if(isset($_POST['allowvids'.$oldid])) {
					$updated_board['allowvids'] = ($_POST['allowvids'.$oldid]=="on");
				} else {
					$updated_board['allowvids'] = "";
				}
				if(isset($_POST['filter'.$oldid])) {
					$updated_board['filter'] = ($_POST['filter'.$oldid]=="on");
				} else {
					$updated_board['filter'] = "";
				}
				if(isset($_POST['requireregistration'.$oldid])) {
					$updated_board['requireregistration'] = ($_POST['requireregistration'.$oldid]=="on");
				} else {
					$updated_board['requireregistration'] = "";
				}
				if(isset($_POST['hidden'.$oldid])) {
					$updated_board['hidden'] = ($_POST['hidden'.$oldid]=="on");
				} else {
					$updated_board['hidden'] = "";
				}
				if(isset($_POST['tlock'.$oldid])) {
					$updated_board['tlock'] = ($_POST['tlock'.$oldid]=="on");
				} else {
					$updated_board['tlock'] = "";
				}
				if(isset($_POST['rlock'.$oldid])) {
					$updated_board['rlock'] = ($_POST['rlock'.$oldid]=="on");
				} else {
					$updated_board['rlock'] = "";
				}

				// Add the assoc-array with the updated information into the array
				$boards_to_update[] = $updated_board;
								//var_dump($updated_board);echo "<hr>";
								//var_dump($boards_to_update);
				$db->updateboards($boards_to_update);
				//var_dump($boards_to_update); die();
				$actionstring = "Board edit\tid:".$boardnumber;
				writelog($actionstring,"admin");
				$location=THurl."admin.php?a=b&boardselect=".$folder;
			}
		}
		else
		{
			if ($_POST['namenew']!=null)  //Adding a new board
			{
				$name=trim($_POST['namenew']);
				$folder=trim($_POST['foldernew']);
				
				if( $name == "")
				{
					THdie("You must provide a valid board name!");
				}
				
				if( $folder == "")
				{
					THdie("You must provide a valid folder name!");
				}
								
				// Make sure we don't have a folder name conflict
				$folder_exists = $db->getboardnumber($folder);
				
				if( $folder_exists != null)
				{
					THdie("An existing board already has a folder named \"".$folder."\"!");
				}
				
				$about=$_POST['aboutnew'];
				$rules=$_POST['rulesnew'];
				
				// This will return the last insert ID.
				$id = $db->makeboard($name, $folder, $_POST['aboutnew'], $_POST['rulesnew']);
				
				$actionstring = "Board add\tid:".$id;
				writelog($actionstring,"admin");
				
				if($_POST['nextaction']=="edit")
				{
					$location=THurl."admin.php?a=b&boardselect=".$folder;
				} 
				else 
				{
					$location=THurl."admin.php?a=b";
				}
			}
		}
		//print_r($boards);
		$sm->clearallcache();
		$sm->clearCompiledTemplate();
		rebuild_filters();
		rebuild_capcodes();
		rebuild_htaccess();
		rebuild_linkbars();
		rebuild_hovermenu();
		header("Location: ".$location);
		die();
	}
	elseif ($_GET['t']=="ax") //Add ban
	{
		// Regular subnet ban (ipsub value of 1)
		$ip4 = "0";
		if ($_POST['ipsub'] < 1)
		{
			$ip4=$_POST['ip4'];
		}
		
		// Class C subnet ban (ipsub value of 2)
		$ip3 = "0";
		if ($_POST['ipsub'] < 2)
		{
			$ip3=$_POST['ip3'];
		}
		
		$ip=ip2long($_POST['ip1'].".".$_POST['ip2'].".".$ip3.".".$ip4);
		if ($ip==-1 || $ip==false)
		{
			THdie("ADbanbadip");
		}
		$banreason = 'This is an admin ban, you were not banned for a specific post.';
		$bannedby = $_SESSION['username']." via admin ban panel";
		$db->banip($ip,($_POST['ipsub']=="on"),$banreason,'admin ban',$_POST['adminreason'],"",$_POST['duration'],$bannedby);
		header("Location: ".THurl."admin.php?a=x");
	}
	elseif ($_GET['t']=="ux") //Remove ban
	{
		$reason = $_SESSION['username']." via admin ban panel";
		if( isset($_GET['reason']) )
		{
			$reason = $_GET['reason'];
		}
		
		$bans=$db->getallbans();
		foreach ($bans as $ban)
		{
			if ($_POST['del'.$ban['id']])
			{
				$db->delban($ban['id'], $reason);
			}
		}
		header("Location: ".THurl."admin.php?a=x");
	}
	elseif ($_GET['t']=="lx") // Lookup ban
	{
		if( isset($_POST['ip']) )
		{
			$ban_info = $db->getban($_POST['ip']);
			
			// Did we find at least one ban?
			// If so, redirect to the ban ID of the first element in the array.
			if(count($ban_info) > 0)
			{
				header("Location: ".THurl."admin.php?a=x&banselect=".$ban_info[0]['id']);
			}
			else
			{
				header("Location: ".THurl."admin.php?a=x"); // failure
			}
		}
		else
		{
			header("Location: ".THurl."admin.php?a=x"); // even worse failure
		}	
	}
	elseif ($_GET['t']=="ac") //Add capcode
	{
		if($_POST['capcodefrom'] == null || $_POST['capcodeto'] == null)
		{
			THdie('Invalid field provided.'); // don't know if this is right
		}
		
		// insertBCW takes care of the rest.
		$db->insertBCW(THbcw_capcode, $_POST['capcodefrom'], $_POST['capcodeto'], $_POST['notes']);

		rebuild_capcodes();
		
		$actionstring = "CP add\tfrom:".$_POST['capcodefrom']."\tto:".$_POST['capcodeto'];
		writelog($actionstring,"admin");
		
		header("Location: ".THurl."admin.php?a=c");
	}
	elseif ($_GET['t']=="rc") //Edit capcode
	{
		$capcodes = $db->fetchBCW(THbcw_capcode);
		
		foreach ($capcodes as $cap)
		{
			if ($_POST['del'.$cap['id']])
			{			
				$db->deleteBCW(THbcw_capcode, $cap['id']);
				
				$actionstring = "CP delete\tid:".$cap['id'];
				writelog($actionstring,"admin");
			}
			else
			{
				$capcode=array(
					'id'=>(int)$_POST['id'.$cap['id']],
					'from'=>$_POST['from'.$cap['id']],
					'to'=>$_POST['to'.$cap['id']],
					'notes'=>$_POST['notes'.$cap['id']]
				);

				$db->updateBCW(THbcw_capcode, $capcode['id'], $capcode['from'], $capcode['to'], $capcode['notes']);
			}
		}

		header("Location: ".THurl."admin.php?a=c");
	}
	elseif ($_GET['t']=="aw") //Add wordfilter
	{
		if($_POST['filterfrom'] == null || $_POST['filterto'] == null)
		{
			THdie('Invalid field provided.'); // don't know if this is right
		}
		
		// insertBCW takes care of the rest.
		$db->insertBCW(THbcw_filter, $_POST['filterfrom'], $_POST['filterto'], $_POST['notes']);
		
		rebuild_filters();
		
		$actionstring = "WF add\tfrom:".$_POST['filterfrom']."\tto:".$_POST['filterto'];
		writelog($actionstring,"admin");
		
		header("Location: ".THurl."admin.php?a=w");
	}
	elseif ($_GET['t']=="ew") //Edit filter
	{
		$filters = $db->fetchBCW(THbcw_filter);
		
		foreach ($filters as $filt)
		{
			if ($_POST['del'.$filt['id']])
			{
				$db->deleteBCW(THbcw_filter, $filt['id']);
				
				$actionstring = "WF delete\tid:".$filt['id'];
				writelog($actionstring,"admin");
			}
			else
			{
				$filter=array(
					'id'=>(int)$_POST['id'.$filt['id']],
					'from'=>$_POST['from'.$filt['id']],
					'to'=>$_POST['to'.$filt['id']],
					'notes'=>$_POST['notes'.$filt['id']]
				);
				
				$db->updateBCW(THbcw_filter, $filter['id'], $filter['from'], $filter['to'], $filter['notes']);
			}
		}
		
		rebuild_filters();
		
		header("Location: ".THurl."admin.php?a=w");
	}
	elseif ($_GET['t']=="au") // Manually add user
	{
		$errorstring = "";
		if(isset($_POST['user']))
		{	
			$profile_dbi = new ThornProfileDBI(); // This encapsulates the DB queries we need
			
			$username = trim($_POST['user']);
			$password = trim($_POST['password']);
			$email = trim($_POST['email']);
			
			// Name validation
			// Check if the account exists
			if($profile_dbi->userexists($username) == true)
			{
			$errorstring .= "Sorry, an account with this name already exists.<br />\n";
			}
			if(!preg_match('/^([\w\.])+$/i', $username))
			{
	        $errorstring .= "Sorry, your name must be alphanumeric and contain no spaces.<br />\n";
	        }
			
			// Password validation
			if($password)
			{
				$passlength = strlen($password);
				if($passlength < 4)
				{
					$errorstring .= "Sorry, your password must be at least 4 characters.<br />\n";
				}
			}
			else
			{
				$errorstring .= "You must provide a password!<br />\n";
			}
			
			// Email validation
			if(isset($_POST['email']) && strlen($email))
			{
		         /* Check if valid email address */
				if( !validateemail($email) ) // Provided in common.php
				{
					$errorstring .= "You must provide a valid email address!<br />\n";
				}
				// Check if it exists already
				if($profile_dbi->emailexists($email) == true)
				{
					$errorstring .= "That email has already been used to register an account!<br />\n";
				}
			}
			else
			{
				$errorstring .= "You must provide an email address!<br />\n";
			}
			
			// No errors encountered so far, attempt to register
			if($errorstring == "") 
			{ 	
				// Insert them, with approval from the beginning (hence the 1 at the end)
				$profile_dbi->registeruser($username, $password, THprofile_userlevel, $email, 1);
				
				$actionstring = "Add user\tname:".$username;
				writelog($actionstring,"admin");
				//header("Location: ".THurl."admin.php?a=p");
				//Forward them to the newly created profile - this will hopefully get rid of confusion on whether or not the profile was created.
				header("Location: ".THurl."profiles.php?action=viewprofile&user=".$username);
			}
			// <chopperdave> UHHHHHH OOOOOOHHHHHH </chopperdave>
			THdie($errorstring);
		}
			THdie("Username field must not be blank.");
	}
	elseif( $_GET['t'] == "spa") // Add static page
	{
		// verify parameters
		if( !isset($_POST['name']) || !isset($_POST['title']))
		{
			THdie("Name and/or title parameter not specified!");
		}
		
		$name = trim($_POST['name']);
		$title = trim($_POST['title']);
		
		if( $title == "" || $name == "")
		{
			THdie("Invalid name and/or title parameter provided.");
		}
		
		// Now we check if it exists
		if( $db->checkstaticpagename($name) == true )
		{
			THdie("Another static page already has name '".$name."'.");
		}
		
		// I guess this is OK. Add it.
		$pageid = $db->addstaticpage($name, $title);
		
		// Redirect!
		header("Location: ".THurl."admin.php?a=spe&id=".$pageid);
	}
	elseif( $_GET['t'] == "spx") // Delete static page
	{
		// verify parameters
		if( !isset($_GET['id']) )
		{
			THdie("ID parameter not specified!");
		}
		
		$id = intval($_GET['id']);
		
		// Clear the cache
		smclearpagecache($id);
		// Delete it from the DB
		$db->delstaticpage($id);
		
		// Redirect to the static pages list
		header("Location: ".THurl."admin.php?a=sp");
	}
	elseif( $_GET['t'] == "spe") // Edit static page (POST receiver)
	{
		// Verify parameters
		if( !isset($_POST['id']) )
		{
			THdie("ID parameter not specified!");
		}
		
		if( !isset($_POST['name']) || !isset($_POST['title']))
		{
			THdie("Name and/or title parameter not specified!");
		}

		if( !isset($_POST['publish']) || !isset($_POST['content']))
		{
			THdie("Publish and/or content parameter not specified!");
		}		
		
		// Don't bother checking if the id actually
		// refers to something that exists - the way our SQL
		// queries work, it won't make a difference because in
		// that case there will be nothing matching the "WHERE ID=___"
		// clause.
		
		// Clean up the incoming parameters
		$id = intval($_POST['id']);
		$name = trim($_POST['name']);
		$title = trim($_POST['title']);
		$content = $_POST['content'];
		$publish = intval($_POST['publish']);
		
		// Check name/title aren't empty
		if( $name == "" || $title == "")
		{
			THdie("Invalid name and/or title parameter provided.");
		}
		
		// Now we check if it exists (we check with ID because we don't
		// want to match the current page we're editing)
		if( $db->checkstaticpagename($name, $id) == true )
		{
			THdie("Another static page already has name '".$name."'.");
		}
		
		// Check publish parameter
		if( $publish < 0 || $publish > 3)
		{
			THdie("Invalid publish option specified!");
		}
		
		// Everything checked out, so let's clear the cache and update
		// the info
		smclearpagecache($id);
		$db->editstaticpage($id, $name, $title, $content, $publish);
		
		// Redirect!
		header("Location: ".THurl."admin.php?a=spe&id=".$id);
	}
?>
