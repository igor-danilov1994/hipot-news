<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Hipot\News\Installer;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// Загружаем языковые строки из lang/ru/install/index.php
Loc::loadMessages(__FILE__);

/**
 * Класс-установщик модуля hipot.news.
 *
 * Имя класса — MODULE_ID с заменой точек на подчёркивания (требование Bitrix).
 * Методы DoInstall/DoUninstall вызываются из административного раздела
 * «Marketplace → Установленные решения / Модули».
 *
 * Порядок установки:
 *   1. Guard: проверяем наличие модуля highloadblock.
 *   2. Регистрируем модуль в ядре Bitrix.
 *   3. Подключаем модуль (активирует autoload из include.php).
 *   4. InstallDB  — Installer::install() создаёт HL-блок, UF-поля, 8 новостей.
 *   5. InstallFiles — копируем компонент и публичную страницу /news/.
 */
class hipot_news extends CModule
{
    // Без типа — CModule::$MODULE_ID нетипизирован; PHP 8.x запрещает сужение/расширение типа при наследовании
    public $MODULE_ID = 'hipot.news';

    public function __construct()
    {
        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME         = Loc::getMessage('HIPOT_NEWS_MODULE_NAME');
        $this->MODULE_DESCRIPTION  = Loc::getMessage('HIPOT_NEWS_MODULE_DESC');
        $this->PARTNER_NAME        = Loc::getMessage('HIPOT_NEWS_PARTNER_NAME');
        $this->PARTNER_URI         = 'https://www.hipot-studio.com';
    }

    // -------------------------------------------------------------------------
    // Установка
    // -------------------------------------------------------------------------

    public function DoInstall(): bool
    {
        global $APPLICATION;

        // Guard: highloadblock обязателен
        if (!Loader::includeModule('highloadblock')) {
            $APPLICATION->ThrowException(Loc::getMessage('HIPOT_NEWS_ERR_HLBLOCK'));
            return false;
        }

        // Регистрируем модуль в системе Bitrix
        ModuleManager::registerModule($this->MODULE_ID);

        // Подключаем модуль — после этого autoload из include.php активен
        if (!Loader::includeModule($this->MODULE_ID)) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->ThrowException(Loc::getMessage('HIPOT_NEWS_ERR_INCLUDE'));
            return false;
        }

        try {
            $this->InstallDB();
            $this->InstallFiles();
        } catch (Throwable $e) {
            // Откатываем регистрацию, чтобы модуль не остался в «битом» состоянии
            ModuleManager::unRegisterModule($this->MODULE_ID);
            $APPLICATION->ThrowException(
                Loc::getMessage('HIPOT_NEWS_ERR_DB') . $e->getMessage()
            );
            return false;
        }

        return true;
    }

    public function InstallDB(): void
    {
        // autoload уже зарегистрирован (Loader::includeModule выше),
        // поэтому Installer доступен без явного require
        Installer::install();
    }

    public function InstallFiles(): void
    {
        // Компонент: install/components/hipot/ → DOCUMENT_ROOT/local/components/hipot/
        CopyDirFiles(
            __DIR__ . '/components',
            $_SERVER['DOCUMENT_ROOT'] . '/local/components',
            true,  // перезаписывать существующие файлы
            true   // рекурсивно
        );

        // Публичная страница: install/public/news/ → DOCUMENT_ROOT/news/
        CopyDirFiles(
            __DIR__ . '/public',
            $_SERVER['DOCUMENT_ROOT'],
            true,
            true
        );
    }

    // -------------------------------------------------------------------------
    // Удаление
    // -------------------------------------------------------------------------

    public function DoUninstall(): bool
    {
        global $APPLICATION, $step;

        $step = (int) ($_REQUEST['step'] ?? 1);

        if ($step < 2) {
            // Шаг 1: показываем форму подтверждения с чекбоксом
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('HIPOT_NEWS_UNINSTALL_TITLE'),
                __DIR__ . '/unstep1.php'
            );
        } else {
            // Шаг 2: выполняем удаление
            $this->UnInstallFiles();

            // Чекбокс не отправляется если снят → дефолт 'N' (удалить данные)
            $saveData = (string) ($_REQUEST['save_data'] ?? 'N');
            if ($saveData !== 'Y' && Loader::includeModule($this->MODULE_ID)) {
                $this->UnInstallDB();
            }

            ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('HIPOT_NEWS_UNINSTALL_TITLE'),
                __DIR__ . '/unstep2.php'
            );
        }

        return true;
    }

    public function UnInstallDB(): void
    {
        Installer::uninstall();
    }

    public function UnInstallFiles(): void
    {
        // Удаляем публичную страницу /news/
        $newsDir = $_SERVER['DOCUMENT_ROOT'] . '/news';
        if (is_file($newsDir . '/index.php')) {
            @unlink($newsDir . '/index.php');
        }
        if (is_dir($newsDir) && count(scandir($newsDir)) === 2) {
            // Каталог пуст (только . и ..) — удаляем
            @rmdir($newsDir);
        }

        // Удаляем компонент
        $componentDir = $_SERVER['DOCUMENT_ROOT'] . '/local/components/hipot/news.list';
        if (is_dir($componentDir)) {
            DeleteDirFilesEx($componentDir);
        }
    }
}
