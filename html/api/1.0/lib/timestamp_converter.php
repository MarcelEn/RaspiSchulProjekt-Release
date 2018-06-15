<?php

function convertTimestampToDateTime($timestampUX)
{
    $timestamp = $timestampUX/1000;
    $timestamp = (int) $timestamp;
    $dateTime = new DateTime();
    $dateTime->setTimestamp($timestamp);
    return $dateTime;
}

function convertDateTimeToTimestamp($dateTime)
{
    $timestamp = $dateTime->getTimestamp();
    $result = $timestamp * 1000;
    return $result;
}
?>
