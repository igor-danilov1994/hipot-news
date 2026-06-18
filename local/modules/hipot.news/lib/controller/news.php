<?php

declare(strict_types=1);

namespace Hipot\News\Controller;

use Bitrix\Main\Engine\Controller;
use Hipot\News\NewsTable;
use Hipot\News\Renderer;

/**
 * AJAX-контроллер модуля hipot.news.
 *
 * Маршрут: POST /bitrix/services/main/ajax.php?action=hipot:news.News.getPage
 * JS-вызов: BX.ajax.runAction('hipot:news.News.getPage', { data: { page: N } })
 *
 * Строка действия строится Bitrix по схеме:
 *   vendor:module . ClassName . actionName
 *   → hipot:news   . News      . getPage
 *
 * Контроллер возвращает готовый HTML (карточки двух новостей).
 * Bitrix D7 оборачивает возвращённую строку в стандартный ответ:
 *   { "status": "success", "data": "<html>", "errors": [] }
 * JS читает response.data и подставляет в DOM.
 *
 * Безопасность:
 * — BX.ajax.runAction автоматически включает sessid (CSRF-токен) в POST.
 * — Параметр $page жёстко типизирован как int — SQL-инъекции исключены.
 * — prefilters убирают обязательную авторизацию (лента новостей публична).
 */
class News extends Controller
{
    /**
     * Разрешаем вызов без авторизации — новости открыты для всех посетителей.
     * CSRF-защита (sessid) остаётся активной на уровне ajax.php.
     */
    public function configureActions(): array
    {
        return [
            'getPage' => [
                'prefilters' => [],
            ],
        ];
    }

    /**
     * Возвращает HTML-разметку двух новостей запрошенной страницы.
     *
     * @param int $page  Номер страницы (1-based). Нормализуется по кольцу.
     */
    public function getPageAction(int $page = 1): string
    {
        $totalPages = NewsTable::getTotalPages();

        if ($totalPages === 0) {
            return '<p class="news-feed__empty">Новостей пока нет.</p>';
        }

        // Кольцевая нормализация: страница 0 → последняя, страница total+1 → первая
        $page = (($page - 1) % $totalPages + $totalPages) % $totalPages + 1;

        $items = NewsTable::getPage($page);
        $html  = '';

        foreach ($items as $item) {
            $html .= Renderer::renderCard($item);
        }

        return $html;
    }
}
