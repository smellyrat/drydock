<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:          banners.php
		Description:	Random banner script (based on work by Matt Mullenweg)

						This basically will pull a single random banner file from the
						folder defined ("banderole" by default as per drydock dev team
						sense of humor) and "push" the image to the user, rather than
						just "pulling" it to the script.

		Unless otherwise stated, this code is copyright 2008
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/


	require_once("config.php");
	$folder = 'banderole/';
	$exts = 'jpg jpeg png gif';
	$files = array(); $i = -1;
	if ('' == $folder) $folder = './';

	$handle = opendir($folder);
	$exts = explode(' ', $exts);
	while (false !== ($file = readdir($handle)))
	{
		foreach($exts as $ext) // for each extension check the extension
		{
			if (preg_match('/\.'.$ext.'$/i', $file, $test)) // faster than ereg, case insensitive
			{
				$files[] = $file; // it's good
				++$i;
			}
		}
	}
	closedir($handle); // We're not using it anymore
	mt_srand((double)microtime()*1000000); // seed for PHP < 4.2
	$rand = mt_rand(0, $i); // $i was incremented as we went along
	echo "<img src=\"".THurl."$folder$files[$rand]\" alt=\"a hip and trendy banner image=\" />"; // Voila!
?>
