<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-post.php
	Description:    Code for the ThornPostDBI class, based upon the MySQL version of ThornDBI
	Its abstract interface is in dbi/ABSTRACT-post.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornPostDBI extends ThornDBI
{
	//This class will not be seen by Smarty, so we can neglect unsetting IPs and such.
	function ThornPostDBI()
	{
		$this->ThornDBI();
	}

	function gettinfo($t)
	{
		return ($this->myassoc("select * from " . THthreads_table . " where id=" . intval($t)));
	}

	function putthread($name, $tpass, $boardnumber, $title, $body, $link, $ip, $mod, $pin, $lock, $permasage, $password= "",$tyme = false)
	{
		if ($tyme === false)
		{
			$tyme = time() + (THtimeoffset * 60);
		}
		$q = "insert into " . THthreads_table . " set board='" . $boardnumber . "', title='" . $this->clean($title) . "', body='";
		if ($boardnumber == THnewsboard || $boardnumber == THmodboard) //don't filter the news board nor the mod board
		{
			$q .= $this->escape_string($body);
		}
		else
		{
			$q .= $this->clean($body);
		}
		$q .= "', ip=" . $ip . ", pin=" . $pin . ", permasage=" . $permasage . ", lawk=" . $lock . ", time=" . $tyme . ", bump=" . $tyme;
		$globalid = $this->getglobalid($boardnumber);
		$q .= ", globalid=" . $globalid;
		if ($name != null)
		{
			$q .= ", name='" . $this->clean($name) . "'";
		}
		if ($tpass != null)
		{
			$q .= ", trip='" . $tpass . "'";
			//Not cleaning trip since it should be encoded.
		}
		if ($link != null)
		{
			if (!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/", $link))
			{
				$link = "mailto:" . $link;
			}
			$q .= ", link='" . $this->clean($link) . "'";
		}
		
		if( $password != "") // password for post deletion
		{
			$q .= ", password='" . $this->escape_string(md5(THsecret_salt.$password)) . "'";
		}
		
		//echo($q.", time=".$tyme);
//		echo $q;
//		if ($link != null)
//		{
//			$q .= ", link='" . $this->clean($link) . "'";
//		}
		$this->myquery($q) or THdie("DBpost");
		if ($boardnumber == THnewsboard)
		{
			rebuild_rss();
		}
		smclearcache($boardnumber, -1, -1); // clear the cache for this board
		$tnum = mysql_insert_id();
		$this->myquery("update " . THboards_table . " set lasttime=" . $tyme . " where id='" . $boardnumber . "'") or THdie("DBpost");
		return ($tnum);
	}

	function putpost($name, $tpass, $link, $boardnumber, $thread, $body, $ip, $mod, $password = "", $tyme = false)
	{
		$q = "insert into " . THreplies_table . " set thread=" . $thread . ", board=" . $boardnumber . ", body='";
		if ($boardnumber == THmodboard) //don't filter the mod board since it should be all locked up anyway
		{
			$q .= $this->escape_string($body);
		}
		else
		{
			$q .= $this->clean($body);
		}
		
		$bump = preg_match("/^(mailto:)?sage$/", $link); // sage check
		
		$q .= "', ip=" . $ip . ", bump=" . (int) $bump;
		$globalid = $this->getglobalid($boardnumber);

		$q .= ", globalid=" . $globalid;
		if ($name != null)
		{
			$q .= ", name='" . $this->clean($name) . "'";
		}
		if ($tpass != null)
		{
			$q .= ", trip='" . $tpass . "'";
		}
		$bump = preg_match("/^(mailto:)?sage$/", $link);
		if ($link != null)
		{
			if (!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/", $link))
			{
				$link = "mailto:" . $link;
			}
			$q .= ", link='" . $this->clean($link) . "'";
		}
		if ($tyme === false)
		{
			$tyme = time() + (THtimeoffset * 60);
		}
		
		if( $password != "") // password
		{
			$q .= ", password='" . $this->escape_string(md5(THsecret_salt.$password)) . "'";
		}
		//echo($q);
		$this->myquery($q . ", time=" . $tyme) or THdie("DBpost");
		//if ($board == THnewsboard) { buildnews(); }	
		$pnum = mysql_insert_id();
		if (!$bump)
		{
			$this->myquery("update " . THthreads_table . " set bump=" . $tyme . " where id=" . $thread . " and permasage = 0");
		}
		$this->myquery("update " . THboards_table . " set lasttime=" . $tyme . " where id='" . $boardnumber . "'") or THdie("DBpost");
		smclearcache($boardnumber, -1, -1); // clear cache for the board
		smclearcache($boardnumber, -1, $thread); // and for the thread
		return ($pnum);
	}

	function putimgs($num, $isthread, $files)
	{

		$id = $this->myresult("select max(id) from " . THimages_table) + 1;
		foreach ($files as $file)
		{
			$values[] = "(" . $id . ",'" . $file['hash'] . "','" . $this->clean($file['name']) . "'," . $file['width'] . "," . $file['height'] . ",'" . $this->clean($file['tname']) . "'," . $file['twidth'] . "," . $file['theight'] . "," . $file['fsize'] . "," . (int) $file['anim'] . "," . (int) $file['extra_info'] . ")";
		}
		$this->myquery("insert into " . THimages_table . " values " . implode(",", $values));
		if ($isthread)
		{
			$this->myquery("update " . THthreads_table . " set imgidx=" . $id . " where id=" . $num);
		}
		else
		{
			$this->myquery("update " . THreplies_table . " set imgidx=" . $id . " where id=" . $num);
		}

		return ($id);
	}

	function purge($boardid)
	{
		$board = $this->getbinfo($boardid);
		
		$threadcount = $this->myresult("SELECT COUNT(*) FROM " . THthreads_table . " WHERE board=" . $board['id'] . " AND pin=0");
		
		// Do we need to do anything?
		if ($threadcount > $board['tmax'])
		{
			// An array of imgidxes
			$badimgs = array ();
			// An array of thread IDs
			$threadids = array ();
			
			$targetthreads = $this->mymultiarray("SELECT * FROM " . THthreads_table . " WHERE board=" . $board['id'] . 
					" AND pin=0 ORDER BY bump ASC LIMIT ". ($threadcount - $board['tmax']));
			
			foreach ( $targetthreads as $thread )
			{
				if( $thread['imgidx'] != 0)
				{
					// add to the images array
					$badimgs[] = $thread['imgidx']; 
				}
				
				// Add to the ids array
				$threadids[] = $thread['id']; 
				
				// Clear the reports
				$this->myquery("UPDATE ".THreports_table." SET status=3 WHERE postid=".$thread['globalid']." AND board=".$board['id']);
				
				// Clear the cache
				smclearcache($board['id'], -1, $thread['globalid']); 
			}
			
			// String representation of all of the thread IDs to be deleted
			$badthstr = implode(",", $threadids);
			
			// Retrieve the replies that are in these threads to be deleted
			$targetreplies = $this->mymultiarray("SELECT imgidx, globalid FROM " . THreplies_table . " WHERE board=" . 
					$board['id'] . " AND thread in (" . $badthstr . ")");
					
			foreach ( $targetreplies as $reply )
			{
				if( $reply['imgidx'] != 0)
				{
					// add to the images array
					$badimgs[] = $reply['imgidx'];
				}
				
				// Clear the reports
				$this->myquery("UPDATE ".THreports_table." set status=3 where postid=".$reply['globalid']." and board=".$board['id']);
			}
			
			// Delete these posts from the database
			$this->myquery("delete from " . THthreads_table . " where id in (" . $badthstr . ")");
			$this->myquery("delete from " . THreplies_table . " where thread in (" . $badthstr . ")");
			
			// Delete the image info from the database
			if (count($badimgs) > 0)
			{
				$badimgsstr = implode(",", $badimgs);
				
				// Remove extra_info sections first
				$extra_info_entries = $this->myarray("select extra_info from " . THimages_table . 
						" where id in (" . $badimgsstr . ")");
	
				if (count($extra_info_entries) > 0)
				{
					$this->myquery("delete from " . THextrainfo_table . " where id in (" . implode(",", $extra_info_entries) . ")");
				}
	
				$this->myquery("delete from " . THimages_table . " where id in (" . $badimgsstr . ")");
			}
			
			return ($badimgs);
		}
		else
		{
			return (array ());
		}
	}

	function dupecheck($hashes)
	{
		if (count($hashes) > 0)
		{
			$boardid = intval($boardid);
			
			// Check threads
			$count = $this->myresult("SELECT COUNT(*) FROM " . THimages_table . 
				" INNER JOIN ".THthreads_table." ON ".THthreads_table.".board = ".$boardid.
				" WHERE " . THimages_table .".hash IN ('" . implode("','", $hashes) . "') AND "
				.THthreads_table.".imgidx = " . THimages_table .".id" );
				
			// Then replies			
			$count += $this->myresult("SELECT COUNT(*) FROM " . THimages_table . 
				" INNER JOIN ".THreplies_table." ON ".THreplies_table.".board = ".$boardid.
				" WHERE " . THimages_table .".hash IN ('" . implode("','", $hashes) . "') AND "
				.THreplies_table.".imgidx = " . THimages_table .".id" );
				
			return $count;
		}
		else
		{
			return (0);
		}
	}
	
	function getglobalid($board)
	{
		$sql = "select globalid from " . THboards_table . " where id='" . $board."'";
		$globalid = $this->myresult($sql);
//		echo $sql;
		if( $globalid == null )
		{
			return null;
		}
		
		$globalid++;
		$newsql = "update " . THboards_table . " set globalid=" . $globalid . " where id='" . $board . "'";
		$this->myquery($newsql);
		return ($globalid);
	}
	
	function movethread($id, $newboard)
	{
		$newboard = intval($newboard); // Save a bit of time
		$id = intval($id);
		
		$newthreadspot = $this->getglobalid($newboard);
		$threadinfo = $this->gettinfo($id);
		
		if( $newthreadspot == null )
			return null;
		
		$this->myquery("update " . THthreads_table . " set globalid=" . $newthreadspot . ", " .
				"board=" . $newboard . " where id=" . $id);
				
		// Update reports table
		$this->myquery("update " . THreports_table . " set postid=" . $newthreadspot . 
				",board=" . $newboard . " where postid=" . $threadinfo['globalid'] . 
				" and board =" . $threadinfo['board']);		
	
		// Get an array of reply IDs to move
		$posts = array();
		$posts = $this->mymultiarray("select id, globalid from " . THreplies_table . " where thread=" . $id . 
									" order by globalid asc");
	
		foreach( $posts as $reply )
		{
			$newid = $this->getglobalid($newboard);
			$this->myquery("update " . THreplies_table . " set globalid=" . $newid . 
				",board=" . $newboard . " where id=" . $reply['id']);
				
			// Update reports table
			$this->myquery("update " . THreports_table . " set postid=" . $newid . 
				",board=" . $newboard . " where postid=" . $reply['globalid'] . 
				" and board =" . $threadinfo['board']);
		}
		
		return $newthreadspot;
	}
	
	function updatepost($id, $board, $name, $trip, $link, $subject, $body, $visible, $pin, $lock, $permasage)
	{
		$loc = $this->findpost($id, $board);
			
		if( $loc == 2 ) // reply
		{
			$querystring = "UPDATE " . THreplies_table . " SET ".
					"name='".$this->escape_string($name)."'," .
					"trip='".$this->escape_string($trip)."'," .
					"title='".$this->escape_string($subject)."'," .
					"link='".$this->escape_string($link)."'," .
					"body='".$this->escape_string($body)."'," .
					"visible=".intval($visible)."," .
					"unvisibletime=".(time() + (THtimeoffset * 60)).
					" WHERE globalid=".intval($id).
					" AND board=".intval($board);
		}
		elseif( $loc == 1 ) // thread
		{
			$querystring = "UPDATE " . THthreads_table . " SET ".
					"name='".$this->escape_string($name)."'," .
					"trip='".$this->escape_string($trip)."'," .
					"title='".$this->escape_string($subject)."'," .
					"link='".$this->escape_string($link)."'," .
					"body='".$this->escape_string($body)."'," .
					"visible=".intval($visible)."," .
					"pin=".intval($pin)."," .
					"lawk=".intval($lock)."," .
					"permasage=".intval($permasage)."," .
					"unvisibletime=".(time() + (THtimeoffset * 60)).
					" WHERE globalid=".intval($id).
					" AND board=".intval($board);			
		}
		else
		{
			// Neither?  This is messed up.
			return;
		}
		
		$this->myquery($querystring);
	}
	
	function deleteimage($imgidx, $hash, $extra_info = -1)
	{
		if ($extra_info > 0) // delete any associated extra_info
		{
			$this->myquery("delete from " . THextrainfo_table . " where id=" . intval($extra_info)); 
		}

		$this->myquery("delete from " . THimages_table . " where id=" . intval($imgidx) . 
			" and hash='" . $this->escape_string($hash) . "'");
	}
	
	function postedwithintime($ip, $timeframe = 30)
	{
		$ip = intval($ip);
		$searchtime = time() + (THtimeoffset * 60) - $timeframe; // Minimum time
		
		// Check replies first
		if( $this->myresult("SELECT COUNT(*) FROM ".THreplies_table." WHERE ip=".$ip.
				" AND time>=".$searchtime) > 0)
		{
			return true;
		}
		
		// Check threads next
		if( $this->myresult("SELECT COUNT(*) FROM ".THthreads_table." WHERE ip=".$ip.
				" AND time>=".$searchtime) > 0)
		{
			return true;
		}
		
		// Since we fell through, we must be OK
		return false;
	}
	
} //ThornPostDBI
?>
