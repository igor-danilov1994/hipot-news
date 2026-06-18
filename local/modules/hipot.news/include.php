<?php

/**
 * Точка входа модуля hipot.news.
 * Этот файл подключается Bitrix автоматически при Loader::includeModule('hipot.news').
 * Регистрирует карту автозагрузки классов (явная регистрация надёжнее PSR-4-сканирования,
 * особенно в момент установки модуля, когда ядро ещё не имеет кешей путей).
 */

use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::registerAutoLoadClasses('hipot.news', [
    // Имена файлов — строчные (Bitrix-автозагрузчик нормализует класс к lowercase
    // и ищет файл по этому пути относительно корня модуля)
    'Hipot\\News\\Installer'        => 'lib/installer.php',
    'Hipot\\News\\NewsTable'        => 'lib/newstable.php',
    'Hipot\\News\\Renderer'         => 'lib/renderer.php',
    'Hipot\\News\\Controller\\News' => 'lib/controller/news.php',
]);
