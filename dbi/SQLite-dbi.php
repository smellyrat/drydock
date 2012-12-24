<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/SQLite.php
	Description:    Handles interface between database and board functions using a SQLite database
	Its abstract interface is in dbi/ABSTRACT-dbi.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

define("THdblitefn", sqlite_open(THpath . "unlinked/drydock.sqlite", 0666, $sqliteerror));
require_once ("ABSTRACT-dbi.php"); // abstract interface
//	if(!isset($DDDEBUG)) { define("DDDEBUG",1); } // Could break things if enabled, use only for testing


class ThornDBI implements absThornDBI
{
	function ThornDBI()
	{
		if (isset ($this->cxn) == false)
		{
			$this->cxn = THdblitefn or THdie($sqliteerror);
		}
	}
	
	function escape_string($call)
	{
		return sqlite_escape_string($call);
	}
	
	/*  provided by Mell03d0ut from anonib */
	function clean($call)
	{
		$call = $this->escape_string($call);
		$call = trim($call);
		return ($call);
	}	
	
	function lastid()
	{
		return sqlite_last_insert_rowid( THdblitefn );
	}
	
	function affectedrows()
	{
		return sqlite_changes( THdblitefn );
	}
	
	function getvisibleboards()
	{
		return $this->mymultiarray("SELECT * FROM " . THboards_table . " WHERE hidden != 1 order by folder asc");
	}
	
	function getbinfo($id)
	{
		return ($this->myassoc("select * from " . THboards_table . " where id='" . intval($id) ."'"));
	}
	
	function myassoc($call)
	{
		if(DDDEBUG==1) { echo ("myassoc: " . $call . "<br />"); } 
		$pup = sqlite_query(THdblitefn, $call);
		$dog = sqlite_fetch_array($pup, SQLITE_ASSOC); // or return null;
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
	}
	
	function myarray($call)
	{
		if(DDDEBUG==1) { echo ("myarray: " . $call . "<br />"); } 
		$pup = sqlite_query(THdblitefn, $call);
		$dog = sqlite_fetch_array($pup, SQLITE_NUM); // or return null;
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
	}

	function myresult($call)
	{
		if(DDDEBUG==1) { echo ("myresult: " . $call . "<br />"); }
		$dog = sqlite_query(THdblitefn, $call);
		if ($dog === false || sqlite_num_rows($dog) == 0)
		{
			return (null);
		}
		return (sqlite_fetch_single($dog, 0));
	}

	function myquery($call)
	{
		if(DDDEBUG==1) { echo ("myquery: " . $call . "<br />"); }
		$dog = sqlite_query(THdblitefn, $call); // or die(mysql_error()."<br />".$call);
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
	}

	function mymultiarray($call)
	{
		if(DDDEBUG==1) { echo ("mymultiarray: " . $call . "<br />"); } 

		$multi = array ();

		$queryresult = $this->myquery($call);
		if ($queryresult != null)
		{
			while ($entry = sqlite_fetch_array($queryresult))
			{
				$multi[] = $entry;
			}
		}
		return $multi;
	}

	function timecount($start, $end)
	{
		//Returns the number of threads between two specified times.
		if (isset ($this->binfo))
		{
			return ($this->myresult("select count(*) from " . THthreads_table . " where board=" . $this->binfo['id'] . " and time>=" . $start . " and time<=" . $end));
		}
		else
		{
			return ($this->myresult("select count(*) from " . THthreads_table . " where time>=" . $start . " and time<=" . $end));
		}
	}

	function gettimessince($since)
	{
		//Returns the times of all threads since $since.
		if (isset ($this->binfo))
		{
			//echo "Binfo";
			//Will there be cases where this will be called without binfo being set?
			if ($since != null)
			{
				$yay = $this->myquery("select time from " . THthreads_table . " where board=" . $this->binfo['id'] . " and time>=" . $since);
			}
			else
			{
				$yay = $this->myquery("select time from " . THthreads_table . " where board=" . $this->binfo['id']);
			}
		}
		else
		{
			//echo "No binfo";
			if ($since != null)
			{
				$yay = $this->myresult("select time from " . THthreads_table . " where time>=" . $since);
			}
			else
			{
				$yay = $this->myresult("select time from " . THthreads_table);
			}
		}
		//array($wows);
		$wows = array ();
		echo "Row count: " . sqlite_num_rows($yay);
		while ($row = sqlite_current($yay)) //help
		{
			//var_dump($row);
			$wows[] = (int) $row[0];
		}
		return ($wows);
	}

	function getimgs($imgidx)
	{
		if ($imgidx == 0 || $imgidx == null)
		{
			return (array ());
		}
		
		$imgs = array ();
		/*
		$querystring = "select ". THimages_table .".*, ".THextrainfo_table.".extra_info AS exif_text FROM "
				 . THimages_table ." LEFT OUTER JOIN ".THextrainfo_table. " on ".THimages_table
				 .".extra_info = ".THextrainfo_table.".id WHERE ".THimages_table.".id=".intval($imgidx);
		*/
		//$querystring = "select * FROM drydock_images where id=".intval($imgidx);
		
		$querystring = "select "
				 . THimages_table .".id as id, "
				 . THimages_table .".hash as hash, "
				 . THimages_table .".name as name, "
				 . THimages_table .".width as width, "
				 . THimages_table .".height as height, "
				 . THimages_table .".tname as tname, "
				 . THimages_table .".twidth as twidth, "
				 . THimages_table .".theight as theight, "
				 . THimages_table .".fsize as fsize, "
				 . THimages_table .".anim as anim, "
				 . THextrainfo_table.".extra_info AS exif_text"
				 . " FROM ". THimages_table ." LEFT OUTER JOIN ".THextrainfo_table. " on "
				 . THimages_table.".extra_info = ".THextrainfo_table.".id WHERE "
				 . THimages_table.".id=".intval($imgidx);

				 
		$imgs = $this->mymultiarray($querystring);
		//echo '<pre>' . var_export($imgs,true).'</code></pre>';
		//var_dump($imgs);
		return ($imgs);
	}

	function getblotter($board)
	{
		$entries = array ();
		$count = 0;
		$blotter = $this->myquery("select * from " . THblotter_table . " ORDER BY time ASC");
		while ($entry = sqlite_fetch_array($blotter)) //help
		{
			if ($entry['board'] == "0" || is_in_csl($board, $entry['board']))
			{
				$entries[] = $entry;
				$count++;
			}

			if ($count >= 5)
			{
				break;
			}
		}
		return ($entries);
	}

	function getindex($p, & $sm)
	{
		if (isset ($p['full']) == false)
		{
			$p['full'] = false;
		}
		if (isset ($p['sortmethod']) == false)
		{
			$p['sortmethod'] = "id";
		}
		if (isset ($p['desc']) == false)
		{
			$p['desc'] = false;
		}

		if ($p['full'])
		{
			$q = "select * from " . THboards_table;
		}
		else
		{
			$q = "select id, name, about, hidden from " . THboards_table;
		}

		if ($p['sortmethod'] = "id")
		{
			$q .= " order by id";
		}
		elseif ($p['sortmethod'] = "last")
		{
			$q .= " order by lasttime";
		}
		elseif ($p['sortmethod'] = "name")
		{
			$q .= " order by name";
		}

		if ($p['desc'])
		{
			$q .= " desc";
		}
		$iguana = $this->myquery($q);
		$boards = array ();
		while ($board = sqlite_fetch_array($iguana)) //help
		{
			$boards[] = $board;
		}
		return ($boards);
	}

	function checkban($ip = null)
	{		
		// If it's null
		if ($ip == null)
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else if ( is_int($ip) ) // If it's an int
		{
			$ip = long2ip($ip);
		}
		
		// Break up into octets
		$octets = explode(".", $ip, 4);

		//Check already banned...
		if ($this->myresult("select count(*) from " . THbans_table . " where 
			ip_octet1=" . intval($octets[0]) . " 
			AND ip_octet2=" . intval($octets[1]) . " 
			AND (ip_octet3=" . intval($octets[2]) . " OR ip_octet3 = -1 )
			AND (ip_octet4=" . intval($octets[3]) . " OR ip_octet4 = -1 )
		") > 0)
		{
			return (true);
		}
		else
		{
			return (false);
		}
		
	}

	function getban($ip = null, $clear = true)
	{
		// If it's null
		if ($ip == null)
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else if ( is_int($ip) ) // If it's an int
		{
			$ip = long2ip($ip);
		}
		
		// Break up into octets
		$octets = explode(".", $ip, 4);

		//Retrieve the bans
	/*	$bans = $this->mymultiarray("select * from '" . THbans_table . "' where 
			'ip_octet1'=" . intval($octets[0]) . " 
			AND 'ip_octet2'=" . intval($octets[1]) . " 
			AND ('ip_octet3'=" . intval($octets[2]) . " OR 'ip_octet3' = -1 )
			AND ('ip_octet4'=" . intval($octets[3]) . " OR 'ip_octet4' = -1 )");
			
	*/		
		$bans = $this->mymultiarray("SELECT *
			FROM " . THbans_table . "
			WHERE ip_octet1 = " . intval($octets[0]) . "
			AND ip_octet2 = " . intval($octets[1]) . "
			AND ( ip_octet3 = " . intval($octets[2]) . " OR ip_octet3 = -1 ) 
			AND ( ip_octet4 = " . intval($octets[3]) . " OR ip_octet4 = -1 )");

			// Clear old bans if $clear is true
		if( $clear == true )
		{
			// Move old bans to the ban history table
			foreach( $bans as $singleban )
			{
				if( $singleban['duration'] == 0 ) // Warning
				{
					// Move to ban history table
					$history = "insert into '".THbanhistory_table."' 
					(ip_octet1,ip_octet2,ip_octet3,ip_octet4,privatereason,publicreason,adminreason,postdata,duration,bantime,bannedby,unbaninfo) values (
					" . $singleban['ip_octet1'] . ",
					" . $singleban['ip_octet2'] . ",
					" . $singleban['ip_octet3'] . ",
					" . $singleban['ip_octet4'] . ",
					'" . $this->clean($singleban['privatereason']) . "', 
					'" . $this->clean($singleban['publicreason']) . "', 
					'" . $this->clean($singleban['adminreason']) . "', 
					'" . $this->clean($singleban['postdata']) . "', 
					'" . $singleban['duration'] . "', 
					" . $singleban['bantime'] . ", 
					'" . $singleban['bannedby'] . "',
					'viewed')";
				
					$this->myquery($history);
					
					// Delete this ban from the active bans table
					$this->myquery("delete from ".THbans_table." where id=".intval($singleban['id']));
				}
				else if( $singleban['duration'] != -1 ) // May have expired, so we'll have to check
				{
					//we'll need to know the difference between the ban time and the duration for actually expiring the bans
					$offset = THtimeoffset*60;
					$now = time()+$offset;
					$banoffset = $singleban['duration']*3600; // convert to hours
					$expiremath = $banoffset+$singleban['bantime'];
				
					if($now>$expiremath) // It expired.
					{
						// Move to ban history table
						$history = "insert into '".THbanhistory_table."' 
						(ip_octet1,ip_octet2,ip_octet3,ip_octet4,privatereason,publicreason,adminreason,postdata,duration,bantime,bannedby,unbaninfo) values (
						" . $singleban['ip_octet1'] . ",
						" . $singleban['ip_octet2'] . ",
						" . $singleban['ip_octet3'] . ",
						" . $singleban['ip_octet4'] . ",
						'" . $this->clean($singleban['privatereason']) . "', 
						'" . $this->clean($singleban['publicreason']) . "', 
						'" . $this->clean($singleban['adminreason']) . "', 
						'" . $this->clean($singleban['postdata']) . "', 
						'" . $singleban['duration'] . "', 
						" . $singleban['bantime'] . ", 
						'" . $singleban['bannedby'] . "',
						'expired')";
					
						$this->myquery($history);
						
						// Delete from active bans table
						$this->myquery("delete from ".THbans_table." where id=".intval($singleban['id']));
					} 
				}
			}
		}
		
		return $bans;
	}

	
	function getboard($id = 0, $folder = "")
	{
		$querystring = "select * from " . THboards_table . " where ";
		$id = intval($id); // Make it explicitly an integer

		if ($id == 0 and $folder == "") // No filtering at all
		{
			$querystring = $querystring . "1";
		}
		elseif ($id != 0 and $folder != "") // Filtering by both folder AND ID
		{
			$querystring = $querystring . "id=" . $id . " AND folder='" . $this->escape_string($folder) . "'";
		}
		elseif ($id != 0) // Filtering by only ID
		{
			$querystring = $querystring . "id=" . $id;
		}
		else // Filtering by only folder
		{
			$querystring = $querystring . "folder='" . $this->escape_string($folder) . "'";
		}
		
		if( $id == 0 && $folder == "" )
		{
			$multi = array ();

			$queryresult = $this->myquery($querystring);
			if ($queryresult != null)
			{
				while ($entry = sqlite_fetch_array($queryresult))
				{
					$multi[$entry['id']] = $entry;
				}
			}
			
			return $multi;
		}
		else
		{
			return $this->mymultiarray($querystring);
		}
	}
	
	function getboardname($number)
	{
		$boardquery = "SELECT folder FROM ".THboards_table." WHERE id =".intval($number);
		$name = $this->myresult($boardquery);
		if($name != null)
		{ 
			return $name;
		} 
		else 
		{ 
			return false;
		}
	}

	function getboardnumber($folder)
	{
		$boardquery = "SELECT id FROM ".THboards_table." WHERE folder ='".$this->escape_string($folder)."'";
		$number = $this->myresult($boardquery);
		if($number != null)
		{ 
			return $number;
		} 
		else 
		{ 
			return false;
		}
	}
	
	function isboardreg($board)
	{
		return $this->myresult("select requireregistration from ".THboards_table." where id=".intval($board));
	}
	
	function getboardtemplate($board)
	{
		return $this->myresult("select boardlayout from ".THboards_table." where id=".intval($board));
	}
	
	function addexifdata($exif)
	{
		$ex_inf_result = 
			$this->myquery("INSERT INTO ".THextrainfo_table." ( id, extra_info ) VALUES (NULL, '".$this->escape_string($exif)."')");
		
		if($ex_inf_result)
		{
			return $this->lastid();
		}
		else
		{
			return 0;
		}
	}

	function findpost($globalid, $board)
	{
		// Safe it.
		$globalid = intval($globalid);
		$board = intval($board);
		
		// Check if it's a thread
	 	if( $this->myresult("SELECT COUNT(*) FROM ".THthreads_table." WHERE globalid=".$globalid." AND board=".$board) > 0)
	 	{
	 		return 1; // found it, return 1 for thread
	 	}
	 	elseif( $this->myresult("SELECT COUNT(*) FROM ".THreplies_table." WHERE globalid=".$globalid." AND board=".$board) > 0)
	 	{
	 		return 2; // found it, return 2 for reply
	 	}
	 	
	 	// We fell through- not found.
	 	return 0;
	}
	
	function getsinglepost($id, $board)
	{
		$postassoc = array();
		
		// Try replies first
		
		$qstring = "SELECT * FROM " . THreplies_table . " WHERE globalid=" . intval($id) . 
						" AND board=" . intval($board);
		$postassoc = $this->myassoc($qstring);

		if ($postassoc == null)
		{
			$qstring = "SELECT * FROM " . THthreads_table . " WHERE globalid=" . intval($id) . 
						" AND board=" . intval($board);
			$postassoc = $this->myassoc($qstring);
		}
	
		return $postassoc;
	}
		
	function getpostlocation($threadid, $postid = -1)
	{
		$location = array();
		
		if ( $postid > -1 ) // Retrieving information for a reply
		{
			$location['post_loc'] = $this->myresult("select globalid from ".THreplies_table." where id=".intval($postid));
			$location['thread_loc'] = $this->myresult("select globalid from ".THthreads_table." where id=".intval($threadid));
		}
		else // For a thread
		{
			$location['thread_loc'] = $this->myresult("select globalid from ".THthreads_table." where id=".intval($threadid));
		}
		
		return $location;
	}

} //ThornDBI

//===========================================================================================

// This concludes the main body of ThornDBI- the following includes contain derived classes 
// which encapsulate the other (more specialized) functions required by various tasks

require_once ("SQLite-board.php"); //ThornBoardDBI
require_once ("SQLite-mod.php"); //ThornModDBI
require_once ("SQLite-post.php"); //ThornPostDBI
require_once ("SQLite-thread.php"); //ThornThreadDBI
require_once ("SQLite-profile.php"); // ThornProfileDBI
require_once ("SQLite-tools.php"); // ThornToolsDBI
?>
