<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:           		logviewer.php
		Description:	This is used for admins to view logs.

		Unless otherwise stated, this code is copyright 2008
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/
	
	require_once("config.php");
	require_once("common.php");

	if(!$_SESSION['admin']) 
	{ 
		THdie("Sorry, you do not have the proper permissions set to be here, or you are not logged in."); 
	} 
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<link rel="stylesheet" type="text/css" href="<?php echo THurl.'tpl/'.THtplset;?>/futaba.css" title="Stylesheet" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo THurl; ?>js.js"></script>
<title><?php echo THname;?> &#8212; Administration &#8212; Log Viewer</title>
</head>
<body>	
<div id="main">
    <div class="box">
		<div class="pgtitle">
			Log Viewer
</div>
<br />
<?php	
	if( isset($_GET['log']) == false)
	{
		$valid_logs = array();
		
		if( $handle = opendir(THpath.'unlinked/') )
		{
			while (($file = readdir($handle)) !== false) 
			{
				// If it's a file and it ends in .log, WE CAN READ IT
				if(/*is_file($file) && */preg_match('/\.log$/i', $file))
				{
					$valid_logs[] = basename($file, '.log');
				}
			}
			closedir($handle);
		}
		echo "Available logs:<br />\n";
		echo "<ul>\n";
		foreach( $valid_logs as $logchoice )
		{
			echo '<li><a href="'.THurl.'logviewer.php?log='.$logchoice.'">'.$logchoice."</a></li>\n";
		}
		echo "</ul>\n";
		
	}
	else
	{
		$logname = THpath."unlinked/".trim($_GET['log']).".log";
		$logcontents = array();
		$logentries = array();
		$count = 0;			
	
		if(file_exists($logname) == true)
		{
			$logcontents = file($logname, FILE_SKIP_EMPTY_LINES);
			$count = sizeof($logcontents);
			$logentries = array_chunk( array_reverse($logcontents), 40, true);
			
			if(isset($_GET['offset']))
			{
				$chunk_to_use = intval($_GET['offset']) / 40;
			}
			else
			{
				$chunk_to_use = 0;
			}
			
			// Add navigation arrows, if the logs are big enough
			if($count > 40)
			{
					echo '<table width=100%><tr>';
					if($offset > 0)
					{
						$offsetback = $offset - 40;
						if($offsetback < 0)
						{
							$offsetback = 0;
						}
						echo '<td align=left width=50%><a href="logviewer.php?log='.$_GET['log'].'&offset='.$offsetback.'">&lt;&lt;</a></td>';
					} 
					else 
					{
						echo '<td align=left width=30%>&lt;&lt;</td>';
					}

					echo '<td align=center width=40%>Viewing '.trim($_GET['log']).' log</td>';
					
					if($beginning > 0)
					{
						$offsetfwd = $offset += 40;
						echo '<td align=right width=30%><a href="logviewer.php?log='.$_GET['log'].'offset='.$offsetfwd.'">&gt;&gt;</a></td>';
					} 
					else 
					{
						echo '<td align=right width=50%>&gt;&gt;</td>';
					}
					echo '</tr></table>';
			}
			
			echo '<div align="left"><pre>';
			
			if( sizeof($logentries[$chunk_to_use]) == 0 )
			{
				echo "No log entries on record.";
			}
			else
			{
				// We could format this later.
				foreach($logentries[$chunk_to_use] as $logentry)
				{
					echo $logentry;
				}
			}
			
			echo '</pre></div>';
		}
		else
		{
			THdie("Log '".$logname." does not exist!");
		}
	}

?>	
</div>
</div>
<?php include("menu.php"); ?>	
</body>
</html>
