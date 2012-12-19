<?php
function smarty_modifier_THtrunc($string, $length = 80) {
    /*
		This is a modified version of Smarty's normal "truncate" modifier, especiallly for use
		for Thorn (though, of course, you're free to use it elsewhere). It will always break by
		words, and it has no "..." feature, but instead of returning just a string, it returns
		an array: 'text', which is the processed string, and 'wastruncated', a bool that represents
		whether the string was actually truncated or not.
	*/

    if (strlen($string) > $length) {
        $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length+1));
        return(array("text"=>substr($string, 0, $length),"wastruncated"=>true));
        }
    else {
        return(array("text"=>$string,"wastruncated"=>false));
        }
    }
?>