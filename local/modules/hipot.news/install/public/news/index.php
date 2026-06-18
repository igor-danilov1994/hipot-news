<?php
/**
 * Публичная страница ленты новостей.
 * Этот файл копируется в DOCUMENT_ROOT/news/index.php при установке модуля.
 *
 * Не редактируйте этот файл напрямую — он перезаписывается при переустановке модуля.
 * Всю логику меняйте в компоненте hipot:news.list или его шаблоне.
 */
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetTitle('Лента новостей');
?>

<?php $APPLICATION->IncludeComponent(
    'hipot:news.list',
    '.default',
    [
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '3600',
    ]
); ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
