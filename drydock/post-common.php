<?php
	/*
		drydock imageboard script (http://code.573chan.org/)
		File:			post-common.php
		Description:	Common functions for posting.
		
		Unless otherwise stated, this code is copyright 2008 
		by the drydock developers and is released under the
		Artistic License 2.0:
		http://www.opensource.org/licenses/artistic-license-2.0.php
	*/

    if(THpearpath) // PEAR PEAR LOL
    {
        ini_set( 'include_path', ini_get( 'include_path' ) . PATH_SEPARATOR . THpearpath);
        //if the user somehow manages to enable these without having the libraries (it should error on enabling if they
        //don't have them), we need to stop them from being included
        if(THuseSWFmeta)
		{
			require_once("File/File_SWF.php");
		}
        if(THuseSVG)
		{
			require_once("HTML/HTML_Safe/Safe.php");
		}
		if(THusePDF > 2)
		{
			require_once('unlinked/pdf/fpdf.php');
		}
    }    
        
    /**
     * Check a verification code, possibly messing
     * with the session data if necessary.
     */
    function checkvc()
    {
		require_once('recaptchalib.php');
		$resp = recaptcha_check_answer (reCAPTCHAPrivate,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) {
			// What happens when the CAPTCHA was entered incorrectly
			THdie ("Wrong CAPTCHA.");
		} 
	}
/*
        //Disabling VCs at code level - probably need to do this from inside config :[[[
        //return(true);
        //Check the verification code.
        session_start();
        if (isset($_SESSION['vc'])==false) 
        {
            THdie("VCbad");
        }
        elseif ($_POST['vc']!=$_SESSION['vc'])
        {
            if (THcaptest==false)
            {
                session_destroy();
            }
            THdie("VCbad");
        }
        if (THcaptest==false)
        {
            session_destroy();
        }
    }
 */  
 	/**
 	 * Validate things from $_FILES and add them into an array if they meet our
 	 * basic requirements (file format, file size, not duplicates, etc).  Note
 	 * that this function will attempt to check the "magic numbers" of a file
 	 * rather than just a simple file extension check.
 	 * 
	 * @param array $binfo A reference to an assoc-array containing board information
	 * @param array $errs A reference to an array of strings; populate with error messages for each file
 	 * 
 	 * @return array An array of files which are, at least at this stage "valid".
 	 */
    function checkfiles(&$binfo, &$errs) 
    {
        //Are the files we're uploading okay?
        global $db;
        //Ug. I hate using global.
        $goodfiles=array();
        $hashes=array();
        for ($x=0;$x<$binfo['pixperpost'];$x++)
        {
            $dis="file".$x;
            if ($_FILES[$dis]>0)
            {
                if ($_FILES[$dis]['size']>$binfo['maxfilesize'])
                {
					$errs[] = $_FILES[$dis]['name'] . " is too big (> ".$binfo['maxfilesize']." bytes)";
                   	continue;
                } 
				else 
				{  
                    $dotpos=strrpos($_FILES[$dis]['name'],".");
                    if ($dotpos && $_FILES[$dis]['name']{0}!="." && strpos($_FILES[$dis]['name'],"/")===false)
                    {
                        $pin=pathinfo($_FILES[$dis]['name']);
                        $ext=strtolower($pin['extension']);
                        if ($ext=="jpg")
                        {
                            $ext="jpeg";
                        }
                        if(bitlookup($ext) & $binfo['allowedformats'])  //LOL BITWISE ARITHMETIC
                        {
							// Let's verify using MAGIC NUMBERS
							// my reference for this is:
							// http://www.garykessler.net/library/file_sigs.html
							// We'll do this for everything but SVGs.
							$check_pointer = fopen($_FILES[$dis]['tmp_name'], "rb");
							
                            if ($ext=="svg" && THuseSVG && THpearpath)
                            {
                                if( $svgdom = DOMDocument::load($_FILES[$dis]['tmp_name']) ) // ARE YOU AN IMPOSTER?
                                {
                                    $svgelements=$svgdom->getElementsByTagName("svg");
                                    if ( $svgelements->item(0) == null )
									// Didn't find an SVG element, so it's not a valid file.
									{
										$errs[] = $_FILES[$dis]['name'] . " is a malformed SVG file";
                   						continue;
									}
                                }
                            }
                            else if( in_array( $ext, array("jpeg", "jpg", "gif", "png")))
                            {
								if ($ext=="jpeg" || $ext=="jpg") 
						        {
						            $theimg=imagecreatefromjpeg($_FILES[$dis]['tmp_name']);
									
									if($check_pointer == true)
									{
										// It starts differing after 3 characters, but let's see if this will work for now.
										if(fread($check_pointer, 3) != "\xFF\xD8\xFF")
										{
											$errs[] = $_FILES[$dis]['name'] . " is a malformed JPEG file";
                   							continue;
										}
									}
						        }
						        elseif ($ext=="png" && is_callable("imagecreatefrompng")) 
						        {
						            $theimg=imagecreatefrompng($_FILES[$dis]['tmp_name']);
									
									if($check_pointer == true)
									{
										if(fread($check_pointer, 8) != "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A")
										{
											$errs[] = $_FILES[$dis]['name'] . " is a malformed PNG file";
                   							continue;
										}
									}
						        }
						        elseif ($ext=="gif" && is_callable("imagecreatefromgif")) 
						        {
						            $theimg=imagecreatefromgif($_FILES[$dis]['tmp_name']);
									
									if($check_pointer == true)
									{
										// It starts differing after 3 characters, but let's see if this will work for now.
										if(fread($check_pointer, 3) != "GIF")
										{
											$errs[] = $_FILES[$dis]['name'] . " is a malformed GIF file";
                   							continue;
										}
									}
						        }
								
								$width=imagesx($theimg);
								$height=imagesy($theimg);
								
								if($width > $binfo['maxres'] or $height > $binfo['maxres'])
								{
									$errs[] = $_FILES[$dis]['name'] . " exceeds acceptable dimensions";
           							continue;
								}
							}
							else if( $ext=="swf" )
							{
								if($check_pointer == true)
								{
									if(fread($check_pointer, 3) != "CWS")
									{
										$errs[] = $_FILES[$dis]['name'] . " is a malformed SWF file";
               							continue;
									}
								}
							}
							else if( $ext=="pdf" )
							{
								if($check_pointer == true)
								{
									if(fread($check_pointer, 4) != "%PDF")
									{
										$errs[] = $_FILES[$dis]['name'] . " is a malformed PDF file";
               							continue;										
									}
								}
							}
                            else
                            {
								$errs[] = "Sorry! The filetype ".$ext." is not currently supported (for file " . 
									$_FILES[$dis]['name'] . ").";
       							continue;
                            }
							
							fclose($check_pointer);

							// If we got this far, then it's a valid file.
							// (we would have THdied beforehand)
							$goodfiles[$x]=$_FILES[$dis];
							//replace certain characters with underscores - tyam
							$badchars = array("?","\"","'",";");
							$goodfiles[$x]['name']=
								str_replace($badchars,"_",$pin['basename']);

							$goodfiles[$x]['noext']=
								str_replace($badchars,"_",substr($pin['basename'],0,$dotpos-strlen($pin['basename'])));
								
							$goodfiles[$x]['type']=$ext;
							$hash=sha1_file($_FILES[$dis]['tmp_name']);
							$goodfiles[$x]['hash']=$hash;
							$hashes[]=$hash;
							$goodfiles[$x]['anim']=false;
							$goodfiles[$x]['extra_info']=0;
							
                        } // bitlookup
                        else 
                        {
							$errs[] = "The filetype ".$ext." is not allowed on this board (for file " . 
								$_FILES[$dis]['name'] . ").";                       
                            continue;
                        }
                    }//dotpos
                }//filesize
            }//dis
        }//for perpost
        if (THdupecheck && $db->dupecheck($hashes, $binfo['id'])>0)
        {
            THdie("POdupeimg");
        } 
		else 
		{
            //var_dump($goodfiles);
            return($goodfiles);
        }
    }//function

	/**
	 * This function converts all possible CSS2 units to a pixel value, assuming 72 DPI and a 720x720
	 * image (used for percentages). For information on all the possible units, read section 4.3.2 of 
	 * the W3 CSS2 spec.
	 * 
	 * @param string $string The incoming string to convert.  String syntax:
	 * (+|-)?(number)(unit or percentage)? where ? indicates an optional section
	 * 
	 * @return int The equivalent amount in pixels
	 */
    function convertw3unit($string)
    {

        // Syntax: (+|-)?(number)(unit or percentage)?
        preg_match("/(\+?|\-?)(\d+|\.+)(\w+|%?)/", $string, $lengthmatches);  //WHAT HAS SCIENCE DONE
        //$lengthmatches[1] is used to determine the sign of the number
        //$lengthmatches[2] is the number.  It matches either a digit or a decimal point.  SVGs in scientific notation can fuck off.
        //$lengthmatches[3] is the unit or a percentage

        if($lengthmatches[1] == "-")
        {
            $lengthmatches[2] = $lengthmatches[2] * -1; // Negative number.
        }
  
        if ($lengthmatches[3] == "px" 
        ||         $lengthmatches[3] == "pt" 
        ||      $lengthmatches[3] == "em" 
        ||         $lengthmatches[3] == "ex"    )
        {
            // This covers pixels, points, and the em and ex relative units
            return $lengthmatches[2];
        }
        elseif ($lengthmatches[3] == "in") // Inches
        {
            return ($lengthmatches[2] * 72); // 72 DPI
        }
        elseif ($lengthmatches[3] == "pc") //Picas
        {
            return ($lengthmatches[2] * 12);
        }
        elseif ($lengthmatches[3] == "cm") //Centimeters
        {
            return ($lengthmatches[2] * (72/2.54));
        }
        elseif ($lengthmatches[3] == "mm") //Millimeters
        {
            return ($lengthmatches[2] * (72/25.4));
        }
        elseif ($lengthmatches[3] == "%") //Percentage
        {
            return ($lengthmatches[2] * 720);
        } 
        else {
            // Okay, guess there's no unit attached to it.  We can do something about that.  Let's try this again.
            preg_match("/(\+?|\-?)(\d+|\.+)/", $string, $lengthmatches);
            
            if($lengthmatches[1] == "-")
            {
                $lengthmatches[2] = $lengthmatches[2] * -1; // Negative number.
            }
            
            if($lengthmatches[2] != null)
            return $lengthmatches[2];
            
            return intval($string);  // Something fucked up.  Oh well, this is the last resort. :[
        }
    }//function

	/**
	 * Process an array of files.  This includes (indirectly)
	 * generating thumbnails, metadata, calculating certain image
	 * qualities, and storage of such information in the database.
	 * 
	 * @param array $goodfiles A reference to an array of files that
	 * have passed checkfiles validation
	 * @param int $tpnum A temporary location ID for images
	 * @param bool $isthread If the post associated with these images
	 * is a thread or not
	 * @param array $binfo A reference to an assoc-array containing board information
	 */
    function movefiles(&$goodfiles, $tpnum, $isthread, &$binfo)
    {
        //Process the uploaded files.
        global $db;
       
        if (count($goodfiles)>0)
        {
            if ($isthread)
            {
                $thedir=THpath."images/t".$tpnum."/";
            } 
            else 
            {
                $thedir=THpath."images/p".$tpnum."/";
            }
            mkdir($thedir) or THdie("POmakeimgdir");
            $yayimgs=array();
            foreach ($goodfiles as $fyle)
            {
                //tyam hopes this doesn't fuck everything up!
                $badchars = array("?","\"","'",";");
                $fyle['name']=str_replace($badchars,"_",$fyle['name']);
                $fyle['path']=$thedir.$fyle['name'];
                move_uploaded_file($fyle['tmp_name'],$fyle['path']) or THdie("POmoveimg");
                
                // Choose how to process
                if(in_array($fyle['type'],array("jpeg","png","gif")))
                {
                    $fyle = handleimage($fyle, $thedir, $binfo);
                }
                elseif($fyle['type']=="svg")
                {
                    $fyle = handlesvg($fyle, $thedir, $binfo);
                }
                elseif($fyle['type']=="swf")
                {
                    $fyle = handleswf($fyle, $thedir, $binfo);
                }
				elseif($fyle['type']=="pdf")
				{
					$fyle = handlepdf($fyle, $thedir, $binfo);
				}

                $yayimgs[]=$fyle;
            }//foreach
            
            //DB insert
            //var_dump($yayimgs);
            $id=$db->putimgs($tpnum,$isthread,$yayimgs);
			//echo $id;
			
            //rename dir
            rename($thedir,THpath."images/".$id."/");
            
        }//if count($goodfiles)
    }//end function

	/**
	 * Turn a name (in name#tripcode form) into an array
	 * containing the name and 2ch-style tripcode hash!
	 * 
	 * @param string $name The name/tripcode in name#tripcode form
	 * @param string $tpass Does nothing, left in for future
	 * functionality
	 * 
	 * @return array An array with two elements: nombre (the name)
	 * and trip (the hash).  For example, an incoming name value
	 * of "joe#cool" would produce an array with a nombre element
	 * of "joe" and a trip element of "QkO1sgFXdY".
	 */
    function preptrip($name,$tpass)
    {
        $pos=strrpos($name,"#");
        if ($pos!==false)
        {
            $nombre=substr($name,0,$pos);
            $trip=substr($name,$pos+1);
            //echo($trip);
            //2ch-style tripcodes...
            //More or less stolen from Futallaby
            $salt=substr($trip."H.",1,2);
            $salt=preg_replace("/[^\.-z]/",".",$salt);
            $salt=strtr($salt,":;<=>?@[\\]^_`","ABCDEFGabcdef"); 
            $trip=substr(crypt($trip,$salt),-10)."";
        } 
		else 
		{
            $nombre=$name;
            $trip="";
        }
        return(array("nombre"=>$nombre,"trip"=>$trip));
    }

	/**
	 * Generate file information for a SVG-format file, as well
	 * as safe potentially dangerous scripting elements and generate
	 * a thumbnail.
	 * 
	 * @param array $fyle An array of file information to populate
	 * @param string $thedir The directory where this file is located
	 * @param array $binfo A reference to an assoc-array containing board information
	 * 
	 * @return array The populated assoc-array of file information
	 */
    function handlesvg($fyle, $thedir, &$binfo)
    {
        //Since movefiles calls this, I think it's safe to use.
        global $db;
		
        $fyle['fsize']=ceil(filesize($fyle['path'])/1024);
        $fyle['extra_info']=0;

        // If either of the next two checks fail something's messed up.  After all,
        // DOMDocument::load and getElementById get called in checkfiles, so
        // the file appears to be good so far.
        if( !($svgdom = DOMDocument::load($fyle['path']) ) )
        {
            $fyle['width']=1;
            $fyle['height']=1;
        }//dom
        else {
            $svgelements=$svgdom->getElementsByTagName("svg");
            $svgelement = $svgelements->item(0);
            //if ( !($svgelement=$svgdom->getElementById("svg") ) ) {
            if (!$svgelement)
            {
                $fyle['width']=1;
                $fyle['height']=1;
            }//get element by id svg
            else {
                if ( !($svgviewbox=$svgelement->getAttribute("viewBox") ) )
                {
                    $fyle['width']=1;
                    $fyle['height']=1;
                    if ( $svgheight=$svgelement->getAttribute("height") )
                    {
                        $fyle['height']=convertw3unit($svgheight);
                    } // get attrib height
                    if ( $svgwidth=$svgelement->getAttribute("width") )
                    {
                        $fyle['width']=convertw3unit($svgwidth);
                    }// get attrib width
                }//get attrib viewbox
                else {
                    // DO REGEXP STUFF WITH VIEWBOX
                    // The viewBox attribute is in format viewBox="0 0 width height" usually
                    preg_match("/(\S+) (\S+) (\S+) (\S+)/", $svgviewbox, $coordmatches);
                    /*
					"If matches is provided, then it is filled with the results of search. $matches[0] 
					will contain the text that matched the full pattern, $matches[1] will have the text 
					that matched the first captured parenthesized subpattern, and so on."
					-http://www.php.net/manual/en/function.preg-match.php

					o ok
					*/

                    $fyle['width'] = convertw3unit($coordmatches[3]) - convertw3unit($coordmatches[1]);
                    $fyle['height'] = convertw3unit($coordmatches[4]) - convertw3unit($coordmatches[2]);

                    //I guessed about the [1] and [2] referring to the width and height respectively but it makes sense like this

                }//regex else
            }// get svg element else
            //I feel so dirty after writing this code. :(
        } // DOM document load else

        if ($fyle['width']<=$binfo['thumbres'] && $fyle['height']<=$binfo['thumbres'])
        {
            $targw=$fyle['width'];
            $targh=$fyle['height'];
        } 
		else 
		{
            //Thumbnail code.
            //Man, this code is a female canine.
            if ($fyle['width']>$fyle['height'])
            {
                $targh=$binfo['thumbres'];
                $targw=($binfo['thumbres']/$fyle['height'])*$fyle['width'];
                if ($targw>$binfo['thumbres'])
                {
                    $ratio=$binfo['thumbres']/$targw;
                    $targw=$binfo['thumbres'];
                    $targh=$targh*$ratio;
                }
            } 
			else 
			{
                $targw=$binfo['thumbres'];
                $targh=($binfo['thumbres']/$fyle['width'])*$fyle['height'];
                if ($targh>$binfo['thumbres'])
                {
                    $ratio=$binfo['thumbres']/$targh;
                    $targh=$binfo['thumbres'];
                    $targw=$targw*$ratio;
                }
            }//if width>height
        }//if need to resize
		
        $targw=round($targw);
        $targh=round($targh);
        $fyle['twidth']=$targw;
        $fyle['theight']=$targh;

        $fyle['tname']="_t".$fyle['name'].".png"; //this is different from the others- it will have a .svg.png ext
        $thumbpath = $thedir.$fyle['tname'];

        if (THsvgthumbnailer == 1) // imagemagick
        {
            $commandstring = "convert \"".$fyle['path']."\" -resize ".$targw."x".$targh." \"".$thumbpath."\"";
			exec($commandstring);
        }
        else if (THsvgthumbnailer == 2) // rsvg
        {
            $commandstring = "rsvg --width ".$targw." --height ".$targh." \"".$fyle['path']."\" \"".$thumbpath."\"";
			exec($commandstring);
        }
        else // no thumbnailer, just make a "HAY THIS IS AN SVG" image
        { 
			copy(THpath."static/svg.png", $thumbpath);
        }
        
        // Okay.... now let's safe everything.
        $svgdata = file_get_contents($fyle['path']);
        
        if($svgdata !== false)
        {
            // Instantiate the handler
            $safehtml = new HTML_Safe();

            // Style and title are okay tags
            $safehtml->deleteTags = array(
                    'applet', 'base',   'basefont', 'bgsound', 'blink',  'body', 
                    'embed',  'frame',  'frameset', 'head',    'html',   'ilayer', 
                    'iframe', 'layer',  'link',     'meta',    'object',  'script'
                    );
            // Style, title, and XML are okay tags
            $safehtml->deleteTagsContent = array('script');
            
            // Do the safing...
            $result = $safehtml->parse($svgdata);
            // And overwrite the old SVG with the new (hopefully safer) SVG.
            file_put_contents($fyle['path'], $result);
        }
        
        return $fyle;
    }//end function

	/**
	 * Generate file information for a SWF-format file, as well
	 * as possibly metadata information
	 * 
	 * @param array $fyle An array of file information to populate
	 * @param string $thedir The directory where this file is located
	 * @param array $binfo A reference to an assoc-array containing board information
	 * 
	 * @return array The populated assoc-array of file information
	 */
    function handleswf($fyle, $thedir, &$binfo)
    {
        //Since movefiles calls this, I think it's safe to use.
        global $db;
		
        $fyle['fsize']=ceil(filesize($fyle['path'])/1024);
        $fyle['extra_info']=0;
        $fyle['width']=0;
        $fyle['height']=0;

		if(THuseSWFmeta)
		{
	        $flash = new File_SWF($fyle['path']);
			
	        if($flash)
	        {

	            $size = $flash->getMovieSize();
	            $fyle['width']=$size['width'];
	            $fyle['height']=$size['height'];

	            $type = $flash->getFileType();
	            $version = $flash->getVersion();
	            $fps = $flash->getFrameRate();
	            $framecount = $flash->getFrameCount();

	            $prot = $flash->getProtected();
	            $compr = $flash->getCompression();

	            $extrainfo = $type." file, version ".intval($version).".<br />";
	            $extrainfo = $extrainfo . "FPS: ".floatval($fps)."<br />";

	            if($framecount != 1)
	            {
	                $extrainfo = $extrainfo . "Frame count: ".intval($framecount)." frames<br />";
	            } else{
	                $extrainfo = $extrainfo . "Frame count: 1 frame<br />";
	            }

	            $bg = $flash->getBackgroundColor();
	            if($bg)
	            {
	                $hex = "#".dechex($bg[0]).dechex($bg[1]).dechex($bg[2]);
	                $extrainfo = $extrainfo . 'Background color: '.$hex;
	            }

	            if($prot)
	            {
	                $extrainfo = $extrainfo . "<br />Protected file";
	            }
	            if($compr)
	            {
	                $extrainfo = $extrainfo . "<br />Zlib compression";
	            }
				
				$fyle['extra_info'] = $db->addexifdata($extrainfo);
				
	        }//end if flash
		}

        $fyle['twidth']=100;
        $fyle['theight']=100;

        $fyle['tname']="_t".$fyle['name'].".png"; //this is different from the others- it will have a .swf.png ext
        $thumbpath = $thedir.$fyle['tname'];
    
		copy(THpath."static/flash.png", $thumbpath);

        return $fyle;
    }//end function
	
	/**
	 * Generate file information for a PDF file, and possibly
	 * metadata information and a thumbnail
	 * 
	 * @param array $fyle An array of file information to populate
	 * @param string $thedir The directory where this file is located
	 * @param array $binfo A reference to an assoc-array containing board information
	 * 
	 * @return array The populated assoc-array of file information
	 */
    function handlepdf($fyle, $thedir, &$binfo)
    {
        //Since movefiles calls this, I think it's safe to use.
        global $db;
		
        $fyle['fsize']=ceil(filesize($fyle['path'])/1024);
        $fyle['extra_info']=0;
        $fyle['width']=0;
        $fyle['height']=0;

		// Is metadata enabled?
		if(THusePDF>2)
		{
			$pdf = new FPDI(); 
			$pagecount = $pdf->setSourceFile($fyle['path']);
			$extrainfo = intval($pagecount)." pages";
			
			$fyle['extra_info'] = $db->addexifdata($extrainfo);	
		}

        $fyle['twidth']=100;
        $fyle['theight']=100;

        $fyle['tname']="_t".$fyle['name'].".png"; //this is different from the others- it will have a .pdf.png ext
        $thumbpath = $thedir.$fyle['tname'];
    
		// Is thumbnailing (through ImageMagick) enabled?
		if(THusePDF == 1 || THusePDF == 3)
		{
			$commandstring = "convert \"".$fyle['path']."\"[0] -resize ".$binfo['thumbres']."x".$binfo['thumbres']." -background #000 -extent 100x100 \"".$thumbpath."\"";
			$fyle['twidth']=$binfo['thumbres'];
			$fyle['theight']=$binfo['thumbres'];
			exec($commandstring);
		}
		else
		{
			copy(THpath."static/pdf.png", $thumbpath);
		}
        

        return $fyle;
    }//end function

	/**
	 * Generate file information for a JPEG, PNG, or GIF file,
	 * as well as a thumbnail and possibly metadata information
	 * 
	 * @param array $fyle An array of file information to populate
	 * @param string $thedir The directory where this file is located
	 * @param array $binfo A reference to an assoc-array containing board information
	 * 
	 * @return array The populated assoc-array of file information
	 */
    function handleimage($fyle, $thedir, &$binfo)
    {
        //Since movefiles calls this, I think it's safe to use.
        global $db;
        $theimg=null;
        $fyle['extra_info']=0;
        if ($fyle['type']=="jpeg") 
        {
            $theimg=imagecreatefromjpeg($fyle['path']);
        }
        elseif ($fyle['type']=="png" && is_callable("imagecreatefrompng")) 
        {
            $theimg=imagecreatefrompng($fyle['path']);
        }
        elseif ($fyle['type']=="gif" && is_callable("imagecreatefromgif")) 
        {
            $theimg=imagecreatefromgif($fyle['path']);

            $han=fopen($fyle['path'],"r");  //This used to be in checkfiles.  I have NO idea why. :albright:  ~tyam
            $look=fread($han,1024);
            fclose($han);
            if (strstr($look,"NETSCAPE2.0")!==false)
            {
                $fyle['anim']=true;
            }
        }

        if ($theimg==null )
        {
            unlink($fyle['path']);
            unset($fyle);
        } 
		else 
		{
            $fyle['fsize']=ceil(filesize($fyle['path'])/1024);

            $fyle['width']=imagesx($theimg);
            $fyle['height']=imagesy($theimg);

            if ($fyle['width']<=$binfo['thumbres'] && $fyle['height']<=$binfo['thumbres'])
            {
                $targw=$fyle['width'];
                $targh=$fyle['height'];
            } 
			else
			{
                //Thumbnail code.
                //Man, this code is a female canine.
                if ($fyle['width']>$fyle['height'])
                {
                    $targh=$binfo['thumbres'];
                    $targw=($binfo['thumbres']/$fyle['height'])*$fyle['width'];
                    if ($targw>$binfo['thumbres'])
                    {
                        $ratio=$binfo['thumbres']/$targw;
                        $targw=$binfo['thumbres'];
                        $targh=$targh*$ratio;
                    }
                }
				else 
				{
                    $targw=$binfo['thumbres'];
                    $targh=($binfo['thumbres']/$fyle['width'])*$fyle['height'];
                    if ($targh>$binfo['thumbres'])
                    {
                        $ratio=$binfo['thumbres']/$targh;
                        $targh=$binfo['thumbres'];
                        $targw=$targw*$ratio;
                    }
                }//if width>height
            }//if need to resize

            $targw=round($targw);
            $targh=round($targh);
            $fyle['twidth']=$targw;
            $fyle['theight']=$targh;

            $targ=imagecreatetruecolor($targw,$targh);
            imagecopyresampled($targ,$theimg,0,0,0,0,$targw,$targh,$fyle['width'],$fyle['height']);
            if ($fyle['type']=="png")
            {
                $fyle['tname']="_t".$fyle['noext'].".png";
                imagepng($targ,$thedir.$fyle['tname']);
            }
			else if ($fyle['type']=="gif")
			{
				if(($fyle['width']<=$binfo['thumbres']) && ($fyle['height']<=$binfo['thumbres']))
				{
					$fyle['tname']="_t".$fyle['name'];
					copy($fyle['path'],$thedir.$fyle['tname']);
				}
				else
				{
					$fyle['tname']="_t".$fyle['noext'].".png";
					imagepng($targ,$thedir.$fyle['tname']);
				}
			
			}
			else 
			{
                $fyle['tname']="_t".$fyle['noext'].".jpg";
                imagejpeg($targ,$thedir.$fyle['tname'],THjpegqual);
                $exif = exif_read_data($fyle['path'], 'IFD0,COMMENT', TRUE);
            }
            if($exif)
            {
                $extrainfo = "";
                foreach ($exif as $key => $section)
                {
                    if($key == "FILE" || $key == "COMPUTED" || $key == "THUMBNAIL" || $key == "EXIF" || $key == "INTEROP"){continue;}

                    foreach ($section as $name => $val)
                    {

                        if(strstr($name, "Resolution") || strstr($name, "Orientation") || strstr($name, "Unknown") 
                            || strstr($name, "Positioning") || strstr($name, "_IFD_Pointer") || strstr($name, "Undefined")){continue;}

                        switch ($name)
                        {
                            case "Software":
                                $extrainfo = $extrainfo . "Created with: ".htmlentities($val)."<br />\n";
                                break;
                            case "DateTime":
                                $extrainfo = $extrainfo . "Created: ".htmlentities($val)."<br />\n";
                                break;
                            case "Artist":
                                $extrainfo = $extrainfo . "Created by: ".htmlentities($val)."<br />\n";
                                break;
                            case "Make":
                                $extrainfo = $extrainfo . "Camera make: ".htmlentities($val)."<br />\n";
                                break;
                            case "Model":
                                $extrainfo = $extrainfo . "Camera model: ".htmlentities($val)."<br />\n";
                                break;
                            case "ImageDescription":
                                $extrainfo = $extrainfo . "Image description: ".htmlentities($val)."<br />\n";
                                break;
                            default:
                                $extrainfo = $extrainfo . htmlentities($key).".".htmlentities($name).": ".htmlentities($val)."<br />\n";
                                break;
                        }
                    }
                }

                if($extrainfo)
                {
                    $fyle['extra_info'] = $db->addexifdata($extrainfo);
                }
            }
        }//theimg=="" else
        return $fyle;
    }//end function
?>
