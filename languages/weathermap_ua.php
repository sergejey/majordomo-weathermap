<?php
/**
 * Ukrainian language file for weathermap module
 */

$dictionary = array(
/* general */

//wind full
'OW_WIND_FULL_N' => 'Північ',
'OW_WIND_FULL_NNE' => 'Північно-Північно-Схід',
'OW_WIND_FULL_NE' => 'Північно-Схід',
'OW_WIND_FULL_ENE' => 'Схід-Північно-Схід',
'OW_WIND_FULL_E' => 'Схід',
'OW_WIND_FULL_ESE' => 'Схід-Південний-Схід',
'OW_WIND_FULL_SE' => 'Південно-Схід',
'OW_WIND_FULL_SSE' => 'Південно-Південно-Схід',
'OW_WIND_FULL_S' => 'Південь',
'OW_WIND_FULL_SSW' => 'Південно-Південно-Захід',
'OW_WIND_FULL_SW' => 'Південно-Захід',
'OW_WIND_FULL_WSW' => 'Захід-Південь-Захід',
'OW_WIND_FULL_W' => 'Захід',
'OW_WIND_FULL_WNW' => 'Захід-Північ-Захід',
'OW_WIND_FULL_NW' => 'Північ-Захід',
'OW_WIND_FULL_NNW' => 'Північ-Північ-Захід',

/* end module names */
);

foreach ($dictionary as $k=>$v)
{
   if (!defined('LANG_' . $k))
   {
      define('LANG_' . $k, $v);
   }
}

?>
