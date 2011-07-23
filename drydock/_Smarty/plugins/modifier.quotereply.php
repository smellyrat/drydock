<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			modifier.quotereply.php
		Description:	Provides >>1 quote reply features


		step 1:	parse the incoming text (expected format /&gt;&gt;\040?([0-9]{1,6})/)
				split the >> from the [numbers] (this is done with the subexpression in the regexp)
		step 2:	look up that number in posts&board.
				if it exists, look up it's thread
					form link
				if it doesnt exist, look it up in thread table
					if that exists, link it
				if neither of those are true, it's a nonexistant >>quotereply
		step 3:	link formation:
					if op drydock/board/thread/[numbers]
					if reply drydock/board/thread/op#[numbers]
	
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/

/**
 * Look up a quotereply link, and convert it to the necessary anchor
 * tag equivalent if necessary.
 * 
 * @param int $bid The board ID
 * @param string $bfold The board folder name
 * @param int $threadid The (unique) ID for this thread
 * @param int $threadglob The global ID for this thread
 * @param int $pulledvalue The parsed value (i.e. would be 2 for ">>2")
 * 
 * @return string The anchor link (or just "&gt;&gt;$pulledvalue" if it was invalid)
 */
function lookup_qr_link($bid, $bfold, $threadid, $threadglob, $pulledvalue)
{
	$db = new ThornPostDBI();
	
	// Default to not finding anything of use.
	$link = $link = "&gt;&gt;".$pulledvalue;
	
	// This abstracts so much stuff for us.
	$postinfo = $db->getsinglepost($pulledvalue, $bid);
	
	if( $postinfo != null )
	{
		if( $postinfo['thread'] == 0) // This is a thread
		{
			// Just add an anchor tag and go
			if(THuserewrite)
			{	
				$link = '<a href="'.THurl.$bfold."/thread/".$pulledvalue.'#'.$pulledvalue.'">&gt;&gt;'.$pulledvalue."</a>";
			}
			else
			{
				$link = '<a href="'.THurl.'drydock.php?b='.$bfold.'&amp;i='.$pulledvalue.'#'.$pulledvalue.'">&gt;&gt;'.$pulledvalue."</a>";
			}
		}
		else // This is a reply
		{
			if( $postinfo['thread'] == $threadid ) // Reply in this thread
			{
				if(THuserewrite)
				{
					$link = "<a href=\"".THurl.$bfold."/thread/".$threadglob."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
				}
				else
				{
					$link = "<a href=\"".THurl.'drydock.php?b='.$bfold.'&amp;i='.$threadglob."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
				}			
			}
			else // Cross-thread reply
			{
				// We need to look up the global ID.
				$threadinfo = $db->gettinfo($postinfo['thread']);
				
				// Was it a valid lookup?
				if( $threadinfo != null )
				{
					$threadop = $threadinfo['globalid'];
					
					if(THuserewrite)
					{
						$link = "<a href=\"".THurl.$bfold."/thread/".$threadop."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
					}
					else
					{
						$link = "<a href=\"".THurl.'drydock.php?b='.$bfold.'&amp;i='.$threadop."#".$pulledvalue."\">&gt;&gt;".$pulledvalue."</a>";
					}
				}
			}
		}
	}

	return $link;
}

/**
 * Quotereply! (i.e. turn >>573 into a link to post 573)
 * 
 * @param string $text The text to modify
 * @param array $binfo The board information
 * @param array $post The post information
 * @param array $thread The thread information
 * 
 * @return string The modified post
 */
function smarty_modifier_quotereply($text, $binfo, $post, $thread)
{
	$search = '/&gt;&gt;\040?([0-9]{1,6})/e';
	return preg_replace($search, "lookup_qr_link( ".$binfo['id'].",".$binfo['folder']." ,".$thread['id'].",".$thread['globalid'].",'\\1')", $text);
	//return preg_replace($search, "lookup_qr_link( ".$binfo['id'].",".$binfo['folder']." ,".$thread['id'].",".$thread['globalid'].",'\\1')", $text);
	
}

?>

