<?php


/*
	drydock imageboard script (http://code.573chan.org/)
	File:           dbi/MySQL-thread.php
	Description:    Code for the ThornThreadDBI class, based upon the MySQL version of ThornDBI
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
var_dump($this->head);
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
		while ($reply = mysql_fetch_assoc($tadpole))
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
} //class ThornThreadDBI
?>
