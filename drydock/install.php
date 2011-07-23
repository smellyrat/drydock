<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:           		install.php
		Description:	Handles installation of the script.

			todo: output each page's set vars to a file?  fuck i dont know

		
		Unless otherwise stated, this code is copyright 2008
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/


	function smsimple($path) {
		require_once($path."_Smarty/Smarty.class.php");
		$sm=new Smarty;
		//$sm->debugging=true;
		return($sm);
	}

	function makeuser ($path, $link, $configarray) {
		//shove in some defaults for our new database, should be standard SQL so it should work with all future expansions without editing.
		$query = "INSERT INTO ".$configarray['THdbprefix']."users ( username , password , userid , userlevel , email , mod_array , mod_global , mod_admin , timestamp , age , gender , location , contact , description , capcode , has_picture , approved , pic_pending , proposed_capcode ) VALUES 
			('".$configarray['adminname']."', '".$configarray['adminpass']."', '".$configarray['adminpass']."', '9', NULL , '0', '0', '1', '0', NULL , NULL , NULL , NULL , NULL , NULL , NULL , '1', NULL , NULL)";
		if($configarray['THdbtype']=="MySQL") { mysql_query($query) or die("Database settings aren't correct"); } else { sqlite_query($link, $query) or die("Database settings aren't correct"); }
		$pages = "INSERT INTO ".$configarray['THdbprefix']."pages ( id , name , title , content , publish ) VALUES (1, 'FrontPage', 'News Page', 'This site is powered by the drydock image board script.', 3)";
		if($configarray['THdbtype']=="MySQL") { mysql_query($pages) or die("Database settings aren't correct"); } else { sqlite_query($link, $pages) or die("Database settings aren't correct"); }		
		
	}

	function unlink_placeholders($path) {
		//don't report errors here, it's confusing
		@unlink($path."cache/placeholder.txt");
		@unlink($path."compd/placeholder.txt");
		@unlink($path."images/profiles/placeholder.txt");
		@unlink($path."unlinked/placeholder.txt");
		return;
	}

	function writedb($link, $file, $prefix) {
		//God, where did I get this?  I think the original idea came from like phpscripts.org or something, and then I just gutted it and changed what I needed to get it working.
	
		// Getting the SQL file content        
		$content = file_get_contents("dbi/".$file."-setup.sql");  //Opens the DBI we've specified
		// Processing the SQL file content            
		$file_content = explode("\n",$content);            
		$query = "";
		// Parsing the SQL file content            
		foreach($file_content as $sql_line)
		{
			if(trim($sql_line) != "" && strpos($sql_line, "--") === false)
			{
				$query .= $sql_line;
				// Checking whether the line is a valid statement
				if(preg_match("/(.*);/", $sql_line))
				{
					$query = substr($query, 0, strlen($query)-1);  
					//$prefix = "branch031_";
					$THthis_table = "TH\\1_table";
					$$THthis_table = $prefix."\\1";  //closer..
					$query = preg_replace("/~TH(.*)_table~/",${"TH"."\\1"."_table"},$query);
					//Executing the parsed string, returns the error code in failure
					//$result = mysql_query($query)or die(mysql_error());
					if($file=="MySQL")
					{
						$result = mysql_query($query);
						//echo $query;
					}
					elseif($file=="SQLite")
					{
						$result = sqlite_query($link, $query);
						//echo $query."<br \>";
					}
					$query = "";
				}
			}
		} //End of foreach
		return;
		//die("parse over: ".$file);
	} //End of function

	function initial_builds($path, $configarray) {
	// Build menu, empty wordfilters cache, empty capcodes cache, empty spam blacklist cache
		$sidelinks = fopen($path."menu.php", "w") or die("Could not open menu.php for writing.");
		fwrite($sidelinks, '<div id="idxmenu">'."\n".
				'<div id="idxmenuitem">'."\n".
				'<div class="idxmenutitle">'."\n");
		fwrite($sidelinks, '<?php if($_SESSION["admin"]){ echo "'."\n");
		fwrite($sidelinks, "<a href=".$configarray['THurl']."admin.php?a=hk>Housekeeping Functions</a><br />\";\n");
		fwrite($sidelinks, '} ?>'."\n");
		fwrite($sidelinks, '<a href="'.$configarray['THurl'].'">Site Index</a><br />'."\n");
		fwrite($sidelinks, '<?php if($_SESSION["username"]) {'."\n".'echo "<a href=".THurl."profiles.php?action=logout>Log Out</a> / <a href=".THurl."profiles.php>Profiles</a>";'."\n".' } else {'."\n");
		fwrite($sidelinks, 'echo "<a href=".THurl."profiles.php?action=login>Login</a>'."\n".' / '."\n".'<a href=".THurl."profiles.php?action=register>Register</a>";'."\n".'}?>'."\n");
		fwrite($sidelinks, '</div>');
		fwrite($sidelinks, '</div>'."\n");
		fclose($sidelinks);
		
		$fp_cache = fopen($path."unlinked/filters.php", "w") or die("Could not open unlinked/filters.php for writing.");
		fwrite($fp_cache, "<?php\n");
		fwrite($fp_cache, '$to'."=();\n");
		fwrite($fp_cache, '$from'."=();\n");
		fwrite($fp_cache, "?>");
		fclose($fp_cache);		
		
		$fp_cache = fopen($path."unlinked/capcodes.php", "w") or die("Could not open unlinked/capcodes.php for writing.");
		fwrite($fp_cache, "<?php\n");
		fwrite($fp_cache, '$capcodes'."=();\n");
		fwrite($fp_cache, "?>");
		fclose($fp_cache);		

		$fp_cache = fopen($path."unlinked/blacklist.php", "w") or die("Could not open unlinked/blacklist.php for writing.");
		fwrite($fp_cache, "<?php\n");
		fwrite($fp_cache, '$spamblacklist'."=();\n");
		fwrite($fp_cache, "?>");
		fclose($fp_cache);	

		$htaccess = fopen($path."unlinked/.htaccess", "w") or die("Could not open unlinked/htaccess for writing.");
		fwrite($htaccess, "#drydock htaccess module\n");
		fwrite($htaccess, "Deny from all\n");
		fclose($htaccess);

		return;
	}
	
	include("version.php");  //for version infos
	$path=dirname (__FILE__) . "/";
	@header('Content-type: text/html; charset=utf-8');
	if (function_exists("mb_internal_encoding")) {
		//Unicode support :]
		mb_internal_encoding("UTF-8");
		mb_language("uni");
		mb_http_output("UTF-8");
	}
	if (file_exists("config.php")) {
		//uh oh, looks like we've already tried to set up
		die("The configuration file already exists.  If you are trying to reinstall, please delete config.php");
	}

?>
<html><head>
<title>drydock <?php echo THversion; ?> installer</title>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<style type="text/css">
body {
background-image:url('static/watermark.png');
background-repeat: no-repeat;
background-attachment: fixed;
background-position: bottom right; 
}
p.centertext {
    margin-left: auto;
    margin-right: auto;
    width: 40em
}
.logo { clear:both; text-align:center; font-size:2em; font-weight: bold; color:#FF6600; }
#main { margin-right:154px; }
.box { padding-left:10px; margin-bottom:10px; border-style:none; border-color:black; border-width:1px; }
.pgtitle { text-decoration:none; color:#2266AA; font-family:sans-serif; font-size:x-large; border-width:0px 0px 2px 0px; border-color:#FF6600; border-style:solid; margin-right:10px; margin-top:5px; }
</style>
</head><body>
<div id="main">
    <div class="box">
        <div class="pgtitle">
            Drydock Installation Script
        </div>
	<br>
<?php if(!isset($_GET['p'])) {
	//Attempt to touch a file in the directories that need to be chmodded.
	
	$chmod=array();
	//List of places that must be writable at least by the server
	$paths=array($path,$path."compd/",$path."cache/",$path."images/",$path."unlinked/");
	foreach ($paths as $pith) {
		if (@touch($pith."test")==false){
			$chmod[]=$pith;
		} else {
			unlink($pith."test");
		}
	}
	//Attempt to touch single files that need to be written in
	$files=array($path."menu.php",$path."linkbar.php",$path."rss.xml",$path.".htaccess",$path."unlinked/.htaccess");
	foreach ($files as $file){
		if (@touch($file)==false){
			$chmod[]=$file;
		}
	}
	//Did it all work?
	if (count($chmod)>0) {
		die("There seems to be a problem writting files to the server.<br />
			See the documentation for more information about chmod.  These files must be writable:<br /><br />".implode("<br />",$chmod)."<br />
			If you have shell access, you can try this (or do it manually): <br /><br />chmod 0777 ".implode(" ",$chmod)."<br /><br />
			The server (specifically the <b>user</b> that the http server runs as) needs to be able to read and write these.");
	}

	if(@!file_exists($path."_Smarty/Smarty.class.php"))
	{
		die("Smarty templating engine is either not present, or is in the wrong place.<br />
			Please download the latest version of Smarty 2 from <a href=\"http://smarty.net\" target=\"_blank\">the Smarty homepage</a>.<br/>
			Place the contents of smarty.zip/lib into ".$path."_Smarty/ (so you have something like _Smarty/Smarty.class.php and _Smarty/Smarty_Compiler.class.php) and try again.");
	}

?>
Welcome to the drydock image board script interactive setup.  Thank you for your choice to set sail with us.<br /><br />
If you have not already done so, please read as much of the documentation as you can.  While we will not help you set up drydock on your own server (as in, we will not do it for you), we will be more than happy to answer any questions you might have.<br/><br/>
Over the next few pages, you will be setting up options that will be written to your webserver as a configuration file.  You will be able to change any of these settings later by editing that file directly.  Certain options are also available through the configuration menus when logged in as an administrator.<br/><br/>
If you make a mistake, you'll likely get an error along with possible solutions at the end. If you don't know what something means, either look it up in the documentation, or leave it at the default value.<br /><br />
To continue, click the button below.
<form method="post" enctype="multipart/form-data" action="install.php?p=1">
<input type="submit" value="Continue">
</form><?php } elseif($_GET['p']==1) { 

	//Well, we've already been using $path, but let's go ahead and make sure we're good.
	//Let's do a little hack here for windows, because I've seen forward slashes fail half the time
	$path = str_replace('\\','/',$path);
	if ($path{strlen($path)-1}!="/") { $path.="/"; }
	//In most cases, this will provide us with the desired output.
	$url=str_replace("install.php", "", $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	$url=str_replace("?".$_SERVER['QUERY_STRING'], "", $url);
	if ($url{strlen($url)-1}!="/") { $url.="/"; }

?>
<div class="logo">location settings</div>
<form method="post" enctype="multipart/form-data" action="install.php?p=2">
The script will attempt to guess these values (in fact, it has already been using the assumed path) but often times you will need to adjust them. <b>If these values are incorrect, the script will not function as expected.</b><br /><br />
If you are installing on a Windows system, forward slashes may not work (but they should).  If you experience problems during the install, try replacing these forward slashes with back slashes.<br /><br />
Be sure to double check these values before you continue.<br />
Your image board file path (<b>with trailing slash</b>):<br />
<input type="text" name="THpath" size="60" value="<?php echo $path; ?>" ><br />
Your image board URL:<br />
<input type="text" name="THurl" size="60" value="http://<?php echo $url; ?>" ><br />
<input type="submit" value="Continue">
</form>

<?php } elseif($_GET['p']==2) { 
	//pass our current info on to the next page
	$post = array('THurl' => $_POST['THurl'], 'THpath' => $_POST['THpath']);
	$configarray = serialize($_POST);

	//Prep our dbtypes from the dbi directory and shove them into an array for selection purposes
	$sets=array();
	$it=opendir("dbi/");
	while (($set=readdir($it))!==false)
	{
		if(preg_match("/^.*-dbi\.php$/", $set, $dbi))
		{
			$sets[]=str_replace("-dbi.php","", $dbi[0]);
		}
	}
	?>
<div class="logo">database type</div>
<form method="post" enctype="multipart/form-data" action="install.php?p=3">
Database type <select name="THdbtype">
<?php
	//Output selection boxes
	foreach ($sets as $dbtype) {
		if($dbtype!="ABSTRACT") {
			echo '<option value="'.$dbtype.'">'.$dbtype.'</option>';
		}
	}
?>
	</select>
<input type="hidden" name="configarray" value="<?php echo htmlspecialchars($configarray); ?>">
<br />
<input type="submit" value="Continue">
</form>
<?php } elseif($_GET['p']==3) { 

//pass our current info on to the next page
$configarray = unserialize(str_replace('\"','"',$_POST['configarray']));
$post = array('THdbtype' => $_POST['THdbtype']);
$configarray = array_merge($post,$configarray);
$configarray = serialize($configarray);
$check = unserialize($configarray);

?>
<div class="logo">database settings</div>
<form method="post" enctype="multipart/form-data" action="install.php?p=4">

If you will be running more than one drydock installation, feel free to specify a different prefix.  Do not begin this with a number, include any weird characters, or any symbols other than underscores.<br /><br />
Database prefix: <input type="text" name="THdbprefix" size="12" value="drydock_"/><br />
<?php
	if($check['THdbtype']=="SQLite")
	{
	echo "Because you are using SQLite, you do not need to doing any further setup.  Everything should be automatic.<br />";
echo '
<input type="hidden" name="THdbserver" value="" />
<input type="hidden" name="THdbbase" value="" />
<input type="hidden" name="THdbuser" value="" />
<input type="hidden" name="THdbpass" value="" />
<input type="hidden" name="THdbpassver" value="" />';
	} else {
	echo '
	Database server: <input type="text" name="THdbserver" size="12" /><br />
	Database name: <input type="text" name="THdbbase" size="12" /><br />
	Database username: <input type="text" name="THdbuser" size="12" /><br />
	Database password: <input type="password" name="THdbpass" size="12" /><br />
	Verify password: <input type="password" name="THdbpassver" size="12" /><br />';
	}
?>
	<input type="hidden" name="configarray" value="<?php echo htmlspecialchars($configarray); ?>">
<input type="submit" value="Continue">
</form>

<?php } elseif($_GET['p']==4) {
//var_dump($_POST);
//pass our current info on to the next page
$configarray = unserialize(str_replace('\"','"',$_POST['configarray']));
$post = array('THdbprefix' => $_POST['THdbprefix'], 'THdbserver' => $_POST['THdbserver'], 'THdbuser' => $_POST['THdbuser'], 'THdbpass' => $_POST['THdbpass'], 'THdbbase' => $_POST['THdbbase']);
$configarray = array_merge($post,$configarray);
$configarray = serialize($configarray);
$check = unserialize($configarray);

	if($check['THdbtype']=="MySQL")
	{
		if(empty($_POST['THdbpass']))
		{
			die("Database password is not set.  If your database user does not have a password, you can not install this script without disabling this check.  This is extremely unsecure.");
		}
		if ($_POST['THdbpass'] != $_POST['THdbpassver'])
		{
			die("Passwords do not match.");
		}
		$link = @mysql_connect($_POST['THdbserver'], $_POST['THdbuser'],  $_POST['THdbpass']);
		if (!$link) {
		    die('Could not connect: ' . mysql_error() ."<br />Check your settings on the previous page.");
		}
		mysql_close($link);
	}

?>
<div class="logo">admin settings</div>
<form method="post" enctype="multipart/form-data" action="install.php?p=5">
In order to access the admin panel and do various moderation related tasks, you must set up a super user account.  This will be your main administrative account.<br/>
	Admin name: <input type="text" name="adminname" size="12" /><br />
	Admin pass: <input type="password" name="adminpass" size="12" /><br />
	Verify pass: <input type="password" name="adminpassver" size="12" /><br />
	<input type="hidden" name="configarray" value="<?php echo htmlspecialchars($configarray); ?>">
<input type="submit" value="Continue">
</form>
<?php } elseif($_GET['p']==5) { 
//var_dump($_POST);
		if ($_POST['adminpassver'] == $_POST['adminpass']) {
			if($_POST['adminpassver'] == "") {
				die("Administrator password is not set.");
			}
		} else {
			die("Passwords do not match.");
		}
//pass our current info on to the next page
$configarray = unserialize(str_replace('\"','"',$_POST['configarray']));
$post = array('adminpass' => $_POST['adminpass'], 'adminname' => $_POST['adminname']);
$configarray = array_merge($post,$configarray);
$configarray = serialize($configarray);

?>
<div class="logo">extra settings</div>
<form method="post" enctype="multipart/form-data" action="install.php?p=6">
Everything should work just fine if you leave these defaults in place, but in certain situations it may be
better for you to change it.  	<br /><br />
The following features require external libraries that we do not distribute or control. SVG support is
currently limited to ImageMagick.  The next release should allow you to select different programs (such as rsvg).<br />
Please see the documentation for more information about these settings.  If you're not sure, leave them blank.<br /><br />
	Path to PEAR <input type="text" name="THpearpath" size="12" /><br />
	Enable cURL support (for automated spam list filter) <input type="checkbox" name="THusecURL" checked><br />
	Enable SWF metatag support (requires PEAR libraries) <input type="checkbox" name="THuseSWFmeta" ><br />
	Enable SVG support (requires PEAR libraries and rsvg, ImageMagick, or other external library) <input type="checkbox" name="THuseSVG" ><br />
	<input type="hidden" name="configarray" value="<?php echo htmlspecialchars($configarray); ?>">
<input type="submit" value="Continue">
</form>
<?php } elseif($_GET['p']==6) { 
//var_dump($_POST);
//pass our current info on to the next page
$configarray = unserialize(str_replace('\"','"',$_POST['configarray']));
$post = array('THpearpath' => $_POST['THpearpath'], 'THusecURL' => $_POST['THusecURL'], 'THuseSWFmeta' => $_POST['THuseSWFmeta'], 'THuseSVG' => $_POST['THuseSVG']);
$configarray = array_merge($post,$configarray);
$configarray = serialize($configarray);
$check = unserialize($configarray);

?>
<div class="logo">confirm settings</div>
Here you can review your settings before they are written to the server.
If everything here looks good, go ahead and hit continue.<br><br>
<?php
	echo "Database type: ".$check['THdbtype']."<br>";
	echo "Database table prefix: ".$check['THdbprefix']."<br>";
	if($check['THdbtype']!="SQLite")
	{
		echo "Database server: ".$check['THdbserver']."<br>";
		echo "Database username: ".$check['THdbuser']."<br>";
		echo "Database name: ".$check['THdbbase']."<br>";
	}
	echo "Install location: ".$check['THpath']."<br>";
	echo "Install URL: ".$check['THurl']."<br>";

	echo "Administrator username: ".$check['adminname']."<br>";

	echo "PEAR path: ".$check['THpearpath']."<br>";
	echo "Enable cURL: ".$check['THusecURL']."<br>"; //THusecURL
	echo "Enable SWF metatags: ".$check['THuseSWFmeta']."<br>"; //THuseSWFmeta
	echo "Enable SVG support: ".$check['THuseSVG']."<br>"; //THuseSVG
?>
<br>
<form method="post" enctype="multipart/form-data" action="install.php?p=7">
<input type="hidden" name="configarray" value="<?php echo htmlspecialchars($configarray); ?>">
<input type="submit" value="Continue">
</form>
<?php } elseif($_GET['p']==7) {


	//Time to finish up with everything.
	$configarray = unserialize(str_replace('\"','"',$_POST['configarray']));
	echo "The configuration file is being written now, as well as any database interaction that needs 
		to be done.  If all has gone well up to this point, you can probably log in now by going to the
		<a href='".$configarray['THurl']."profiles.php?action=login'>login page</a> and using the administrator account you set up.  
		From there you should rebuild all items in the housekeeping menu.";
	
		$seed = mt_rand(0,100000); // I like the Mersenne Twister random number generation more.

		// lol.
		// It's 4:30 AM and I can't think of a better way to generate a random character string (generated via the Mersenne Twister algorithm), to be used for
		// salting passwords before they're hashed and entered into the DB.
		// so uh, this is pretty kludgy.  but it works.  16-character salt.
		//WELL IT DIDN'T WORK SO GUESS WHO HAD TO COME TO THE RESCUE THAT'S RIGHT IT WAS TYAM  :[
		$secret_salt = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c",
				mt_rand(40,126), mt_rand(40,126), mt_rand(40,126), mt_rand(40,126),
				mt_rand(40,126), mt_rand(40,126), mt_rand(40,126), mt_rand(40,126),
				mt_rand(40,126), mt_rand(40,126), mt_rand(40,126), mt_rand(40,126),
				mt_rand(40,126), mt_rand(40,126), mt_rand(40,126), mt_rand(40,126) );
		$cookieid = "dd".$seed;
		$configarray['adminpass'] = md5($secret_salt.$configarray['adminpass']);
		//Let's make the initial config file
		$sm=smsimple($configarray['THpath']);
		$sm->caching=0;
		$sm->compile_dir=$path."compd/";
		$sm->template_dir=$path."tpl/_admin/";
		$sm->cache_lifetime=0;
		$sm->assign("THtplurl",$configarray['THurl']."tpl/_admin/");
		//let's write to config
		if(touch($configarray['THpath']."config.php")==false) {
			die($configarray['THpath']."config.php cannot be written");
		} else {
			//write a quick config, this format isn't good, but hopefully they'll change something in the regular config and trigger a rewrite
			$config = fopen($configarray['THpath']."config.php", 'w');
			fwrite($config, '<?php'."\n");
			fwrite($config, 'define("THpath","'.$configarray['THpath'].'");'."\n");
			fwrite($config, 'define("THurl","'.$configarray['THurl'].'");'."\n");
			fwrite($config, 'define("THdbtype","'.$configarray['THdbtype'].'");'."\n");
			fwrite($config, 'define("THdbserver","'.$configarray['THdbserver'].'");'."\n");
			fwrite($config, 'define("THdbuser","'.$configarray['THdbuser'].'");'."\n");
			fwrite($config, 'define("THdbpass","'.$configarray['THdbpass'].'");'."\n");
			fwrite($config, 'define("THdbbase","'.$configarray['THdbbase'].'");'."\n");
			fwrite($config, 'define("THdbprefix","'.$configarray['THdbprefix'].'");'."\n");
			//oh god, i spent like 3 hours trying to set up a regex to match these, dont change them
			fwrite($config, 'define("THbanhistory_table","'.$configarray['THdbprefix']."banhistory".'");'."\n");
			fwrite($config, 'define("THbans_table","'.$configarray['THdbprefix']."bans".'");'."\n");
			fwrite($config, 'define("THblotter_table","'.$configarray['THdbprefix']."blotter".'");'."\n");
			fwrite($config, 'define("THboards_table","'.$configarray['THdbprefix']."boards".'");'."\n");
			fwrite($config, 'define("THcapcodes_table","'.$configarray['THdbprefix']."capcodes".'");'."\n");
			fwrite($config, 'define("THextrainfo_table","'.$configarray['THdbprefix']."extrainfo".'");'."\n");
			fwrite($config, 'define("THfilters_table","'.$configarray['THdbprefix']."filters".'");'."\n");
			fwrite($config, 'define("THimages_table","'.$configarray['THdbprefix']."images".'");'."\n");
			fwrite($config, 'define("THpages_table","'.$configarray['THdbprefix']."pages".'");'."\n");
			fwrite($config, 'define("THreplies_table","'.$configarray['THdbprefix']."replies".'");'."\n");
			fwrite($config, 'define("THreports_table","'.$configarray['THdbprefix']."reports".'");'."\n");
			fwrite($config, 'define("THthreads_table","'.$configarray['THdbprefix']."threads".'");'."\n");
			fwrite($config, 'define("THusers_table","'.$configarray['THdbprefix']."users".'");'."\n");
			fwrite($config, 'define("THsecret_salt","'.$secret_salt.'");'."\n");  //salt for passwords
			fwrite($config, 'define("THname","drydock image board");'."\n");
			fwrite($config, 'define("THtplset","drydock-image");'."\n");
			fwrite($config, 'define("THcookieid","'.$cookieid.'");'."\n");  //cookie seed
			fwrite($config, 'define("THcaptest", 0);'."\n");
			fwrite($config, 'define("THtpltest",0);'."\n");
			fwrite($config, 'define("THjpegqual",65);'."\n");
			fwrite($config, 'define("THdupecheck", 1);'."\n");
			fwrite($config, 'define("THtimeoffset",0);'."\n");
			fwrite($config, 'define("THvc", 2);'."\n");
			fwrite($config, 'define("THnewsboard",0);'."\n");
			fwrite($config, 'define("THmodboard",0);'."\n");
			fwrite($config, 'define("THdefaulttext","No text entered");'."\n");
			fwrite($config, 'define("THdefaultname","Anonymous");'."\n");
			fwrite($config, 'define("THdatetimestring","%m/%d/%y(%a)%H:%M:%S");'."\n");
			fwrite($config, 'define("THSVGthumbnailer",0);'."\n");
			//this is a mess, i won't fix it later so i won't promise to   ~tyam :]
			fwrite($config, "define(\"THuserewrite\", 0);\ndefine(\"THpearpath\",");
			fwrite($config, "\"".$configarray['THpearpath']."\"");
			fwrite($config, ");\ndefine(\"THuseSWFmeta\",");
			if($configarray['THuseSWFmeta']=="on"){fwrite($config, "1");}else{fwrite($config, "0");}
			fwrite($config, ");\ndefine(\"THuseSVG\",");
			if($configarray['THuseSVG']=="on"){fwrite($config, "1");}else{fwrite($config, "0");}
			fwrite($config, ");\ndefine(\"THusePDF\",0);");
			fwrite($config, "\ndefine(\"THusecURL\",");
			if($configarray['THusecURL']=="on"){fwrite($config, "1");}else{fwrite($config, "0");}
			fwrite($config, ");\n");
			fwrite($config, 'define("THprofile_adminlevel",9);'."\n");
			fwrite($config, 'define("THprofile_userlevel",1);'."\n");
			fwrite($config, 'define("THprofile_emailname","CHANGE THIS");'."\n");
			fwrite($config, 'define("THprofile_emailaddr","THIS IS NOT AN EMAIL");'."\n");
			fwrite($config, 'define("THprofile_emailwelcome",0);'."\n");
			fwrite($config, 'define("THprofile_cookietime",8640000);'."\n");
			fwrite($config, 'define("THprofile_cookiepath","/");'."\n");
			fwrite($config, 'define("THprofile_lcnames",0);'."\n");
			fwrite($config, 'define("THprofile_maxpicsize",512000);'."\n");
			fwrite($config, 'define("THprofile_regpolicy",1);'."\n");
			fwrite($config, 'define("THprofile_viewuserpolicy",1);'."\n");
			fwrite($config, '?>');
			fclose($config);  //file's closed, fwrites, etc
		} //} FOLDING IS FUN
		
		if($configarray['THdbtype']=="MySQL")
		{
			$link = mysql_connect($configarray['THdbserver'],$configarray['THdbuser'],$configarray['THdbpass']);
			@mysql_select_db($configarray['THdbbase']) or die;
		} else {
			$link = sqlite_open(str_replace("/install.php", "", $_SERVER['SCRIPT_FILENAME'])."/unlinked/drydock.sqlite", 0666, $sqliteerror);
		}
		writedb($link, $configarray['THdbtype'], $configarray['THdbprefix']);
		makeuser($path, $link, $configarray);
		if($configarray['THdbtype']=="MySQL") { mysql_close($link); } else { sqlite_close($link); }
		initial_builds($path, $configarray);
		unlink_placeholders($path);
}//p=7
else
{
echo "There appears to be a problem!";
}
?>
	</div>
</div>