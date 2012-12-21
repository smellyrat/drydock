<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/SQLite-thread.php
	Description:    Code for the ThornThreadDBI class, based upon the SQLite version of ThornDBI
	Its abstract interface is in dbi/ABSTRACT-thread.php.
	
	Unless otherwise stated, this code is copyright 2008 
	by the drydock developers and is released under the
	Artistic License 2.0:
	http://www.opensource.org/licenses/artistic-license-2.0.php
*/

class ThornThreadDBI extends ThornDBI
{
	function ThornThreadDBI($th, $brd)
	{
		$this->ThornDBI();
        //this should fix it!
        $this->head = $this->myassoc("select * from " . THthreads_table . " where globalid=" . $th . " and board=" . $brd);
        if( $this->head != null )
        {
            unset ($this->head['ip']);
            $this->head['images'] = $this->getimgs($this->head['imgidx']);
            $this->binfo = $this->myassoc("select * from " . THboards_table . " where id=" . $this->head['board']);
            $this->blotterentries = $this->getblotter($binfo['id']);
        }
//		var_dump($this);
//		die();
	}
	function head()
	{
	    $this->head = $this->myassoc("select * from " . THthreads_table . " where globalid=" . $this->head['globalid'] . " and board=" . $this->head['board']);
        if( $this->head != null )
        {
            unset ($this->head['ip']);
            $this->head['images'] = $this->getimgs($this->head['imgidx']);
            $this->binfo = $this->myassoc("select * from " . THboards_table . " where id=" . $this->head['board']);
            $this->blotterentries = $this->getblotter($binfo['id']);
        }
	}
	function binfo()
	{
		$bored = $this->binfo['id'];
		$wut = $this->myassoc("select * from " . THboards_table . " where id=" . $bored);
		return $wut;
	}
	function page($payj)
	{
		return $payj;
	}
	function blotterentries()
	{
		$this->getblotter($this->binfo['id']);
	}
	function getreplies($p, & $sm)
	{
		if (isset ($p['sortmethod']) == false)
		{
			$p['sortmethod'] = "time";
		}
		if (isset ($p['desc']) == false)
		{
			$p['desc'] = false;
		}
		if (isset ($p['withhead']) == false)
		{
			$p['withhead'] = false;
		}
		if (isset ($p['full']) == false)
		{
			$p['full'] = true;
		}

		if ($p['full'])
		{
			$q = "select * from " . THreplies_table . " where thread=" . $this->head['id'] . " order by ";
		}
		else
		{
			$q = "select id, name, trip, link, time, globalid from " . THreplies_table . " where thread=" . $this->head['id'] . " order by ";
		}
		if ($p['sortmethod'] == "time")
		{
			$q .= "time";
		}
		elseif ($p['sortmethod'] = "id")
		{
			$q .= "id";
		}

		if ($p['desc'])
		{
			$q .= " desc";
		}
		$tadpole = $this->myquery($q);

		$replies = array ();
		if ($p['withhead'] && $p['desc'] == false)
		{
			$replies[] = $this->head;
		}
		while ($reply = sqlite_fetch_array($tadpole)) //help
		{
			unset ($reply['ip']);
			if ($p['full'])
			{
				$reply['images'] = $this->getimgs($reply['imgidx']);
			}
			$replies[] = $reply;
		}
		if ($p['withhead'] && $p['desc'])
		{
			$replies[] = $this->head;
		}

		return ($replies);
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

		//What the frick is this crap?
		//var_dump($this->on);
		//var_dump($p);
		//die("here");
		$orderby = "";
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
		//print_r($this->binfo);
		$orderby .= " limit " . ($this->page * $this->binfo['perpg']) . "," . $this->binfo['perpg'];
		$thatid = $this->binfo['id'];

		$frog = $this->myquery("select * from " . THthreads_table . " where board=" . $thatid . $orderby);
		$sthreads = array ();
		while ($th = sqlite_fetch_array($frog)) //help
		{
			//print_r($th); echo "<br />";
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
				$toad = $this->myquery("select * from " . THreplies_table . " where thread=" . $th['id'] . $orderby);
				//Check replies for the last time
				$th['lastrep'] = $this->myresult("SELECT time FROM ".THreplies_table." WHERE thread=".$th['id']." ORDER BY time DESC LIMIT 1");
				while ($reply = sqlite_fetch_array($toad)) //help
				{
					unset ($reply['ip']);
					$reply['images'] = $this->getimgs($reply['imgidx']);
					$th['reps'][] = $reply;
				}
				$th['scount'] = count($th['reps']);
			}
			//var_dump($th);
			$sthreads[] = $th;
		}
		return ($sthreads);
	} //getsthreads
} //class ThornThreadDBI
?>
