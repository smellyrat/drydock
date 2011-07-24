<?php

/*
 * This is basically a Smarty wrapper around the is_in_csl function (in common.php)
 * that we use in various places.
 * Two parameters: item and list.
 */

function smarty_function_listcontains($params, &$smarty)
{
    return is_in_csl($params['item'],$params['list']);
}
?>
