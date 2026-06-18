/**
 * Навигация ленты новостей.
 *
 * В Bitrix-версии использует BX.ajax.runAction('hipot:news.News.getPage').
 * В статичном демо — данные встроены прямо в скрипт (PAGES), переключение
 * происходит без сетевых запросов.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Предгенерированные страницы (аналог ответов Controller\News::getPageAction)
    var PAGES = {
        1: '<article class="news-card"><time class="news-card__date" datetime="2026-06-01">01.06.2026</time><h2 class="news-card__title">Запуск нового интернет-проекта</h2><p class="news-card__preview">Команда hipot-studio успешно запустила масштабный интернет-проект для ведущего ритейлера региона. Проект включает интернет-магазин, мобильное приложение и интеграцию с 1С.</p></article><article class="news-card"><time class="news-card__date" datetime="2026-05-25">25.05.2026</time><h2 class="news-card__title">PHP 8.2 — новые возможности</h2><p class="news-card__preview">В PHP 8.2 появились readonly-классы, DNF-типы и улучшенная поддержка Fibers. Рассказываем, как применять эти возможности в проектах на 1С-Битрикс без нарушения обратной совместимости.</p></article>',
        2: '<article class="news-card"><time class="news-card__date" datetime="2026-05-18">18.05.2026</time><h2 class="news-card__title">Highload-блоки: лучшие практики</h2><p class="news-card__preview">Highload-блоки в 1С-Битрикс — мощный инструмент для хранения произвольных данных. Разбираем тонкости создания UF-полей, кеширования ORM-запросов и индексирования таблиц.</p></article><article class="news-card"><time class="news-card__date" datetime="2026-05-10">10.05.2026</time><h2 class="news-card__title">AJAX через D7-контроллер модуля</h2><p class="news-card__preview">Контроллеры Bitrix D7 — правильный способ обработки AJAX-запросов. Используем BX.ajax.runAction на фронтенде и Engine\\Controller на бэкенде: сессия, CSRF и роутинг «из коробки».</p></article>',
        3: '<article class="news-card"><time class="news-card__date" datetime="2026-04-28">28.04.2026</time><h2 class="news-card__title">Оптимизация производительности</h2><p class="news-card__preview">Комплексный аудит интернет-магазина: тегированный кеш, CDN, сжатие CSS/JS, пересмотр ORM-запросов — результат 40% снижение TTFB.</p></article><article class="news-card"><time class="news-card__date" datetime="2026-04-15">15.04.2026</time><h2 class="news-card__title">Открыта вакансия PHP-разработчика</h2><p class="news-card__preview">Ищем опытного PHP-разработчика для долгосрочного сотрудничества по развитию интернет-проектов на 1С-Битрикс. Удалённая работа, гибкий график.</p></article>',
        4: '<article class="news-card"><time class="news-card__date" datetime="2026-04-05">05.04.2026</time><h2 class="news-card__title">Апгрейд с Bitrix 14 на актуальную версию</h2><p class="news-card__preview">Провели масштабный апгрейд: обновили ядро, перевели шаблон с D5 на D7, переписали компоненты на ООП-стиль. Проект не закрывался ни на минуту.</p></article><article class="news-card"><time class="news-card__date" datetime="2026-03-20">20.03.2026</time><h2 class="news-card__title">Кеширование в 1С-Битрикс: полный гайд</h2><p class="news-card__preview">Разбираем все уровни кеширования Битрикса: кеш компонента (startResultCache), ORM-кеш, тегированный кеш и managed-cache. Когда что применять и как сбрасывать по событию.</p></article>'
    };

    var feed    = document.getElementById('news-feed');
    var list    = document.getElementById('news-list');
    var counter = document.getElementById('news-counter');

    if (!feed || !list) { return; }

    var btnPrev = feed.querySelector('.news-feed__btn--prev');
    var btnNext = feed.querySelector('.news-feed__btn--next');

    function getCurrentPage() { return parseInt(feed.dataset.page, 10) || 1; }
    function getTotalPages()  { return parseInt(feed.dataset.total, 10) || 1; }

    function calcNextPage(cur, tot) { return (cur % tot) + 1; }
    function calcPrevPage(cur, tot) { return ((cur - 2 + tot) % tot) + 1; }

    function goToPage(page) {
        var total = getTotalPages();
        list.innerHTML = PAGES[page] || '';
        feed.dataset.page = String(page);
        if (counter) { counter.textContent = page + ' / ' + total; }
    }

    if (btnNext) {
        btnNext.addEventListener('click', function () {
            goToPage(calcNextPage(getCurrentPage(), getTotalPages()));
        });
    }
    if (btnPrev) {
        btnPrev.addEventListener('click', function () {
            goToPage(calcPrevPage(getCurrentPage(), getTotalPages()));
        });
    }
});
