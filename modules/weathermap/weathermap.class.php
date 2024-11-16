<?php
/**
 * WeatherMap
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 22:11:40 [Nov 07, 2024])
 */
//
//
class weathermap extends module
{
    /**
     * weathermap
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "weathermap";
        $this->title = "WeatherMap";
        $this->module_category = "<#LANG_SECTION_APPLICATIONS#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 1)
    {
        $p = array();
        if (isset($this->id)) {
            $p["id"] = $this->id;
        }
        if (isset($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (isset($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (isset($this->data_source)) {
            $p["data_source"] = $this->data_source;
        }
        if (isset($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $data_source;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($data_source)) {
            $this->data_source = $data_source;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (isset($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (isset($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['DATA_SOURCE'] = $this->data_source;
        $out['TAB'] = $this->tab;
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {
        $this->getConfig();
        $out['API_URL'] = $this->config['API_URL'];
        if (!$out['API_URL']) {
            $out['API_URL'] = 'http://';
        }
        $out['API_KEY'] = $this->config['API_KEY'];
        $out['API_USERNAME'] = $this->config['API_USERNAME'];
        $out['API_PASSWORD'] = $this->config['API_PASSWORD'];
        if ($this->view_mode == 'update_settings') {
            global $api_url;
            $this->config['API_URL'] = $api_url;
            global $api_key;
            $this->config['API_KEY'] = $api_key;
            global $api_username;
            $this->config['API_USERNAME'] = $api_username;
            global $api_password;
            $this->config['API_PASSWORD'] = $api_password;
            $this->saveConfig();
            $this->redirect("?");
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'weathermap_locations' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_weathermap_locations') {
                $this->search_weathermap_locations($out);
            }
            if ($this->view_mode == 'edit_weathermap_locations') {
                $this->edit_weathermap_locations($out, $this->id);
            }
            if ($this->view_mode == 'delete_weathermap_locations') {
                $this->delete_weathermap_locations($this->id);
                $this->redirect("?data_source=weathermap_locations");
            }
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'weathermap_properties') {
            if ($this->view_mode == '' || $this->view_mode == 'search_weathermap_properties') {
                $this->search_weathermap_properties($out);
            }
            if ($this->view_mode == 'edit_weathermap_properties') {
                $this->edit_weathermap_properties($out, $this->id);
            }
        }
    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        $this->admin($out);
    }

    function refreshLocation($location_id)
    {
        $location = SQLSelectOne("SELECT * FROM weathermap_locations WHERE ID=" . (int)$location_id);
        if (!isset($location['ID'])) return false;

        $location['UPDATED'] = date('Y-m-d H:i:s');
        SQLUpdate('weathermap_locations', $location);

        $this->getConfig();
        $api = $this->config['API_KEY'];
        $lat = $location['LAT'];
        $lon = $location['LON'];


        $lang = SETTINGS_SITE_LANGUAGE;
        if ($lang == 'default') {
            $lang = 'en';
        }

        $cache_file = ROOT . 'cms/cached/weathermap_' . $location['ID'] . '_weather.txt';
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 1 * 60) {
            $weather_data = LoadFile($cache_file);
        } else {
            $weather_data = getURL("https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=" . $api . "&units=metric&lang=".$lang);
            if ($weather_data != '') {
                SaveFile($cache_file, $weather_data);
            }
        }
        if (preg_match('/"cod":401/', $weather_data)) {
            dprint();
            unlink($cache_file);
        }

        $cache_file = ROOT . 'cms/cached/weathermap_' . $location['ID'] . '_forecast.txt';
        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 1 * 60) {
            $forecast_data = LoadFile($cache_file);
        } else {
            $forecast_data = getURL("https://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lon&appid=" . $api . "&units=metric&lang=".$lang);
            if ($forecast_data != '') {
                SaveFile($cache_file, $forecast_data);
            }
        }
        if (preg_match('/"cod":401/', $forecast_data)) {
            unlink($cache_file);
        }

        $properties = array();
        //$weather_data
        $data = json_decode($weather_data, true);

        $properties['fact_weather'] = strtolower($data['weather'][0]['main']);
        $properties['fact_weather_description'] = $data['weather'][0]['description'];
        $properties['fact_weather_icon'] = $data['weather'][0]['icon'];
        $properties['fact_weather_icon_url'] = 'https://openweathermap.org/img/wn/' . $properties['fact_weather_icon'] . '@2x.png';
        $properties['fact_temperature'] = $data['main']['temp'];
        $properties['fact_temperature_feels'] = $data['main']['feels_like'];
        $properties['fact_pressure'] = $data['main']['pressure'];
        $properties['fact_pressure_mmHg'] = round($properties['fact_pressure'] * 750.06 / 1000);
        $properties['fact_humidity'] = $data['main']['humidity'];
        $properties['fact_wind'] = $data['wind']['speed'];
        $properties['fact_wind_direction'] = $data['wind']['deg'];
        $properties['fact_wind_dir_text'] = $this->getWindDirection(round($data['wind']['deg'], $round));
	    $properties['fact_wind_dir_full'] = $this->getWindDirection(round($data['wind']['deg'], $round), true);
        $properties['fact_visibility'] = $data['visibility'];
        $properties['fact_sunrise'] = date('H:i', ($data['sys']['sunrise']));
        $properties['fact_sunset'] = date('H:i', ($data['sys']['sunset']));

        //$forecast_data
        $data = json_decode($forecast_data, true);

        $datetimeNow = new DateTime(date('Y-m-d 00:00:00.000000'));
        if (is_array($data['list'])) {
            $total = count($data['list']);
            for ($i = 0; $i < $total; $i++) {
                $rec = $data['list'][$i];
                $datetimeThen = new DateTime(date('Y-m-d h:i:s.000000', (int)$rec['dt']));
                $difference = $datetimeNow->diff($datetimeThen);
                $hour = (int)date('H', $rec['dt']);
                $day_part = '';
                if ($hour > 5 && $hour <= 10) $day_part = '1_morning';
                if ($hour > 11 && $hour <= 15) $day_part = '2_day';
                if ($hour > 17 && $hour <= 21) $day_part = '3_evening';
                if ($hour > 22) $day_part = '4_night';

                $day_key = 'day_' . $difference->d;
                if (!isset($day_temp_min[$day_key]) || $day_temp_min[$day_key] > $rec['main']['temp']) {
                    $day_temp_min[$day_key] = $rec['main']['temp'];
                }
                if (!isset($day_temp_max[$day_key]) || $day_temp_max[$day_key] < $rec['main']['temp']) {
                    $day_temp_max[$day_key] = $rec['main']['temp'];
                }
                if (!isset($wind_max[$day_key]) || $wind_max[$day_key] < $rec['wind']['speed']) {
                    $wind_max[$day_key] = $rec['wind']['speed'];
                }
                if (!isset($rain[$day_key])) {
                    $rain[$day_key] = 0;
                }
                if (strtolower($rec['weather'][0]['main']) == 'rain') {
                    $rain[$day_key] = 1;
                }
                $properties['forecast_' . $difference->d . '_temperature_min'] = $day_temp_min[$day_key];
                $properties['forecast_' . $difference->d . '_temperature_max'] = $day_temp_max[$day_key];
                $properties['forecast_' . $difference->d . '_wind_max'] = $wind_max[$day_key];
                $properties['forecast_' . $difference->d . '_rain'] = $rain[$day_key];
                if ($day_part != '') {
                    $day_title = 'forecast_' . $difference->d . '_' . $day_part;
                    $properties[$day_title . '_dt'] = date('Y-m-d H:i:s', $rec['dt']);
                    $properties[$day_title . '_weather'] = strtolower($rec['weather'][0]['main']);
                    $properties[$day_title . '_weather_description'] = $rec['weather'][0]['description'];
                    $properties[$day_title . '_weather_icon'] = $rec['weather'][0]['icon'];
                    $properties[$day_title . '_weather_icon_url'] = 'https://openweathermap.org/img/wn/' . $properties[$day_title . '_weather_icon'] . '@2x.png';
                    $properties[$day_title . '_temperature'] = $rec['main']['temp'];
                    $properties[$day_title . '_temperature_feels'] = $rec['main']['feels_like'];
                    $properties[$day_title . '_pressure'] = $rec['main']['pressure'];
                    $properties[$day_title . '_pressure_mmHg'] = round($properties[$day_title . '_pressure'] * 750.06 / 1000);
                    $properties[$day_title . '_humidity'] = $rec['main']['humidity'];
                    $properties[$day_title . '_wind'] = $rec['wind']['speed'];
                    $properties[$day_title . '_wind_direction'] = $rec['wind']['deg'];
                    $properties[$day_title . '_wind_dir_text'] = $this->getWindDirection(round($data['wind']['deg'], $round));
		            $properties[$day_title . '_wind_dir_full'] = $this->getWindDirection(round($data['wind']['deg'], $round), true);
                    $properties[$day_title . '_visibility'] = $rec['visibility'];
                }
                //echo $diff_hours." ($diff_days $hour)<br/>";
            }
        }
        foreach ($properties as $k => $v) {
            $this->processProperty($location_id, $k, $v);
        }
    }

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

    function processProperty($location_id, $property, $value)
    {
        $prop = SQLSelectOne("SELECT * FROM weathermap_properties WHERE WEATHER_LOCATION_ID=" . $location_id . " AND TITLE='" . $property . "'");
        $old_value = '';
        $prop['TITLE'] = $property;
        $prop['WEATHER_LOCATION_ID'] = $location_id;
        if (isset($prop['VALUE'])) {
            $old_value = $prop['VALUE'];
        }
        $prop['VALUE'] = $value;
        if ($prop['VALUE'] != $old_value || !isset($prop['ID'])) {
            $prop['UPDATED'] = date('Y-m-d H:i:s');
        }
        if (!isset($prop['ID'])) {
            $prop['ID'] = SQLInsert('weathermap_properties', $prop);
        } else {
            SQLUpdate('weathermap_properties', $prop);
        }
        if ($prop['VALUE'] != $old_value) {
            if ($prop['LINKED_PROPERTY']) {
                setGlobal($prop['LINKED_OBJECT'] . '.' . $prop['LINKED_PROPERTY'], $prop['VALUE'], array($this->name => '0'));
            }
            if ($prop['LINKED_METHOD']) {
                callMethod($prop['LINKED_OBJECT'] . '.' . $prop['LINKED_METHOD'], array(
                    'VALUE' => $prop['VALUE'], 'NEW_VALUE' => $prop['VALUE']
                ));
            }
        }

    }

    function processSubscription($event_name, $details = '')
    {
        if ($event_name == 'HOURLY') {
            $this->getConfig();
            $locations = SQLSelect("SELECT ID FROM weathermap_locations");
            $total = count($locations);
            for ($i = 0; $i < $total; $i++) {
                $this->refreshLocation($locations[$i]['ID']);
            }
        }
    }

    /**
     * weathermap_locations search
     *
     * @access public
     */
    function search_weathermap_locations(&$out)
    {
        require(dirname(__FILE__) . '/weathermap_locations_search.inc.php');
    }

    /**
     * weathermap_locations edit/add
     *
     * @access public
     */
    function edit_weathermap_locations(&$out, $id)
    {
        require(dirname(__FILE__) . '/weathermap_locations_edit.inc.php');
    }

    /**
     * weathermap_locations delete record
     *
     * @access public
     */
    function delete_weathermap_locations($id)
    {
        $rec = SQLSelectOne("SELECT * FROM weathermap_locations WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM weathermap_properties WHERE WEATHER_LOCATION_ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM weathermap_locations WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * weathermap_properties search
     *
     * @access public
     */
    function search_weathermap_properties(&$out)
    {
        require(dirname(__FILE__) . '/weathermap_properties_search.inc.php');
    }

    /**
     * weathermap_properties edit/add
     *
     * @access public
     */
    function edit_weathermap_properties(&$out, $id)
    {
        require(dirname(__FILE__) . '/weathermap_properties_edit.inc.php');
    }

    function propertySetHandle($object, $property, $value)
    {
        $this->getConfig();
        $table = 'weathermap_properties';
        $properties = SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '" . DBSafe($object) . "' AND LINKED_PROPERTY LIKE '" . DBSafe($property) . "'");
        $total = count($properties);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                //to-do
            }
        }
    }

    function getModuleProperty($property_name)
    {
        list($location_id, $property_name) = explode('.', $property_name);
        $data = SQLSelectOne("SELECT * FROM weathermap_properties WHERE WEATHER_LOCATION_ID=" . (int)$location_id . " AND TITLE='" . $property_name . "'");
        if (isset($data['VALUE'])) return $data['VALUE'];
        return false;
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {

        parent::install();
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        SQLExec('DROP TABLE IF EXISTS weathermap_locations');
        SQLExec('DROP TABLE IF EXISTS weathermap_properties');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {
        /*
        weathermap_locations -
        weathermap_properties -
        */
        $data = <<<EOD
 weathermap_locations: ID int(10) unsigned NOT NULL auto_increment
 weathermap_locations: TITLE varchar(100) NOT NULL DEFAULT ''
 weathermap_locations: LAT varchar(255) NOT NULL DEFAULT ''
 weathermap_locations: LON varchar(255) NOT NULL DEFAULT ''
 weathermap_locations: UPDATED datetime
 
 weathermap_properties: ID int(10) unsigned NOT NULL auto_increment
 weathermap_properties: TITLE varchar(100) NOT NULL DEFAULT ''
 weathermap_properties: VALUE varchar(255) NOT NULL DEFAULT ''
 weathermap_properties: WEATHER_LOCATION_ID int(10) NOT NULL DEFAULT '0'
 weathermap_properties: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 weathermap_properties: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 weathermap_properties: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 weathermap_properties: UPDATED datetime
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTm92IDA3LCAyMDI0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
