<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = [
    'NAME'        => 'Лента новостей',
    'DESCRIPTION' => 'Список новостей из Highload-блока с AJAX-навигацией по кольцу (по 2 записи).',
    'ICON'        => '',
    'COMPLEX'     => 'N',
    'CACHE_PATH'  => 'N',
    'PATH'        => [
        'ID'   => 'content',
        'NAME' => 'Контент',
    ],
];
