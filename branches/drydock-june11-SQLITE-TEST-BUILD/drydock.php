<?php
	$db=new ThornDBI();
	//Drop them out right now if they are banned! - tyam
				if($db->myresult("select count(*) from ".THthreads_table." where globalid=".intval($_GET['i'])." and board=".$boardid." and visible=1")=="0")

				$sm->display($threadtpl,$cid);
				if(($_SESSION['admin'] ==1) || ($_SESSION['moderator'] ==1) || ($modvar)) { $sm->display("modscript.tpl",$cid); }
				$sm->display("bottombar.tpl",$cid);

			$sm->display($tpl,$cid);
			if(($_SESSION['admin']) || ($_SESSION['moderator']) || ($modvar)) { $sm->display("modscript.tpl",$cid); }
			echo $sm->display("bottombar.tpl",$cid);

			//$sm->display($tpl,$cid);
			include("news.php");
		}//no argument given after index