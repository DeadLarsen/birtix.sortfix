<?php

class deadlarsen_iblocksortfix extends CModule
{
    public function __construct()
    {
        $this->MODULE_VERSION      = '1.1.0';
        $this->MODULE_VERSION_DATE = '2025-01-29';

        $this->MODULE_ID           = 'deadlarsen.iblocksortfix';
        $this->MODULE_NAME         = 'IBlock Sort Fix';
        $this->MODULE_DESCRIPTION  = 'Модуль для исправления поля SORT в элементах инфоблоков с шагом 100';
        $this->MODULE_GROUP_RIGHTS = 'N';
    }

    public function doInstall(): void
    {
        RegisterModule($this->MODULE_ID);
        
        // Добавляем пункт в админ-меню
        $this->installFiles();
    }

    public function doUninstall(): void
    {
        $this->uninstallFiles();
        UnRegisterModule($this->MODULE_ID);
    }

    public function installFiles(): void
    {
        // Можно добавить копирование файлов в папку /bitrix/admin/ если нужно
    }

    public function uninstallFiles(): void
    {
        // Удаление файлов при деинсталляции
    }
} 