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
} //class ThornThreadDBI
?>
