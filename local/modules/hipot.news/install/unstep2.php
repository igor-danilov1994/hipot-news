<?php
// Страница результата удаления модуля (шаг 2)
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<p><?= GetMessage('HIPOT_NEWS_UNINSTALL_OK') ?></p>
<p>
    <a href="/bitrix/admin/partner_modules.php?lang=<?= LANG ?>">
        &larr; Назад к списку модулей
    </a>
</p>
