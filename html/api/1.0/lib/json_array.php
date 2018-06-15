<?php
function arrayToJSON($array)
{
    $json = "[";
    $first = true;
    foreach($array as $object) {
        if(!$first) {
        $json = $json . ", ";
    }
    $first = false;
    $json = $json . $object->toJSON();
    }
    return $json . "]";
}
?>
