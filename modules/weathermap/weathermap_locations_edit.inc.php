<?php
/*
* @version 0.1 (wizard)
*/
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'weathermap_locations';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
if ($this->mode == 'update') {
    $ok = 1;
    // step: default
    if ($this->tab == '') {
        //updating '<%LANG_TITLE%>' (varchar, required)
        $rec['TITLE'] = gr('title');
        if ($rec['TITLE'] == '') {
            $out['ERR_TITLE'] = 1;
            $ok = 0;
        }
        //updating 'LAT' (varchar)
        $rec['LAT'] = gr('lat');
        //updating 'LON' (varchar)
        $rec['LON'] = gr('lon');
        //updating '<%LANG_UPDATED%>' (datetime)
    }
    // step: data
    if ($this->tab == 'data') {
    }
    //UPDATING RECORD
    if ($ok) {
        if (isset($rec['ID'])) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['UPDATED'] = date('Y-m-d H:i:s');
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        subscribeToEvent($this->name, 'HOURLY');
        $this->refreshLocation($rec['ID']);
        $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=data");
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}
// step: default
// step: data
if ($this->tab == 'data') {
    if ($this->mode == 'refresh') {
        $this->refreshLocation($rec['ID']);
        $this->redirect("?view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&id=" . $rec['ID']);
    }
    //dataset2
    $new_id = 0;
    global $delete_id;
    if ($delete_id) {
        SQLExec("DELETE FROM weathermap_properties WHERE ID='" . (int)$delete_id . "'");
    }
    $prop_id = gr('prop_id', 'int');

    $properties = SQLSelect("SELECT * FROM weathermap_properties WHERE WEATHER_LOCATION_ID='" . $rec['ID'] . "' ORDER BY TITLE");
    $total = count($properties);
    for ($i = 0; $i < $total; $i++) {
        if ($properties[$i]['ID'] == $new_id) continue;
        if ($properties[$i]['ID'] == $prop_id) {
            if ($this->mode == 'update') {

                $old_linked_object = $properties[$i]['LINKED_OBJECT'];
                $old_linked_property = $properties[$i]['LINKED_PROPERTY'];
                $properties[$i]['LINKED_OBJECT'] = gr('linked_object', 'trim');
                $properties[$i]['LINKED_PROPERTY'] = gr('linked_property', 'trim');
                $properties[$i]['LINKED_METHOD'] = gr('linked_method', 'trim');
                SQLUpdate('weathermap_properties', $properties[$i]);

                if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
                    addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
                } elseif ($old_linked_object && $old_linked_property && function_exists('removeLinkedPropertyIfNotUsed')) {
                    removeLinkedPropertyIfNotUsed('weathermap_properties', $old_linked_object, $old_linked_property, $this->name);
                }
                $this->redirect("?id=" . $rec['ID'] . "&view_mode=" . $this->view_mode . "&tab=" . $this->tab . "&prop_id=" . $prop_id . "&ok=1");
            }
            foreach ($properties[$i] as $k => $v) {
                $out['PROP_' . $k] = $v;
            }
        }
        if (preg_match('/_icon\_url$/',$properties[$i]['TITLE'])) {
            $properties[$i]['URL']=1;
        }
    }
    $out['PROPERTIES'] = $properties;
}
if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);

$out['country']=gr('country');
