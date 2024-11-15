<?php

	/**
	* Get wind direction name by direction in degree
	* @param mixed $degree Wind degree
	* @return string
	*/
function getWindDirection($degree, $full=false)
   {
	if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . 'weathermap_' .SETTINGS_SITE_LANGUAGE . '.php'))
	{
		include_once (ROOT . 'languages/' . 'weathermap_' .SETTINGS_SITE_LANGUAGE . '.php');
	} else {
		include_once (ROOT . 'languages/'.'weathermap_default.php');
	}
	if($full) {
		$windDirection = array(
			LANG_OW_WIND_FULL_N,
			LANG_OW_WIND_FULL_NNE,
			LANG_OW_WIND_FULL_NE,
			LANG_OW_WIND_FULL_ENE,
			LANG_OW_WIND_FULL_E,
			LANG_OW_WIND_FULL_ESE,
			LANG_OW_WIND_FULL_SE,
			LANG_OW_WIND_FULL_SSE,
			LANG_OW_WIND_FULL_S,
			LANG_OW_WIND_FULL_SSW,
			LANG_OW_WIND_FULL_SW,
			LANG_OW_WIND_FULL_WSW,
			LANG_OW_WIND_FULL_W,
			LANG_OW_WIND_FULL_WNW,
			LANG_OW_WIND_FULL_NW,
			LANG_OW_WIND_FULL_NNW,
			LANG_OW_WIND_FULL_N
		);
	} else {
		$windDirection = array(
			LANG_N,
			LANG_NNE,
			LANG_NE,
			LANG_ENE,
			LANG_E,
			LANG_ESE,
			LANG_SE,
			LANG_SSE,
			LANG_S,
			LANG_SSW,
			LANG_SW,
			LANG_WSW,
			LANG_W,
			LANG_WNW,
			LANG_NW,
			LANG_NNW,
			LANG_N
		);
	}
    $direction = $windDirection[round(intval($degree) / 22.5)];
    return $direction;
   }

?>
