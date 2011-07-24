<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-mod.php
	Description:    Code for the ThornModDBI class, based upon the MySQL version of ThornDBI
	Its abstract interface is in dbi/ABSTRACT-mod.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornModDBI extends ThornDBI
{

	// This class should only be created if the user is a mod or admin.
	function ThornModDBI($server = THdbserver, $user = THdbuser, $pass = THdbpass, $base = THdbbase)
	{
		$this->ThornDBI($server, $user, $pass, $base);
	}

	function banip($ip, $subnet, $privatereason, $publicreason, $adminreason, $postdata, $duration, $bannedby)
	{
		if ($this->checkban($ip) && $subnet == 0) // Check if it's a ban for a specific IP that's already covered by a subnet (the reverse is OK)
		{
			return (false);
		}

		if ( is_numeric($ip) ) // If it's an int, change it back over to the other format
		{
			$ip = long2ip($ip);
		}
		
		// Start messing around with the octets
		$octets = explode(".", $ip, 4);
		
		$octets[0] = intval($octets[0]);
		$octets[1] = intval($octets[1]);
		
		if( $subnet > 0 ) // Ban a subnet
		{
			$octets[3] = -1;
		}
		
		if( $subnet > 1) // Ban a class C subnet
		{
			$octets[2] = -1;
		}
		
		// Calculate when the ban was made
		$when = time() + (THtimeoffset * 60);
		
		$banquery = "insert into `".THbans_table."` 
		set ip_octet1=" . $octets[0] . ",
		ip_octet2=" . $octets[1] . ",
		ip_octet3=" . $octets[2] . ",
		ip_octet4=" . $octets[3] . ",
		privatereason='" . $this->clean($privatereason) . "', 
		publicreason='" . $this->clean($publicreason) . "', 
		adminreason='" . $this->clean($adminreason) . "', 
		postdata='" . $this->clean($postdata) . "', 
		duration='" . intval($duration) . "', 
		bantime=" . $when . ", 
		bannedby='" . $this->clean($bannedby) . "'";

		$this->myquery($banquery);
		
		return (true);
	}

	function banbody($id, $isthread, $publicbanreason = "USER HAS BEEN BANNED FOR THIS POST")
	{
		if ($publicbanreason)
		{
			$publicbanreason = '<br /><br /><span class=ban>(' . $publicbanreason . ')</span>';
		}
		else // return if we don't have one
		{
			$publicbanreason = "<br /><br /><span class=ban>(USER HAS BEEN BANNED FOR THIS POST)</span>";  //I guess this will work for now
		}

		if ($isthread)
		{
			$thebody = $this->myresult("select body from " . THthreads_table . " where id=" . $id);
			$thebody .= $publicbanreason;
			$updatequery = "update " . THthreads_table . " set body='" . $thebody . "' where id=" . $id;
			$myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
		}
		else
		{
			$thebody = $this->myresult("select body from " . THreplies_table . " where id=" . $id);
			$thebody .= $publicbanreason;
			$updatequery = "update " . THreplies_table . " set body='" . $thebody . "' where id=" . $id;
			$myresult = $this->myquery($updatequery); //or die('Could not add to post body. Another mod may have already deleted this post');
		}
		return;
	}
	
	function banipfrompost($id, $isthread, $subnet, $privatereason, $publicreason, $adminreason, $duration, $bannedby)
	{
		if ($isthread)
		{
			$q1 = "select ip from " . THthreads_table . " where id=" . $id;
			$ip = $this->myresult($q1);
			$q2 = "select globalid,board,body from " . THthreads_table . " where id=" . $id;
			$postdata = $this->myassoc($q2);
			$postdata = 'Post ' . $postdata['globalid'] . ' in /' . $this->getboardname($postdata['board']) .
			"/:<br />" . $postdata['body'];
		}
		else
		{
			$ip = $this->myresult("select ip from " . THreplies_table . " where id=" . $id);
			$postdata = $this->myassoc("select globalid,board,body from " . THreplies_table . " where id=" . $id);
			$postdata = 'Post ' . $postdata['globalid'] . ' in /' . $this->getboardname($postdata['board']) .
			"/:<br />" . $postdata['body'];
		}
		$this->banbody($id, $isthread, $publicreason);
		$this->touchpost($id, $isthread); // Mark a moderation action as performed
		$this->touchreports($postdata['globalid'], $postdata['board'], 1); // Mark as valid all reports for this post

		return ($this->banip($ip, $subnet, $privatereason, $publicreason, $adminreason, $postdata, $duration, $bannedby));
	}

	function banipfromthread($id, $privatereason, $publicreason, $adminreason, $duration, $bannedby)
	{
		$this->banipfrompost($id, true, 0, $privatereason, $publicreason, $adminreason, 
				$duration, $bannedby);
			
		$replies = $this->myarray("select id from " . THreplies_table . " where thread=" . $id);		
		foreach ($replies as $reply)
		{
			$this->banipfrompost($reply, false, 0, $privatereason, $publicreason, $adminreason, 
				$duration, $bannedby);		
		}
	}
	

	function delban($id, $reason="None provided")
	{	
		$singleban = $this->myassoc("select * from " . THbans_table . " where id=" . intval($id));
		
		if( $singleban )
		{
			// Move to the ban history table
			$history = "insert into `".THbanhistory_table."` 
			set ip_octet1=" . $singleban['ip_octet1'] . ",
			ip_octet2=" . $singleban['ip_octet2'] . ",
			ip_octet3=" . $singleban['ip_octet3'] . ",
			ip_octet4=" . $singleban['ip_octet4'] . ",
			privatereason='" . $this->clean($singleban['privatereason']) . "', 
			publicreason='" . $this->clean($singleban['publicreason']) . "', 
			adminreason='" . $this->clean($singleban['adminreason']) . "', 
			postdata='" . $this->clean($singleban['postdata']) . "', 
			duration='" . $singleban['duration'] . "', 
			bantime=" . $singleban['bantime'] . ", 
			bannedby='" . $singleban['bannedby'] . "',
			unbaninfo='" . $this->clean($reason) . "'";
		
			$this->myquery($history);
		
			$this->myquery("delete from " . THbans_table . " where id=" . intval($id));
		}
	}
	
	function getbanfromid($id)
	{		
		return $this->myassoc("select * from " . THbans_table . " where id=" . intval($id));
	}
	
	function getiphistory($ip)
	{	
		// Break up into octets
		$octets = explode(".", long2ip($ip), 4);

		//Retrieve the bans
		$bans = $this->mymultiarray("select * from `" . THbanhistory_table . "` where 
			`ip_octet1`=" . intval($octets[0]) . " 
			and `ip_octet2`=" . intval($octets[1]) . " 
			and (`ip_octet3`=" . intval($octets[2]) . " or `ip_octet3` = -1 )
			and (`ip_octet4`=" . intval($octets[3]) . " or `ip_octet4` = -1 )");
		
		return $bans;
	}

	function getallbans()
	{
		$baddies = array ();
		$baddies = $this->mymultiarray("select * from " . THbans_table);

		// Rendered unnecessary with our new ban format!
		// foreach ($baddies as $row)
		// {
			// $row['subnet'] = (bool) $row['subnet'];
		// }
		return ($baddies);
	}

	function delpost($id, $isthread)
	{
		if ($isthread)
		{
			// Get the thread's info
			$postarray = $this->myassoc("select * from " . THthreads_table  . " where id=" . $id);
			
			// Retrieve these for report updating at the end
			$reply_ids = $this->myarray("select globalid from " . THreplies_table . " where thread=". $id);
					
			if($reply_ids == null)
				$reply_ids = array();
						
			// Make an array of image indexes, starting with the replies (because then we can just optionally
			// add on the thread OP's imgidx at the end, instead of having to jump through hoops like before)
			$affimg = array();
			$affimg = $this->myarray("select distinct imgidx from " . THreplies_table . " where thread=" . $id . " and imgidx!=0");
			
			// Add the OP's imgidx to $affimg if it's nonzero
			if ($postarray['imgidx'] != 0)
			{
				$affimg[] = $postarray['imgidx'];
			}			
		
			// Actually delete the posts
			$this->myquery("delete from " . THreplies_table . " where thread=" . $id);
			$this->myquery("delete from " . THthreads_table . " where id=" . $id);
			
			// Remove from the images table
			if (count($affimg) > 0)
			{
				$extra_info_entries = $this->myarray("select extra_info from " . THimages_table . " where id in (" . implode(",", $affimg) . ")"); // Remove extra_info sections

				if (count($extra_info_entries) > 0)
				{
					$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
				}

				$this->myquery("delete from " . THimages_table . " where id in (" . implode(",", $affimg) . ")");
			}
			
			// Mark a moderation action as complete for the OP
			$this->touchreports($postarray['globalid'], $postarray['board'], 1); // 1 means a valid report
			
			// Now do it for all of those replies
			foreach( $reply_ids as $replyid )
			{
				$this->touchreports($replyid, $postarray['board'], 1); // 1 means a valid report
			}

		}
		else
		{
			$postarray = $this->myassoc("select * from " . THreplies_table . " where id=" . $id);
			
			// Delete images for this reply, if there are any
			if ($postarray['imgidx'] != 0)
			{
				// Since we're deleting a reply, we only have one element in this at most
				$affimg = array ( $postarray['imgidx'] );

				// Delete extra_info entries
				$extra_info_entries = $this->myarray("select extra_info from " . THimages_table . " where id=" . $postarray['imgidx']); // remove extra_info sections
				if (count($extra_info_entries) > 0)
				{
					$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
				}

				// Finally, delete from the DB
				$this->myquery("delete from " . THimages_table . " where id=" . $postarray['imgidx']);
			}
			else
			{
				$affimg = array ();
			}
			
			// Mark a moderation action as complete for this
			$this->touchreports($postarray['globalid'], $postarray['board'], 1); // 1 means a valid report
			
			// Finally, delete from the replies table
			$this->myquery("delete from " . THreplies_table . " where id=" . $id);
		}
		return ($affimg);
	}

	function delip($ip, $delsub = false)
	{
		if ($delsub) // Delete a subnet?
		{
			// calculate the subnet
			$sub = ipsub($ip);
			$submax = $sub +255;
			
			// Get the imgidxes for the affected posts
			$reply_imgidx = $this->myarray("select distinct imgidx from " . THreplies_table . " where ip between " . $sub . " and " . $submax . " and imgidx!=0");
			$thread_imgidx = $this->myarray("select distinct imgidx from " . THthreads_table . " where ip between " . $sub . " and " . $submax . " and imgidx!=0");
			
			// Get the affected replies/threads
			$affreplies = $this->mymultiarray("select id, globalid, board from " . THreplies_table . " where ip between " . $sub . " and " . $submax);
			$affthreads = $this->mymultiarray("select id, globalid, board from " . THthreads_table . " where ip between " . $sub . " and " . $submax);
			
			// Delete from the tables
			$this->myquery("delete from " . THreplies_table . " where ip between " . $sub . " and " . $submax);
			$this->myquery("delete from " . THthreads_table . " where ip between " . $sub . " and " . $submax);
		}
		else
		{
			// Get the imgidxes for the affected posts
			$reply_imgidx = $this->myarray("select distinct imgidx from " . THreplies_table . " where ip=" . $ip . " and imgidx!=0");
			$thread_imgidx = $this->myarray("select distinct imgidx from " . THthreads_table . " where ip=" . $ip . " and imgidx!=0");
			
			// Get the affected replies/threads
			$affreplies = $this->mymultiarray("select id, globalid, board from " . THreplies_table . " where ip=" . $ip);
			$affthreads = $this->mymultiarray("select id, globalid, board from " . THthreads_table . " where ip=" . $ip);
			
			// Delete from the tables
			$this->myquery("delete from " . THreplies_table . " where ip=" . $ip);
			$this->myquery("delete from " . THthreads_table . " where ip=" . $ip);
		}

		if( $reply_imgidx == null )
			$reply_imgidx = array();
			
		if( $thread_imgidx == null )
			$thread_imgidx = array();
		
		// $affimgs will hold imgidxes to delete later by other functions
		$affimgs = array ();
		$affimgs = array_merge($affimgs, $reply_imgidx, $thread_imgidx);
		
		// $affthreadids is an array of thread IDs so that we can later
		// turn into a string to use in a SQL query
		$affthreadids = array();
		
		// Clear the caches for each thread, add the thread ID to the $affthreadids array,
		// and mark valid all the reports for it
		foreach( $affthreads as $thread )
		{
			$affthreadids[] = $thread['id'];
			smclearcache($thread['board'], -1, $thread['globalid']); // clear the associated cache for this thread
			smclearcache($thread['board'], -1, -1); // AND this board
			$this->touchreports($thread['globalid'], $thread['board'], 1 ); // Mark as valid all reports for this thread
		}
		
		// All we have to do is mark the reports as valid
		foreach( $affreplies as $reply )
		{
			$this->touchreports($reply['globalid'], $reply['board'], 1 ); // Mark as valid all reports for this reply
		}
		
		// We need to handle replies in threads (started by this IP or IP range)
		if (count($affthreads) > 0)
		{
			// Get an array of thread IDs and turn it into a string so that we can
			// pop it into an SQL query easily
			$affstr = implode(",", $affthreadids);
			
			// Get replies to deleted threads
			$threadreplies = $this->mymultiarray("select globalid, board, imgidx from " . THreplies_table . " where thread in (" . $affstr . ")");
			
			// All we have to do is mark the reports as other (status 3) and
			// add nonzero imgidxes to $affimgs
			foreach( $threadreplies as $reply )
			{
				$this->touchreports($reply['globalid'], $reply['board'], 3 ); 
				
				// Add valid imgidxes to $affimgs
				if( $reply['imgidx'] != 0)
				{
					$affimgs[] =  $reply['imgidx'];
				}			
			}
			
			// Now delete the replies
			$this->myquery("delete from " . THreplies_table . " where thread in (" . $affstr . ")");	
		}

		
		// Delete DB info for all of these images (in $affimgs)
		if (count($affimgs) > 0)
		{
			$extra_info_entries = $this->myarray("select extra_info from " . THimages_table . " where id in (" . implode(",", $affimgs) . ")"); // Remove extra_info sections

			if (count($extra_info_entries) > 0)
			{
				$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
			}

			$this->myquery("delete from " . THimages_table . " where id in (" . implode(",", $affimgs) . ")");
		}
		
		// Return the affected images info
		return ($affimgs);
	}

	function delipfrompost($id, $isthread, $subnet = false)
	{
		if ($isthread)
		{
			$ip = $this->myresult("select ip from " . THthreads_table . " where id=" . $id);
		}
		else
		{
			$ip = $this->myresult("select ip from " . THreplies_table . " where id=" . $id);
		}
		return ($this->delip($ip, $subnet));
	}

	function updateboards($boards)
	{
		$boardchanges = array ();
		foreach ($boards as $board)
		{
			// Just a precautionary measure.
			if( $board['oldid'] == null || $board['oldid'] < 1)
			{
				$board['oldid'] = $board['id'];
			}
			
			if ($board['oldid'] != $board['id'])
			{		
				$max = $this->myresult("select max(id) from " . THboards_table) + 1;
				
				//Way to keep posts and threads on their intended board. A bit hackneyed, but should work.
				$boardchanges[] = array (
					"now" => $max,
					"to" => $board['id']
				);
				$this->myquery("update " . THthreads_table . " set board=" . $max . " where board=" . $board['oldid']);
				$this->myquery("update " . THreplies_table . " set board=" . $max . " where board=" . $board['oldid']);
				$max++;
			}
			
			$query = "update " . THboards_table . " set id=" . $board['id'] 
				. ", name='" . $this->escape_string($board['name'])
				. "', folder='" . $this->escape_string($board['folder']) 
				. "', about='" . $this->escape_string($board['about']) 
				. "', rules='" . $this->escape_string($board['rules'])
				. "', boardlayout ='" . $this->escape_string($board['boardlayout'])
				. "', perpg='" . $board['perpg'] 
				. "', perth='" . $board['perth'] 
				. "', allowedformats = '". $board['allowedformats']	
				. "', tpix='" . $board['tpix'] 
				. "', rpix='" . $board['rpix'] 
				. "', tmax='" . $board['tmax'] 							
				. "', thumbres='" . $board['thumbres']
				. "', maxfilesize='" . $board['maxfilesize']
				. "', maxres='" . $board['maxres']
				. "', pixperpost='" . $board['pixperpost']	
				. "', forced_anon='" . $board['forced_anon'] 
				. "', customcss='" . $board['customcss'] 
				. "', allowvids='" . $board['allowvids'] 
				. "', filter='" . $board['filter']
				. "', requireregistration='" . $board['requireregistration'] 
				. "', hidden='" . $board['hidden'] 
				. "', tlock='" . $board['tlock'] 
				. "', rlock='" . $board['rlock'] 
				. "' where id=".$board['oldid'];
				
			//print_r($query);
			$this->myquery($query);
		}
		
		foreach ($boardchanges as $change)
		{
			$this->myquery("update " . THthreads_table . " set board=" . $change['to'] . " where board=" . $change['now']);
			$this->myquery("update " . THreplies_table . " set board=" . $change['to'] . " where board=" . $change['now']);
			$this->myquery("update " . THreports_table . " set board=" . $change['to'] . " where board=" . $change['now']); // handle reports
		}
	}
	
	function makeboard($name, $folder, $about, $rules)
	{
		// Set up some default values
		$globalid=0;
		$perpg=20;
		$perth=4;
		$hidden=1;
		$allowedformats=7;
		$forced_anon=0;
		$filter=1;
		$maxfilesize=2097152;
		$allowvids=0;
		$customcss=0;
		$requireregistration=0;
		$boardlayout="drydock-image";
		$tlock=1;
		$rlock=1;
		$tpix=1;
		$rpix=1;
		$pixperpost=8;
		$maxres=3000;
		$thumbres=150;
		$tmax=100;
		$now=(THtimeoffset*60) + time();
		
		$query = "INSERT INTO ".THboards_table." ( " .
				"globalid , " .
				"name , " .
				"folder , " .
				"about , " .
				"rules , " .
				"perpg , " .
				"perth , " .
				"hidden , " .
				"allowedformats , " .
				"forced_anon , " .
				"maxfilesize ," .
				"maxres , " .
				"thumbres , " .
				"pixperpost , " .
				"customcss , " .
				"allowvids , " .
				"filter , " .
				"boardlayout , " .
				"requireregistration , " .
				"tlock , " .
				"rlock , " .
				"tpix , " .
				"rpix , " .
				"tmax , " .
				"lasttime " .
			")VALUES (".
				$globalid.",'".
				$this->escape_string($name)."','".
				$this->escape_string($folder)."','".
				$this->escape_string($about)."','".
				$this->escape_string($rules)."','".
				$perpg.	"','".
				$perth."','".
				$hidden."','".
				$allowedformats."','".
				$forced_anon."','".
				$maxfilesize."','".
				$maxres."','".
				$thumbres."','".
				$pixperpost."','".
				$customcss."','".
				$allowvids."','".
				$filter."','".
				$boardlayout."','".
				$requireregistration."','".
				$tlock."','".
				$rlock."','".
				$tpix."','".
				$rpix."','".
				$tmax."', ".
				$now." );";
				
		$this->myquery($query);
		
		return $this->lastid();
	}

	function fragboard($board)
	{
		$imgidxes = array ();
		$threadimgs = $this->myarray("select distinct imgidx from " . THthreads_table . " where board=" . $board . " and imgidx!=0");
		$replyimgs = $this->myarray("select distinct imgidx from " . THreplies_table . " where board=" . $board . " and imgidx!=0");
		
		if( $threadimgs == null )
			$threadimgs = array();
			
		if( $replyimgs == null )
			$replyimgs = array();
		
		$imgidxes = array_merge($imgidxes, $threadimgs, $replyimgs);

		$this->myquery("delete from " . THthreads_table . " where board=" . $board);
		$this->myquery("delete from " . THreplies_table . " where board=" . $board);
		$this->myquery("update ".THreports_table . " set status = 3 where status = 0 and board=".$board); // Clear existing reports
		
		if (count($imgidxes) != 0)
		{
			// First clear extra information
			$extra_info_entries = $this->myarray("select extra_info from " .THimages_table . " where extra_info != 0 and id in (" . implode(",", $imgidxes) . ")" );
			
			if( count($extra_info_entries) > 0)
			{
				$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
			}
						
			$this->myquery("delete from " . THimages_table . " where id in (" . implode(",", $imgidxes) . ")");
		}
		
		smclearcache($board, -1, -1, true); // clear EVERYTHING in the cache associated with this board
		return ($imgidxes);
	}
	
	function removeboard($board)
	{
		$this->myquery("DELETE from ".THboards_table." WHERE id=".intval($board));
	}

	function insertBCW($type = -1, $field1 = "", $field2 = "", $field3 = "")
	{
		$type = intval($type);
		switch ($type)
		{
			case 1 : // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$query = 'INSERT INTO ' . THblotter_table . ' ( entry, board, time ) VALUES ("' .
				$this->escape_string($field1) . '","' . intval($field2) . '","' . (THtimeoffset * 60) + time() . '")';
				break;

			case 2 : // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO ' . THcapcodes_table . ' ( capcodefrom, capcodeto, notes ) VALUES ("' .
				$this->escape_string($field1) . '","' . $this->escape_string($field2) . '","' . $this->escape_string($field3) . '");';
				break;

			case 3 : // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'INSERT INTO ' . THfilters_table . ' ( filterfrom, filterto, notes ) VALUES ("' .
				$this->escape_string($field1) . '","' . $this->escape_string($field2) . '","' . $this->escape_string($field3) . '");';
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
		return mysql_insert_id($this->cxn); // Return the insertion ID.
	}

	function updateBCW($type = -1, $id, $field1 = "", $field2 = "", $field3 = "")
	{
		$type = intval($type);

		// It is assumed that all of these will have some sort of ID by which to identify what to update, therefore it is not listed in the FIELD comments.
		switch ($type)
		{
			case 1 : // Blotter posts
				// FIELD 1: The entry (string)
				// FIELD 2: The target board (integer)
				$query = 'UPDATE ' . THblotter_table . " SET entry = '" . $this->escape_string($field1) . "', board=" . intval($field2) . " WHERE id=" . intval($id);
				break;

			case 2 : // Capcodes
				// FIELD 1: Capcode from (string)
				// FIELD 2: Capcode to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE ' . THcapcodes_table . " SET capcodefrom='" .
				$this->escape_string($field1) . "', capcodeto='" . $this->escape_string($field2) . "', notes='" . $this->escape_string($field3) . "' WHERE id=" . intval($id);
				break;

			case 3 : // Wordfilters
				// FIELD 1: Filter from (string)
				// FIELD 2: Filter to (string)
				// FIELD 3: Notes (string)
				$query = 'UPDATE ' . THfilters_table . " SET filterfrom='" .
				$this->escape_string($field1) . "', filterto='" . $this->escape_string($field2) . "', notes='" . $this->escape_string($field3) . "' WHERE id=" . intval($id);
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
	}

	function deleteBCW($type = -1, $id)
	{
		$type = intval($type);
		switch ($type)
		{
			case 1 : // Blotter posts
				$query = "DELETE FROM " . THblotter_table . " WHERE id=" . intval($id);
				break;

			case 2 : // Capcodes
				$query = "DELETE FROM " . THcapcodes_table . " WHERE id=" . intval($id);
				break;

			case 3 : // Wordfilters
				$query = "DELETE FROM " . THfilters_table . " WHERE id=" . intval($id);
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		$this->myquery($query);
	}

	function fetchBCW($type = -1)
	{
		$type = intval($type);
		switch ($type)
		{
			case 1 : // Blotter posts
				$query = "SELECT * FROM " . THblotter_table;
				break;

			case 2 : // Capcodes
				$query = "SELECT * FROM " . THcapcodes_table;
				break;

			case 3 : // Wordfilters
				$query = "SELECT * FROM " . THfilters_table;
				break;

			default :
				die("BCW error: Invalid type provided!");
				break;
		}

		return $this->mymultiarray($query);
	}

	function userdelpost($posts, $board, $password)
	{
		// Get the threads deleted so we can clear their caches!
		$threads_deleted = array();
		$threads_deleted = $this->mymultiarray("SELECT id,globalid FROM ".THthreads_table." WHERE board=".intval($board)." " .
				"AND globalid IN (" . implode(",", $posts) . ") AND password='".
				$this->escape_string(md5(THsecret_salt.$password))."' AND password IS NOT NULL");
		
		if( $threads_deleted != null && count($threads_deleted) > 0)
		{				
			foreach($threads_deleted as $thread)
			{
				delimgs($this->delpost($thread['id'], true));
				$this->touchreports($thread['globalid'], $board, 3); // Clear all reports for it
				smclearcache($board, -1, $thread['globalid']); // clear the cache
			}
		}
		
		$posts_deleted = array();
		$posts_deleted = $this->myarray("SELECT id FROM ".THreplies_table." WHERE board=".intval($board)." " .
				"AND globalid IN (" . implode(",", $posts) . ") AND password='".
				$this->escape_string(md5(THsecret_salt.$password))."' AND password IS NOT NULL");
				
		if( $posts_deleted != null && count($posts_deleted) > 0)
		{				
			foreach($posts_deleted as $post)
			{
				delimgs($this->delpost($post, false));
				$this->touchreports($post, $board, 3); // Clear all reports for it
			}
		}
		
		// Return the total number of threads deleted
		return count($posts_deleted) + count($threads_deleted);
	}

	function touchpost($id, $isthread, $time = null)
	{
		if( $time == null )
		{
			$time = time() + (THtimeoffset * 60);
		}
		else
		{
			$time = intval($time);
		}
		
		if( $isthread == true )
		{
			$this->myquery("UPDATE ".THthreads_table." set unvisibletime=".$time." WHERE id=".intval($id));
		}
		else
		{
			$this->myquery("UPDATE ".THreplies_table." set unvisibletime=".$time." WHERE id=".intval($id));
		}
	}
	
	 function gettopreports($board=0)
	 {
	 	if( $board == 0) // No board filtering
	 	{
	 		return $this->mymultiarray("SELECT 
						*,
						COUNT(DISTINCT ip) AS reporter_count,
						MIN(time) AS earliest_report,
						AVG(category) AS avg_category
					FROM 
						".THreports_table."
					WHERE 
						status = 0 
					GROUP BY 
						postid, board 
					ORDER BY 
						avg_category ASC,
						reporter_count DESC,
						earliest_report ASC
					LIMIT 20");
	 	}
	 	else
	 	{
	 		return $this->mymultiarray("SELECT 
						*,
						COUNT(DISTINCT ip) AS reporter_count,
						MIN(time) AS earliest_report,
						AVG(category) AS avg_category
					FROM 
						".THreports_table."
					WHERE 
						status = 0 AND board = ".intval($board)."
					GROUP BY 
						postid, board 
					ORDER BY 
						avg_category ASC,
						reporter_count DESC,
						earliest_report ASC
					LIMIT 20");	 		
	 	}
	 }
	 
	 function touchreports($post, $board, $status=3)
	 {
	 	$this->myquery("UPDATE ".THreports_table." set status=".intval($status).
			" where status=0 and postid=".intval($post)." and board=".intval($board));
	 }
	 
	function recentreportsfromip($ip = null)
	{	
		// If it's null
		if ($ip == null)
		{
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
		}
		else
		{
			$ip = intval($ip);
		}
		
		return $this->mymultiarray("SELECT * FROM ".THreports_table." WHERE status != 0 AND ip=".$ip.
					" ORDER BY time DESC LIMIT 15");
	}
	
	function recentpostsfromip($ip = null)
	{
		// If it's null
		if ($ip == null)
		{
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
		}
		else
		{
			$ip = intval($ip);
		}
		
		// Set up some things
		$initial_posts = array(); // This will contain the combination of the previous arrays
		
		$initial_threads = $this->myarray("SELECT time FROM ".THthreads_table.
								" WHERE ip=".$ip." ORDER BY time DESC LIMIT 10");
								
		$initial_replies = $this->myarray("SELECT time FROM ".THreplies_table.
								" WHERE ip=".$ip." ORDER BY time DESC LIMIT 10");
								
		if( $initial_threads == null )
			$initial_threads = array();
			
		if( $initial_replies == null )
			$initial_replies = array();
								
		$initial_posts = array_merge($initial_threads, $initial_replies);
		
		// Do we have to do filtering?
		if( count($initial_posts) > 10 )
		{
			// We need to do filtering. Sort this array, find the minimum time
			// (i.e. the time of the 10th element)
			// and retrieve all the posts that fall within this
			
			rsort($initial_posts); // reverse because we want to go highest->lowest
			$min_time = $initial_posts[9]; // 10th (least recent in our view) post
			
			$initial_threads = $this->mymultiarray("SELECT * FROM ".THthreads_table.
								" WHERE ip=".$ip." AND time >= ".$min_time." LIMIT 10");
								
			$initial_replies = $this->mymultiarray("SELECT * FROM ".THreplies_table.
								" WHERE ip=".$ip." AND time >= ".$min_time." LIMIT 10");
								
			if( $initial_threads == null )
				$initial_threads = array();
			
			if( $initial_replies == null )
				$initial_replies = array();
							
			$initial_posts = array_merge($initial_threads, $initial_replies);			
		}
		else
		{
			// No filtering required, just retrieve the assocs
			$initial_threads = $this->mymultiarray("SELECT * FROM ".THthreads_table.
									" WHERE ip=".$ip." ORDER BY time DESC LIMIT 10");
									
			$initial_replies = $this->mymultiarray("SELECT * FROM ".THreplies_table.
									" WHERE ip=".$ip." ORDER BY time DESC LIMIT 10");
									
			if( $initial_threads == null )
				$initial_threads = array();
			
			if( $initial_replies == null )
				$initial_replies = array();
									
			$initial_posts = array_merge($initial_threads, $initial_replies);
		}

		// Don't bother sorting if we have 0 or 1 entries
		if( count($initial_posts) > 1)
		{
			// Sort the $initial_posts array.  The implementation for this sort
			// is in common.php
			usort($initial_posts, 'comp_post_times');
		}
		
		return $initial_posts;
	}
	
	function getpostfromimgidx($imgidx)
	{
		$location = array(); // This will contain all of the information
		$imgidx = intval($imgidx);
		
		// don't allow any funny business >:[
		if( $imgidx == 0 )
		{
			return null;
		}
		
		// Try replies first
		$post_test = $this->myassoc("SELECT * FROM ".THreplies_table." WHERE imgidx=".$imgidx);
		if( $post_test != null )
		{
			// Hah, found it.
			$location['board'] = $post_test['board'];
			$location['post_loc'] = $post_test['globalid'];
			
			// Get the thread globalid now.
			$location['thread_loc'] = $this->myresult("SELECT globalid FROM ".THthreads_table." WHERE id=".$post_test['thread']);
		}
		else
		{
			// Didn't find it, try threads now
			$post_test = $this->myassoc("SELECT * FROM ".THthreads_table." WHERE imgidx=".$imgidx);
			if( $post_test != null )
			{
				$location['board'] = $post_test['board'];
				$location['thread_loc'] = $post_test['globalid'];
				$location['post_loc'] = $post_test['globalid'];
			}
			else
			{
				// Didn't find it.
				return null;
			}
		}
		
		// We can assume everything was initialized OK, so just return this.
		return $location;
	}
	
	function addstaticpage($name, $title)
	{
		$this->myquery('INSERT INTO ' . THpages_table . ' ( name, title, content, publish ) VALUES ("' .
			$this->clean($name) . '","' . $this->clean($title) . '","This is a blank page.", 0);');
			
		return $this->lastid();
	}

	function checkstaticpagename($name, $id=null)
	{
		$count = 0;
		
		if( $id == null )
		{
			$count = $this->myresult("SELECT COUNT(*) FROM ".THpages_table.
						" WHERE name='".$this->clean($name)."'");			
		}
		else // Check for a different ID as well
		{
			$count = $this->myresult("SELECT COUNT(*) FROM ".THpages_table.
						" WHERE name='".$this->clean($name)."' AND id!=".intval($id));					
		}
		
		return ($count > 0);	
	}
	
	function delstaticpage($id)
	{
		$this->myquery("DELETE FROM ".THpages_table." WHERE id=".intval($id));
	}
	
	function editstaticpage($id, $name, $title, $content, $publish)
	{
		$this->myquery("UPDATE ".THpages_table." SET name='".$this->clean($name)."',
				title='".$this->clean($title)."', content='".$this->escape_string($content)."',
				publish=".intval($publish)." WHERE id=".intval($id));
	}
	
	function getstaticpages()
	{
		return $this->mymultiarray("SELECT * FROM ".THpages_table);
	}
		
} //class ThornModDBI
?>
