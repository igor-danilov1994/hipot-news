# hipot.news — Тестовое задание: лента новостей на 1С-Битрикс

Кастомный модуль Bitrix D7, который после установки из админки создаёт:
- **Highload-блок** `HipotNews` с 8 тестовыми новостями
- **ООП-компонент** `hipot:news.list` для отображения ленты
- **Публичную страницу** `/news/` с AJAX-навигацией по кольцу (по 2 новости)

## Структура репозитория

```
hipot studio/
├── local/modules/hipot.news/   ← Сам модуль (копируется в Bitrix /local/modules/)
│   ├── include.php             ← Автозагрузка классов модуля
│   ├── .settings.php           ← Namespace контроллеров (для BX.ajax.runAction)
│   ├── install/
│   │   ├── index.php           ← Установщик (class hipot_news extends CModule)
│   │   ├── version.php
│   │   ├── components/hipot/news.list/  ← Исходник компонента
│   │   └── public/news/index.php        ← Страница /news/
│   └── lib/                    ← PHP-классы модуля
│       ├── installer.php       ← Создание HL-блока, UF-полей, засев данных
│       ├── newstable.php       ← ORM-выборки с кешированием
│       ├── renderer.php        ← Единый рендер HTML-карточки новости
│       └── controller/news.php ← AJAX D7-контроллер
└── dev/                        ← Docker-стенд для локальной проверки
    └── README.md               ← Инструкция по развёртыванию
```

## Быстрый старт

### 1. Скопировать модуль в Bitrix

```bash
cp -r local/modules/hipot.news  /path/to/bitrix/local/modules/
```

### 2. Установить из админки

Административный раздел → **Marketplace → Модули** → найти **«Лента новостей»** → Установить.

### 3. Открыть результат

[http://your-site.local/news/](http://your-site.local/news/)

## Локальная установка через Docker

Смотри [dev/README.md](dev/README.md).

---

## Архитектура (поток данных)

```
/news/index.php
  │
  ├─ Первый рендер (PHP)
  │   IncludeComponent('hipot:news.list') → class.php → NewsTable::getPage(1)
  │   → template.php → Renderer::renderCard() × 2 → HTML страницы
  │
  └─ Клик стрелки (AJAX)
      BX.ajax.runAction('hipot:news.News.getPage', { data: { page: N } })
        → Controller\News::getPageAction(int $page)
          → NewsTable::getPage($page)  [ORM-кеш]
          → Renderer::renderCard() × 2
          → return HTML-строка
        → JS: list.innerHTML = response.data
```

## Технологии

| Слой       | Инструмент                               |
|------------|------------------------------------------|
| Данные     | Bitrix Highload-блоки (ORM D7)           |
| Кеш        | ORM-кеш (`cache => [ttl => 36000]`)      |
| AJAX       | Bitrix D7 Engine\Controller + BX.ajax    |
| Компонент  | ООП CBitrixComponent + startResultCache  |
| Фронтенд   | Vanilla JS (BX.ajax.runAction), CSS BEM  |
