<?php

declare(strict_types=1);

namespace Hipot\News;

use Bitrix\Main\Type\DateTime;

/**
 * Единственный источник разметки карточки новости.
 *
 * Используется и в template.php компонента (первичный рендер страницы),
 * и в контроллере (AJAX-ответ). Это исключает дублирование вёрстки
 * и гарантирует идентичный вид при первом рендере и при навигации стрелками.
 *
 * Все пользовательские строки экранируются через htmlspecialcharsbx() —
 * Bitrix-обёртка над htmlspecialchars с корректной кодировкой.
 */
final class Renderer
{
    /**
     * Рендерит HTML-карточку одной новости.
     *
     * @param array<string, mixed> $item  Строка из NewsTable::getPage()
     */
    public static function renderCard(array $item): string
    {
        $title   = htmlspecialcharsbx((string) ($item['UF_TITLE'] ?? ''));
        $preview = htmlspecialcharsbx((string) ($item['UF_PREVIEW_TEXT'] ?? ''));
        // Два формата даты: machine-readable для атрибута datetime, человеческий для отображения
        $dateIso     = self::formatDate($item['UF_DATE'] ?? null, 'Y-m-d');
        $dateDisplay = self::formatDate($item['UF_DATE'] ?? null, 'd.m.Y');

        return <<<HTML
        <article class="news-card">
            <time class="news-card__date" datetime="{$dateIso}">{$dateDisplay}</time>
            <h2 class="news-card__title">{$title}</h2>
            <p class="news-card__preview">{$preview}</p>
        </article>
        HTML;
    }

    private static function formatDate(mixed $date, string $format): string
    {
        if ($date instanceof DateTime) {
            return $date->format($format);
        }

        return '';
    }
}
