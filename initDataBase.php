<?php
    require_once 'public/api/1.0/data/calendar_database.php';
    $database = CalendarDatabase::getStd();
    $database->create();
?>