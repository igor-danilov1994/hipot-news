<?php
/**
 * Демо-страница — точный аналог /news/ из модуля hipot.news.
 *
 * Воспроизводит то, что Bitrix отрисовывает через:
 *   template.php + Renderer::renderCard() + style.css + script.js
 *
 * Данные берутся из тех же тестовых записей, что создаёт Installer::getSeedItems().
 */

declare(strict_types=1);

if (!function_exists('htmlspecialcharsbx')) {
    function htmlspecialcharsbx(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$seedItems = [
    ['title' => 'Запуск нового интернет-проекта',         'preview' => 'Команда hipot-studio успешно запустила масштабный интернет-проект для ведущего ритейлера региона. Проект включает интернет-магазин, мобильное приложение и интеграцию с 1С.',                                                           'date' => '01.06.2026'],
    ['title' => 'PHP 8.2 — новые возможности',            'preview' => 'В PHP 8.2 появились readonly-классы, DNF-типы и улучшенная поддержка Fibers. Рассказываем, как применять эти возможности в проектах на 1С-Битрикс без нарушения обратной совместимости.',                                 'date' => '25.05.2026'],
    ['title' => 'Highload-блоки: лучшие практики',        'preview' => 'Highload-блоки в 1С-Битрикс — мощный инструмент для хранения произвольных данных. Разбираем тонкости создания UF-полей, кеширования ORM-запросов и индексирования таблиц.',                                        'date' => '18.05.2026'],
    ['title' => 'AJAX через D7-контроллер модуля',        'preview' => 'Контроллеры Bitrix D7 — правильный способ обработки AJAX-запросов. Используем BX.ajax.runAction на фронтенде и Engine\Controller на бэкенде: сессия, CSRF и роутинг «из коробки».',                                   'date' => '10.05.2026'],
    ['title' => 'Оптимизация производительности',         'preview' => 'Комплексный аудит интернет-магазина: тегированный кеш, CDN, сжатие CSS/JS, пересмотр ORM-запросов — результат 40% снижение TTFB.',                                                                                        'date' => '28.04.2026'],
    ['title' => 'Открыта вакансия PHP-разработчика',      'preview' => 'Ищем опытного PHP-разработчика для долгосрочного сотрудничества по развитию интернет-проектов на 1С-Битрикс. Удалённая работа, гибкий график.',                                                                           'date' => '15.04.2026'],
    ['title' => 'Апгрейд с Bitrix 14 на актуальную версию', 'preview' => 'Провели масштабный апгрейд: обновили ядро, перевели шаблон с D5 на D7, переписали компоненты на ООП-стиль. Проект не закрывался ни на минуту.',                                                                      'date' => '05.04.2026'],
    ['title' => 'Кеширование в 1С-Битрикс: полный гайд',  'preview' => 'Разбираем все уровни кеширования Битрикса: кеш компонента (startResultCache), ORM-кеш, тегированный кеш и managed-cache. Когда что применять и как сбрасывать по событию.',                                           'date' => '20.03.2026'],
];

$perPage    = 2;
$totalPages = (int) ceil(count($seedItems) / $perPage);
$firstPage  = array_slice($seedItems, 0, $perPage);

function renderCard(array $item): string
{
    $title   = htmlspecialcharsbx($item['title']);
    $preview = htmlspecialcharsbx($item['preview']);
    $date    = htmlspecialcharsbx($item['date']);
    $parts   = explode('.', $date);
    $dateIso = count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : '';

    return <<<HTML
    <article class="news-card">
        <time class="news-card__date" datetime="{$dateIso}">{$date}</time>
        <h2 class="news-card__title">{$title}</h2>
        <p class="news-card__preview">{$preview}</p>
    </article>

    HTML;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лента новостей — Demo (hipot.news)</title>
    <link rel="stylesheet" href="/demo-assets/style.css">
    <style>
        body { margin: 0; padding: 20px; background: #f5f5f5; font-family: -apple-system, sans-serif; }
        .demo-header {
            max-width: 760px; margin: 0 auto 8px; padding: 0 16px;
            font-size: 12px; color: #999; border-bottom: 1px dashed #ddd; padding-bottom: 8px;
        }
        .demo-header strong { color: #0070c5; }
    </style>
</head>
<body>
    <div class="demo-header">
        <strong>DEMO</strong> — воспроизводит страницу <code>/news/</code> из модуля <strong>hipot.news</strong>.
        Навигация и рендер карточек идентичны реальному Bitrix-компоненту.
    </div>

    <h1 style="max-width:760px;margin:16px auto 0;padding:0 16px;font-size:22px;color:#1a1a1a;">
        Лента новостей
    </h1>

    <!-- Точная копия HTML, который генерирует template.php -->
    <section class="news-feed"
             id="news-feed"
             data-page="1"
             data-total="<?= $totalPages ?>">

        <div class="news-feed__list" id="news-list">
            <?php foreach ($firstPage as $item): ?>
                <?= renderCard($item) ?>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="news-feed__nav" aria-label="Навигация по новостям">
            <button class="news-feed__btn news-feed__btn--prev"
                    type="button"
                    aria-label="Предыдущие новости">&lsaquo;</button>

            <span class="news-feed__counter" id="news-counter">
                1 / <?= $totalPages ?>
            </span>

            <button class="news-feed__btn news-feed__btn--next"
                    type="button"
                    aria-label="Следующие новости">&rsaquo;</button>
        </nav>
        <?php endif; ?>

    </section>

    <script src="/demo-assets/script.js"></script>
</body>
</html>
