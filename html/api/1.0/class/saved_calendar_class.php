<?php

require_once 'data/calendar_database.php';

class SavedCalendar {

    public static function get($uid)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "SELECT * FROM Calendar c INNER JOIN SavedCalendar s " .
            "ON c.calendar_id = s.calendar_id  WHERE s.user_id = ?"
        );
        $sql->bind_param('i', $uid);
        $sql->execute();
        $result = $sql->get_result();
        $resultArray = array();
        while ($row = $result->fetch_assoc()) {
            $calendar = CalendarModel::byArray($row);
            array_push($resultArray, $calendar);
        }
        return $resultArray;
    }

    public static function add($uid, $calendar_id)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "INSERT INTO SavedCalendar (user_id, calendar_id) VALUES (?, ?)"
        );
        $sql->bind_param('ii', $uid, $calendar_id);
        $success = $sql->execute();
        return $success;
    }

    public static function getOne($uid, $calendar_id)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "SELECT * FROM Calendar c INNER JOIN SavedCalendar s " .
            "ON c.calendar_id = s.calendar_id  WHERE s.user_id = ? " .
            "AND s.calendar_id = ?"
        );
        $sql->bind_param('ii', $uid, $calendar_id);
        $sql->execute();
        $result = $sql->get_result();
        if ($row = $result->fetch_assoc()) {
            $calendar = CalendarModel::byArray($row);
            return $calendar;
        }
        return null;
    }

    public static function delete($uid, $calendar_id)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "DELETE FROM SavedCalendar WHERE user_id = ? AND calendar_id = ?"
        );
        $sql->bind_param('ii', $uid, $calendar_id);
        $success = $sql->execute();
        return $success;
    }
}
?>
