<?php

use Bitrix\Main\Loader;
use Hipot\News\NewsTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * ООП-компонент «Лента новостей» (hipot:news.list).
 *
 * Отвечает только за первичный серверный рендер (страница 1).
 * Дальнейшая навигация по страницам — через AJAX-контроллер
 * \Hipot\News\Controller\News::getPageAction().
 *
 * Результат компонента кешируется через startResultCache/endResultCache
 * (кеш результата компонента — уровень целиком отрендеренного шаблона).
 * ORM-выборки внутри NewsTable дополнительно кешируются на уровне ORM.
 */
class NewsListComponent extends CBitrixComponent
{
    public function onPrepareComponentParams(array $arParams): array
    {
        $arParams['CACHE_TIME'] = (int) ($arParams['CACHE_TIME'] ?? 3600);
        $arParams['CACHE_TYPE'] = (string) ($arParams['CACHE_TYPE'] ?? 'A');

        return $arParams;
    }

    public function executeComponent(): void
    {
        if (!Loader::includeModule('hipot.news')) {
            ShowError('Модуль hipot.news не установлен.');
            return;
        }

        // Формируем уникальный ID кеша (здесь он один — компонент всегда
        // показывает первую страницу при первом рендере)
        $cacheId = $this->getComponentCacheId();

        if ($this->startResultCache($this->arParams['CACHE_TIME'], $cacheId)) {
            $this->arResult = $this->fetchData();
            $this->endResultCache();
        }

        $this->includeComponentTemplate();
    }

    // -------------------------------------------------------------------------

    /**
     * Загружает данные из HL-блока для первичного рендера (страница 1).
     *
     * @return array{ITEMS: array<int, array<string, mixed>>, TOTAL_PAGES: int, CURRENT_PAGE: int}
     */
    private function fetchData(): array
    {
        $totalPages = NewsTable::getTotalPages();

        return [
            'ITEMS'        => $totalPages > 0 ? NewsTable::getPage(1) : [],
            'TOTAL_PAGES'  => $totalPages,
            'CURRENT_PAGE' => 1,
        ];
    }

    /**
     * ID кеша компонента. При необходимости можно расширить (сайт, язык и т.п.).
     */
    private function getComponentCacheId(): string
    {
        return md5('hipot.news.list.page.1');
    }
}
