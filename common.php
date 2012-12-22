<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			common.php
		Description:	Contains many functions for general use.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	require_once("version.php");
	session_start();
	checklogin(); // Update session variables if needed (needed meaning: HAY WE HAVE A COOKIE)
	@header('Content-type: text/html; charset=utf-8');
	if (function_exists("mb_internal_encoding"))
	{
		//This server will support multi-byte strings. Unicodey goodness!
		mb_internal_encoding("UTF-8");
		mb_language("uni");
		mb_http_output("UTF-8");
	}
	if (file_exists("config.php"))
	{
		require_once("config.php");
	} 
	//error_reporting(E_ALL);

	//Find DB code
	if (THdbtype!=null)
	{
		$findpath=THpath."dbi/".THdbtype."-dbi.php";
		if (file_exists($findpath))
		{
			require_once($findpath);
		} 
		else 
		{
			THdie("DBcode");
		}
	}
	require_once("rebuilds.php");  //frown
	
	define("THbcw_blotter", 1);
	define("THbcw_capcode", 2);
	define("THbcw_filter", 3);
	
	/**
	 * Generate an error page for whatever reason.  If $err is
	 * equal to "ADbanned" or "PObanned" it looks up the ban data
	 * and displays that.  Otherwise it uses the standard error
	 * Smarty template.
	 * 
	 * @param string $err The kind of error that occurred
	 */
	function THdie($err)
	{
		//die($err);
		if($err=="ADbanned" || $err =="PObanned") // THIS USED TO READ $err="ADbanned", which meant that whenever THdie was called it would tell someone they're banned, gg
		{
			$db=new ThornDBI();

			// Get bans associated with an IP (there could be multiple bans)
			$bans = $db->getban();
			$unbanned = 1; // boolean to indicate whether they've been unbanned or not, gets changed in the foreach loop if appropriate
			
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">';
			echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
			echo '<meta http-equiv="Content-Style-Type" content="text/css" />';
                        echo '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>';
			echo '<link rel="stylesheet" type="text/css" href="href="'.THurl.'tpl/'.THtplset.'" title="Stylesheet" />';
			echo '<title>b&</title>';
			echo '</head>';
			echo '<body>';
			echo '<div style="text-align: center;">You have been banned.<br /></div>';

			foreach( $bans as $singleban )
			{
				// Display wildcards as appropriate.
				printf("Associated IP: %d.%d.%s.%s<br />\n",
				$singleban['ip_octet1'],
				$singleban['ip_octet2'],
				(($singleban['ip_octet3'] == -1) ? "*" : $singleban['ip_octet3']),
				(($singleban['ip_octet4'] == -1) ? "*" : $singleban['ip_octet4']));
			
				if( $singleban['postdata'] )
				{
					$fixbody = str_replace("&gt;",">",$singleban['postdata']);
					$fixbody = str_replace("&amp;gt;",">",$fixbody);
					$fixbody = str_replace("&lt;","<",$fixbody);
					$fixbody = str_replace("&amp;lt;","<",$fixbody);
					echo 'Associated post:<br />'.nl2br($fixbody).'<br /><br />';
				}
				
				$reason = "";
				if(!$singleban['privatereason'])
				{
					$reason=$singleban['publicreason'];
				} 
				else 
				{
					$reason=$singleban['privatereason'];
				}
				
				if(!$reason)
				{
					$reason='No reason given';
				}
				else
				{
					echo 'Reason given: '.$reason.'<br /><br />';
				}
				
				if( $singleban['duration'] == 0 )
				{
					echo 'This is only a warning and will be removed from the active bans list. Keep in mind however that if you are warned multiple times you may be permabanned.';
				}
				else if( $singleban['duration'] == -1 )
				{
					echo 'This ban will not expire.<br /><br />';
					$unbanned = 0; // still banned
				}
				else // Neither permanent nor a warning
				{
					//we'll need to know the difference between the ban time and the duration for actually expiring the bans
					$offset = THtimeoffset*60;
					$now = time()+$offset;
					$banoffset = $singleban['duration']*3600; // convert to hours
					$expiremath = $banoffset+$singleban['bantime'];
				
					if($now>$expiremath)
					{
						echo 'This ban has expired.  Keep in mind that you may be rebanned at any time.<br /><br />';
					} 
					else 
					{
						echo 'This ban duration was set to '.$singleban['duration'].' hours.  The ban will expire on '.
							strftime(THdatetimestring,$expiremath).'<br /><br />';
						$unbanned = 0; // still banned
					}
				}
				
			}
	
			if($unbanned == 1)
			{
				echo '<div style="text-align: center;"><a href="'.THurl.'">Continue to the main index</a></div>';
			} 
			else 
			{
				echo "If you feel this ban is in error, please email an administrator.";
			}
			
			echo '</body></html>';
		} 
		else 
		{
			$sm=sminit("error.tpl",$err);
			$sm->assignbyref("error",$err);
			$sm->display("error.tpl",null);
			die();
		}
	}//THdie
	//Below are functions that are used in various places throughout Thorn.
	
	/**
	 * Take an incoming IP and strip the last octet in it, replacing with 0
	 * 
	 * @param int $ip The incoming IP in long-int form
	 * 
	 * @return int The incoming IP but with the last octet replaced with 0
	 */
	function ipsub($ip)
	{
		$sub=explode(".",long2ip($ip));
		return(ip2long(implode(".",array($sub[0],$sub[1],$sub[2],0))));
	}

	/**
 	* Initialize a new Smarty object.
 	* 
 	* @return object A new instance of the Smarty class
 	*/	
	function smsimple()
	{
		require_once("_Smarty/Smarty.class.php");
		$sm=new Smarty;
		//$sm->debugging=true;  //uncomment to enable debugging window for smarty
		return($sm);
	}
	
	/**
	 * Initialize a new Smarty object with certain parameters set, including
	 * the cache directory, the caching mode, the template directory, the ID
	 * used for caching, and with certain common variables, such as THurl,
	 * intialized
	 * 
	 * @param string $tpl The template file to use (make sure to include the .tpl)
	 * @param string $id The ID to use for caching (will perform a lookup and may
	 * even potentially result in a cached version being used if there is a match).
	 * Defaults to null.
	 * @param string $template The template set to use.  Defaults to THtplset.
	 * @param bool $admin Whether this is considered an "administrator" page, in
	 * which case the template set is overridden to "_admin" and caching is
	 * always disabled.
	 */
	function sminit($tpl,$id=null,$template=THtplset,$admin=false,$modvar=false)
	{
		$smarty=smsimple();
		$smarty->cache_dir=THpath."cache/";
		if ($admin)
		{
			//echo("ADMIM MODE ZOMG");
			$smarty->caching=0;
			$smarty->template_dir=THpath."tpl/_admin/";
			$smarty->cache_lifetime=0;
			$smarty->assign("THtplurl",THurl."tpl/_admin/");

		}
		elseif (THtpltest || $tpl=="error.tpl" || $tpl=="preview.tpl" || $tpl=="popup.tpl")
		{
			//We don't want to cache error pages, post previews, or popups
			$smarty->caching=0;
			$smarty->force_compile=true;
			$smarty->template_dir=THpath."tpl/".$template."/";
			$smarty->cache_lifetime=-1;
			$smarty->assign("THtplurl",THurl."tpl/".$template."/");
		} 
		else 
		{
			$smarty->caching=2;
			$smarty->compile_check=false;
			$smarty->template_dir=THpath."tpl/".$template."/";
			$smarty->cache_lifetime=-1;
			$smarty->assign("THtplurl",THurl."tpl/".$template."/");
		}
		$smarty->compile_dir=THpath."compd/";
		if ($id!=null && $admin==false && $smarty->iscached($tpl,$id))
		{
			//$smarty->display($tpl,$id);
			echo $smarty->display($tpl,$id);
			if(($_SESSION['admin']) || ($_SESSION['moderator']) || ($modvar)) { $smarty->display("modscript.tpl",null); }
			$smarty->caching = false;
			echo $smarty->display("bottombar.tpl",null);
			die("<!-- Loaded from cache /-->");
		}
		$smarty->assignByRef("THcname",$id);
		$smarty->assign("THname",THname);
		$smarty->assign("THurl",THurl);
		$smarty->assign("THtpltest",THtpltest);
		$smarty->assign("THversion",THversion);
		$smarty->assign("THcodename",THcodename);  //we're trendy now right?
		$smarty->assign("THvc",THvc);
		$smarty->assign("THnewsboard",THnewsboard);
		$smarty->assign("THmodboard",THmodboard);
		$smarty->assign("THdefaulttext",THdefaulttext);
		$smarty->assign("THdefaultname",THdefaultname);
		$smarty->assign("THdatetimestring",THdatetimestring);
		$smarty->assign("THuserewrite",THuserewrite);
		$smarty->assign("GET",$_GET);
		$smarty->assign("THcookieid", THcookieid);
		$smarty->registerPlugin("function","smcount", "smcount");
		return($smarty);
	}
	
	/**
	 * Get a count of elements in an array. Since Smarty doesn't have an integrated
	 * array counting function, this serves as a wrapper for PHP's. $p is an array 
	 * which has various values used for the produced output:
	 * 
	 * $p['array'] contains the array whose elements will be counted.
	 * 
	 * If $p['assign'] is set and true, the function will change $p['assign']
	 * to be the array count
	 * 
	 * @param array $p The array containing the previously mentioned values.
	 * @param reference $sm A reference to a Smarty object
	 * 
	 * @return mixed Nothing is returned if $p['assign'] is true- in all other instances,
	 * however, the elements count is returned
	 */
	function smcount($p,&$sm)
	{
		//Sweet mother, why didn't I think of this sooner?!
		//Smarty doesn't have an array counting function built in, so here's a wrapper for PHP's.
		if (isset($p['assign'])==true)
		{
			$sm->assign($p['assign'],count($p['array']));
		} 
		else 
		{
			return(count($p['array']));
		}
	}
	
	/**
	 * Clear the cache for a thread, page, and/or entire board
	 * 
	 * @param int $board The affected board ID
	 * @param int $page The affected page (if any). Defaults to -1, meaning no page
	 * @param int $thread The affected thread ID (unique, not globalid). Defaults to -1,
	 * meaning no thread
	 * @param bool $delete_everything Deletes the board cache AND ALL thread caches for a
	 * particular board (meant for use ONLY in fragboard)
	 */
	function smclearcache($board, $page=-1, $thread=-1, $delete_everything=false)
	{
		// Oh, we're actually clearing the cache for a thread
		if($thread != -1)
		{
			$files_to_delete = glob(THpath."cache/t".$board."-".$thread."*");
		}
		else if($page != -1) // clearing the cache for a page
		{
			$files_to_delete = glob(THpath."cache/b".$board."-".$page."-*");
		}
		else if($delete_everything == true) // delete board cache AND ALL thread caches (meant for use ONLY in fragboard)
		{
			$files_to_delete = glob(THpath."cache/b".$board."-*");
			$files_to_delete2 = glob(THpath."cache/t".$board."-*");
		}
		else // we're clearing the cache for a whole board.
		{
			$files_to_delete = glob(THpath."cache/b".$board."-*");
		}
		
		foreach($files_to_delete as $deletion)
		{
			unlink($deletion);
		}
	}
	
	/**
	 * Delete a cache for a static page, given its ID.
	 * 
	 * @param int $pageid The ID of the static page
	 */
	function smclearpagecache($pageid)
	{
		@unlink(THpath."cache/p".$pageid);
	}

	/**
	 * Delete images from the images folder.  The 
	 * directories to clear out are provided in the
	 * incoming array of imgidxes
	 * 
	 * @param array $badimgs The array of imgidxes whose corresponding
	 * directories will be cleared out
	 */
	function delimgs($badimgs)
	{
		if($badimgs != null)
		{
			//Delete these images
			foreach ($badimgs as $bad)
			{
				$bad=(int)$bad;
				if($bad!=0)
				{
					$pyath=THpath."images/".$bad."/";
					$it=opendir($pyath);
					while (($img=readdir($it))!==false)
					{
						// Skip over the directory items
						if ($img == "." || $img == ".." )
						{
							continue;	
						}
						
						if( unlink($pyath.$img) == false )
						{
							// Handle error and write to rmfailures log
							$error = error_get_last();
							$errorstring = "file: ".$pyath.$img."\tmsg: ".$error['message'];
							writelog($errorstring, "rmfailures");
						}
					}
					
					if( rmdir($pyath) == false )
					{
						// Handle error and write to rmfailures log
						$error = error_get_last();
						$errorstring = "dir: ".$pyath."\tmsg: ".$error['message'];
						writelog($errorstring, "rmfailures");
					}
				}
			}
		}
	}

	/**
	 * Convert an incoming file extension into its corresponding
	 * bit, or 0 if there was no match
	 * 
	 * @param string $ext The extension
	 * 
	 * @return int The bit value, or 0 if there was no match
	 */
	function bitlookup($ext)
	{
		// OH YEAH BITFLAGS
		if($ext == "jpg" || $ext == "jpeg"){
			return 1;
		}
		else if($ext == "gif"){
			return 2;
		}
		else if($ext == "png"){
			return 4;
		}
		else if($ext == "svg"){
			return 8;
		}
		else if($ext == "swf"){
			return 16;
		}
		else if($ext == "pdf"){
			return 32;
		}
	
		//not found!  should this error?
		return 0;
	}
	
	/**
	 * Find if an item is in an array whose elements are stored as a comma-separated
	 * list (hence the CSL) which is represented as a string
	 * 
	 * @param string $item The item to search for
	 * @param string $csl The string which represents the comma-separated list
	 * 
	 * @return bool If $item was found
	 */
	function is_in_csl($item, $csl)
	{
		return in_array($item, explode(",",$csl));
	}
	
	/**
	 * Compare two posts and determine which is earlier based
	 * on the "time" element of each
	 * 
	 * @param array $a An assoc-array containing post data
	 * @param array $b An assoc-array containing post data
	 * 
	 * @return int 1 if $a comes before $b, 
	 * -1 if $b comes before $a,
	 * 0 if the two are equal
	 */
	function comp_post_times($a, $b)
	{
		$first=$a['time'];
		$second=$b['time'];
		if ($first == $second) 
		{ //Unlikely, but possible
			return 0;
		}

		return ($first < $second) ? 1 : -1;
	}
	
	/**
	* This function takes a string or array and
	* replaces every instance of < and > with their HTML-encoded
	* equivalent, so it doesn't mess up our HTML forms
	* if we use it as a value attribute or something like that.
	*
	* @param mixed $filter A string or array or whatever, see how 
	* the PHP function str_replace works for the $subject param
	*
	* @return mixed Whatever was passed in but with the substitution performed
	*/
	function replacewedge($input)
	{
		$output = str_replace("<", "&lt;", $input);
		$output = str_replace(">", "&gt;", $output);
		return $output;
	}
	
	/**
	* This function takes a string or array and
	* replaces every instance of ' with its HTML-encoded
	* equivalent, so it doesn't mess up our HTML forms
	* if we use it as a value attribute or something like that.
	*
	* @param mixed $filter A string or array or whatever, see how 
	* the PHP function str_replace works for the $subject param
	*
	* @return mixed Whatever was passed in but with the substitution performed
	*/
	function replacequote($filter)
	{
		// ~simple and clean is the way that you're making me feel tonight~
		// ^-- imagine tyam singing this while wearing a dress...
		return str_replace("\'", "&#039;", $filter);
		// ...or a tank top

		//oh hello i found this comment thanks diff ~tyam
	}
	
	/**
	* This function takes a string or array and
	* replaces every instance of " with its HTML-encoded
	* equivalent, so it doesn't mess up our HTML forms
	* if we use it as a value attribute or something like that.
	*
	* @param mixed $filter A string or array or whatever, see how 
	* the PHP function str_replace works for the $subject param
	*
	* @return mixed Whatever was passed in but with the substitution performed
	*/
	function replacedouble($filter)
	{
		return str_replace('"', "&#034;", $filter);
	}
	
	/**
	 * Generate a random ID. 16^32 possible values SHOULD
	 * decrease the chances of hash collisions. ;)
	 * 
	 * @return string A 32-character hex string (an MD5 hash)
	 */
	function generateRandID()
	
	{
		return md5(generateRandStr(16));
	}
	
	/**
	 * Generate a random string made up of randomized letters
	 * (lower and upper case) and digits, with a certain length
	 * 
	 * @param int $length How long a string to make
	 * 
	 * @return string The generated string with length $length
	 */
	function generateRandStr($length)
	{
		$randstr = "";
		for($i=0; $i<$length; $i++)
		{
			$randnum = mt_rand(0,61);
			if($randnum < 10)
			{
				$randstr .= chr($randnum+48);
			}
			else if($randnum < 36)
			{
				$randstr .= chr($randnum+55);
			} else {
				$randstr .= chr($randnum+61);
			}
		}
		return $randstr;
	}

	/**
	 * Send an email welcoming a new user
	 * 
	 * @param string $user The user's name
	 * @param string $email Destination email
	 * @param string $pass The password for this new account
	 * 
	 * @return bool True if the mail was accepted for delivery (see the workings
	 * of the PHP mail function for more explanation)
	 */
	function sendWelcome($user, $email, $pass)
	{
		$from = "From: ".THprofile_emailname." <".THprofile_emailaddr.">";
		$subject = THname." welcomes you!";
		$body = $user.",\n\n"
			."Welcome! You've just registered at ".THname." "
			."with the following information:\n\n"
			."Username: ".$user."\n"
			."Password: ".$pass."\n\n"
			."If you ever lose or forget your password, a new "
			."password will be generated for you and sent to this "
			."email address, if you would like to change your "
			."email address you can do so by going to the "
			."My Account page after signing in.\n\n"
			."- ".THname." Automailer";
		return mail($email,$subject,$body,$from);
	}

	/**
	 * Send an email containing a newly-reset password to a user
	 * 
	 * @param string $user The user's name
	 * @param string $email Destination email
	 * @param string $pass The new password
	 * @param string $ip The IP requesting the email change
	 * 
	 * @return bool True if the mail was accepted for delivery (see the workings
	 * of the PHP mail function for more explanation)
	 */	
	function sendNewPass($user, $email, $pass, $ip)
	{
		$from = "From: ".THprofile_emailname." <".THprofile_emailaddr.">";
		$subject = THname." - Your new password";
		$body = $user.",\n\n"
			."We recieved a request to reset your password from "
			."the IP ".$ip.".\n\n"
			."We've generated a new password for you at your "
			."request, you can use this new password with your "
			."username to log in to ".THname.".\n\n"
			."Username: ".$user."\n"
			."New Password: ".$pass."\n\n"
			."It is recommended that you change your password "
			."to something that is easier to remember, which "
			."can be done by going to the My Account page "
			."after signing in.\n\n"
			."- ".THname." Automailer";
		return mail($email,$subject,$body,$from);
	}
	
	/**
	 * Send an email notifying a user that their approval status was denied.
	 * 
	 * @param string $user The user's name
	 * @param string $pass The password for this new account
	 * 
	 * @return bool True if the mail was accepted for delivery (see the workings
	 * of the PHP mail function for more explanation)
	 */
	function sendDenial($user, $email)
	{
		$from = "From: ".THprofile_emailname." <".THprofile_emailaddr.">";
		$subject = THname." : pending registration";
		$body = $user.",\n\n"
			."We regret to inform you that your request for an "
			."account at ".THname." with the username of: \"".$user
			."\" has been denied. This could have been done for a "
			."variety of reasons, and if you have any specific"
			."questions simply contact the administrators.\n\n"
			."- ".THname." Automailer";
		return mail($email,$subject,$body,$from);
	}

	/**
	 * Record a particular (significant) action in a log file
	 * 
	 * @param string $actionstring What's going down
	 * @param string $type The kind of log file to write to
	 */	
	function writelog($actionstring,$type)
	{
		$logfile = fopen("unlinked/".$type.".log", "a") or error_log("Could not write ".$type." log: ".$actionstring."\n",3,"error.log");
		
		$username = "(none)";
		
		if(isset($_SESSION['username'])) 
		{
			$username = $_SESSION['username'];
		}
		
		fwrite($logfile, strftime("%m/%d/%y %H:%M:%S",time()+(THtimeoffset*60)).": ".$username."\t".$_SERVER['REMOTE_ADDR']."\t");
		fwrite($logfile, $actionstring."\n");
		fclose($logfile);
	}

	/**
	 * Check if the current session has administrator status
	 * 
	 * @return bool Is the user an admin?
	 */	
	function checkadmin()  //quick, simple, to the point, this is how I like it
	{
		if($_SESSION['admin']!=true)
		{
			THdie("You are not logged in as an administrator!");
		}
	}

	/**
	 * Check the user's login status based on certain stored cookie values
	 * and the state of their $_SESSION variables.  Sets their session vars
	 * as appropriate (if it's an invalid login state, their session data
	 * is reset, but if they're logged in correctly with no session data,
	 * that is rectified as well) 
	 */		
	function checklogin() //look for cookies, set session variables if needed
	{
		if(isset($_COOKIE['THcookieid'."-uname"]) && isset($_COOKIE['THcookieid'."-id"]))
		{
			// verify login information
			$db=new ThornProfileDBI();
			$userdata  = $db->getuserdata_cookielogin($_COOKIE[THcookieid."-uname"], $_COOKIE[THcookieid."-id"]);
			
			if($userdata == null)
			{
				// No dice.
				setcookie(THcookieid."-uname", "", time()-THprofile_cookietime, THprofile_cookiepath);
				setcookie(THcookieid."-id",   "", time()-THprofile_cookietime, THprofile_cookiepath);

				/* Unset PHP session variables */
				unset($_SESSION['username']);
				unset($_SESSION['userid']);
				unset($_SESSION['userlevel']);
				unset($_SESSION['admin']);
				unset($_SESSION['moderator']);
				unset($_SESSION['mod_array']);
				
			}
			elseif( !isset($_SESSION['username']) )
			{
				// Okay, they have a valid ID for a login, but no session data.  Let's rectify that.
				$_SESSION['username'] 	= $userdata['username'];
				$_SESSION['userlevel'] 	= $userdata['userlevel'];
				$_SESSION['admin'] 		= $userdata['mod_admin'];
				$_SESSION['mod_array'] 	= $userdata['mod_array'];
				$_SESSION['mod_global'] = $userdata['mod_global'];
					
				if ($userdata['mod_global'] || $userdata['mod_array'])
				{
					$_SESSION['moderator']=true;
				}
			}			
		}
//		elseif (isset($_SESSION['username']))
//		{
//			/* Unset PHP session variables */
//			unset($_SESSION['username']);
//			unset($_SESSION['userid']);
//			unset($_SESSION['userlevel']);
//			unset($_SESSION['admin']);
//			unset($_SESSION['moderator']);
//			unset($_SESSION['mod_array']);
//		}
	}
	
	/***
	 * Check if an email address is valid or not
	 * 
	 * @param string $email The address to validate
	 * 
	 * @return bool True if the provided string is a valid email address
	 */
	function validateemail($email)
	{
		// based on the discussion at http://php.net/preg_match
		
		return preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' .
		'(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', $email);
	}
	
?>
