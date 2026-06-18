<?php

use Bitrix\Main\UI\Extension;
use Hipot\News\Renderer;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Шаблон компонента hipot:news.list (первичный рендер, страница 1).
 *
 * Файлы style.css и script.js из этой папки Bitrix подключает автоматически:
 *   — style.css добавляется в <head> через $APPLICATION->ShowCSS()
 *   — script.js добавляется перед </body> через $APPLICATION->ShowJS()
 *
 * Extension 'main.core' гарантирует наличие BX.ajax.runAction в браузере.
 * JS-файл шаблона использует эту функцию для AJAX-навигации.
 */

Extension::load('main.core');

$totalPages  = (int) ($arResult['TOTAL_PAGES']  ?? 0);
$currentPage = (int) ($arResult['CURRENT_PAGE'] ?? 1);
$items       = $arResult['ITEMS'] ?? [];
?>
<section class="news-feed"
         id="news-feed"
         data-page="<?= $currentPage ?>"
         data-total="<?= $totalPages ?>">

    <div class="news-feed__list" id="news-list">
        <?php if (empty($items)): ?>
            <p class="news-feed__empty">Новостей пока нет.</p>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <?= Renderer::renderCard($item) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="news-feed__nav" aria-label="Навигация по новостям">
        <button class="news-feed__btn news-feed__btn--prev"
                type="button"
                aria-label="Предыдущие новости">&lsaquo;</button>

        <span class="news-feed__counter" id="news-counter">
            <?= $currentPage ?> / <?= $totalPages ?>
        </span>

        <button class="news-feed__btn news-feed__btn--next"
                type="button"
                aria-label="Следующие новости">&rsaquo;</button>
    </nav>
    <?php endif; ?>

</section>
