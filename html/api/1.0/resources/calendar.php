<?php

require_once 'class/token_class.php';
require_once 'class/calendar_class.php';
require_once 'lib/json_array.php';


$app->get('/rest/calendar/{id}', function ($requ, $resp, $args) {
    if (!Token::validate()) {
        return $resp->withStatus(UNAUTHORITED);
    }

    $cal = CalendarModel::get($args["id"]);

    if (is_null($cal)) {
        return $resp->withStatus(NOT_FOUND);
    }

    if (!Token::validateUser($cal->owner_id) && $cal->visibility == V_PRIVATE) {
        return $resp->withStatus(FORBIDDEN);
    }

    return $resp->getBody()->write($cal->toJSON());
});

$app->post('/rest/calendar', function ($requ, $resp, $args) {
    if (!Token::validate()) {
        return $resp->withStatus(UNAUTHORIZED);
    }
    $cal = CalendarModel::byArray($requ->getParsedBody());

    if (!Token::validateUser($cal->owner_id)) {
        return $resp->withStatus(FORBIDDEN);
    }

    $id = $cal->post();

    if (is_null($id)) {
        return $resp->withStatus(500);
    }

    $resp->getBody()->write($id);

    return $resp->withStatus(CREATED);
});

$app->put('/rest/calendar', function ($requ, $resp, $args) {
    if (!Token::validate()) {
      return $resp->withStatus(UNAUTORIZED);
    }

    $cal = CalendarModel::byArray($requ->getParsedBody());
    $cal_old = CalendarModel::get($cal->calendar_id);

    if (!is_null($cal_old) && $cal->owner_id != $cal_old->owner_id) {
      return $resp->withStatus(FORBIDDEN);
    }

    $vis = $cal_old->visibility;

    if (
        !Token::validateUser($cal->owner_id)
        && (is_null($cal_old) || $vis < V_PUBLIC)
    ) {
      return $resp->withStatus(FORBIDDEN);
    }

    $id = $cal->put();
    $resp->getBody()->write($id);

    return $resp->withStatus(CREATED);
});

$app->delete('/rest/calendar/{id}', function ($requ, $resp, $args) {
    if (!Token::validate()) {
      return $resp->withStatus(UNAUTORIZED);
    }

    $cal = CalendarModel::get($args['id']);

    if (is_null($cal)) {
      return $resp->withStatus(NOT_FOUND);
    }

    if (!Token::validateUser($cal->owner_id)){
      return $resp->withStatus(FORBIDDEN);
    }

    if ($cal->delete()) {
      return $resp->withStatus(NO_CONTENT);
    }

    return $resp->withStatus(500);
});

$app->get('/rest/calendar', function ($requ, $resp, $args) {
    if (!Token::validate()) {
      return $response->withStatus(UNAUTHORIZED);
    }

    $user = $requ->getQueryParam("user_id", null);
    $search = $requ->getQueryParam("search_string", "");
    $array = CalendarModel::getByUserAndSearch($user, $search);

    $resultArray = array();
    foreach ($array as $calendar) {
        if (
            Token::validate($calendar->owner_id)
            || $calendar->visibility > V_PRIVATE
        ) {
            array_push($resultArray, $calendar);
        }
    }

    $json = arrayToJSON($array);

    return $resp->getBody()->write($json);
});
?>
