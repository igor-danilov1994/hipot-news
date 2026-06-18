<?php
/**
 * Демо-имитация D7-контроллера \Hipot\News\Controller\News::getPageAction().
 *
 * В реальном Bitrix этот маршрут обрабатывается через:
 *   POST /bitrix/services/main/ajax.php?action=hipot:news.News.getPage
 *
 * Здесь воспроизводится та же бизнес-логика:
 *   — кольцевая нормализация страницы
 *   — выборка 2 новостей с нужного смещения
 *   — рендер через renderCard() с htmlspecialchars (вместо htmlspecialcharsbx)
 */

declare(strict_types=1);

// --- Bitrix-stub: htmlspecialcharsbx → htmlspecialchars -------------------------
if (!function_exists('htmlspecialcharsbx')) {
    function htmlspecialcharsbx(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// --- Тестовые данные (дублируют Installer::getSeedItems()) ---------------------
function getDemoItems(): array
{
    return [
        ['title' => 'Запуск нового интернет-проекта',    'preview' => 'Команда hipot-studio успешно запустила масштабный интернет-проект для ведущего ритейлера региона. Проект включает интернет-магазин, мобильное приложение и интеграцию с 1С.',                                                                          'date' => '01.06.2026'],
        ['title' => 'PHP 8.2 — новые возможности',       'preview' => 'В PHP 8.2 появились readonly-классы, DNF-типы и улучшенная поддержка Fibers. Рассказываем, как применять эти возможности в проектах на 1С-Битрикс без нарушения обратной совместимости.',                                          'date' => '25.05.2026'],
        ['title' => 'Highload-блоки: лучшие практики',   'preview' => 'Highload-блоки в 1С-Битрикс — мощный инструмент для хранения произвольных данных. Разбираем тонкости создания UF-полей, кеширования ORM-запросов и индексирования таблиц.',                                               'date' => '18.05.2026'],
        ['title' => 'AJAX через D7-контроллер модуля',   'preview' => 'Контроллеры Bitrix D7 — правильный способ обработки AJAX-запросов. Используем BX.ajax.runAction на фронтенде и Engine\Controller на бэкенде: сессия, CSRF и роутинг «из коробки».',                                          'date' => '10.05.2026'],
        ['title' => 'Оптимизация производительности',    'preview' => 'Комплексный аудит интернет-магазина: тегированный кеш, CDN, сжатие CSS/JS, пересмотр ORM-запросов — результат 40% снижение TTFB.',                                                                                               'date' => '28.04.2026'],
        ['title' => 'Открыта вакансия PHP-разработчика', 'preview' => 'Ищем опытного PHP-разработчика для долгосрочного сотрудничества по развитию интернет-проектов на 1С-Битрикс. Удалённая работа, гибкий график.',                                                                                  'date' => '15.04.2026'],
        ['title' => 'Апгрейд с Bitrix 14 на актуальную версию', 'preview' => 'Провели масштабный апгрейд: обновили ядро, перевели шаблон с D5 на D7, переписали компоненты на ООП-стиль. Проект не закрывался ни на минуту.',                                                                         'date' => '05.04.2026'],
        ['title' => 'Кеширование в 1С-Битрикс: полный гайд', 'preview' => 'Разбираем все уровни кеширования Битрикса: кеш компонента (startResultCache), ORM-кеш, тегированный кеш и managed-cache. Когда что применять и как сбрасывать по событию.',                                              'date' => '20.03.2026'],
    ];
}

// --- Логика контроллера (зеркало Controller\News::getPageAction) ---------------
$items      = getDemoItems();
$total      = count($items);
$perPage    = 2;
$totalPages = (int) ceil($total / $perPage);

$page = (int) ($_GET['page'] ?? 1);
// Кольцевая нормализация — точная копия PHP-формулы из контроллера
$page = (($page - 1) % $totalPages + $totalPages) % $totalPages + 1;

$offset     = ($page - 1) * $perPage;
$pageItems  = array_slice($items, $offset, $perPage);

// --- Рендер карточек (зеркало Renderer::renderCard) ---------------------------
foreach ($pageItems as $item) {
    $title   = htmlspecialcharsbx($item['title']);
    $preview = htmlspecialcharsbx($item['preview']);
    $date    = htmlspecialcharsbx($item['date']);
    // Конвертируем d.m.Y → Y-m-d для атрибута datetime
    $parts   = explode('.', $date);
    $dateIso = count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : '';

    echo <<<HTML
    <article class="news-card">
        <time class="news-card__date" datetime="{$dateIso}">{$date}</time>
        <h2 class="news-card__title">{$title}</h2>
        <p class="news-card__preview">{$preview}</p>
    </article>

    HTML;
}
