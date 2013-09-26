<?php	/*		drydock imageboard script (http://code.573chan.org/)		File:           		drydock.php		Description:	This is used to access the site.		Unless otherwise stated, this code is copyright 2008		by the drydock developers and is released under the		Artistic License 2.0:		http://www.opensource.org/licenses/artistic-license-2.0.php	*/		//Configure script still here?  Crap, this isn't good, let's deny access, just in case someone didn't read the directions	if (file_exists("install.php") && DDDEBUG!=1)	{		if(file_exists("config.php"))		{			die("This script cannot be run with the configuration utility still sitting here!  Please delete the configuration scripts (install.php and upgrade_install.php)!");		} 		else 		{			header("Location: install.php");		}	}		//Like above, but with the upgrade script	if (file_exists("upgrade_install.php") && DDDEBUG!=1)	{		die("This script cannot be run with the upgrade utility still sitting here!  Please delete the upgrade script!");	}		require_once("common.php");	
	$db=new ThornDBI();	
	//Drop them out right now if they are banned! - tyam	if ($db->checkban())	{		THdie("PObanned");	} 	else 	{ //whole file		if (isset($_GET['b'])==true) //Check a board by its name		{			$boardid = $db->getboardnumber($_GET['b']);						//Does the board even exist?			if($boardid == null || $boardid < 0)			{				THdie("Board not found.");			}			$template = $db->getboardtemplate($boardid); //what is our template						if ($boardid==THmodboard) //check for mod access			{				if(!$_SESSION['moderator'] && !$_SESSION['admin'])				{					THdie("You are not authorized to access this board.");				}			}						if ($db->isboardreg($boardid) == true) //let's check for registration required boards here - tyam			{				if(!$_SESSION['username'])				{					THdie("You must register to view this board.");				}			}						if (isset($_GET['i'])==true) //Looking for a bento box.			{				$threadtpl = "thread.tpl";  //oh boy let's split it up more								// Caching ID format: t<board>-<thread global id>-<template>				$cid="t".$boardid."-".(int)$_GET['i']."-".$template;				$modvar = is_in_csl($boardid, $_SESSION['mod_array']); // individual board moderator				$sm=sminit($threadtpl,$cid,$template,false,$modvar);				$sm->assign('modvar',$modvar);								$sm->assign('boardmode',$boardmode);				$sm->assign('template', $template);				//here we go with a bunch of retarded variables that later we can turn into an array				$sm->assign('username',$_SESSION['username']);

				//Are we using reCAPTCHA?
				if(THvc==1)
				{
					require_once('recaptchalib.php');
					$sm->assign('reCAPTCHAPublic', reCAPTCHAPublic);
				}				$sm->assign('comingfrom',"thread");								//OOPS!  This will let us pull the thread we WANT not the thread we ASKED FOR.  -tyam				$db=new ThornThreadDBI(intval($_GET['i']), $boardid);								// The constructor initializes the $head member to be the assoc-array from the corresponding				// entry in the threads table, so if it's null that means that it wasn't found in the DB.
				if($db->head == null)				{					THdie("Sorry, this thread does not exist.");				}
				$sm->register_object("it",$db,array("getreplies","getsthreads","getindex","binfo","head","blotterentries"));								//$sm->display($threadtpl,$cid);
				$sm->display($threadtpl,$cid);								// display extra mod stuff if they have access
				if(($_SESSION['admin'] ==1) || ($_SESSION['moderator'] ==1) || ($modvar)) 				{ 					$sm->assign('modvar',1);					$sm->display("modscript.tpl", null); 				}				
				$sm->display("bottombar.tpl", null);				die();			}
			if (isset($_GET['tlist'])==true) //For anonBBS thread list, but could be used for any template
			{				// Caching ID format: t<board>-tlist-<template>				$cid="t".$boardid."-tlist";				$sm=sminit("threadlist.tpl",$cid,$template,false);				$db=new ThornBoardDBI($boardid,$page,$on);
				$sm->register_object("it",$db,array("getallthreads","binfo"));
				$sm->display("threadlist.tpl",$cid);
				$sm->display("bottombar.tpl", null);				die();			}			elseif (isset($_GET['g'])==true)			{				//This page of the board...				$page=abs((int)$_GET['g']);			} 			else 			{				$page=0;			}			$tpl="board.tpl";			// Caching ID format: b<board>-<page>-<template>			$cid="b".$boardid."-".$page."-".$template;			$modvar = is_in_csl($boardid, $_SESSION['mod_array']); // individual board moderator			$sm=sminit($tpl,$cid,$template,false,$modvar);			$sm->assign('modvar',$modvar);			//var_dump($obj);			$db=new ThornBoardDBI($boardid,$page,$on);													$sm->register_object("it",$db,array("getallthreads","getsthreads","getindex","binfo","page","allthreadscalmerge","blotterentries"));			$sm->assign('template', $template);			//here we go with a bunch of retarded variables that later we can turn into an array  (looks like i kopiped this)            $sm->assign('username',$_SESSION['username']);

			//Are we using reCAPTCHA?
			if(THvc==1)
			{
				require_once('recaptchalib.php');
				$sm->assign('reCAPTCHAPublic', reCAPTCHAPublic);
			}
			$sm->assign('comingfrom',"board");			if (isset($ogd)==true)			{				$sm->assign("on",$ogd);			}

			// Display mod-specific stuff			if(($_SESSION['admin'] == 1) || ($_SESSION['moderator'] == 1) || ($modvar)) 			{ 				$sm->assign('modvar',1);				$sm->display("modscript.tpl",null); 			}			$sm->display($tpl,$cid);
			$sm->display("bottombar.tpl", null);

			//$sm->display($tpl,$cid);			die();    		} //get=b		else 		{ 
			include("news.php");
		}//no argument given after index	}//ban check ends here?>