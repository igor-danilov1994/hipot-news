<?php

declare(strict_types=1);

namespace Hipot\News;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;
use RuntimeException;

/**
 * Слой доступа к данным Highload-блока hipot_news.
 *
 * Все публичные методы кешируют результаты через механизм ORM-кеша Bitrix
 * (параметр 'cache' => ['ttl' => ...] в getList/getCount).
 * Это соответствует требованию ТЗ п.5 «выборки данных должны кешироваться».
 *
 * Статическое свойство $dataClass — кеш разрешения сущности внутри запроса,
 * чтобы не обращаться к HighloadBlockTable на каждом вызове.
 */
final class NewsTable
{
    public const TABLE_NAME    = 'hipot_news';
    public const ITEMS_PER_PAGE = 2;
    public const CACHE_TTL      = 36000; // 10 часов

    /** @var class-string|null */
    private static ?string $dataClass = null;

    /**
     * Возвращает FQCN DataManager-класса для HL-блока hipot_news.
     * Кеш статического свойства хранится до конца PHP-запроса.
     *
     * @return class-string
     * @throws RuntimeException если HL-блок не найден
     */
    public static function getDataClass(): string
    {
        if (self::$dataClass !== null) {
            return self::$dataClass;
        }

        Loader::includeModule('highloadblock');

        $row = HighloadBlockTable::getList([
            'select' => ['ID', 'NAME', 'TABLE_NAME'],
            'filter' => ['=TABLE_NAME' => self::TABLE_NAME],
        ])->fetch();

        if (!$row) {
            throw new RuntimeException(
                'Highload-блок «' . self::TABLE_NAME . '» не найден. Переустановите модуль hipot.news.'
            );
        }

        $entity           = HighloadBlockTable::compileEntity($row);
        self::$dataClass  = $entity->getDataClass();

        return self::$dataClass;
    }

    /**
     * Общее число активных новостей (с ORM-кешем).
     */
    public static function getCount(): int
    {
        $dataClass = self::getDataClass();

        return (int) $dataClass::getCount(
            ['=UF_ACTIVE' => 1],
            ['cache' => ['ttl' => self::CACHE_TTL]]
        );
    }

    /**
     * Общее число страниц при показе по 2 новости.
     */
    public static function getTotalPages(): int
    {
        $count = self::getCount();
        if ($count === 0) {
            return 0;
        }

        return (int) ceil($count / self::ITEMS_PER_PAGE);
    }

    /**
     * Возвращает массив из 2 новостей запрошенной страницы (с ORM-кешем).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getPage(int $page): array
    {
        $dataClass = self::getDataClass();
        $offset    = ($page - 1) * self::ITEMS_PER_PAGE;

        $result = $dataClass::getList([
            'order'  => ['UF_SORT' => 'ASC', 'UF_DATE' => 'DESC'],
            'filter' => ['=UF_ACTIVE' => 1],
            'select' => ['ID', 'UF_TITLE', 'UF_PREVIEW_TEXT', 'UF_DATE', 'UF_SORT'],
            'offset' => $offset,
            'limit'  => self::ITEMS_PER_PAGE,
            'cache'  => ['ttl' => self::CACHE_TTL],
        ]);

        $items = [];
        while ($row = $result->fetch()) {
            $items[] = $row;
        }

        return $items;
    }
}
