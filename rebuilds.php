<?php


/*
       drydock imageboard script (http://code.573chan.org/)
       File:           rebuilds.php
       Description:    Functions for rebuilding files from db contents

				I moved these to their own file to clean up common.  >:[   ~tyam

       
       Unless otherwise stated, this code is copyright 2008 
       by the drydock developers and is released under the
       Artistic License 2.0:
       http://www.opensource.org/licenses/artistic-license-2.0.php
   */
//List of rebuild_ functions (so I don't have to scroll)

//rss, htaccess, linkbars, spamlist, hovermenu, filters, capcodes, config

require_once ("config.php");
require_once ("common.php");
//functions below are responsible for rebuilding certain files

/**
 * Rebuild the cached capcodes file (in unlinked/capcodes.php)
 */
function rebuild_capcodes()
{
	$capcodes = array ();
	$db_capcodes = array();
	
	// Load stuff from the DB
	$db = new ThornModDBI();
	$db_capcodes = $db->fetchBCW(THbcw_capcode);
	foreach ($db_capcodes as $row_item )
	{
		$capcodes[$row_item['capcodefrom']] = $row_item['capcodeto'];
	}

	// And write it to the cache file.
	$fp_cache = fopen(THpath . "unlinked/capcodes.php", "w");
	if ($fp_cache)
	{
		fprintf($fp_cache, "<?php\n" . '$capcodes' . " =\n");
		$string = var_export($capcodes, true);
		fprintf($fp_cache, "%s;\n?>", $string);
	}
	else
	{
		die("Could not open unlinked/capcodes.php for writing!");
	}
	fclose($fp_cache);

}

/**
 * Rebuild the config file, and update options based on the incoming
 * array, which contains various parameters
 * 
 * @param array $configpost An array of values whose new values will
 * take effect in the new config file
 */
function rebuild_config($configpost)
{
	$config = fopen(THpath . "config.php", 'w');
	fwrite($config, '<?php' . "\n");
	//Stuff that doesn't change
	fwrite($config, 'define("THlocale","' . THlocale . '");' . "\n"); //Changing locale is not supported as of 12/18/2012, maybe later
	fwrite($config, 'define("ddversion","' . THversion . '");' . "\n");  //we can check against this later, in the upgrade script
	fwrite($config, 'define("THpath","' . THpath . '");' . "\n");
	fwrite($config, 'define("THurl","' . THurl . '");' . "\n");
	fwrite($config, 'define("THcookieid","' . THcookieid . '");' . "\n"); //cookie seed.
	fwrite($config, 'define("THsecret_salt","' . THsecret_salt . '");' . "\n");
	//Database stuff that doesn't change
	fwrite($config, 'define("THdbserver","' . THdbserver . '");' . "\n");
	fwrite($config, 'define("THdbuser","' . THdbuser . '");' . "\n");
	fwrite($config, 'define("THdbpass","' . THdbpass . '");' . "\n");
	fwrite($config, 'define("THdbbase","' . THdbbase . '");' . "\n");
	fwrite($config, 'define("THdbtype","' . THdbtype . '");' . "\n");
	fwrite($config, 'define("THdbprefix","' . THdbprefix . '");' . "\n");
	//tables
	fwrite($config, 'define("THbans_table","' . THbans_table . '");' . "\n");
	fwrite($config, 'define("THbanhistory_table","' . THbanhistory_table . '");' . "\n");
	fwrite($config, 'define("THblotter_table","' . THblotter_table . '");' . "\n");
	fwrite($config, 'define("THboards_table","' . THboards_table . '");' . "\n");
	fwrite($config, 'define("THcapcodes_table","' . THcapcodes_table . '");' . "\n");
	fwrite($config, 'define("THextrainfo_table","' . THextrainfo_table . '");' . "\n");
	fwrite($config, 'define("THfilters_table","' . THfilters_table . '");' . "\n");
	fwrite($config, 'define("THimages_table","' . THimages_table . '");' . "\n");
	fwrite($config, 'define("THpages_table","' . THpages_table . '");' . "\n");
	fwrite($config, 'define("THreplies_table","' . THreplies_table . '");' . "\n");
	fwrite($config, 'define("THreports_table","' . THreports_table . '");' . "\n");
	fwrite($config, 'define("THthreads_table","' . THthreads_table . '");' . "\n");
	fwrite($config, 'define("THusers_table","' . THusers_table . '");' . "\n");
	fwrite($config, "\n");
	//Stuff that might have changed
	$ppp = (int) abs($configpost['THjpegqual']);
	if ($ppp > 100)
	{
		$ppp = 100;
	} //yeah, let's upsample the jpegs >:[
	fwrite($config, 'define("THjpegqual",' . $ppp . ');' . "\n");
	if(!isset($configpost['THdupecheck'])) { $configpost['THdupecheck'] = NULL; }
	fprintf($config, "define(\"THdupecheck\", %d);\n", ($configpost['THdupecheck'] == "on"));
	fwrite($config, "\n");

	//Template settings
	if(!isset($configpost['THuserewrite'])) { $configpost['THuserewrite'] = NULL; }
	fprintf($config, "define(\"THuserewrite\", %d);\n", ($configpost['THuserewrite'] == "on"));

	//Default template set
	$newtplset = str_replace('"', "", $configpost['THtplset']);
	fwrite($config, 'define("THtplset","' . $newtplset . '");' . "\n");
	if(!isset($configpost['THtpltest'])) { $configpost['THtpltest'] = NULL; }
	fprintf($config, "define(\"THtpltest\", %d);\n", ($configpost['THtpltest'] == "on"));

	// I think this code is for when we were restricted to only one template set for the boards.
	// Ah, the bad old days.
	//if ($tpltest || $newtplset!=THtplset)
	//{
	//Frag cache for template testing mode and if template set was changed
	//$sm->clear_all_cache();
	//$sm->clear_compiled_tpl();
	//}

	//We need to handle this differently now.  If they turn on reCAPTCHA without the lib, the pages just won't load and won't produce an error.  So let's fix that by causing an error.
	if((int) $configpost['THvc']==1 && !file_exists($path."recaptchalib.php"))  //Did they get the file?
	{
		fwrite($config, 'define("THvc",' . THvc.');' . "\n");  //NO CHANGE ALLOWED!
		$recaptchaerror = "You need to get recaptchalib.php from <a href='http://google.com/recaptcha/'>Google</a>!<br />"
			."All settings were saved except anti-spam.<br /><br />"
			.'<a href="'.$path.'admin.php?a=g">continue</a>';
	} else {  //Allow the change... unless the keys aren't set!
		if($configpost['reCAPTCHAPublic'] == NULL || $configpost['reCAPTCHAPrivate'] == NULL)
		{
			fwrite($config, 'define("reCAPTCHAPublic","' . reCAPTCHAPublic . '");' . "\n");
			fwrite($config, 'define("reCAPTCHAPrivate","' . reCAPTCHAPrivate . '");' . "\n");
			$recaptchaerror = "reCAPTCHA keys must be set to use it.  You can get keys from <a href='http://google.com/recaptcha/'>Google</a>!<br />"
				."All settings were saved except anti-spam.<br /><br />"
				.'<a href="'.$path.'admin.php?a=g">continue</a>';
		} else {  //Sounds good, chief.
			fwrite($config, 'define("THvc",' . (int) $configpost['THvc'] . ');' . "\n");
			fwrite($config, 'define("reCAPTCHAPublic","' . $configpost['reCAPTCHAPublic'] . '");' . "\n");
			fwrite($config, 'define("reCAPTCHAPrivate","' . $configpost['reCAPTCHAPrivate'] . '");' . "\n");
		}
	}
	fwrite($config, "\n");

	//Time settings
	fwrite($config, 'define("THtimeoffset",' . (int) $configpost['THtimeoffset'] . ');' . "\n");
	fwrite($config, 'define("THdatetimestring","' . str_replace('"', "", $configpost['THdatetimestring']) . '");' . "\n");
	fwrite($config, "\n");

	//Site settings
	fwrite($config, 'define("THname","' . str_replace('"', "", $configpost['THname']) . '");' . "\n");
	fwrite($config, 'define("THnewsboard",' . (int) $configpost['THnewsboard'] . ');' . "\n");
	fwrite($config, 'define("THmodboard",' . (int) $configpost['THmodboard'] . ');' . "\n");
	fwrite($config, 'define("THdefaulttext","' . str_replace('"', "", $configpost['THdefaulttext']) . '");' . "\n");
	fwrite($config, 'define("THdefaultname","' . str_replace('"', "", $configpost['THdefaultname']) . '");' . "\n");
	fwrite($config, "\n");

	//Utility settings
	fwrite($config, 'define("THpearpath","' . str_replace('"', "", $configpost['THpearpath']) . '");' . "\n");
	if(!isset($configpost['THuseSVG'])) { $configpost['THuseSVG'] = NULL; }
	fprintf($config, "define(\"THuseSVG\", %d);\n", ($configpost['THuseSVG'] == "on"));
	fwrite($config, 'define("THSVGthumbnailer",' . (int) $configpost['THSVGthumbnailer'] . ');' . "\n");
	if(!isset($configpost['THusePDF'])) { $configpost['THusePDF'] = NULL; }
	fprintf($config, "define(\"THusePDF\", %d);\n", ($configpost['THusePDF'] == "on"));
	if(!isset($configpost['THuseSWFmeta'])) { $configpost['THuseSWFmeta'] = NULL; }
	fprintf($config, "define(\"THuseSWFmeta\", %d);\n", ($configpost['THuseSWFmeta'] == "on"));
	if(!isset($configpost['THusecURL'])) { $configpost['THusecURL'] = NULL; }
	fprintf($config, "define(\"THusecURL\", %d);\n", ($configpost['THusecURL'] == "on"));
	if(!isset($configpost['DDDEBUG'])) { $configpost['DDDEBUG'] = NULL; }
	fprintf($config, "define(\"DDDEBUG\", %d);\n", ($configpost['DDDEBUG'] == "on"));
	fwrite($config, "\n");

	//Profile settings
	fwrite($config, 'define("THprofile_adminlevel",' . THprofile_adminlevel . ');' . "\n"); //should not need to be changed
	fwrite($config, 'define("THprofile_userlevel",' . THprofile_userlevel . ');' . "\n"); //ditto
	fwrite($config, 'define("THprofile_emailname","' . $configpost['THprofile_emailname'] . '");' . "\n");
	fwrite($config, 'define("THprofile_emailaddr","' . $configpost['THprofile_emailaddr'] . '");' . "\n");
	fwrite($config, 'define("THprofile_regpolicy",' . (int) $configpost['THprofile_regpolicy'] . ');' . "\n"); //1=manual,  !=1 = auto approve
	fwrite($config, 'define("THprofile_viewuserpolicy",' . (int) $configpost['THprofile_viewuserpolicy'] . ');' . "\n"); //1=logged in only, 2=anyone, 0=mods only
	fwrite($config, 'define("THprofile_cookietime",' . ((int) $configpost['THprofile_cookietime'] * 3600) . ');' . "\n");
	fwrite($config, 'define("THprofile_cookiepath","' . $configpost['THprofile_cookiepath'] . '");' . "\n"); //should be "/" probably
	if(!isset($configpost['THprofile_emailwelcome'])) { $configpost['THprofile_emailwelcome'] = NULL; }
	fprintf($config, "define(\"THprofile_emailwelcome\", %d);\n", ($configpost['THprofile_emailwelcome'] == "on")); //1=send
	if(!isset($configpost['THprofile_lcnames'])) { $configpost['THprofile_lcnames'] = NULL; }
	fprintf($config, "define(\"THprofile_lcnames\", %d);\n", ($configpost['THprofile_lcnames'] == "on")); //1 = names are converted to lowercase
	fwrite($config, 'define("THprofile_maxpicsize",' . $configpost['THprofile_maxpicsize'] . ');' . "\n"); //in bytes
	fwrite($config, '?>'); //some editors break colors here so <?
	fclose($config); //file's closed, fwrites, etc

	//Let's take care of that error.
	if($recaptchaerror)
	{
		THdie($recaptchaerror);
	}

}

/**
 * Rebuild the cached wordfilters file (in unlinked/filters.php)
 */
function rebuild_filters()
{
	
	$to = array ();
	$from = array ();
	
	// Load stuff from the DB
	$db = new ThornModDBI();
	$db_filters = $db->fetchBCW(THbcw_filter);

	foreach ($db_filters as $row_item)
	{
		$to[] = $row_item['filterto'];
		$from[] = $row_item['filterfrom'];
	}

	// And write it to the wordfilter cache file.
	$fp_cache = fopen(THpath . "unlinked/filters.php", "w");
	if ($fp_cache)
	{
		fprintf($fp_cache, "<?php\n" . '$to' . " =\n");
		$string = var_export($to, true);
		fprintf($fp_cache, "%s;\n\n" . '$from' . " =\n", $string);
		$string = var_export($from, true);
		fprintf($fp_cache, "%s;\n?>", $string);
	}
	else
	{
		die("Could not open unlinked/filters.php for writing!");
	}
	fclose($fp_cache);
}

/**
 * Rebuild the menu file (in menu.php), which contains a list
 * of boards and potentially to moderation/adminstrative sections
 * as well
 */
function rebuild_hovermenu()
{
	$db = new ThornDBI();

	$boards = $db->getvisibleboards();
	$showcount = count($boards);

	$sidelinks = fopen("menu.php", "w") or die("Could not open menu.php for writing.");
	//this is all still kind of hackish but we'll wrok on it.
	fwrite($sidelinks, '<div id="idxmenu">' . "\n" .
	'<div id="idxmenuitem">' . "\n" .
	'<div class="idxmenutitle">' . "\n"); //this is long, is this right?
	if ($showcount > 0)
	{
		fwrite($sidelinks, "Board Navigation<br />\n");
		if (THnewsboard != 0)
		{
			fwrite($sidelinks, '<a href="' . THurl . 'news.php">' . "News page</a><br />\n");
		}
		foreach ($boards as $boardentry)
		{
			if ($boardentry['hidden'] != 1)
			{
				if (THuserewrite) //compatibility~~~
				{
					fwrite($sidelinks, '<a href="' . THurl);
				}
				else
				{
					fwrite($sidelinks, '<a href="' . THurl . 'drydock.php?b=');
				}
				fwrite($sidelinks, $boardentry['folder'] . '">' . $boardentry['folder'] . "</a><br />\n"); //finish it up
			}
		}
	} //count>0

	//check for admin/mod cookie here
	fwrite($sidelinks, '<?php if(isset($_SESSION["admin"])){ echo "Administration Menu<br />"; } elseif(isset($_SESSION["moderator"])) { echo "Moderator Menu<br />"; } ?>' . "\n");
	fwrite($sidelinks, '<?php if(isset($_SESSION["admin"])){ echo "' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=g\">Global Settings</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=b\">Board Setup</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=bl\">Blotter Posts</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=sp\">Static Pages</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=x\">Bans</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=c\">Capcodes</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=w\">Filters</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=p\">Profile Admin</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=hk\">Housekeeping</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=lv\">Log Viewer</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=t\">Recent Pics</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=q\">Recent Posts</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=r\">Reports</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."admin.php?a=l\">Lookup Tools</a><br /><br />";' . "\n");
	fwrite($sidelinks, '} elseif(isset($_SESSION["moderator"])){' . "\n");
	fwrite($sidelinks, 'echo "' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."recentpics.php\">Recent Pics</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."recentposts.php\">Recent Posts</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."reports.php\">Reports</a><br />' . "\n");
	fwrite($sidelinks, '<a href=\"".THurl."lookups.php\">Lookup Tools</a><br /><br />";' . "\n");
	fwrite($sidelinks, '} ?>' . "\n");
	fwrite($sidelinks, '<a href="' . THurl . '">Site Index</a><br />' . "\n");
	fwrite($sidelinks, '<?php if($_SESSION["username"]) {' . "\n" . 'echo "<a href=\"".THurl."profiles.php?action=logout\">Log Out</a> / <a href=\"".THurl."profiles.php\">Profiles</a>";' . "\n" . ' } else {' . "\n");
	fwrite($sidelinks, 'echo "<a href=\"".THurl."profiles.php?action=login\">Login</a>' . "\n" . ' / ' . "\n" . '<a href=\"".THurl."profiles.php?action=register\">Register</a>";' . "\n" . '}?>' . "\n");
	fwrite($sidelinks, '</div></div></div>' . "\n");
	fclose($sidelinks);
}

/**
 * Rebuild the root .htaccess file
 */
function rebuild_htaccess()
{
	$htaccess = fopen(".htaccess", "w") or die("Could not open .htaccess for writing.");
	fwrite($htaccess, "#drydock htaccess module\n");
	fwrite($htaccess, "Options -Indexes\n"); //disable snooping fuckers
	fwrite($htaccess, "<Files ~ \"\\.tpl$\">\n");
	fwrite($htaccess, "Order allow,deny\n");
	fwrite($htaccess, "Deny from all\n");
	fwrite($htaccess, "</Files>\n");
	if (THuserewrite)
	{
		fwrite($htaccess, "RewriteEngine on\n");

		$db = new ThornDBI();
		$boards = $db->getboard();

		foreach ($boards as $boardentry)
		{
			fwrite($htaccess, '#  /' . $boardentry['folder'] . '/ - ' . $boardentry['id'] . "\n");
			fwrite($htaccess, 'RewriteRule ^' . $boardentry['folder'] . '/?$ ' . THpath . 'drydock.php?b=' . $boardentry['folder'] . "\n");
			fwrite($htaccess, 'RewriteRule ^' . $boardentry['folder'] . '/([0-9]{1,2})/?$ ' . THpath . 'drydock.php?g=$1&b=' . $boardentry['folder'] . "\n");
			fwrite($htaccess, 'RewriteRule ^' . $boardentry['folder'] . '/thread/([0-9]{1,6})/?$ ' . THpath . 'drydock.php?i=$1&b=' . $boardentry['folder'] . "\n");
			fwrite($htaccess, 'RewriteRule ^' . $boardentry['folder'] . '/edit/([0-9]{1,6})/?$ ' . THpath . 'editpost.php?post=$1&board=' . $boardentry['folder'] . "\n");
			if($boardentry['boardlayout']=="drydock-anonbbs")
			{ 
				fwrite($htaccess, 'RewriteRule ^' . $boardentry['folder'] . '/tlist/?$ ' . THpath . 'drydock.php?tlist&b=' . $boardentry['folder'] . "\n");
			} 
		}
	} //end block only needed for rewrite
	fclose($htaccess);
}

/**
 * Rebuild the linkbar file (in linkbar.php), which
 * spans the top/bottom of pages
 */
function rebuild_linkbars()
{
	$db = new ThornDBI();
	$looper = 1;
	$boards = $db->getvisibleboards();
	$showcount = count($boards);

	$sidelinks = fopen("linkbar.php", "w") or die("Could not open linkbar.php for writing.");
	fwrite($sidelinks, '<table style="width: 100%;"><tr><td style="text-align: left;">[');
	foreach ($boards as $boardentry)
	{
		if (THuserewrite) //compatibility~~~
		{
			fwrite($sidelinks, '<a class="info" href="' . THurl);
		}
		else
		{
			fwrite($sidelinks, '<a class="info" href="' . THurl . 'drydock.php?b=');
		}
		fwrite($sidelinks, $boardentry['folder'] . '">' . $boardentry['folder'] . '<span>' . $boardentry['name'] . " - " . $boardentry['about'] . "</span></a>\n"); //finish it up

		if ($looper < $showcount)
		{
			fwrite($sidelinks, '/');
		};
		$looper++;
	}
	fwrite($sidelinks, ']</td><td style="text-align: right;">[');
	if (THnewsboard > 0)
	{
		if (THuserewrite) //compatibility~~~
		{
			fwrite($sidelinks, ' <a class=info href="' . THurl);
		}
		else
		{
			fwrite($sidelinks, ' <a class=info href="' . THurl . 'drydock.php?b=');
		}
		fwrite($sidelinks, $db->getboardname(THnewsboard) . '">' . $db->getboardname(THnewsboard) . "</a> /\n");

	}
	//uncomment this out if you have the irc stuff installed - we don't ship it ~tyam

	//THuseirc doesn't exist, it's a placeholder for future integration
	/*
			if (THnewsboard>0 && THuseirc ) { fwrite($sidelinks, '/'); };
			if irc {
				fwrite($sidelinks, ' <a class=info href="'.THurl.'irc/">irc</a> /'."\n");
			}//if irc
	*/
	fwrite($sidelinks, ' <a class=info href="' . THurl . '">idx</a> ]</td>');
	fwrite($sidelinks, '</tr></table>');
	fclose($sidelinks);
}

/**
 * Rebuild the RSS feed associated with the news board (at rss.xml)
 */
function rebuild_rss()
{
	if (THnewsboard)
	{
		$sidelinks = fopen("rss.xml", "w") or die("Could not open rss.xml for writing.");
		fwrite($sidelinks, "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n");
		fwrite($sidelinks, "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n");
		fwrite($sidelinks, "\t<channel>\n");
		fwrite($sidelinks, "\t" . '<atom:link href="' . THurl . 'rss.xml" rel="self" type="application/rss+xml" />' . "\n");
		fwrite($sidelinks, "\t\t<title>" . THname . "</title>\n");
		fwrite($sidelinks, "\t\t<description>" . THname . " drydock RSS feeder - " . THurl . "</description>\n");
		fwrite($sidelinks, "\t\t<language>en</language>\n");
		fwrite($sidelinks, "\t\t<link>" . THurl . "</link>\n");
		fwrite($sidelinks, "\t\t<generator>drydock rss feed generator</generator>\n");
		fwrite($sidelinks, "\t\t<copyright>tyam/ordog/kchan devs - http://573chan.org / http://drydock.googlecode.com/</copyright>\n");
		
		//pull everything from the news page
		$db = new ThornToolsDBI();
		$posts = $db->getnewsthreads();
		$newsboard = $db->getboardname(THnewsboard); //get the name of the board

		foreach ($posts as $news_post)
		{
			//set up our variables so we're not using raws
			if ($news_post['name'])
			{
				if ($news_post['trip'])
				{
					$author = $news_post['name'] . "!" . $news_post['trip'];
				}
				else
				{
					$author = $news_post['name'];
				} //tripcode check
			}
			else
			{
				$author = "Anonymous";
			} //name check
			$text = $news_post['body']; //no filters
			if ($news_post['title'] != NULL)
			{
				$subject = $news_post['title'];
			}
			else
			{
				$subject = "News post";
			}

			if (THuserewrite)
			{
				$link = THurl . $newsboard . '/thread/' . $news_post['globalid'];
			}
			else
			{
				$link = THurl . 'drydock.php?b=' . $newsboard . '&amp;i=' . $news_post['globalid'];
			}
			$guid = $news_post['globalid'];
			$body = replacewedge(nl2br($text)) . '&lt;br/&gt;~' . $author;
			//post template
			fwrite($sidelinks, "\n");
			fwrite($sidelinks, "\t\t<item>\n");
			fwrite($sidelinks, "\t\t<guid isPermaLink=\"false\">news $guid</guid>\n");
			fwrite($sidelinks, "\t\t\t<title>$subject</title>\n");
			fwrite($sidelinks, "\t\t\t<description>$body</description>\n");
			fwrite($sidelinks, "\t\t\t<link>$link</link>\n");
			fwrite($sidelinks, "\t\t\t<pubDate>" . date(DATE_RSS, $news_post['time']) . "</pubDate>\n");
			fwrite($sidelinks, "\t\t</item>\n");
			fwrite($sidelinks, "\n");
		}
		fwrite($sidelinks, "\t</channel>\n");
		fwrite($sidelinks, "</rss>\n");
		fclose($sidelinks);
	}
}

/**
 * Rebuild the cached spam blacklist file (in unlinked/blacklist.php).  This
 * will take the items contained in unlinked/spam.txt (one URL a line format)
 * and possibly download Wakaba's anti-spam list as well, combining these two
 * elements into a single array of prohibited URLs
 */
function rebuild_spamlist()
{
	/* manual spam filter downloading - parse unlinked/spam.txt */
	/* Note that this will do it IN addition to cURL. */
	$spamfile = THpath . "unlinked/spam.txt";
	if (!file_exists($spamfile) && !THusecURL)
	{
		die("To use the anti spam features of drydock, 
					you should enable the cURL functions in the general configuration.  If these do not work, 
					you may need to manually download <a href=\"http://wakaba.c3.cx/antispam/spam.txt\">spam.txt</a>
					and place it in the unlinked/ directory.  You should then rebuild.  While this message will appear
					each time you rebuild without fixing either of these, all items except the spam list have been rebuilt.
					Click <a href='" . THurl . "admin.php?a=hk'>here</a> to return to the housekeeping menu.");
	}

	$bannedwords = array ();
	if (THusecURL)
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://wakaba.c3.cx/antispam/spam.txt");

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$string = curl_exec($ch);

		curl_close($ch);

		//echo $string;
		$bannedwords = explode("\n", $string);
	}


	if(is_file($spamfile))
	{
		@ $fp_blacklist = fopen($spamfile, "r") or die();
		while (!feof($fp_blacklist))
		{
			$buffer = fgets($fp_blacklist, 4096);

			if (rtrim($buffer))
			{
				$bannedwords[] = $buffer;
			}
		}
	}
	
	$fp_cache = fopen(THpath . "unlinked/blacklist.php", "w");
	if ($fp_cache)
	{
		fprintf($fp_cache, "<?php\n" . '$spamblacklist' . "=array(\n");

		foreach ($bannedwords as $word)
		{
			// A legitimate use of addslashes? HOLY SHIT WHAT THE FUCK IS GOING ON
			// Print to the cache file, it's already in the array.
			if ($word != null)
			{
				fprintf($fp_cache, '"' . addslashes(rtrim($word)) . "\",\n");
			}
		}

		fprintf($fp_cache, "\n);\n?>");
	}
	else
	{
		die("Could not open blacklist for caching.");
	}
	fclose($fp_cache);
	return;
	
}
//end rebuild blocks
?>