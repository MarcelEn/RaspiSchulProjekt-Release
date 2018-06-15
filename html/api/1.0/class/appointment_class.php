<?php

require_once 'data/calendar_database.php';
require_once 'lib/timestamp_converter.php';

class Appointment {

    function __construct(
        $appointment_id,
        $start,
        $end,
        $calendar_id,
        $appointment_title,
        $appointment_description
    ) {
        $this->appointment_id = (int)$appointment_id;
        $this->start = $start;
        $this->end = $end;
        $this->calendar_id = (int)$calendar_id;
        $this->appointment_title = $appointment_title;
        $this->appointment_description = $appointment_description;
    }
    
    static function bySQLArray($array)
    {
        $start = DateTime::createFromFormat(SQL_TIMESTAMP, $array['start']);
        $end = DateTime::createFromFormat(SQL_TIMESTAMP, $array['end']);
        $array['start'] = $start->getTimestamp()*1000; 
        $array['end'] =  $end->getTimestamp()*1000;
        $appointment = Appointment::byArray($array);
        return $appointment;
    }

    static function byArray($array)
    {
        $start=convertTimestampToDateTime($array['start']);
        $end=convertTimestampToDateTime($array['end']);
        return new Appointment(
            $array['appointment_id'],
            $start,
            $end,
            $array['calendar_id'],
            $array['appointment_title'],
            $array['appointment_description']
        );
    }

    //TODO: change name to byId()
    static function get($id)
    {
        $getStatement = 'SELECT * FROM Appointment WHERE appointment_id = ?';
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare($getStatement);
        $sql->bind_param('i', $id);
        $sql->execute();

        $result = $sql->get_result();
        if ($row = $result->fetch_assoc()) {
            $appointment = Appointment::bySQLArray($row);
            return $appointment;
        }

        return null;
    }

    //TODO: change name to create()
    function post()
    {
        $postStatement =
            'INSERT INTO Appointment (start, end, calendar_id, ' .
            'appointment_title, appointment_description) VALUES (?, ?, ?, ?, ?)';
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare($postStatement);
        $startTime = $this->start->format(SQL_TIMESTAMP);
        $endTime = $this->end->format(SQL_TIMESTAMP);
        $sql->bind_param(
            'ssiss',
            $startTime,
            $endTime,
            $this->calendar_id,
            $this->appointment_title,
            $this->appointment_description
        );

        if ($sql->execute()) {
            return $database->getInsertId();
        }
        return null;
    }

    //TODO: change name to update()
    function put()
    {
        $putStatement = 'INSERT INTO Appointment (' .
                'appointment_id, start, end, calendar_id, appointment_title,' .
                'appointment_description' .
            ') VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ' .
                'start = ?, end = ?, calendar_id = ?, appointment_title = ?,' .
                'appointment_description = ?';
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare($putStatement);
        $sql->bind_param(
            'ississssiss',
            $this->appointment_id,
            $this->start->format(SQL_TIMESTAMP),
            $this->end->format(SQL_TIMESTAMP),
            $this->calendar_id,
            $this->appointment_title,
            $this->appointment_description,
            $this->start->format(SQL_TIMESTAMP),
            $this->end->format(SQL_TIMESTAMP),
            $this->calendar_id,
            $this->appointment_title,
            $this->appointment_description
        );
        
        if ($sql->execute()) {
            return $database->getInsertId();
        }
        return false;
    }

    function delete()
    {
        $deleteStatement = 'DELETE FROM Appointment WHERE appointment_id = ?';
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare($deleteStatement);
        $sql->bind_param('i', $this->appointment_id);

        return $sql->execute();
    }

    //TODO: change name to deleteByCalendar()
    public static function deleteAllAppointments($calendar_id) {
        $database = CalendarDatabase::getStd();
        $sqlString =
            'SELECT * FROM Appointment' .
            ' WHERE calendar_id = ?';
        $sql = $database->prepare($sqlString);
        $sql->bind_param('i', $calendar_id);
        $sql->execute();
        $result = $sql->get_result();
        while ($row = $result->fetch_assoc()) {
            $appointment = Appointment::byArray($row);
            $appointment->delete();
        }
    }

    //TODO: change name to search()
    static function searchAppointments($after, $before, $calId)
    {
        $searchStatement =
            'SELECT * FROM Appointment' .
            ' WHERE calendar_id = ? AND start >= ? AND end <= ?';
        $resultArray = array();

        if (is_null($calId) || is_null($before) || is_null($after)) {
            return $resultArray;
        }

        $database = CalendarDatabase::getStd();
        $beforeTS = convertTimestampToDateTime($before);
        $afterTS = convertTimestampToDateTime($after);

        $sql = $database->prepare($searchStatement);
        $sql->bind_param(
            'iss',
            $calId,
            $afterTS->format(SQL_TIMESTAMP),
            $beforeTS->format(SQL_TIMESTAMP)
        );
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
            array_push($resultArray, Appointment::bySQLArray($row));
            }
        }
        return $resultArray;
    }

    function toJSON()
    {
        $start = convertDateTimeToTimestamp($this->start);
        $end = convertDateTimeToTimestamp($this->end);
        $array = array(
            'appointment_id' => $this->appointment_id,
            'start' => $start,
            'end' => $end,
            'calendar_id' => $this->calendar_id,
            'appointment_title' => $this->appointment_title,
            'appointment_description' => $this->appointment_description
        );
        return json_encode($array);
    }
  }
?>
