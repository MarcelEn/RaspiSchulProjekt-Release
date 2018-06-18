<?php

require_once 'data/calendar_database.php';

class CalendarModel {

    public $owner_id;

    public function __construct(
        $calendar_id,
        $calendar_title,
        $calendar_description,
        $owner_id,
        $visibility
    ) {
      $this->calendar_id = (int) $calendar_id;
      $this->calendar_title = $calendar_title;
      $this->calendar_description = $calendar_description;
      $this->owner_id = (int) $owner_id;
      $this->visibility = (int) $visibility;
    }

    public static function byArray($array)
    {
        $calendar = new CalendarModel(
            $array['calendar_id'],
            $array['calendar_title'],
            $array['calendar_description'],
            $array['owner_id'],
            $array['visibility']
        );
        return $calendar;
    }

    public static function byId($id)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "SELECT * FROM Calendar WHERE calendar_id = ?"
        );
        $sql->bind_param("i", $id);
        $sql->execute();

        $result = $sql->get_result();

        if ($row = $result->fetch_assoc()) {
            $calendar = CalendarModel::byArray($row);
            return $calendar;
        }
        return null;
    }

    public function create()
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "INSERT INTO Calendar (calendar_title, calendar_description," .
            "owner_id, visibility) VALUES (?, ?, ?, ?)"
        );

        $sql->bind_param(
            "ssii",
            $this->calendar_title,
            $this->calendar_description,
            $this->owner_id,
            $this->visibility);

        if ($sql->execute()){
            return $database->getInsertId();
        }
        return null;
    }

    public function update()
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "INSERT INTO Calendar (" .
                "calendar_id, calendar_title, calendar_description, " .
                "owner_id, visibility" .
            ") VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE " .
                "calendar_title=?, calendar_description=?, " .
                "owner_id=?, visibility=?"
        );

        $sql->bind_param(
            "issiissii",
            $this->calendar_id,
            $this->calendar_title,
            $this->calendar_description,
            $this->owner_id,
            $this->visibility,
            $this->calendar_title,
            $this->calendar_description,
            $this->owner_id,
            $this->visibility
        );

        if ($sql->execute()) {
            return $database->getInsertId();
        }
        return null;
    }

    public function delete()
    {
        Appointment::deleteByCalendar($this->calendar_id);
        SavedCalendar::deleteByCalendar($this->calendar_id);
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "DELETE FROM Calendar WHERE calendar_id = ?"
        );
        $sql->bind_param("i", $this->calendar_id);
        $success = $sql->execute();
        return $success;
    }

    public static function deleteByUser($uid) {
        $database = CalendarDatabase::getStd();
        $sqlString =
            "SELECT * FROM Calendar" .
            " WHERE owner_id = ?";
        $sql = $database->prepare($sqlString);
        $sql->bind_param('i', $uid);
        $sql->execute();
        $result = $sql->get_result();
        while ($row = $result->fetch_assoc()) {
            $calendar = CalendarModel::byArray($row);
            $calendar->delete();
        }
    }

    public static function search($user_id, $search)
    {
        $resultArray = array();

        if (is_null($search)) {
            $search = "";
        }
        $search = "%$search%";

        $database = CalendarDatabase::getStd();
        $sqlString =
            "SELECT * FROM Calendar" .
            " WHERE calendar_title like ?";

        if(!is_null($user_id) && $user_id > 0) {
            $sqlString = $sqlString . " AND owner_id = ?";
            $sql = $database->prepare($sqlString);
            $sql->bind_param("si", $search, $user_id);
        } else {
            $sql = $database->prepare($sqlString);
            $sql->bind_param("s", $search);
        }

        $sql->execute();
        $result = $sql->get_result();

        while ($row = $result->fetch_assoc()) {
            $calendar = CalendarModel::byArray($row);
            array_push($resultArray, $calendar);
        }

        return $resultArray;
    }

    public function toJSON()
    {
        $array = array(
            'calendar_id' => $this->calendar_id,
            'calendar_title' => $this->calendar_title,
            'calendar_description' => $this->calendar_description,
            'owner_id' => $this->owner_id,
            'visibility' => $this->visibility
        );

      return json_encode($array);
    }
}
?>
