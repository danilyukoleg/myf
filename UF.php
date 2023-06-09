<?php

// Функция отображения даты на русском
function rdate($format, $timestamp = null, $case = 0)
{
if ($timestamp === null)
$timestamp = time();

static $loc =
'Январ,ь,я,е,ю,ём,е
Феврал,ь,я,е,ю,ём,е
Март, ,а,е,у,ом,е
Апрел,ь,я,е,ю,ем,е
Ма,й,я,е,ю,ем,е
Июн,ь,я,е,ю,ем,е
Июл,ь,я,е,ю,ем,е
Август, ,а,е,у,ом,е
Сентябр,ь,я,е,ю,ём,е
Октябр,ь,я,е,ю,ём,е
Ноябр,ь,я,е,ю,ём,е
Декабр,ь,я,е,ю,ём,е';

if (is_string($loc)) {
$months = array_map('trim', explode("\n", $loc));
$loc = array();
foreach ($months as $monthLocale) {
$cases = explode(',', $monthLocale);
$base = array_shift($cases);

$cases = array_map('trim', $cases);

$loc[] = array(
'base' => $base,
'cases' => $cases,
);
}
}

$m = (int)date('m', $timestamp) - 1;

$F = $loc[$m]['base'] . $loc[$m]['cases'][$case]; // Полное название месяца с заглавной буквы
$f = mb_convert_case($F,MB_CASE_LOWER,"UTF-8"); // Полное название месяца с прописной буквы
$format = strtr($format, array(
'F' => $F,
'f' => $f,
'M' => substr($F, 0, 3),
));

return date($format, $timestamp);
}