<?php

/*
  drydock imageboard script (http://code.573chan.org/)
  File:			profiles.php
  Description:	Profile management script.  IT DOES IT ALL

  Unless otherwise stated, this code is copyright 2008
  by the drydock developers and is released under the
  Artistic License 2.0:
  http://www.opensource.org/licenses/artistic-license-2.0.php
 */

require_once ("config.php");
require_once ("common.php");

function renderPermissionDenied() {
$sm = sminit("nopermission.tpl", null, "profiles", false, false);
$sm->display("nopermission.tpl", null);
}

function renderError($errmsg) {
$sm = sminit("error.tpl", null, "profiles", false, false);
$sm->assign("errmsg", $errmsg);
$sm->display("error.tpl", null);
}

session_start();

if (isset($_POST['remember'])) {
setcookie(THcookieid . "-uname", $_SESSION['username'], time() + THprofile_cookietime, THprofile_cookiepath);
setcookie(THcookieid . "-id", $_SESSION['userid'], time() + THprofile_cookietime, THprofile_cookiepath);
}

if (!isset($_GET['action'])) {
$_GET['action'] = '';
}

$db = new ThornProfileDBI();

if ($_GET['action'] == "login") {

$sm = sminit("login.tpl", null, "profiles", false, false);

// Three POST parameters:
// $_POST['name'], $_POST['password'], $_POST['remember']

if (isset($_POST['name']) && isset($_POST['password'])) {
$userdata = $db->getuserdata_login($_POST['name'], $_POST['password']);

if ($userdata != NULL) {
$_SESSION['username'] = $userdata['username'];
$_SESSION['userid'] = generateRandID();
$_SESSION['userlevel'] = $userdata['userlevel'];
$_SESSION['admin'] = $userdata['mod_admin'];
$_SESSION['mod_array'] = $userdata['mod_array'];
$_SESSION['mod_global'] = $userdata['mod_global'];
if ($userdata['mod_global'] || $userdata['mod_array']) {
$_SESSION['moderator'] = true;
}

// Update userid field
$db->updateuser($_POST['name'], $_SESSION['userid']);
} else {
// Login error - show that in the template
$sm->assign("loginerror", 1);
}
}

//This checks to see if end user has even bothered to change the default email.  No use giving a link to something that won't work.  ~tyamzzz
if (THprofile_emailaddr != "THIS IS NOT AN EMAIL") {
$sm->assign("showreset", 1);
}

if (isset($_SESSION['username'])) {
// Set logged-in vars
$sm->assign("loggedin", 1);
$sm->assign("username", $_SESSION['username']);
}

$sm->display("login.tpl", null);
} else
if ($_GET['action'] == "logout") {

$sm = sminit("logout.tpl", null, "profiles", false, false);

if (isset($_SESSION['username'])) {
if (isset($_COOKIE[THcookieid . '-uname']) && isset($_COOKIE[THcookieid . '-id'])) {
setcookie(THcookieid . "-uname", "", time() - THprofile_cookietime, THprofile_cookiepath);
setcookie(THcookieid . "-id", "", time() - THprofile_cookietime, THprofile_cookiepath);
}

/* Unset PHP session variables */
unset($_SESSION['username']);
unset($_SESSION['userid']);
unset($_SESSION['userlevel']);
unset($_SESSION['admin']);
unset($_SESSION['moderator']);
unset($_SESSION['mod_array']);
} else {
// Not logged in, weird.
$sm->assign("notloggedout", 1);
}

$sm->display("logout.tpl", null);
} else
if ($_GET['action'] == "memberlist") {
$can_access = 0;

if (THprofile_viewuserpolicy == 2) {
$can_access = 1;
} elseif (THprofile_viewuserpolicy == 1 && isset($_SESSION['username'])) {
$can_access = 1;
} elseif (THprofile_viewuserpolicy == 0 && ($_SESSION['admin'] || $_SESSION['moderator'])) {
$can_access = 1;
}

if ($can_access) {

$sm = sminit("memberlist.tpl", null, "profiles", false, false);
$sm->assign("users", $db->getuserlist());
$sm->display("memberlist.tpl", null);
} else {
renderPermissionDenied();
}
} else
if ($_GET['action'] == "viewprofile") {
if (!isset($_GET['user'])) {
die("You must specify a user!");
}

if (THprofile_lcnames) {
$username = strtolower($_GET['user']);
} else {
$username = $_GET['user'];
}

$user = $db->getuserdata($username);

if (!$user) {
die("Invalid user specified!");
}

$can_access = 0;

if (THprofile_viewuserpolicy == 2) {
$can_access = 1;
} elseif (THprofile_viewuserpolicy == 1 && isset($_SESSION['username'])) {
$can_access = 1;
} elseif (THprofile_viewuserpolicy == 0 && ($_SESSION['admin'] || $_SESSION['moderator'])) {
$can_access = 1;
}

if ($can_access) {
$sm = sminit("viewprofile.tpl", null, "profiles", false, false);
$sm->assign("user", $user);
$sm->assign("caneditprofile", $db->caneditprofile($user['username']));
$sm->assign("isadmin", isset($_SESSION['admin']) && $_SESSION['admin']);
if ($user['capcode']) {
$sm->assign("capcode", $db->getusercapcode($user['capcode']));
} else {
$sm->assign("capcode", null);
}
$sm->display("viewprofile.tpl", null);
} else {
renderPermissionDenied();
}
} else
if ($_GET['action'] == "edit") {

$imgErrString = ""; // This only gets set if there is a problem
$passErrString = ""; // This only gets set if there is a problem with the password

if (!isset($_GET['user'])) {
renderError("You must specify a user!");
}

if (THprofile_lcnames) {
$username = strtolower($_GET['user']);
} else {
$username = $_GET['user'];
}

if (!$db->userexists($username)) {
renderError("Invalid user specified!");
}

if (!$db->caneditprofile($username)) {
renderPermissionDenied();
}

if (isset($_POST['edit_update'])) {
$user = $db->getuserdata($username);

if (isset($_POST['capcode']) && $user['capcode'] != "") {
$capcode = $db->getusercapcode($user['capcode']);

// Don't bother with the approval process if it's identical to the capcode 
// that's already been approved
if ($capcode != $_POST['capcode']) {
$db->proposeusercapcode($username, $capcode);
}
}

if (isset($_POST['age'])) {
$age = htmlentities(substr(trim($_POST['age']), 0, 3));
} else {
$age = $user['age'];
}

if (isset($_POST['gender'])) {
$gender = htmlentities(substr(trim($_POST['gender']), 0, 1));
} else {
$gender = $user['gender'];
}

if (isset($_POST['location'])) {
$location = htmlentities(substr(trim($_POST['location']), 0, 256));
} else {
$location = $user['location'];
}

if (isset($_POST['contact'])) {
$contact = htmlentities(substr(trim($_POST['contact']), 0, 256));
} else {
$contact = $user['contact'];
}

if (isset($_POST['description'])) {
$description = htmlentities(substr(trim($_POST['description']), 0, 512));
} else {
$description = $user['description'];
}

// Only users can edit their own passwords-while admins can edit just about anything else
if (isset($_POST['password']) && $_SESSION['username'] == $username && isset($_POST['changepass'])) {
$password = $_POST['password'];

$passlength = strlen($password);

if ($passlength < 4) {
$passErrString = "Sorry, your password must be at least 4 characters.<br />\n";
} else {
// Everything checked out, so update.
$db->setuserpass($username, $password);
}
}

$picture_ext = $user['has_picture'];

if (isset($_POST['remove_picture'])) {
if ($picture_ext != null) {
unlink(THpath . "images/profiles/" . $username . $ext);
}

$picture_ext = "";
}

$picture_pending = $user['pic_pending'];

if ($_FILES['picture']['error'] == 0 && $_FILES['picture']) {

if ($picture_pending) {
$imgErrString .= "Picture already pending admin approval.<br />\n";
}

if ($_FILES['picture']['size'] > THprofile_maxpicsize) {
$imgErrString .= "Picture must be no larger than " . THprofile_maxpicsize . " bytes.<br />\n";
}

//check the MIME type, not the extention - tyam
if ($_FILES['picture']['type'] == "image/jpeg") {
$filetype = "jpg";
} elseif ($_FILES['picture']['type'] == "image/gif") {
$filetype = "gif";
} elseif ($_FILES['picture']['type'] == "image/png") {
$filetype = "png";
}

if ($_FILES['picture'] &&!in_array($filetype, array(
"jpg",
 "png",
 "gif"
))) {
$imgErrString .= "Picture must be a JPG, PNG, or GIF.<br />\n";
}

if ($filetype == "jpg") {
$theimg = imagecreatefromjpeg($_FILES['picture']['tmp_name']);
} elseif ($filetype == "png" && is_callable("imagecreatefrompng")) {
$theimg = imagecreatefrompng($_FILES['picture']['tmp_name']);
} elseif ($filetype == "gif" && is_callable("imagecreatefromgif")) {
$theimg = imagecreatefromgif($_FILES['picture']['tmp_name']);
}

if ($theimg == null) {
$imgErrString .= "Unknown error.<br />\n";
} else {
$orig_width = imagesx($theimg);
$orig_height = imagesy($theimg);

// Resize if necessary
if ($_FILES['picture'] && ($orig_height > 500 || $orig_height > 500)) {
//Thumbnail code.
//Man, this code is a female canine. (Good thing I took this from post-common :])
if ($orig_height > $orig_height) {
$targh = 500;
$targw = (500 / $orig_height) * $orig_width;
if ($targw > 500) {
$ratio = 500 / $targw;
$targw = 500;
$targh = $targh * $ratio;
}
} else {
$targw = 500;
$targh = (500 / $orig_width) * $orig_height;
if ($targh > 500) {
$ratio = 500 / $targh;
$targh = 500;
$targw = $targw * $ratio;
}
} //if width>height

$targw = round($targw);
$targh = round($targh);

$resized_image = imagecreatetruecolor($targw, $targh);
imagecopyresampled($resized_image, $theimg, 0, 0, 0, 0, $targw, $targh, $orig_width, $orig_height);
if ($filetype == "png" || $filetype == "gif") {
imagepng($resized_image, $_FILES['picture']['tmp_name']);
} else {
imagejpeg($resized_image, $_FILES['picture']['tmp_name'], THjpegqual);
}
}
}

// Has everything gone okay so far?
if ($imgErrString == "") {
$picpath = THpath . 'unlinked/' . $username . "." . $filetype;

if ($_FILES['picture'] &&!move_uploaded_file($_FILES['picture']['tmp_name'], $picpath)) {
// Error moving the file where it was supposed to be, so don't update the DB
$imgErrString .= "Unknown error.<br />\n";
}
}

// The reason this check is here is because if move_uploaded_file fails 
// $imgErrString gets set to a non-null value
if ($imgErrString == "") {
$picture_pending = $filetype; // guess it worked
}
}

$db->updateuserinfo($username, $age, $gender, $location, $contact, $description, $picture_ext, $picture_pending);

$actionstring = "Profile edit\tprofile:" . $username;
writelog($actionstring, "profiles");
} // end of if isset($_POST['edit_update'])
//
                // Reload the user data
$user = $db->getuserdata($username);

$sm = sminit("editprofile.tpl", null, false, false);
$sm->assign("user", $user);
$sm->assign("sessUsername", $_SESSION['username']);
$sm->assign("maxProfImgSize", THprofile_maxpicsize);

// For errors involving changing of password or uploading of image
if ($imgErrString != "") {
$sm->assign("imgErrString", $imgErrString);
}
if ($passErrString != "") {
$sm->assign("passErrString", $passErrString);
}

// If user has been granted a capcode by the admins, they can specify how to display their name
if ($user['capcode']) {
$capcode = $db->getusercapcode($user['capcode']);
if ($capcode)
$sm->assign("capcode", $capcode);
}
}

$sm->display("editprofile.tpl", null);
}

else
if ($_GET['action'] == "register") {

if (isset($_SESSION['username'])) {
renderError("But you are logged in!");
}

if (THprofile_regpolicy == 0) {
renderError("Registration disabled.");
}

$success = 0;
$errorstring = "";

if (isset($_POST['user'])) {
if (THprofile_lcnames) {
$username = strtolower(trim($_POST['user']));
} else {
$username = trim($_POST['user']);
}

$password = trim($_POST['password']);
$email = trim($_POST['email']);

// you can change this however you wish.
$reserved_words = array(
"admin",
 "guest",
 "root",
 "banned",
 "moderator",
 "mod",
 "administrator",
 "trendster",
 "trendy"
);

$nameexists = $db->userexists($username);

foreach ($reserved_words as $reserved) {
if (stripos($username, $reserved) !== false || $nameexists) {
$errorstring .= "Sorry, an account with this name already exists.<br />\n";
break;
}
}

$namelength = strlen($username);
if ($namelength < 4 || $namelength > 30) {
$errorstring .= "Sorry, your name must be between 4 and 30 characters.<br />\n";
}

if (!preg_match('/^([\w\.])+$/i', $username)) {
$errorstring .= "Sorry, your name must be alphanumeric and contain no spaces.<br />\n";
}

if ($password) {
$passlength = strlen($password);
if ($passlength < 4) {
$errorstring .= "Sorry, your password must be at least 4 characters.<br />\n";
}
} else {
$errorstring .= "You must provide a password!<br />\n";
}

if (isset($_POST['email']) && strlen($email)) {

/* Check if valid email address */
if (!validateemail($email)) { // Provided in common.php
$errorstring .= "You must provide a valid email address!<br />\n";
}

if ($db->emailexists($email) == true) {
$errorstring .= "That email has already been used to register an account!<br />\n";
}
} else {
$errorstring .= "You must provide an email address!<br />\n";
}

if ($errorstring == "") {
// No errors encountered so far, attempt to register

if (THprofile_regpolicy == 1) {
$initial_status = 0; // pending admin approval
} else {
$initial_status = 1; // automatically approved
}

$actionstring = "Register\tname:" . $username;
writelog($actionstring, "profiles");

// I believe this returns non-null on a successful query, so...
$fail = $db->registeruser($username, $password, THprofile_userlevel, $email, $initial_status);
if ($fail == null) {
$errorstring .= "Database error.<br />\n";
} else {
$success = 1;
}
}
}

$sm = sminit("register.tpl", null, "profiles", false, false);
$sm->assign("success", $success);
$sm->assign("errorstring", $errorstring);
$sm->assign("regpolicy", THprofile_regpolicy);
$sm->assign("emailwelcome", THprofile_emailwelcome);

// Set the username for the Smarty template if we succeeded
if ($success == 1) {
$sm->assign("username", $username);

// Send an email if the registration is immediately valid
if (THprofile_regpolicy == 2 && THprofile_emailwelcome) {
sendWelcome($username, $email);
}
}

$sm->display("register.tpl", null);
} else
if ($_GET['action'] == "forgotpass") {
if (isset($_SESSION['username'])) {
renderError("But you are logged in!");
}

$sm = sminit("forgotpass.tpl", null, "profiles", false, false);

if (isset($_POST['user'])) {

// Let Smarty know we're submitting
$sm->assign("submitting", 1);

if (THprofile_lcnames) {
$username = strtolower($_POST['user']);
} else {
$username = $_POST['user'];
}

if (!$db->userexists($username)) {
renderError("Invalid user specified!");
}

$user = $db->getuserdata($username);

// Make sure the provided email address matches the profile name
if (!isset($_POST['email']) || (strtolower($_POST['email']) != strtolower($user['email']))) {

// ...nope, didn't match
$actionstring = "Failed pass reset\tprofile:" . $username;
writelog($actionstring, "profiles");

$sm->assign("mismatch", 1);
} else {
$pass = generateRandStr(8);

$actionstring = "Forgot pass\tprofile:" . $username;
writelog($actionstring, "profiles");

// This way, it will only send an email if the password reset was actually successful
if ($db->setuserpass($username, $pass)) {
sendnewpass($_POST['user'], $user['email'], $pass, $_SERVER['REMOTE_ADDR']);
} else {
// Set error condition
$sm->assign("error", 1);
}
}
}

$sm->display("forgotpass.tpl", null);
} elseif ($_GET['action'] == "permissions") {
if (!isset($_GET['user'])) {
renderError("You must specify a user!");
}

if (THprofile_lcnames) {
$username = strtolower($_GET['user']);
} else {
$username = $_GET['user'];
}

if (!$db->userexists($username)) {
renderError("Invalid user specified!");
}

// Adding one more requirement to canEditProfile: the user has to be an admin (canEditProfile will return true if it is the user's own profile)
if (!$db->caneditprofile($username) ||!$_SESSION['admin']) {
renderPermissionDenied();
}

// We init this up here so that we can add error messages as necessary
$sm = sminit("permissions.tpl", null, "profiles", false, false);

$user = $db->getuserdata($username);
$boards = $db->getboard(); // no parameters means all boards

if (isset($_POST['permsub'])) {
if ($_POST['admin']) {
$admin = 1;
} else {
$admin = 0;
}

if ($_POST['moderator']) {
$moderator = 1;
} else {
$moderator = 0;
}

$mod_array = "";
foreach ($boards as $board_to_mod) {
// This mod_array string will be a comma-separated list of board numbers
if ($_POST['mod_board_' . $board_to_mod['id']]) {
if ($mod_array == "") {
$mod_array = $board_to_mod['id'];
} else {
$mod_array = $mod_array . "," . $board_to_mod['id'];
}
}
}

// Basically how this works is, the benevolent admin, in his/her infinite kindness, will grant some
// users the ability to use capcodes.  How this happens is first, the admin will enter in the grantee's hash
// into the capcode field.  From then on, the user will be able to customize how his/her capcode will appear
// (pending admin approval, of course), due to the fact that any changes made and approved will automatically
// be tied into the tripcode hash.  This allows users to change their capcode without an admin having to
// manually edit the capcodes table or fiddle around with the admin panel.  The user edits their profile to taste,
// and all the admin has to do is click "Approve".
// See, I like this because it makes it easier for non-admins to have capcodes.
if ($_POST['remove_capcode']) {
$capcode = "";
} elseif ($_POST['capcode']) {
$capcode = $_POST['capcode'];
}

if ($_POST['userlevel']) {
// Can the user even set their userlevel that high?
if ($_SESSION['userlevel'] >= intval($_POST['userlevel'])) {
$userlevel = intval($_POST['userlevel']);
} else {
$sm->assign("userlevelerror", 1);
$userlevel = $user['userlevel']; // reset it to normal
}
} else {
$userlevel = $user['userlevel']; // nothing changed
}

$db->updateuserpermissions($username, $admin, $moderator, $userlevel, $mod_array, $capcode);

$actionstring = "Permissions\tprofile:" . $username;
writelog($actionstring, "profiles");
}

$user = $db->getuserdata($username); // reload user info
// Assign standard Smarty stuff
$sm->assign("user", $user);
$sm->assign("boards", $boards);

$sm->display("permissions.tpl", null);
}
//this function is really dangerous because if someone doesn't use this correctly 
//they can lock themselves out of admin stuff completely, unless they have access
//to phpmyadmin.  BE CAREFUL WITH THIS, END-USER >:[                   Love, tyam
elseif ($_GET['action'] == "remove") {

if (!isset($_GET['user'])) {
renderError("You must specify a user!");
}
if (THprofile_lcnames) {
$username = strtolower($_GET['user']);
} else {
$username = $_GET['user'];
}

if (!$db->userexists($username)) {
renderError("Invalid user specified!");
}
// Only admins can do this.
if (!$_SESSION['admin']) {
renderInvalidPermissions();
}

//Don't delete yourself.
if ($_SESSION['username'] == $username) {
renderError("You cannot lock yourself out!");
}

$db->suspenduser($username);

$actionstring = "Remove\tprofile:" . $username;
writelog($actionstring, "profiles");

$sm = sminit("remove.tpl", null, "profiles", false, false);
$sm->assign("username", $username);
$sm->display("remove.tpl", null);
} else {

// Fall-through case - just show all the available options

$canSeeMemberlist = 0;

//is member list available?
if ((THprofile_viewuserpolicy == 0) &&
($_SESSION['admin'] || $_SESSION['moderator'] || $_SESSION['mod_array'])) { //Mods only
$canSeeMemberlist = 1;
} elseif ((THprofile_viewuserpolicy == 1) && ($_SESSION['username'])) { //Only logged in users
$canSeeMemberlist = 1;
} elseif (THprofile_viewuserpolicy == 2) { //Anyone
$canSeeMemberlist = 1;
}

$sm = sminit("menu.tpl", null, "profiles", false, false);
$sm->assign("sessUsername", $_SESSION['username']);
$sm->assign("regpolicy", THprofile_regpolicy);
$sm->assign("canSeeMemberlist", $canSeeMemberlist);

$sm->display("menu.tpl, null");
}
?>