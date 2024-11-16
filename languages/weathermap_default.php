<?php
/**
 * Default language file for weathermap module
 */

$dictionary = array(
/* general */

//wind full
'OW_WIND_FULL_N' => 'North',
'OW_WIND_FULL_NNE' => 'North-North-East',
'OW_WIND_FULL_NE' => 'North-East',
'OW_WIND_FULL_ENE' => 'East-North-East',
'OW_WIND_FULL_E' => 'East',
'OW_WIND_FULL_ESE' => 'East-South-East',
'OW_WIND_FULL_SE' => 'South-East',
'OW_WIND_FULL_SSE' => 'South-South-East',
'OW_WIND_FULL_S' => 'South',
'OW_WIND_FULL_SSW' => 'South-South-West',
'OW_WIND_FULL_SW' => 'South-West',
'OW_WIND_FULL_WSW' => 'West-South-West',
'OW_WIND_FULL_W' => 'West',
'OW_WIND_FULL_WNW' => 'West-North-West',
'OW_WIND_FULL_NW' => 'North-West',
'OW_WIND_FULL_NNW' => 'North-North-West',

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
