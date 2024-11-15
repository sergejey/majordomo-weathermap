<?php
/**
 * Russian language file for weathermap module
 */

$dictionary = array(
/* general */

//wind full
'OW_WIND_FULL_N' => 'Север',
'OW_WIND_FULL_NNE' => 'Северо-Северо-Восток',
'OW_WIND_FULL_NE' => 'Северо-Восток',
'OW_WIND_FULL_ENE' => 'Востоко-Северо-Восток',
'OW_WIND_FULL_E' => 'Восток',
'OW_WIND_FULL_ESE' => 'Востоко-Юго-Восток',
'OW_WIND_FULL_SE' => 'Юго-Восток',
'OW_WIND_FULL_SSE' => 'Юго-Юго-Восток',
'OW_WIND_FULL_S' => 'Юг',
'OW_WIND_FULL_SSW' => 'Юго-Юго-Запад',
'OW_WIND_FULL_SW' => 'Юго-Запад',
'OW_WIND_FULL_WSW' => 'Западо-Юго-Запад',
'OW_WIND_FULL_W' => 'Запад',
'OW_WIND_FULL_WNW' => 'Западо-Северо-Запад',
'OW_WIND_FULL_NW' => 'Северо-Запад',
'OW_WIND_FULL_NNW' => 'Северо-Северо-Запад',

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
