<?php

require_once 'class/token_class.php';
require_once 'class/calendar_class.php';
require_once 'lib/json_array.php';


$app->get('/rest/calendar/{id}', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORITED);
    }

    $calendar = CalendarModel::byId($args["id"]);

    if (is_null($calendar)) {
        return $response->withStatus(NOT_FOUND);
    }

    if (
        !Token::validateUser($calendar->owner_id) &&
        $calendar->visibility == V_PRIVATE
    ) {
        return $response->withStatus(FORBIDDEN);
    }

    return $response->getBody()->write($calendar->toJSON());
});

$app->post('/rest/calendar', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }
    $calendar = CalendarModel::byArray($request->getParsedBody());

    if (!Token::validateUser($calendar->owner_id)) {
        return $response->withStatus(FORBIDDEN);
    }

    $calendarId = $calendar->create();

    if (is_null($calendarId)) {
        return $response->withStatus(500);
    }

    $response->getBody()->write($calendarId);

    return $response->withStatus(CREATED);
});

$app->put('/rest/calendar', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
      return $response->withStatus(UNAUTORIZED);
    }

    $calendar = CalendarModel::byArray($request->getParsedBody());
    $oldCalendar = CalendarModel::byId($calendar->calendar_id);

    if (
        !is_null($oldCalendar) &&
        $calendar->owner_id != $oldCalendar->owner_id
    ) {
      return $response->withStatus(FORBIDDEN);
    }

    if (
        !Token::validateUser($oldCalendar->owner_id)
        && (
            is_null($oldCalendar) ||
            $oldCalendar->visibility < V_PUBLIC
        )
    ) {
      return $response->withStatus(FORBIDDEN);
    }

    $id = $calendar->update();
    $response->getBody()->write($id);
    return $response->withStatus(CREATED);
});

$app->delete('/rest/calendar/{id}', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
      return $response->withStatus(UNAUTORIZED);
    }

    $calendar = CalendarModel::byId($args['id']);
    if (is_null($calendar)) {
      return $response->withStatus(NOT_FOUND);
    }

    if (!Token::validateUser($calendar->owner_id)){
      return $response->withStatus(FORBIDDEN);
    }

    if ($calendar->delete()) {
      return $response->withStatus(NO_CONTENT);
    }

    return $response->withStatus(500);
});

$app->get('/rest/calendar', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
      return $response->withStatus(UNAUTHORIZED);
    }

    $user = $request->getQueryParam("user_id", null);
    $search = $request->getQueryParam("search_string", "");
    $array = CalendarModel::search($user, $search);

    $resultArray = array();
    foreach ($array as $calendar) {
        if (
            Token::validateUser($calendar->owner_id) ||
            $calendar->visibility > V_PRIVATE
        ) {
            array_push($resultArray, $calendar);
        }
    }

    $json = arrayToJSON($resultArray);
    return $response->getBody()->write($json);
});
?>
