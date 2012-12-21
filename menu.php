<div id="idxmenu">
<div id="idxmenuitem">
<div class="idxmenutitle">
<?php if(isset($_SESSION["admin"])){ echo "Administration Menu<br />"; } elseif(isset($_SESSION["moderator"])) { echo "Moderator Menu<br />"; } ?>
<?php if(isset($_SESSION["admin"])){ echo "
<a href=\"".THurl."admin.php?a=g\">Global Settings</a><br />
<a href=\"".THurl."admin.php?a=b\">Board Setup</a><br />
<a href=\"".THurl."admin.php?a=bl\">Blotter Posts</a><br />
<a href=\"".THurl."admin.php?a=sp\">Static Pages</a><br />
<a href=\"".THurl."admin.php?a=x\">Bans</a><br />
<a href=\"".THurl."admin.php?a=c\">Capcodes</a><br />
<a href=\"".THurl."admin.php?a=w\">Filters</a><br />
<a href=\"".THurl."admin.php?a=p\">Profile Admin</a><br />
<a href=\"".THurl."admin.php?a=hk\">Housekeeping</a><br />
<a href=\"".THurl."admin.php?a=lv\">Log Viewer</a><br />
<a href=\"".THurl."admin.php?a=t\">Recent Pics</a><br />
<a href=\"".THurl."admin.php?a=q\">Recent Posts</a><br />
<a href=\"".THurl."admin.php?a=r\">Reports</a><br />
<a href=\"".THurl."admin.php?a=l\">Lookup Tools</a><br /><br />";
} elseif(isset($_SESSION["moderator"])){
echo "
<a href=\"".THurl."recentpics.php\">Recent Pics</a><br />
<a href=\"".THurl."recentposts.php\">Recent Posts</a><br />
<a href=\"".THurl."reports.php\">Reports</a><br />
<a href=\"".THurl."lookups.php\">Lookup Tools</a><br /><br />";
} ?>
<a href="http://localhost/drydock/branches/0.3.2/">Site Index</a><br />
<?php if($_SESSION["username"]) {
echo "<a href=\"".THurl."profiles.php?action=logout\">Log Out</a> / <a href=\"".THurl."profiles.php\">Profiles</a>";
 } else {
echo "<a href=\"".THurl."profiles.php?action=login\">Login</a>
 / 
<a href=\"".THurl."profiles.php?action=register\">Register</a>";
}?>
</div></div></div>
