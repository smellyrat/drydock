<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-board.php
	Description:    Code for the ThornBoardDBI class, based upon the MySQL version of ThornDBI
	Its abstract interface is in dbi/ABSTRACT-board.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

require_once ("ABSTRACT-board.php"); // abstract interface

class ThornBoardDBI extends ThornDBI
{
	function ThornBoardDBI($bored, $payj, $on = array ())
	{
		$this->ThornDBI();
		$this->page = $payj;
		$this->binfo = $this->myassoc("select * from " . THboards_table . " where id=" . $bored);
		$this->on = $on;
		$this->blotterentries = $this->getblotter($bored);
		//$this->st=$st;
		//$this->et=$et;
		//var_dump($this->on);
		//die();
	}

	function getallthreads($p, & $sm)
	{
		if (isset ($p['full']) == false)
		{
			$p['full'] = false;
		}
		if (isset ($p['sortmethod']) == false)
		{
			$p['sortmethod'] = "bump";
		}
		if (isset ($p['desc']) == false)
		{
			$p['desc'] = true;
		}
		if (isset ($p['date']) == false)
		{
			$p['date'] = false;
		}
		if (isset ($p['start']) == false)
		{
			$p['start'] = false;
		}
		if (isset ($p['end']) == false)
		{
			$p['end'] = false;
		}

	/*
		//I don't really know what's going on here with this but it's causing images to not display in board view for some reason.
		if ($p['full'] == true)
		{
			$q = "select * from " . THthreads_table . " where board=" . $this->binfo['id'];
		}
		else
		{
			$q = "select id, title, name, trip, link, time, pin, lawk, bump, globalid from " . THthreads_table . " where board=" . $this->binfo['id'];
		}
	*/
		$q = "select * from " . THthreads_table . " where board=" . $this->binfo['id'];
		if ($p['start'] != false)
		{
			$q .= " and time>=" . $p['start'];
		}
		if ($p['end'] != false)
		{
			$q .= " and time<" . $p['end'];
		}

		if (isset ($this->on['year']))
		{
			if (isset ($this->on['month']))
			{
				if (isset ($this->on['day']))
				{
					$q .= " and time>=" . mktime(0, 0, 0, $this->on['month'], $this->on['day'], $this->on['year']) . " and time<" . mktime(0, 0, 0, $this->on['month'], $this->on['day'] + 1, $this->on['year']);
				}
				else
				{
					$q .= " and time>=" . mktime(0, 0, 0, $this->on['month'], 1, $this->on['year']) . " and time<" . mktime(0, 0, 0, $this->on['month'] + 1, 1, $this->on['year']);
				}
			}
			else
			{
				$q .= " and time>=" . mktime(0, 0, 0, 1, 1, $this->on['year']) . " and time<" . mktime(0, 0, 0, 1, 1, $this->on['year'] + 1);
			}
		}

		if ($p['sortmethod'] == "bump")
		{
			$q .= " order by pin desc, bump";
		}
		elseif ($p['sortmethod'] == "time")
		{
			$q .= " order by pin desc, time";
		}
		elseif ($p['sortmethod'] == "title")
		{
			$q .= " order by pin desc, title";
		}
		elseif ($p['sortmethod'] == "id")
		{
			$q .= " order by pin desc, id";
		}

		if ($p['desc'])
		{
			$q .= " desc";
		}

		$rezs = $this->myquery($q);

		$threads = array ();
		while (@ $th = mysql_fetch_assoc($rezs))
		{
			unset ($th['ip']);
			$th['images'] = $this->getimgs($th['imgidx']);
			$th['rcount'] = $this->myresult("select count(*) from " . THreplies_table . " where thread=" . $th['id']);
			if ($th['rcount'] == 0 || $this->binfo['perth'] == 0)
			{
				$th['reps'] = null;
				$th['scount'] = 0;
			}
			if ($p['full'] == true)
			{
				$th['images'] = $this->getimgs($th['imgidx']);
			}
			if ($p['date'] == true)
			{
				$th['date'] = getdate($th['time']);
			}
			//Check replies for the last time
			$th['lastrep'] = $this->myresult("SELECT time FROM ".THreplies_table." WHERE thread=".$th['id']." ORDER BY time DESC LIMIT 1");
			//$th['images'] = $this->getimgs($th['imgidx']);
			$threads[] = $th;
		}
		//echo"<b>";var_dump($threads);echo"</b>";
		return ($threads);
	}

	function getsthreads($p, & $sm)
	{
		if (isset ($p['sortmethod']) == false)
		{
			$p['sortmethod'] = "bump";
		}
		if (isset ($p['tdesc']) == false)
		{
			$p['tdesc'] = true;
		}
		if (isset ($p['rdesc']) == false)
		{
			$p['rdesc'] = false;
		}

		if (isset ($this->on['year']))
		{
			if (isset ($this->on['month']))
			{
				if (isset ($this->on['day']))
				{
					$orderby = " and time>=" . mktime(0, 0, 0, $this->on['month'], $this->on['day'], $this->on['year']) . " and time<" . mktime(0, 0, 0, $this->on['month'], $this->on['day'] + 1, $this->on['year']);
				}
				else
				{
					$orderby = " and time>=" . mktime(0, 0, 0, $this->on['month'], 1, $this->on['year']) . " and time<" . mktime(0, 0, 0, $this->on['month'] + 1, 1, $this->on['year']);
				}
			}
			else
			{
				$orderby = " and time>=" . mktime(0, 0, 0, 1, 1, $this->on['year']) . " and time<" . mktime(0, 0, 0, 1, 1, $this->on['year'] + 1);
			}
		}
		else
		{
			$orderby = "";
		}

		if ($p['sortmethod'] == "bump")
		{
			$orderby .= " order by pin desc, bump";
		}
		elseif ($p['sortmethod'] == "time")
		{
			$orderby .= " order by pin desc, time";
		}
		elseif ($p['sortmethod'] == "title")
		{
			$orderby .= " order by pin desc, title";
		}
		elseif ($p['sortmethod'] == "id")
		{
			$orderby .= " order by pin desc, id";
		}

		if ($p['tdesc'])
		{
			$orderby .= " desc";
		}

		$orderby .= " limit " . ($this->page * $this->binfo['perpg']) . "," . $this->binfo['perpg'];

		$sthreads = array ();
		$result = $this->myquery("select * from " . THthreads_table . " where board=" . $this->binfo['id'] . $orderby);


		while ($th = mysql_fetch_assoc($result)) 
		{
			unset ($th['ip']);
			$th['images'] = $this->getimgs($th['imgidx']);
			$th['rcount'] = $this->myresult("select count(*) from " . THreplies_table . " where thread=" . $th['id']);
			if ($th['rcount'] == 0 || $this->binfo['perth'] == 0)
			{
				$th['reps'] = null;
				$th['scount'] = 0;
			}
			else
			{
				$start = $th['rcount'] - $this->binfo['perth'];
				if ($start < 0)
				{
					$start = 0;
				}

				if ($p['rdesc'])
				{
					$orderby = " order by time desc limit " . $start . "," . $this->binfo['perth'];
				}
				else
				{
					$orderby = " order by time limit " . $start . "," . $this->binfo['perth'];
				}

				$thread_replies = $this->mymultiarray("select * from " . THreplies_table . " where thread=" . $th['id'] . $orderby);
				//Check replies for the last time
				$th['lastrep'] = $this->myresult("SELECT time FROM ".THreplies_table." WHERE thread=".$th['id']." ORDER BY time DESC LIMIT 1");
				foreach ($thread_replies as $reply)
				{
					unset ($reply['ip']);
					$reply['images'] = $this->getimgs( $reply['imgidx']);
					$th['reps'][] = $reply;
				}
				$th['scount'] = count($th['reps']);			
			}
			
			$sthreads[] = $th;
			//var_dump($th);
		}
		return ($sthreads);
	} //getsthreads
	
} //class ThornBoardDBI
?>
