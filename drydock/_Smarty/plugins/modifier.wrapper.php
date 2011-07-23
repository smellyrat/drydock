<?php

//word wrap plugin for kchan - messy on html source but it works - tyam
function smarty_modifier_wrapper($string,$maxwordlength=84)
//this variable name is misleading, this actually adds the replace char
//after this many characters
{
$length = strlen($string);

for ($i=0; $i<=$length; $i=$i+1)
   {
   $char = substr($string, $i, 1);
   if ($char == "<")
       $skip=1;
   elseif ($char == ">")
       $skip=0;
   elseif ($char == " ")
       $wrap=0;

   if ($skip==0)
       $wrap=$wrap+1;

   $returnvar = $returnvar . $char;

   if ($wrap>$maxwordlength)
       {
       $returnvar = $returnvar . "<br />\n";
       $wrap=0;
       }
   }

return $returnvar;

}

/*
function smarty_modifier_wrapper($string)
{
//return str_rot13($string);
return wordwrap($string, 40, "<wbr>", 0);
}
*/
?>
