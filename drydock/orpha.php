<?php

/* 	Orphaned image deleter - a pile of garbage that only works in the sense that it will find the images that are orphaned and help you remove them
	Alternatively, CPKILLER.


	This needs to be rewritten in places and optimized.

	Based heavily (as in 95% of it) on Thornlight
*/

require_once("config.php");
require_once("common.php");
require_once("auth-common.php");
if(!$_SESSION['admin'] && !$_SESSION['moderator']) { THdie("Sorry, you do not have the proper permissions set to be here, or you are not logged in."); } else {
$db=new ThornDBI();
	
// SELECT COUNT(*) FROM 'img'
$count = $db->myresult("SELECT COUNT(*) FROM ".THimages_table);

$offset = 0;
$orpha=20; //how many on each page
if(isset($_GET['offset'])) {
	$offset = intval($_GET['offset']);
	
	if( $offset < 0 ) {
	$offset = 0;
	}
}
	
$beginning = $count - $orpha - $offset;

if( $beginning < 0 ){
	$beginning = 0;
}
//Beginning should never be greater than $count, for the reason that $offset is always >= 0
	
	$imagequery = "SELECT * FROM ".THimages_table." ORDER BY id ASC LIMIT $beginning , $orpha";
	echo "$imagequery<br />";

	$imgs=array();
	$queryresult=$db->myquery($imagequery);
	if($queryresult!=0)  //Did we return anything at all?
	{
		while ($img=mysql_fetch_assoc($queryresult))
		{
        		$imgs[]=$img;
		}
		foreach ($imgs as $key => $row)
		{
			$id[$key]  = $row['id'];
		}
		//if our other catch doesn't work (which half the time it doesnt) then let's catch it here.  sloppy :[  ~tyam
		@array_multisort($id, SORT_DESC, $imgs) or $bail=1;  
		$row =0;
		$column =0;
		$nomoreimages = 0;
	} else {
		$bail = 1; //this gives us a "no records found" message in the right place if it catches
	}//if empty return
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<title><?php echo THname;?> &#8212; Administration &#8212; ThornLight</title>
<link rel="stylesheet" type="text/css" href="<?php echo THurl.'tpl/'.THtplset;?>/futaba.css" title="Stylesheet" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
</head>
<body>	
<div id="main">
    <div class="box">
        <div class="pgtitle">
            ThornLight
        </div>
		<?php
if($bail==1) { echo "No images on record."; } else {
        if($count > $orpha)
        {
                echo '<table style="width: 100%;"><tr>';
                if($offset > 0){
                        $offsetback = $offset - $orpha;
                        if($offsetback < 0){
                        $offsetback = 0;
                        }
                        echo '<td class="lefthalf"><a href="orpha.php?offset='.$offsetback.'">&lt;&lt;</a></td>';
                } else {
                        echo '<td class="lefthalf">&lt;&lt;</td>';
                        }

                if($beginning > 0){
                        $offsetfwd = $offset += $orpha;
                        echo '<td class="righthalf"><a href="orpha.php?offset='.$offsetfwd.'">&gt;&gt;</a></td>';
                } else {
                        echo '<td class="righthalf">&gt;&gt;</td>';
                        }
                echo '</tr></table>';
        }
$wrapper=0;
//echo "<table><tr>";
$rmstring="";
$deletestring="";
echo '<div style="text-align: center;"><table BORDER="0" CELLPADDING="5" style="width: 90%;">';
while($nomoreimages == 0 && $row < 8)
{
	for($column=0;$column<4;$column++)
	{
		if( $nomoreimages == 1)
		{
			break;
		}
			$thisimage = $imgs[($row*4)+$column];
		
		if( $thisimage == null )
		{
			$nomoreimages = 1;
		} else {

			$threadquery = $db->myresult("SELECT id FROM ".THthreads_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");
			if( $threadquery == NULL )
			{
				$postquery = $db->myresult("SELECT thread FROM ".THreplies_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");

				if( $postquery == NULL )
				{
					$thread=0;
				} else {
					$thread=$postquery;
				}
			} else {
				$thread=$threadquery;
			}
			if ($thread != 0)
			{
				continue;
			}// thread not 0, so we're only seeing thread==0 files now

	//output

			echo '<td>';
			$threadquery = $db->myresult("SELECT board FROM ".THthreads_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");

			if( $threadquery == NULL ){
				$postquery = $db->myresult("SELECT board FROM ".THreplies_table." WHERE imgidx=".$thisimage['id']." ORDER BY id ASC");
				if( $postquery == NULL )
				{
					$board=0;
				} else {
					$board=$postquery;
				}
		 	} else {
				$board=$threadquery;
			}

			echo '<a class=info href="images/'.$thisimage['id'].'/'.$thisimage['name'].'">';

			if($thisimage['hash'] != "deleted")
			{
				echo '<img src="images/'.$thisimage['id'].'/'.$thisimage['tname'].'" border=0>';
			} else {
				echo '<img src="'.THurl.'static/file_deleted.png" alt="File Deleted" border=0 />';			
			}

			echo "</a>";
			//we already know this image is orphaned, so let's output its location and even a link to a deleter script
			echo '<br />';
			echo $wrapper.":";
			echo $wrapper%7;
			echo "; (<i>".$thisimage['fsize']." K, ".$thisimage['width']."x".$thisimage['height']."</i>)";
			$wrapper++;
			if($thisimage['anim'] > 0) { echo " (<i>A</i>)"; }
			$rmstring.='"images/'.$thisimage['id'].'/'.$thisimage['tname'].'" ';
			$rmstring.='"images/'.$thisimage['id'].'/'.$thisimage['name'].'" ';
			$deletestring.=" ID = ".$thisimage['id'];
		}
		if ($wrapper%6==0) { echo "</tr>\n\n<tr>"; }
		echo '</td>';
		$deletestring.=" OR ";

	//end output
	}
	$row++;
}
echo '</table></div>';
echo "rm ".$rmstring;
echo "<br /><br />";
echo "DELETE from ". THimages_table." where ".$deletestring;
//echo "DELETE from images where ";
	
	
$offset = 0;

if(isset($_GET['offset'])) {
        $offset = intval($_GET['offset']);

        if( $offset < 0 ) {
        $offset = 0;
        }
}

$beginning = $count - $orpha - $offset;

if( $beginning < 0 ){
        $beginning = 0;
}
	
       if($count > $orpha)
        {
                echo '<table style="width: 100%;"><tr>';
                if($offset > 0){
                        if($offsetback < 0){
                        $offsetback = 0;
                        }
                        echo '<td class="lefthalf"><a href="orpha.php?offset='.$offsetback.'">&lt;&lt;</a></td>';
                } else {
                        echo '<td class="lefthalf">&lt;&lt;</td>';
                        }

                if($beginning > 0){
                        echo '<td class="righthalf"><a href="orpha.php?offset='.$offsetfwd.'">&gt;&gt;</a></td>';
                } else {
                        echo '<td class="righthalf">&gt;&gt;</td>';
                        }
                echo '</tr></table>';
        }
}
?>		
</div>
</div>
<?php include("menu.php"); ?>
</body>
</html>
<?php } ?>
