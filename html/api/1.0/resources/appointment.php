<?php

require_once 'class/token_class.php';
require_once 'class/calendar_class.php';
require_once 'class/appointment_class.php';

$app->get('/rest/appointment/{id}', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $appointment = Appointment::byId($args["id"]);
    if (is_null($appointment)) {
        return $response->withStatus(NOT_FOUND);
    }

    $calendar = CalendarModel::byId($appointment->calendar_id);
    if (
        !Token::validateUser($calendar->owner_id) &&
        $calendar->visibility == V_PRIVATE
    ) {
        return $response->withStatus(FORBIDDEN);
    }

    $json = $appointment->toJSON();
    $response->getBody()->write($json);
    return $resp;
});

$app->post('/rest/appointment', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $appointment = Appointment::byArray($request->getParsedBody());

    if($appointment->start > $appointment->end) {
        return $response->withStatus(400);
    }

    $calendar = CalendarModel::byId($appointment->calendar_id);

    if (
        !Token::validateUser($calendar->owner_id) &&
        $calendar->visibility < V_PUBLIC
    ) {
        return $resp->withStatus(FORBIDDEN);
    }

    $id = $appointment->create();
    $response->getBody()->write($id);
    return $response->withStatus(CREATED);
});

$app->put('/rest/appointment', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $appointment = Appointment::byArray($request->getParsedBody());
    $oldAppointment = Appointment::byId($appointment->appointment_id);
    $calendar = CalendarModel::byId($appointment->calendar_id);

    if (
        !Token::validateUser($calendar->owner_id) &&
        ($calendar->visibility<V_PUBLIC || is_null($oldAppointment))
    ) {
        return $response->withStatus(FORBIDDEN);
    }

    $id = $appointment->update();
    $response->getBody()->write($id);
    return $response->withStatus(CREATED);
});

$app->delete('/rest/appointment/{id}', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $appointment = Appointment::byId($args['id']);

    if (is_null($appointment)) {
        return $response->withStatus(NOT_FOUND);
    }

    $calendar = CalendarModel::byId($appointment->calendar_id);

    if (is_null($calendar)) {
        return $response->withStatus(NOT_FOUND);
    }

    if (!Token::validateUser($calendar->owner_id)){
        return $response->withStatus(FORBIDDEN);
    }

    if($appointment->delete()) {
        return $response->withStatus(NO_CONTENT);
    }

    return $response->withStatus(500);
});

$app->get('/rest/appointment', function (
    $request,
    $response,
    $args
) {
    if (!Token::validate()) {
        return $response->withStatus(UNAUTHORIZED);
    }

    $after = $request->getQueryParam('after', NULL);
    $before = $request->getQueryParam('before', NULL);
    $calendarId = $request->getQueryParam('calendar_id', NULL);

    $calendar = CalendarModel::byId($calendarId);
    if (is_null($calendar)) {
        return $response->getBody()->write(arrayToJSON(array()));
    }

    if (
        !Token::validateUser($calendar->owner_id) &&
        $calendar->visibility == V_PRIVATE
    ) {
        return $response->withStatus(FORBIDDEN);
    }

    $appointment = Appointment::search($after, $before, $calendarId);
    $json = arrayToJSON($appointment);
    return $response->getBody()->write($json);
});
?>
