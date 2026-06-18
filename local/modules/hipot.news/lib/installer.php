<?php

declare(strict_types=1);

namespace Hipot\News;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use CUserTypeEntity;
use RuntimeException;

/**
 * Управляет жизненным циклом структуры данных модуля:
 *   install() — создаёт Highload-блок, UF-поля, засевает тестовые новости.
 *   uninstall() — удаляет Highload-блок вместе с таблицей.
 */
final class Installer
{
    public const HL_NAME    = 'HipotNews';
    public const TABLE_NAME = 'hipot_news';

    // UF-поля создаются в этом порядке (SORT по 100)
    private const FIELDS = [
        [
            'FIELD_NAME'        => 'UF_TITLE',
            'USER_TYPE_ID'      => 'string',
            'SORT'              => 100,
            'MANDATORY'         => 'Y',
            'EDIT_FORM_LABEL'   => ['ru' => 'Заголовок', 'en' => 'Title'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Заголовок', 'en' => 'Title'],
            'SETTINGS'          => ['SIZE' => 255, 'ROWS' => 1, 'MIN_LENGTH' => 0, 'MAX_LENGTH' => 0],
        ],
        [
            'FIELD_NAME'        => 'UF_PREVIEW_TEXT',
            'USER_TYPE_ID'      => 'string',
            'SORT'              => 200,
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Анонс', 'en' => 'Preview text'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Анонс', 'en' => 'Preview'],
            'SETTINGS'          => ['SIZE' => 255, 'ROWS' => 5, 'MIN_LENGTH' => 0, 'MAX_LENGTH' => 0],
        ],
        [
            'FIELD_NAME'        => 'UF_DATE',
            'USER_TYPE_ID'      => 'datetime',
            'SORT'              => 300,
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Дата публикации', 'en' => 'Published at'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Дата', 'en' => 'Date'],
            'SETTINGS'          => ['DEFAULT_VALUE' => ['VALUE' => '', 'TYPE' => 'NONE']],
        ],
        [
            'FIELD_NAME'        => 'UF_SORT',
            'USER_TYPE_ID'      => 'integer',
            'SORT'              => 400,
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Сортировка', 'en' => 'Sort'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Сорт.', 'en' => 'Sort'],
            'SETTINGS'          => ['DEFAULT_VALUE' => 500, 'MIN_VALUE' => 0, 'MAX_VALUE' => 0],
        ],
        [
            'FIELD_NAME'        => 'UF_ACTIVE',
            'USER_TYPE_ID'      => 'integer',
            'SORT'              => 500,
            'MANDATORY'         => 'N',
            'EDIT_FORM_LABEL'   => ['ru' => 'Активность (1/0)', 'en' => 'Active (1/0)'],
            'LIST_COLUMN_LABEL' => ['ru' => 'Акт.', 'en' => 'Active'],
            'SETTINGS'          => ['DEFAULT_VALUE' => 1, 'MIN_VALUE' => 0, 'MAX_VALUE' => 1],
        ],
    ];

    /**
     * Создаёт Highload-блок, регистрирует UF-поля и засевает 8 тестовых новостей.
     * Идемпотентен: повторный вызов пропускает уже созданный блок.
     *
     * @throws RuntimeException если HL-блок не удалось создать
     */
    public static function install(): void
    {
        Loader::includeModule('highloadblock');

        if (self::findHlId() !== null) {
            return; // блок уже существует — повторная установка не ломает данные
        }

        $hlId = self::createHlBlock();
        self::addUserFields($hlId);
        self::seedData($hlId);
    }

    /**
     * Удаляет Highload-блок вместе с его таблицей и всеми данными.
     */
    public static function uninstall(): void
    {
        Loader::includeModule('highloadblock');

        $hlId = self::findHlId();
        if ($hlId !== null) {
            HighloadBlockTable::delete($hlId);
        }
    }

    // -------------------------------------------------------------------------
    // Внутренние методы
    // -------------------------------------------------------------------------

    private static function findHlId(): ?int
    {
        $row = HighloadBlockTable::getList([
            'select' => ['ID'],
            'filter' => ['=TABLE_NAME' => self::TABLE_NAME],
        ])->fetch();

        return $row ? (int) $row['ID'] : null;
    }

    private static function createHlBlock(): int
    {
        $result = HighloadBlockTable::add([
            'NAME'       => self::HL_NAME,
            'TABLE_NAME' => self::TABLE_NAME,
        ]);

        if (!$result->isSuccess()) {
            throw new RuntimeException(
                'Не удалось создать Highload-блок: ' . implode(', ', $result->getErrorMessages())
            );
        }

        return $result->getId();
    }

    private static function addUserFields(int $hlId): void
    {
        $entityId       = 'HLBLOCK_' . $hlId;
        $userTypeEntity = new CUserTypeEntity();

        foreach (self::FIELDS as $field) {
            $userTypeEntity->Add(
                array_merge($field, [
                    'ENTITY_ID' => $entityId,
                    'MULTIPLE'  => 'N',
                ])
            );
        }
    }

    private static function seedData(int $hlId): void
    {
        $entity    = HighloadBlockTable::compileEntity($hlId);
        $dataClass = $entity->getDataClass();

        foreach (self::getSeedItems() as $item) {
            $dataClass::add($item);
            // Ошибки засева не прерывают установку — блок создан, поля есть
        }
    }

    /**
     * 8 тестовых новостей (чётное число → ровно 4 страницы по 2, без «одиночки»).
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSeedItems(): array
    {
        return [
            [
                'UF_TITLE'        => 'Запуск нового интернет-проекта',
                'UF_PREVIEW_TEXT' => 'Команда hipot-studio успешно запустила масштабный интернет-проект для ведущего ритейлера региона. Проект включает интернет-магазин, мобильное приложение и интеграцию с 1С.',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(10, 0, 0, 6, 1, 2026)),
                'UF_SORT'         => 100,
                'UF_ACTIVE'       => 1,
            ],
            [
                'UF_TITLE'        => 'PHP 8.2 — новые возможности',
                'UF_PREVIEW_TEXT' => 'В PHP 8.2 появились readonly-классы, DNF-типы и улучшенная поддержка Fibers. Рассказываем, как применять эти возможности в проектах на 1С-Битрикс без нарушения обратной совместимости.',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(12, 0, 0, 5, 25, 2026)),
                'UF_SORT'         => 200,
                'UF_ACTIVE'       => 1,
            ],
            [
                'UF_TITLE'        => 'Highload-блоки: лучшие практики',
                'UF_PREVIEW_TEXT' => 'Highload-блоки в 1С-Битрикс — мощный инструмент для хранения произвольных структурированных данных. В статье разбираем тонкости создания UF-полей, кеширования ORM-запросов и индексирования таблиц.',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(9, 30, 0, 5, 18, 2026)),
                'UF_SORT'         => 300,
                'UF_ACTIVE'       => 1,
            ],
            [
                'UF_TITLE'        => 'AJAX через D7-контроллер модуля',
                'UF_PREVIEW_TEXT' => 'Контроллеры Bitrix D7 — правильный способ обработки AJAX-запросов в собственных модулях. Используем BX.ajax.runAction на фронтенде и Engine\Controller на бэкенде: сессия, CSRF и роутинг «из коробки».',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(11, 0, 0, 5, 10, 2026)),
                'UF_SORT'         => 400,
                'UF_ACTIVE'       => 1,
            ],
            [
                'UF_TITLE'        => 'Оптимизация производительности сайта',
                'UF_PREVIEW_TEXT' => 'Комплексный аудит и оптимизация интернет-магазина: настройка тегированного кеша, CDN, сжатие CSS/JS, пересмотр структуры ORM-запросов — результат 40% снижение TTFB.',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(14, 0, 0, 4, 28, 2026)),
                'UF_SORT'         => 500,
                'UF_ACTIVE'       => 1,
            ],
            [
                'UF_TITLE'        => 'Открыта вакансия PHP-разработчика',
                'UF_PREVIEW_TEXT' => 'Ищем опытного PHP-разработчика для долгосрочного сотрудничества по развитию интернет-проектов на 1С-Битрикс. Удалённая работа, гибкий график, почасовая оплата.',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(10, 0, 0, 4, 15, 2026)),
                'UF_SORT'         => 600,
                'UF_ACTIVE'       => 1,
            ],
            [
                'UF_TITLE'        => 'Переход клиента с Bitrix 14 на актуальную версию',
                'UF_PREVIEW_TEXT' => 'Провели масштабный апгрейд: обновили ядро, перевели шаблон с D5 на D7, переписали компоненты на ООП-стиль. Проект не закрывался ни на минуту — живая миграция без простоев.',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(16, 0, 0, 4, 5, 2026)),
                'UF_SORT'         => 700,
                'UF_ACTIVE'       => 1,
            ],
            [
                'UF_TITLE'        => 'Кеширование в 1С-Битрикс: полный гайд',
                'UF_PREVIEW_TEXT' => 'Разбираем все уровни кеширования Битрикса: кеш результата компонента (startResultCache), ORM-кеш запросов, тегированный кеш и managed-cache. Когда что применять и как сбрасывать по событию.',
                'UF_DATE'         => DateTime::createFromTimestamp(mktime(9, 0, 0, 3, 20, 2026)),
                'UF_SORT'         => 800,
                'UF_ACTIVE'       => 1,
            ],
        ];
    }
}
