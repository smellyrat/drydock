<?php
	session_cache_expire(10);
	session_start();
	if (isset($_SESSION['vc'])==false)
	{
		die("U");
	}
	elseif ($_GET['c']==$_SESSION['vc'])
	{
		die("Y");
	}
?>