<?php

require_once 'data/calendar_database.php';
require_once 'class/token_class.php';
require_once 'class/user_class.php';
require_once 'lib/hash.php';

$app->put('/authentification/register', function (
    $request,
    $response,
    $args
) {
    $userData = $request->getParsedBody();
    $userData['user_id'] = -1;

    $user = USER::byArray($userData);
    $user->hashPassword();

    $user_id = $user->create();
    if (is_null($user_id)) {
        return $response->withStatus(400);
    }

    Token::set($user_id, 0);
    $response->getBody()->write($user_id);
    return $response;
});

$app->get('/authentification/username/{name}', function (
    $request,
    $response,
    $args
) {
    $userInDatabase = User::byName($args['name']);
    if(!is_null($userInDatabase)) {
        return $response->withStatus(200);
    }
    return $response->withStatus(NO_CONTENT);
});

$app->post('/authentification/login', function (
    $request,
    $response,
    $args
) {
    $loginData = $request->getParsedBody();
    $user = User::byName($loginData['user_name']);
    if(is_null($user)) {
        return $response->withStatus(404);
    }

    if(!$user->checkPassword($loginData['password_hash'])) {
        return $response->withStatus(400);
    }

    Token::set($user->user_id, $loginData['long_time']);
    return $response->withStatus(200);
});

$app->delete('/authentification/logout', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORISED);
    }

    $uid = Token::getUID();
    if (Token::deleteAllTokens($uid)) {
        return $response->withStatus(NO_CONTENT);
    }
    return $response->withStatus(500);
});

$app->post('/authentification/password', function (
    $request,
    $response,
    $args
) {
    $data = $request->getParsedBody();

    $user_id = Token::getUID();
    $user = User::byId($user_id);
    if(is_null($user)) {
        return $response->withStatus(400);
    }

    if(!$user->checkPassword($data['old_password_hash'])) {
        return $response->withStatus(400);
    }

    if(!$user->setPassword($data['new_password_hash'])) {
        return $response->withStatus(400);
    }
    return $response->withStatus(200);
});

$app->get('/authentification/test_token', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }
    $uid = Token::getUID();
    $response->getBody()->write($uid);
    return $response;
});

?>
