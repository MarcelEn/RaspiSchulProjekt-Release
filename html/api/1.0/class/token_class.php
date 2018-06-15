<?php

require_once 'data/calendar_database.php';

class Token {

    public function __construct($token, $user_id, $active, $long_time, $used, $created) {
        $this->token = $token;
        $this->user_id = $user_id;
        $this->active = $active;
        $this->long_time = $long_time;
        $this->used = $used;
        $this->created = $created;
    }

    public static function byArray($array)
    {
        $token = new Token(
                $array['token'],
                $array['user_id'],
                $array['active'],
                $array['long_time'],
                $array['used'],
                DateTime::createFromFormat(SQL_TIMESTAMP, $array['created'])
            );
        return $token;
    }

    public function isOutdated() {
        if($this->used == 1 && $this->getAge() > 600) {
            return true;
        }
        if($this->long_time == 0 && $this->getAge() > 7200){
            return true;
        }
        return false;
    }

    public function setUsed($used) {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            'UPDATE AccessToken SET used = ? WHERE token = ?'
        );
        $sql->bind_param('ii', $used, $this->token);
        $sql->execute();
    }

    public static function deleteAllTokens($uid){
      $database = CalendarDatabase::getStd();
      $sql = $database->prepare(
          'DELETE FROM AccessToken WHERE user_id=?'
      );
      $sql->bind_param('i', $uid);
      $success = $sql->execute();
      return $success;
    }

    public static function validate()
    {
          $token = Token::get($_COOKIE["token"]);
          if (!is_null($token) && !$token->isOutdated()) {
                $token->reset();
                return true;
          }
          return false;
    }

    private function getAge()
    {
        $age = time() - $this->created->getTimestamp();
        return $age;
    }

    private static function genNewActiveToken($token, $user_id, $long_time)
    {
        if (!is_null($token)) {
            $database = CalendarDatabase::getStd();
            $sql = $database->prepare(
                'UPDATE AccessToken SET active = 0 WHERE token = ?'
            );
            $sql->bind_param('i', $token->token);
            $sql->execute();
        }

        $newToken = null;
        while (is_null($newToken) || !$newToken->write($user_id)) {
            $newTokenId = random_int(1, 99999999);
            $newToken = new Token($newTokenId, $user_id, 1, $long_time, 0, new DateTime());
        }
        return $newToken;
    }

    public function write() {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            'INSERT INTO AccessToken (token, user_id, active, long_time, used, created)
            VALUES (?, ?, ?, ?, ?, ?)'
        );
        $sqlTimestamp = $this->created->format(SQL_TIMESTAMP);
        $sql->bind_param(
            'iiiiis',
            $this->token,
            $this->user_id,
            $this->long_time,
            $this->active,
            $this->used,
            $sqlTimestamp
        );
        return $sql->execute();
    }

    public static function validateUser($user)
    {
	$userByToken = Token::getUID();
        if($user == $userByToken) {
            return true;
        }
        return false;
    }

    public static function getUID()
    {
        $token = Token::get($_COOKIE["token"]);
        if (is_null($token)) {
            return null;
        }
        return $token->user_id;
    }

    private static function get($token)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare("SELECT * FROM AccessToken WHERE token = ?");

        $sql->bind_param('i', $token);

        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return Token::byArray($row);
        }
        return null;
    }

    public function set($user_id, $long_time)
    {
        $newToken = self::getActiveToken($user_id, $long_time);
            setcookie("token", $newToken->token, 0, '/');
    }

    private function reset()
    {
            $this->setUsed(1);
            $newToken = self::getActiveToken($this->user_id, $this->long_time);
            $newToken->setUsed(0);
            setcookie("token", $newToken->token, 0, '/');
    }

    private static function getActiveToken($user_id, $long_time)
    {
        $database = CalendarDatabase::getStd();
        $sql = $database->prepare(
            'SELECT * FROM AccessToken WHERE user_id = ? AND active = 1'
        );
        $sql->bind_param('i', $user_id);

        $sql->execute();
        $result = $sql->get_result();

        $token = null;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $token = Token::byArray($row);
        }

        if (is_null($token) || $token->getAge() > 500) {
            return self::genNewActiveToken($token, $user_id, $long_time);
        }
        return $token;
    }
}
?>
