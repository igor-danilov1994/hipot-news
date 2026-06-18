<?php
// Форма подтверждения удаления модуля (шаг 1)
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>" method="post">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="id"     value="hipot.news">
    <input type="hidden" name="lang"   value="<?= LANG ?>">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step"   value="2">

    <p><?= GetMessage('HIPOT_NEWS_UNINSTALL_TITLE') ?></p>

    <label>
        <input type="checkbox" name="save_data" value="Y" checked>
        <?= GetMessage('HIPOT_NEWS_UNINSTALL_SAVE') ?>
    </label>

    <br><br>
    <input type="submit" value="<?= GetMessage('MOD_UNINST') ?>">
</form>
