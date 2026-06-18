<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentParameters = [
    'GROUPS' => [
        'CACHE' => ['NAME' => 'Кеширование'],
    ],
    'PARAMETERS' => [
        'CACHE_TYPE' => [
            'PARENT' => 'CACHE',
            'NAME'   => 'Тип кеша',
            'TYPE'   => 'STRING',
            'VALUES' => ['A' => 'Авто', 'Y' => 'Включён', 'N' => 'Выключен'],
            'DEFAULT' => 'A',
        ],
        'CACHE_TIME' => [
            'PARENT'  => 'CACHE',
            'NAME'    => 'Время кеша (сек.)',
            'TYPE'    => 'STRING',
            'DEFAULT' => '3600',
        ],
    ],
];
