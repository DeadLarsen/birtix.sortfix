<?php

namespace Bitrix\SortFix\Installer;

use Composer\Script\Event;

/**
 * Класс для автоматической установки модуля после установки через Composer
 */
class ComposerInstaller
{
    /**
     * Автоматическая установка модуля в Bitrix
     * 
     * @param Event $event
     */
    public static function install(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $projectRoot = dirname($vendorDir);
        
        // Определяем пути
        $moduleSourcePath = $vendorDir . '/bitrix/sortfix';
        $moduleTargetPath = $projectRoot . '/local/modules/bitrix.sortfix';
        
        $io = $event->getIO();
        
        try {
            // Проверяем, что это Bitrix проект
            if (!self::isBitrixProject($projectRoot)) {
                $io->write('<warning>Bitrix project structure not detected. Manual installation required.</warning>');
                $io->write('<info>Please copy module files from vendor/bitrix/sortfix to local/modules/bitrix.sortfix/</info>');
                return;
            }
            
            // Создаем директорию модуля если её нет
            if (!is_dir($moduleTargetPath)) {
                if (!mkdir($moduleTargetPath, 0755, true)) {
                    throw new \Exception("Cannot create directory: {$moduleTargetPath}");
                }
            }
            
            // Копируем файлы модуля
            if (is_dir($moduleSourcePath)) {
                self::copyRecursive($moduleSourcePath, $moduleTargetPath);
                $io->write('<info>Bitrix SortFix module installed to local/modules/bitrix.sortfix/</info>');
                
                // Пытаемся установить модуль автоматически
                if (self::installModule($projectRoot, $io)) {
                    $io->write('<info>Module installed and registered in Bitrix</info>');
                } else {
                    $io->write('<warning>Module files copied but automatic registration failed</warning>');
                    $io->write('<info>Please install the module manually via Bitrix admin panel or run migration</info>');
                }
            } else {
                $io->write('<error>Module source directory not found: ' . $moduleSourcePath . '</error>');
            }
            
        } catch (\Exception $e) {
            $io->write('<error>Installation failed: ' . $e->getMessage() . '</error>');
            $io->write('<info>Please install the module manually</info>');
        }
    }
    
    /**
     * Проверяет, является ли проект Bitrix проектом
     * 
     * @param string $projectRoot
     * @return bool
     */
    private static function isBitrixProject(string $projectRoot): bool
    {
        return is_dir($projectRoot . '/bitrix') && 
               is_file($projectRoot . '/bitrix/modules/main/include/prolog_before.php');
    }
    
    /**
     * Копирует файлы рекурсивно
     * 
     * @param string $source
     * @param string $destination
     */
    private static function copyRecursive(string $source, string $destination)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                // Пропускаем некоторые файлы
                if (in_array($item->getFilename(), ['.git', '.gitignore', 'composer.json', 'composer.lock'])) {
                    continue;
                }
                
                copy($item->getRealPath(), $destPath);
            }
        }
    }
    
    /**
     * Попытка автоматической установки модуля в Bitrix
     * 
     * @param string $projectRoot
     * @param mixed $io
     * @return bool
     */
    private static function installModule(string $projectRoot, $io): bool
    {
        try {
            // Подключаем Bitrix
            $_SERVER['DOCUMENT_ROOT'] = $projectRoot;
            
            if (!is_file($projectRoot . '/bitrix/modules/main/include/prolog_before.php')) {
                return false;
            }
            
            // Пытаемся подключить ядро Bitrix (может не сработать в CLI)
            @include_once $projectRoot . '/bitrix/modules/main/include/prolog_before.php';
            
            if (!class_exists('\Bitrix\Main\ModuleManager')) {
                $io->write('<info>Cannot auto-install: Bitrix core not available in CLI context</info>');
                return false;
            }
            
            // Устанавливаем модуль
            if (!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix.sortfix')) {
                $moduleInstaller = new \Bitrix\SortFix\Install\Index();
                
                if (method_exists($moduleInstaller, 'DoInstall')) {
                    $moduleInstaller->DoInstall();
                    return true;
                }
            } else {
                $io->write('<info>Module already installed</info>');
                return true;
            }
            
        } catch (\Exception $e) {
            // Тихо игнорируем ошибки автоустановки
        }
        
        return false;
    }
    
    /**
     * Показывает инструкции по ручной установке
     * 
     * @param mixed $io
     */
    private static function showManualInstructions($io)
    {
        $io->write('');
        $io->write('<info>Manual installation instructions:</info>');
        $io->write('<info>1. Go to Bitrix admin panel</info>');
        $io->write('<info>2. Navigate to Settings > Modules</info>');
        $io->write('<info>3. Find "Sort Fix" module and click Install</info>');
        $io->write('<info>4. Or run migration: vendor/bin/phinx migrate</info>');
        $io->write('');
    }
} 