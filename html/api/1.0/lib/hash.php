<?php
function hashPassword($password)
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    return $password_hash;
}

function checkPassword($password, $password_hash)
{
    $success = password_verify($password, $password_hash);
    return $success;
}
?>
