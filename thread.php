<?php

/*
  drydock imageboard script (http://code.573chan.org/)
  File:			thread.php
  Description:	Script that receives form input for new threads.

  Unless otherwise stated, this code is copyright 2008
  by the drydock developers and is released under the
  Artistic License 2.0:
  http://www.opensource.org/licenses/artistic-license-2.0.php
 */

require_once("common.php");
require_once("post-common.php");

/*
  THINGS THAT WE EXPECT TO COME IN:

  $_POST['body'] (string for the post body)
  $_POST['subj'] (string for the post subject)
  $_POST['link'] (string for the link field)
  $_POST['nombre'] (string for the post name if forced_anon is off)
  $_POST['board'] (for the board id)
  $_POST['password'] (string for post deletion)

  THINGS THAT MIGHT ALSO COME IN:
  $_POST['vc'] (captcha string)
  $_POST['email'] (spambot string)
  $_FILES (for images)
  $_POST['pin']
  $_POST['lock']
  $_POST['permasage']

 */

/*
  //You can see how this would be used (and even expand it to other fields if you wish), but we're not using it right now. - tyam
  if (strlen($_POST['subj'])<3)
  {
  THdie("Subject field not long enough");
  }
 */
$mod = ($_SESSION['moderator'] || $_SESSION['admin']);  //quick fix
//var_dump($_POST);
//This should be for CAPTCHA
if (THvc == 1) {
    checkvc();
}
$db = new ThornPostDBI;
if ($db->checkban()) {
    THdie("PObanned");
}
$binfo = $db->getbinfo($db->getboardnumber($_POST['board']));

// Die if the board doesn't exist.
if ($binfo == null)
    die("Specified board does not exist.");

//check for banned keywords
if ($mod == false) {
    // First, flood protection
    $longip = ip2long($_SERVER['REMOTE_ADDR']);
    if ($db->postedwithintime($longip) == true) {
        THdie("You must wait a while before making another post.");
    }

    // This should have the cached version of banned keywords in an array named $spamblacklist.
    @include(THpath . '/unlinked/blacklist.php');
    //You could use any website, or even CENSORED or some other text.
    if (count($spamblacklist) > 0) {
/*
        $_POST['subj'] = str_replace($spamblacklist, "xxxxx", $_POST['subj']);
        $_POST['body'] = str_replace($spamblacklist, "xxxxx", $_POST['body']);
        $_POST['link'] = str_replace($spamblacklist, "xxxxx", $_POST['link']);
        $_POST['name'] = str_replace($spamblacklist, "xxxxx", $_POST['name']);
*/
	//The logic on this was entirely wrong.  If you want to filter out certain things, set up wordfilters.  This now (as of 12/16/12), stops the post.
	THdie("One of the phrases included in your post is not allowed.");
    }

    // The "email" field will have a big "IF YOU ARE HUMAN DO NOT FILL THIS IN" next to it.  Bots might get tricked.
    if (THvc == 2 && isset($_POST['email']) && $_POST['email'] != "") {
        // get out spambot >:[
        $redhammer = new ThornModDBI();
        $redhammer->banip($longip, 0, "Suspected bot.", "", "Suspected bot.", $_POST['body'], 4, "autoban");
        THdie("Abnormal reply"); // :getprophet:
    }

    // Prevent people from posting new threads if it's the mod or news board
    if ($binfo['id'] == THmodboard || $binfo['id'] == Thnewsboard) {
        THdie("POnonewth");
    }

    // Prevent people from posting to boards that require registration
    // when they're not logged in
    if ($binfo['requireregistration'] == true) {
        if ($_SESSION['username'] == false) {
            THdie("POnonewth");
        }
        // Set the posting username to be the user stored in our session info
        $_POST['nombre'] = $_SESSION['username'];
    }
}

if ($binfo['tlock'] == 1 && $mod == false) {
    THdie("POnonewth");
}

//File checking and processing here, I suppose.
$filemessages = array(); // Array of strings regarding "bad" files
$goodfiles = checkfiles($binfo, $filemessages);
//echo(count($goodfiles));
if ($binfo['tpix'] == 0 && count($goodfiles) > 0 && $mod == false) {
    THdie("POthnopix");
}
if ($binfo['tpix'] == 2 && count($goodfiles) == 0 && $mod == false) {
    THdie("POthmustpix");
}
if (count($goodfiles) == 0 && !$_POST['body'] && $mod == false) {  //oops, tyam moment
    THdie("You must post images or leave a comment.");
}
$pin = (int) ($_POST['pin'] == "on" && $mod);
$lock = (int) ($_POST['lock'] == "on" && $mod);
$permasage = (int) ($_POST['permasage'] == "on" && $mod);

$usethese = preptrip($_POST['nombre']);

if (preg_match("/^(mailto:)?noko$/", $_POST['link'])) {  //hide noko
    $datlink = "";
} else {
    $datlink = $_POST['link'];
}

$tnum = $db->putthread(
                $usethese['nombre'], $usethese['trip'], $binfo['id'], $_POST['subj'], $_POST['body'], $datlink, ip2long($_SERVER['REMOTE_ADDR']), $mod, $pin, $lock, $permasage, $_POST['password']);

movefiles($goodfiles, $tnum, true, $binfo, $db);

$sm = smsimple();
$sm->clear_cache(null, $board);
//$sm->clear_cache(null,"idx"); what
/* 	if (isset($_POST['tedit'])==true)
  {
  $sm->clear_cache(null,"t".$_POST['tedit']);
  } */

if ($binfo['tmax'] != 0 /* && isset($_POST['tedit'])==false */) { //Don't purge if max threads is set to 0
    delimgs($db->purge(intval(1)));
}
//Cookie setting stuff here
if ($_POST['mem'] == "on") {
    if ($_POST['nombre'] !== null) {
        setcookie(THcookieid . "-name", $_POST['nombre'], time() + THprofile_cookietime, THprofile_cookiepath);
    }
    if ($_POST['link'] != null) {
        setcookie(THcookieid . "-link", $_POST['link'], time() + THprofile_cookietime, THprofile_cookiepath);
    }
    setcookie(THcookieid . "-password", $_POST['password'], time() + THprofile_cookietime, THprofile_cookiepath);
}

// Initialize $location variable for HTTP redirects
$location = "drydock.php"; // Default
if ((preg_match("/^(mailto:)?noko$/", $_POST['link']))) { // noko check
    // Look up the global ID for this thread.
    $loc_arr = $db->getpostlocation($tnum);

    if (THuserewrite) {
        $location = THurl . $binfo['folder'] . "/thread/" . $loc_arr['thread_loc'];
    } else {
        $location = THurl . "drydock.php?b=" . $binfo['folder'] . "&amp;i=" . $loc_arr['thread_loc'];
    }
} else {
    if (THuserewrite) {
        $location = THurl . $binfo['folder'];
    } else {
        $location = THurl . "drydock.php?b=" . $binfo['folder'];
    }
}

// Popup.tpl does redirects now
$sm = sminit("popup.tpl");
$sm->assign("redirectURL", $location); // Set redirect URL from $location
if (count($filemessages) > 0) {
    // There was some sort of error encountered when attempting to upload a file
    // Build a string based on the errors
    $popuptext = "The following errors were encountered when attempting to upload files:<br /><ul>\n";
    foreach ($filemessages as $filemsg) {
        $popuptext = $popuptext . "<li>" . htmlspecialchars($filemsg) . "</li>\n";
    }
    $popuptext = $popuptext . '</ul><br />Click <a href="' . $location . '">here to proceed.';

    $sm->assign("text", $popuptext); // Stick into the template

    $sm->assign("timeout", 0); // No automatic redirect
} else {
    // No problems
    $sm->assign("text", "Updating page...");
    $sm->assign("timeout", 3); // Redirect after 3 seconds
}
$sm->display("popup.tpl");
?>
