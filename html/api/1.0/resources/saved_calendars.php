<?php
require_once 'class/token_class.php';
require_once 'lib/json_array.php';
require_once 'class/calendar_class.php';
require_once 'class/saved_calendar_class.php';

$app->get('/rest/calendar/saved', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $uid = Token::getUID();
    $calendars = SavedCalendar::byUser($uid);
    $json = arrayToJSON($calendars);
    $response->getBody()->write($json);
    return $response;
});

$app->post('/rest/calendar/saved/{id}', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $calendar = CalendarModel::byId($args['id']);
    if (Token::validateUser($calendar->owner_id)) {
        return $response->withStatus(FORBIDDEN);
    }

    $uid = Token::getUID();
    $entry = SavedCalendar::getOne($uid, $args['id']);
    if(!is_null($entry)) {
        return $response->withStatus(CONFLICT);
    }

    if (SavedCalendar::add($uid, $args['id'])) {
        return $response->withStatus(CREATED);
    }
    return $response;
});

$app->delete('/rest/calendar/saved/{id}', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $uid = Token::getUID();
    $calendar = SavedCalendar::getOne($uid, $args['id']);
    if (is_null($calendar)) {
        return $response->withStatus(NOT_FOUND);
    }

    if (SavedCalendar::delete($uid, $args['id'])) {
        return $response->withStatus(NO_CONTENT);
    }
    return $response->withStatus(500);
});
?>
