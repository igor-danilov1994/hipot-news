# Локальный стенд (Docker)

## Структура
```
dev/
├── docker-compose.yml   — nginx + PHP 8.2-FPM + MySQL 8.0
├── nginx/site.conf      — виртуальный хост с Bitrix URL-rewrite
├── php/Dockerfile       — PHP с нужными расширениями
├── php/bitrix.ini       — параметры PHP для Bitrix
└── mysql/my.cnf         — sql_mode="", utf8mb4, mysql_native_password
```

Веб-рут смонтирован из папки **`../www/`** (создаётся вручную рядом с `dev/`).

---

## Шаг 1 — Создать папку веб-рута

```bash
mkdir -p "$(pwd)/../www"
```

---

## Шаг 2 — Поднять контейнеры

```bash
cd dev
docker compose up -d
# Дождаться: php и db стартуют ~30 сек
docker compose ps
```

---

## Шаг 3 — Установить 1С-Битрикс (30-дневный триал)

1. Зарегистрируйтесь / войдите на [1c-bitrix.ru](https://www.1c-bitrix.ru/) и скачайте
   **«Управление сайтом — Стандартный» (триал)** — файл `bitrix_setup.php` или полный архив `.tar.gz`.

2. Скопируйте `bitrixsetup.php` в папку `www/`:
   ```bash
   cp ~/Downloads/bitrixsetup.php ../www/
   ```

3. Откройте в браузере: [http://localhost/bitrixsetup.php](http://localhost/bitrixsetup.php)

4. В мастере установки укажите параметры БД из `docker-compose.yml`:
   - Хост: `db`
   - База: `bitrix`
   - Пользователь: `bitrix`
   - Пароль: `bitrix_pass`

5. Завершите установку — по умолчанию создаётся демо-сайт.

---

## Шаг 4 — Подключить модуль hipot.news

```bash
# Копируем модуль в веб-рут
cp -r ../local ../www/
```

После этого:
- Откройте **Административный раздел** → `http://localhost/bitrix/admin/`
- Перейдите: **Marketplace → Установленные решения → Модули**
- Найдите модуль **«Лента новостей»** (`hipot.news`) и нажмите **Установить**

---

## Шаг 5 — Проверить результат

Откройте [http://localhost/news/](http://localhost/news/) — должна появиться страница с
двумя карточками новостей и стрелками `‹ ›`.

### Чек-лист
- [ ] В Bitrix-админке виден HL-блок `HipotNews` с 8 записями (Настройки → Highload-блоки)
- [ ] `/news/` открывается, видно 2 карточки + стрелки
- [ ] Клик `›` — в DevTools Network виден POST на `ajax.php?action=hipot:news.News.getPage`, ответ — JSON с HTML в поле `data`
- [ ] Клик `›` ещё раз — переключаются новости
- [ ] С последней страницы `›` → первая (кольцо замкнуто)
- [ ] С первой страницы `‹` → последняя

---

## Полезные команды

```bash
# Просмотр логов
docker compose logs -f php
docker compose logs -f nginx

# Зайти в PHP-контейнер
docker compose exec php bash

# Остановить стенд
docker compose down

# Удалить стенд вместе с БД
docker compose down -v
```
