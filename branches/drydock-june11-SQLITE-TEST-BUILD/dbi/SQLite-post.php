<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/SQLite-post.php
	Description:    Code for the ThornPostDBI class, based upon the SQLite version of ThornDBI
	
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
		/*
			Basically, just gets the thread head. Getting images are not necessary.
			Parameters:
				int $t
			The thread to fetch.
				Returns: array $thread
		*/
		return ($this->myassoc("select * from " . THthreads_table . " where id=" . intval($t)));
	}


	function putthread($name, $tpass, $board, $title, $body, $link, $ip, $mod, $pin, $lock, $permasage, $tyme = false)
	{
		/*
			Posts a new thread, and updates the respective board's last post time. Note that the storing of image information is done in a separate function, putimgs().
			Parameters:
				string $name
			The poster's name.
				string $tpass
			The poster's encoded tripcode.
				int $board
			The board this thread will go into.
				string $title
			The title of this new thread.
				string $body
			The body text of this new thread.
				string $link
			The link field of this new thread
				int $ip
			The ip2long()'d IP of the poster.
				bool $mod
			Is the poster a mod or admin? (For future feature; currently ignored by this DBI as well as the included templates.)
				bool $pin
			Should the thread be pinned? (Since MySQL doesn't support booleans, this is stored as a 0 or 1 in an integer column.)
				bool $lock
			Should the thread be locked? (Ditto)
				int $tyme
			Time of post (now if set to false)
				Returns: int $thread-id
		*/
		if ($tyme === false)
		{
			$tyme = time() + (THtimeoffset * 60);
		}
		$q = "INSERT INTO " . THthreads_table . " ( board, title, body";
		$v = " VALUES ( " . intval($board) . " ,'" . $this->escape_string($title) . "','";
		$v .= $this->escape_string($body);
		$q .= ", ip, pin, permasage, lawk, time, bump";
		$v .= "'," . $ip . " , " . $pin . " , " . $permasage . " , " . $lock . " , " . $tyme . " , " . $tyme;
		$globalid = $this->getglobalid($board);
		$q .= ", globalid";
		$v .= "," . $globalid;
		if ($name != null)
		{
			$q .= ", name";
			$v .= ",'" . $this->escape_string($name) . "'";
		}
		if ($tpass != null)
		{
			$q .= ", trip";
			$v .= "'" . $tpass . "'";
			//Not cleaning trip since it should be encoded.
		}
		if ($link != null)
		{
			if (!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/", $link))
			{
				$link = "mailto:" . $link;
			}
			else
			{
				$link = $this->escape_string($link);
			}
			$q .= ", link";
			$v .= ", '" . $this->escape_string($link) . "'";
		}
		//echo($q.", time=".$tyme);
		//echo $q;
		$visible = 1;
		$q .= ",time,visible) ";
		$v .= "," . $tyme . "," . $visible . ")";
		$built = $q . $v;
		$this->myquery($built) or THdie("DBpost");
		if ($board == THnewsboard)
		{
			rebuild_rss();
		}
		smclearcache($board, -1, -1); // clear the cache for this board
		$tnum = sqlite_last_insert_rowid(THdblitefn); //help
		$this->myquery("update " . THboards_table . " set lasttime=" . $tyme . " where folder='" . $board ."'") or THdie("DBpost");
		return ($tnum);
	}

	function putpost($name, $tpass, $link, $board, $thread, $body, $ip, $mod, $tyme = false)
	{
		/*
			Posts a reply to a thread, updates the "bump" column of the relevant thread, and updates the last post time of the relevant board. Note that, as with putthread, images are stored using the separate putimgs() function.
			Parameters:
				string $name
			The poster's name.
				string $tpass
			The poster's encoded tripcode.
				string $link
			The poster's link (could be mailto, could be something similar, who knows!)
				int $board
			The board to which this post's thread belongs.
				int $thread
			The thread for which this post is a reply.
				string $body
			This post's body text.
				int $ip
			The ip2long()'d IP address of the poster.
				bool $mod
			Is the poster a mod or admin? (For future feature; currently ignored by this DBI as well as the included templates.)
				Returns: int $post-id
		*/
		$q = "INSERT INTO " . THreplies_table . " (thread,board,body";
		$v = " ) VALUES (" . $thread . ",'" . intval($board) . "','";
		$v .= $this->escape_string($body);

		//FIX THE REST OF THIS QUERY
		$glob = $this->getglobalid($board);
		$q .= ",ip,bump,globalid";
		$v .= "'," . $ip . "," . (int) $bump . "," . $glob;
		if ($name != null)
		{
			$q .= ", name";
			$v .= ",'" . $this->escape_string($name) . "'";
		}
		if ($tpass != null)
		{
			$q .= ", trip";
			$v .= ",'" . $tpass . "'";
		}
		$bump = preg_match("/^(mailto:)?sage$/", $link);
		if ($link != null)
		{
			if (!preg_match("/^(http:|https:|ftp:|mailto:|aim:)/", $link))
			{
				$link = "mailto:" . $link;
			}
			$q .= ", link";
			$v .= ",'" . $this->escape_string($link) . "'";
		}
		if ($tyme === false)
		{
			$tyme = time() + (THtimeoffset * 60);
		}
		//echo($q);
		$visible = 1;
		$v .= "," . $tyme . "," . $visible . ");";
		$q .= ", time, visible";
		//die($q.$v);
		$this->myquery($q . $v) or THdie("DBpost");
		//if ($board == THnewsboard) { buildnews(); }	
		$pnum = sqlite_last_insert_rowid(THdblitefn); //help
		if (!$bump)
		{
			$this->myquery("update " . THthreads_table . " set bump=" . $tyme . " where id=" . $thread . " and permasage = 0");
		}
		$this->myquery("update " . THboards_table . " set lasttime=" . $tyme . " where folder='" . $board."'") or THdie("DBpost");
		smclearcache($board, -1, -1); // clear cache for the board
		smclearcache($board, -1, $thread); // and for the thread
		return ($pnum);
	}

	function putimgs($num, $isthread, $files)
	{
		/*
		Puts image information into the database, then updates the relevant thread or post with the image data's image index.
		Parameters:
			int $num
		The ID number of the post or thread to which we are putting images.
			bool $isthread
		Is $num referring to a post or a thread?
			array $files
		An array containing information about the images we're uploading. The parameters are:
				string $file['hash']
			The sha1 hash of the image file.
				string $file['name']
			The image's filename.
				int $file['width']
			The width in pixels of the image.
				int $file['height']
			Take a wild guess...
				string $file['tname']
			The name of the image's thumbnail.
				int $file['twidth']
			The thumbnail's width in pixels.
				int $file['theight']
			Whatever this is, it is absolutely NOT the height of the thumbnail in pixels. That's just what they WANT you to think...
				int $file['fsize']
			The image's filesize in K, rounded up.
				bool $file['anim']
			Is the image animated?
			Returns: int $image-index
		*/

		$id = $this->myresult("select max(id) from " . THimages_table) + 1;
		foreach ($files as $file)
		{
			$values[] = "(" . $id . ",'" . $file['hash'] . "','" . $this->escape_string($file['name']) . "'," . $file['width'] . "," . $file['height'] . ",'" . $this->escape_string($file['tname']) . "'," . $file['twidth'] . "," . $file['theight'] . "," . $file['fsize'] . "," . (int) $file['anim'] . "," . (int) $file['extra_info'] . ")";
			/*
				fputs($fp,"id:\t");
				fputs($fp,$id);
				fputs($fp,"\n"); 
				fputs($fp,"file[hash]:\t");
				fputs($fp,$file['hash']);
				fputs($fp,"\n"); 
				fputs($fp,"file[name]:\t");
				fputs($fp,$file['name']);
				fputs($fp,"\n"); 
				fputs($fp,"file[width]:\t");
				fputs($fp,$file['width']);
				fputs($fp,"\n"); 
				fputs($fp,"file[height]:\t");
				fputs($fp,$file['height']);
				fputs($fp,"\n"); 
				fputs($fp,"file[tname]:\t");
				fputs($fp,$file['tname']);
				fputs($fp,"\n"); 
				fputs($fp,"file[twidth]:\t");
				fputs($fp,$file['twidth']);
				fputs($fp,"\n"); 
				fputs($fp,"file[theight]:\t");
				fputs($fp,$file['theight']);
				fputs($fp,"\n"); 
				fputs($fp,"file[fsize]:\t");
				fputs($fp,$file['fsize']);
				fputs($fp,"\n"); 
				fputs($fp,"file[anim]:\t");
				fputs($fp,$file['anim']);
				fputs($fp,"\n"); 
			*/
		}
		//var_dump($values); 
foreach($values as $line) { $this->myquery("insert into " . THimages_table . " values $line;"); }

		//$this->myquery("insert into " . THimages_table . " values " . implode(",", $values));
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
		/*
		Purges a board after a new thread is posted. It would be nice if we could include this with the putthread() function, 
		but both these functions need to return very important and very separate things...
		Parameters:
			int $boardid
		The ID of the board we're purging.
			Returns: array $images-from-deleted-threads (to be deleted from the disk by Thorn)
		*/
		$board = $this->getbinfo($boardid);
		if ($this->myresult("select count(*) from " . THthreads_table . " where board=" . $board['id'] . " and pin=0") > $board['tmax'])
		{
			$last = $this->myassoc("select bump from " . THthreads_table . " where board=" . $board['id'] . " and pin=0 order by bump desc limit " . ((int) $board['tmax'] - 1) . ",1"); //-1 'cuz it's zero-based er' somethin'
			//var_dump($last);
			$dels = $this->myquery("select * from " . THthreads_table . " where board=" . $board['id'] . " and bump<" . $last['bump'] . " and pin=0");
			$badimgs = array ();
			$badths = array ();
			while ($del = sqlite_fetch_array($dels)) //help
			{
				//var_dump($del);
				if ($del['imgidx'] != 0)
				{
					$badimgs[] = $del['imgidx'];
				}
				$badths[] = $del['id'];
				smclearcache($board['id'], -1, $del['id']); // clear the associated cache for this thread
			}
			$this->myquery("delete from " . THthreads_table . " where bump<" . $last['bump'] . " and pin=0");
			$badthstr = implode(",", $badths);
			$dels = $this->myquery("select imgidx from " . THreplies_table . " where board=" . $board['id'] . " and thread in (" . $badthstr . ") and imgidx!=0");
			while ($del = mysqlite_current($dels)) //help
			{
				$badimgs[] = $del;
			}
			$this->myquery("delete from " . THreplies_table . " where thread in (" . $badthstr . ")");
			return ($badimgs);
		}
		else
		{
			return (array ());
		}
	}

	function dupecheck($hashes)
	{
		/*
		A simple function to check to see if any of the sha1 hashes in $hashes are already present. 
		Parameters:
			array $hashes
		A one-dimensional string array of hashes.
			Returns: int $num-found-hashes (For other DBIs, returning just true or false should suffice -- the count really isn't important.)
		*/
		if (count($hashes) > 0)
		{
			return ($this->myresult("select count(*) from " . THimages_table . " where hash in ('" . implode("','", $hashes) . "')"));
		}
		else
		{
			return (0);
		}
	}
	
	function getglobalid($board)
	{
		/*
			This function gets a new global id for the specified board.  It will increment the current id by one.
			Parameters:
				string $board
			The folder name of the board
			
			Returns: int representing this new ID
		*/
		$sql = "select globalid from " . THboards_table . " where folder='" . $this->escape_string($board) ."'";
		$globalid = $this->myresult($sql);
		$globalid++;
		$newsql = "update " . THboards_table . " set globalid=" . $globalid . " where folder='" . $this->escape_string($board) ."'";
		$this->myquery($newsql);
		return ($globalid);
	}
	
	function getpostlocation($threadid, $postid = -1)
	{
		/*
			This function gets global IDs for a particular thread and possibly a particular reply.
			If the post ID is not provided it gets treated as a thread lookup.  
			
			Parameters:
				int $threadid
			The ID of the thread
				int $postid
			The ID of the post, defaults to -1.

				
			Returns: 
			If postid is defined, returns an array with the elements 'post_loc' and 'thread_loc'.
			If postid is not defined, returns an array with the element 'thread_loc'.
		*/
		
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
	
} //ThornPostDBI
?>