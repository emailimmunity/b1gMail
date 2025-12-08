<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty json_decode modifier plugin
 *
 * Type:     modifier<br>
 * Name:     json_decode<br>
 * Purpose:  decode JSON string to PHP array/object
 *
 * @param string $string input string
 * @param bool $assoc return associative array instead of object
 * @return mixed decoded value
 */
function smarty_modifier_json_decode($string, $assoc = true)
{
    if (empty($string)) {
        return $assoc ? [] : new stdClass();
    }
    
    $result = json_decode($string, $assoc);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return $assoc ? [] : new stdClass();
    }
    
    return $result;
}
?>
