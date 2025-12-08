<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsModifier
 */

/**
 * Smarty json_encode modifier plugin
 *
 * Type:     modifier<br>
 * Name:     json_encode<br>
 * Purpose:  encode PHP value to JSON string
 *
 * @param mixed $value input value
 * @param int $options json_encode options
 * @return string JSON string
 */
function smarty_modifier_json_encode($value, $options = 0)
{
    if ($options === 0) {
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE;
    }
    
    return json_encode($value, $options);
}
?>
