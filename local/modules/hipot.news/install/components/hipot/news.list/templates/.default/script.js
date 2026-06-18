/**
 * Скрипт AJAX-навигации компонента hipot:news.list.
 *
 * Зависимости (гарантируются template.php через Extension::load('main.core')):
 *   BX.ready        — запуск после загрузки DOM
 *   BX.ajax.runAction — AJAX-вызов D7-контроллера модуля
 *
 * Кольцевая математика (total = 4 страницы):
 *   следующая: page % total + 1      (4 % 4 + 1 = 1  — после последней → первая)
 *   предыдущая: (page - 2 + total) % total + 1  (1-2+4=3; 3%4=3; 3+1=4 — с первой → последняя)
 *
 * Контроллер принимает: { page: N }
 * Контроллер возвращает: HTML двух карточек (строка)
 * BX.ajax.runAction резолвит промис с полем 'data' ответа, то есть с HTML-строкой.
 */
BX.ready(function () {
    'use strict';

    var feed    = document.getElementById('news-feed');
    var list    = document.getElementById('news-list');
    var counter = document.getElementById('news-counter');

    if (!feed || !list) {
        return; // компонент не найден на странице
    }

    var btnPrev   = feed.querySelector('.news-feed__btn--prev');
    var btnNext   = feed.querySelector('.news-feed__btn--next');
    var isLoading = false;

    // -------------------------------------------------------------------

    function getCurrentPage() {
        return parseInt(feed.dataset.page, 10) || 1;
    }

    function getTotalPages() {
        return parseInt(feed.dataset.total, 10) || 1;
    }

    function calcNextPage(current, total) {
        return (current % total) + 1;
    }

    function calcPrevPage(current, total) {
        return ((current - 2 + total) % total) + 1;
    }

    // -------------------------------------------------------------------

    function setLoading(state) {
        isLoading = state;
        feed.classList.toggle('news-feed--loading', state);
        if (btnPrev) { btnPrev.disabled = state; }
        if (btnNext) { btnNext.disabled = state; }
    }

    function goToPage(page) {
        if (isLoading) {
            return;
        }

        var total = getTotalPages();

        setLoading(true);

        BX.ajax.runAction('hipot:news.News.getPage', {
            data: { page: page }
        }).then(
            function (html) {
                // BX.ajax.runAction резолвит промис с полем data из ответа контроллера
                list.innerHTML = html || '';
                feed.dataset.page = String(page);

                if (counter) {
                    counter.textContent = page + ' / ' + total;
                }

                setLoading(false);
            },
            function () {
                // Ошибка AJAX — оставляем текущий контент, просто снимаем блокировку
                setLoading(false);
            }
        );
    }

    // -------------------------------------------------------------------

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
