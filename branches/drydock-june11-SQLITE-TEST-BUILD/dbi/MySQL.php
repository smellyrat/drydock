<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL.php
	Description:    Handles interface between database and board functions using a MySQL database.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

require_once ("config.php");
require_once ("common.php");

function escape_string($string)
{
	return (mysql_real_escape_string($string));
}

class ThornDBI
{
	function ThornDBI($server = THdbserver, $user = THdbuser, $pass = THdbpass, $base = THdbbase)
	{
		if (isset ($this->cxn) == false)
		{
			$this->cxn = mysql_connect($server, $user, $pass) or THdie("DBcxn");
			mysql_select_db($base, $this->cxn) or THdie("DBsel");
		}
	}

	/*  provided by Mell03d0ut from anonib */
	function clean($call)
	{
		$call = htmlspecialchars($call);
		if (get_magic_quotes_gpc() == 0)
		{
			$call = mysql_real_escape_string($call);
		}
		$call = trim($call);
		return ($call);
	}

	function myassoc($call)
	{
		//echo($call."<br />");
		$dog = @ mysql_fetch_assoc(mysql_query($call)); // or return null;
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
	}

	function myarray($call)
	{
		//echo($call."<br />");
		$manta = @ mysql_fetch_array(mysql_query($call)); // or return null;
		if ($manta === false)
		{
			return (null);
		}
		return ($manta);
	}

	function myresult($call)
	{
		//echo($call."<br />");
		$dog = mysql_query($call); // or die(mysql_error()."<br />".$call);
		if ($dog === false || mysql_num_rows($dog) == 0)
		{
			return (null);
		}
		return (mysql_result($dog, 0));
	}

	function myquery($call)
	{
		//echo($call."<br />");
		$dog = mysql_query($call); // or die(mysql_error()."<br />".$call);
		if ($dog === false)
		{
			return (null);
		}
		return ($dog);
	}

	function mymultiarray($call)
	{
		/*
		Encapsulate executing a query and iteratively calling myarray on the result.
		
		Parameters:
			string call
		The SQL query to execute
		
		Returns:
			An array of associative arrays (can be size 0)
		*/

		$multi = array ();

		$queryresult = $this->myquery($call);
		if ($queryresult != null)
		{
			while ($entry = mysql_fetch_array($queryresult))
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
			return ($this->myresult("select count(*) from " . THthreads_table . " where board=" . $this->binfo['id'] . " && time>=" . $start . " && time<=" . $end));
		}
		else
		{
			return ($this->myresult("select count(*) from " . THthreads_table . " where time>=" . $start . " && time<=" . $end));
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
				$yay = $this->myquery("select time from " . THthreads_table . " where board=" . $this->binfo['id'] . " && time>=" . $since);
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
		echo "Row count: " . mysql_num_rows($yay);
		while ($row = mysql_fetch_row($yay))
		{
			//var_dump($row);
			$wows[] = (int) $row[0];
		}
		return ($wows);
	}

	function getimgs($imgidx)
	{
		/*
		Get the images associated with a certain post by its image index.
		Parameters:
			int $imgidx
		The image index to search for.
			Returns: array $images (blank array if none)
		*/
		if ($imgidx == 0 || $imgidx == null)
		{
			return (array ());
		}
		$imgs = array ();
		$turtle = $this->myquery("select * from " . THimages_table . " where id=" . $this->clean($imgidx));
		while ($img = mysql_fetch_assoc($turtle))
		{
			$imgs[] = $img;
		}
		return ($imgs);
	}

	function getblotter($board)
	{
		/*
		Get the latest blotter entries perhaps associated with a certain board
		Parameters:
			int $board
		The board for which the entries are being retrieved
			Returns: array $entries (blank array if none)
		*/
		$entries = array ();
		$count = 0;
		$blotter = $this->myquery("select * from " . THblotter_table . " ORDER BY time ASC");
		while ($entry = mysql_fetch_assoc($blotter))
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
		/*
			Returns an index of the boards.
			Parameters:
				bool $p['full']=false
			If true, all board information will be fetched. If false, only the board ID, name and description ('about') are returned.
				string $p['sortmethod']="id"
			If "id", boards are sorted by ID number. If "name", boards are sorted by name. If "last", boards are sorted by last post time.
				bool $p['desc']=false
			If true, the boards are returned in descending order.
				Returns: array $boards (blank if none)
		*/
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
			$q = "select id, name, about from " . THboards_table;
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
		while ($board = mysql_fetch_assoc($iguana))
		{
			$boards[] = $board;
		}
		return ($boards);
	}

	function checkban($ip = null)
	{
		/*
			Check to see if an IP is banned. Will check both the actual IP and the IP's subnet.
			Parameters:
				int $ip=ip2long($_SERVER['REMOTE_ADDR']);
			The ip2long'd IP address. If blank, it checks the user's IP address. ("function checkban($ip=ip2long($_SERVER['REMOTE_ADDR']))" makes PHP cwy.)
			
			Returns:
				bool $banned
		*/
		if ($ip == null)
		{
			$ip = ip2long($_SERVER['REMOTE_ADDR']);
		}
		//echo();
		$sub = ipsub($ip);
		//Check already banned...
		if ($this->myresult("select count(*) from " . THbans_table . " where (ip=" . $sub . " && subnet=1) || ip=" . $ip) > 0)
		{
			return (true);
		}
		else
		{
			return (false);
		}
	}

	function getboard($id = 0, $folder = "")
	{
		/*
			Get board information, will optionally filter by id and/or folder
			Parameters:
				int id 
			The board ID to optionally filter by
				string folder
			The board filter to optionally filter by
			
			Returns:
				array containing board info
		*/

		$querystring = "select * from " . THboards_table . " where ";
		$id = intval($id); // Make it explicitly an integer

		if ($id == 0 and $folder == "") // No filtering at all
		{
			$querystring = $querystring . "1";
		}
		elseif ($id != 0 and $folder != "") // Filtering by both folder AND ID
		{
			$querystring = $querystring . "id=" . $id . " AND folder='" . $this->clean($folder) . "'";
		}
		elseif ($id != 0) // Filtering by only ID
		{
			$querystring = $querystring . "id=" . $id;
		}
		else // Filtering by only folder
		{
			$querystring = $querystring . "folder='" . $this->clean($folder) . "'";
		}
		
		return $this->mymultiarray($querystring);
	}

} //ThornDBI

//===========================================================================================

// This concludes the main body of ThornDBI- the following includes contain derived classes 
// which encapsulate the other (more specialized) functions required by various tasks

require_once ("MySQL-board.php"); //ThornBoardDBI
require_once ("MySQL-mod.php"); //ThornModDBI
require_once ("MySQL-post.php"); //ThornPostDBI
require_once ("MySQL-thread.php"); //ThornThreadDBI
require_once ("MySQL-profile.php"); // ThornProfileDBI
?>