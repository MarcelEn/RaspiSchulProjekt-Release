<?php
require 'vendor/autoload.php';

$app = new \Slim\App;

//SQL DATE FORMAT
define("SQL_TIMESTAMP", 'Y-m-d H:i:s');

//HTTP STATUS CODES
define("CREATED", 201);
define("NO_CONTENT", 204);
define("UNAUTHORIZED", 401);
define("FORBIDDEN", 403);
define("NOT_FOUND", 404);
define("CONFLICT", 409);

//VISIBILITY
define("V_PRIVATE", 0);
define("V_PROTECTED", 1);
define("V_PUBLIC", 2);

define("NO_LOGIN_MESSAGE", "You are not logged in");

require 'resources/user.php';
require 'resources/saved_calendars.php';
require 'resources/calendar.php';
require 'resources/appointment.php';
require 'resources/authentification.php';
require 'resources/data.php';

$app->get('/version', function ($request, $response, $args) {
    $response->getBody()->write("Raspi-Projekt - Calendar API v1.0");
    return $response;
});

$app->get('/me', function ($request, $response, $args) {
    $user_id = Token::getUID();
    $response->getBody()->write($user_id);
    return $response;
});

$app->run();
?>
