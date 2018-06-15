<?php

require_once 'database.php';

class CalendarDatabase extends Database {

    public static function getStd() {
        $database = new CalendarDatabase('127.0.0.1', 'php', 'php');
        $database->connect();
        return $database;
    }

    public function __construct($servername, $username, $password) {
        parent::__construct(
            $servername,
            $username,
            $password,
            'CalendarManagement_013'
        );
      }

    public function create() {
        parent::create();
        self::connect();
        $q1 = '
            CREATE TABLE User(
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                user_name VARCHAR(50),
                first_name VARCHAR(50),
                last_name VARCHAR(50),
                mail VARCHAR(50),
                password_hash VARCHAR(100),
                UNIQUE (user_name))
        ';
        $q2 = '
            CREATE TABLE Calendar(
                calendar_id INT AUTO_INCREMENT PRIMARY KEY,
                calendar_title VARCHAR(100),
                calendar_description VARCHAR(10000),
                owner_id INT,
                visibility INT,
                FOREIGN KEY (owner_id) REFERENCES User(user_id))
        ';
        $q3 = '
            CREATE TABLE Appointment(
                appointment_id INT AUTO_INCREMENT PRIMARY KEY,
                start TIMESTAMP,
                end TIMESTAMP,
                calendar_id INT,
                appointment_title VARCHAR(100),
                appointment_description VARCHAR(10000),
                FOREIGN KEY (calendar_id) REFERENCES Calendar(calendar_id)
            )
        ';
        $q4 = '
            CREATE TABLE SavedCalendar(
                user_id INT,
                calendar_id INT,
                FOREIGN KEY (user_id) REFERENCES User(user_id),
                FOREIGN KEY (calendar_id) REFERENCES Calendar(calendar_id),
                CONSTRAINT UC_SavedCalendar UNIQUE (user_id, calendar_id)
            )
        ';
        $q5 = '
            CREATE TABLE AccessToken(
                token INT PRIMARY KEY,
                user_id INT,
                active INT,
                long_time INT,
                used INT,
                created TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES User(user_id)
            )
        ';
        if (
            self::query($q1) &&
            self::query($q2) &&
            self::query($q3) &&
            self::query($q4) &&
            self::query($q5)
        ) {
            self::close();
            return true;
        }
        return false;
    }
  }
?>
