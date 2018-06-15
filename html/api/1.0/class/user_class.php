<?php

require_once 'data/calendar_database.php';
require_once 'lib/hash.php';

class User {

    public function __construct(
        $user_id,
        $user_name,
        $first_name,
        $last_name,
        $mail,
        $password_hash
    ) {
        $this->user_id = (int) $user_id;
        $this->user_name = $user_name;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->mail = $mail;
        $this->password_hash = $password_hash;
    }


    public static function byArray($array)
    {
        return new User(
            $array["user_id"],
            $array["user_name"],
            $array["first_name"],
            $array["last_name"],
            $array["mail"],
            $array["password_hash"]
        );
    }

    //TODO: change name to byId()
    public static function get($id)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare("SELECT * FROM User WHERE user_id = ?");

        $sql->bind_param("i", $id);

        $sql->execute();
        $result = $sql->get_result();

        if ($row = $result->fetch_assoc()) {
            $user = User::byArray($row);
            return $user;
        }

        return NULL;
    }

    public function checkPassword($password)
    {
    	$equals = checkPassword($password, $this->password_hash);
        if($equals) {
            return true;
        }
        return false;
    }

    //TODO: change name to setPassword()
    public function changePassword($password)
    {
        $this->password_hash = $password;
        $this->hashPassword();
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "UPDATE User SET password_hash = ? WHERE user_id = ?"
        );

        $sql->bind_param(
            "si",
            $this->password_hash,
            $this->user_id
        );

        $success = $sql->execute();
        return $success;
    }

    //TODO: change name to create()
    public function post()
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "INSERT INTO User (user_name, first_name, " .
                "last_name, mail, password_hash) VALUES (?, ?, ?, ?, ?)"
        );

        $sql->bind_param(
            "sssss",
            $this->user_name,
            $this->first_name,
            $this->last_name,
            $this->mail,
            $this->password_hash);

        if ($sql->execute()){
            return $database->getInsertId();
        }
        return null;
    }

    //change name to update()
    public function put()
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            "UPDATE User SET " .
                "first_name=?, last_name=?, " .
                "mail=? WHERE user_id=?"
        );

        $sql->bind_param(
            "ssss",
            $this->first_name,
            $this->last_name,
            $this->mail,
            $this->user_id
        );

        $success = $sql->execute();
        if ($success) {
            return $this->user_id;
        }
        return null;
    }

    public function delete()
    {
        Token::deleteAllTokens($this->user_id);
        CalendarModel::deleteAllCalendars($this->user_id);
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare("DELETE FROM User WHERE user_id = ?");
        $sql->bind_param("i", $this->user_id);
        $success = $sql->execute();
        return $success;
    }

    //TODO: change name to search()
    public static function getByName($name)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare("SELECT * FROM User WHERE user_name like ?");

        $name = "%$name%";
        $sql->bind_param("s", $name);

        $sql->execute();
        $result = $sql->get_result();

        $array = array();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $user = User::byArray($row);
                array_push($array, $user);
            }
        }

        return $array;
    }

    //TODO: change name to byName()
    public static function getByExactName($name)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare("SELECT * FROM User WHERE user_name = ?");

        $sql->bind_param("s", $name);

        $sql->execute();
        $result = $sql->get_result();


        if ($row = $result->fetch_assoc()) {
            $user = User::byArray($row);
            return $user;
        }
        return null;
    }

    public function hashPassword()
    {
        $hash = hashPassword($this->password_hash);
        $this->password_hash = $hash;
    }

    public function toJSON()
    {
        $array = array(
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mail' => $this->mail
          );

        return json_encode($array);
    }
}
?>
