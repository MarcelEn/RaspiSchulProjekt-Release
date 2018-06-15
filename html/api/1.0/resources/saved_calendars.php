<?php
require_once 'class/token_class.php';
require_once 'lib/json_array.php';
require_once 'class/calendar_class.php';
require_once 'class/saved_calendar_class.php';

$app->get('/rest/calendar/saved', function ($requ, $resp, $args) {
    if (!Token::validate()) {
        return $resp->withStatus(UNAUTHORIZED);
    }
    $uid = Token::getUID();
    $array = SavedCalendar::get($uid);
    $json = arrayToJSON($array);
    $resp->getBody()->write($json);
    return $resp;
});

$app->post('/rest/calendar/saved/{id}', function ($requ, $resp, $args) {
    if (!Token::validate()) {
        return $resp->withStatus(UNAUTHORIZED);
    }
    $calendar = CalendarModel::get($args['id']);
    if (Token::validateUser($calendar->owner_id)) {
        return $resp->withStatus(FORBIDDEN);
    }
    $uid = Token::getUID();
    $entry = SavedCalendar::getOne($uid, $args['id']);
    if(!is_null($entry)) {
        return $resp->withStatus(CONFLICT);
    }
    if (SavedCalendar::add($uid, $args['id'])) {
        return $resp->withStatus(CREATED);
    }
    return $resp;
});

$app->delete('/rest/calendar/saved/{id}', function ($requ, $resp, $args) {
    if (!Token::validate()) {
        return $resp->withStatus(UNAUTHORIZED);
    }
    $uid = Token::getUID();
    $calendar = SavedCalendar::getOne($uid, $args['id']);
    if (is_null($calendar)) {
        return $resp->withStatus(NOT_FOUND);
    }
    if (SavedCalendar::delete($uid, $args['id'])) {
        return $resp->withStatus(NO_CONTENT);
    }
    return $resp->withStatus(500);
});

?>
