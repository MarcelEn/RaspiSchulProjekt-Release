<?php
class Database {

    function __construct($servername, $username, $password, $dbname)
    {
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
    }

    function create()
    {
        $success = FALSE;
        $conn = new mysqli($this->servername, $this->username, $this->password);
        if ($conn->connect_error) {
            return FALSE;
        }
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->dbname;
        if ($conn->query($sql) === TRUE) {
            $success = TRUE;
        } else {
            $success = FALSE;
        }
        $conn->close();
        return $success;
    }

    function getError()
    {
        return $this->conn->error;
    }

    function connect()
    {
        $this->conn = new mysqli(
            $this->servername,
            $this->username,
            $this->password,
            $this->dbname
        );
        if ($this->conn->connect_error) {
            return FALSE;
        }
        return TRUE;
    }

    function close()
    {
          $this->conn->close();
        $this->conn = NULL;
    }

    function testConn()
    {
        if(is_null($this->conn) || $this->conn->error) {
            return FALSE;
        }
        return TRUE;
    }

    function query($query)
    {
        return $this->conn->query($query);
    }

    function prepare($query)
    {
        return $this->conn->prepare($query);
    }

    function getInsertId()
    {
        return $this->conn->insert_id;
    }

    function get()
    {
        $conn = $this->conn;
        $this->conn = NULL;
        return $conn;
    }
}
?>
