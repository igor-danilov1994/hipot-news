<?php

/**
 * Настройки модуля hipot.news.
 *
 * Секция 'controllers' говорит Bitrix D7, в каком PHP-неймспейсе искать классы-контроллеры.
 * Это определяет строку действия для BX.ajax.runAction:
 *   \Hipot\News\Controller\News::getPageAction()
 *   → 'hipot:news.News.getPage'
 *     (vendor:module . ClassName . actionName, без суффикса Action)
 */
return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Hipot\\News\\Controller',
        ],
        'readonly' => true,
    ],
];
