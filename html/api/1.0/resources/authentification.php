<?php

require_once 'data/calendar_database.php';
require_once 'class/token_class.php';
require_once 'class/user_class.php';
require_once 'lib/hash.php';

$app->put('/authentification/register', function ($requ, $resp, $args) {
    $array = $requ->getParsedBody();
    $array['user_id'] = -1;
    $user = USER::byArray($array);
    $user->hashPassword();
    $user_id = $user->post();
    if (is_null($user_id)) {
        return $resp->withStatus(400);
    }
    Token::set($user_id, 0);
    $resp->getBody()->write($user_id);
    return $resp;
});

$app->get('/authentification/username/{name}', function ($requ, $resp, $args) {
    $userInDatabase = User::getByExactName($args['name']);
    if(!is_null($userInDatabase)) {
        return $resp->withStatus(200);
    }
    return $resp->withStatus(NO_CONTENT);
});

$app->post('/authentification/login', function ($requ, $resp, $args) {
    $loginData = $requ->getParsedBody();
    $user = User::getByExactName($loginData['user_name']);
    if(is_null($user)) {
        return $resp->withStatus(404);
    }
    if(!$user->checkPassword($loginData['password_hash'])) {
        return $resp->withStatus(400);
    }
    Token::set($user->user_id, $loginData['long_time']);
    return $resp->withStatus(200);
});

$app->delete('/authentification/logout', function ($requ, $resp, $args) {
    if (!Token::validate()) {
        return $resp->withStatus(UNAUTHORISED);
    }
    $uid = Token::getUID();
    if (Token::deleteAllTokens($uid)) {
        return $resp->withStatus(NO_CONTENT);
    }
    return $resp->withStatus(500);
});

$app->post('/authentification/password', function ($requ, $resp, $args) {
    $data = $requ->getParsedBody();
    $user_id = Token::getUID();
    $user = User::get($user_id);
    if(is_null($user)) {
        return $resp->withStatus(400);
    }
    if(!$user->checkPassword($data['old_password_hash'])) {
        return $resp->withStatus(400);
    }
    if(!$user->changePassword($data['new_password_hash'])) {
        return $resp->withStatus(400);
    }
    return $resp->withStatus(200);
});

$app->get('/authentification/test_token', function ($requ, $resp, $args) {
    if (!Token::validate()) {
        return $resp->withStatus(UNAUTHORIZED);
    }
    $uid = Token::getUID();
    $resp->getBody()->write($uid);
    return $resp;
});

?>
