<?php
/**
 * Пример использования модуля Bitrix SortFix после установки через Composer
 * 
 * Этот файл демонстрирует, как использовать модуль после установки командой:
 * composer require deadlarsen/iblocksortfix
 */

// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// Подключаем Bitrix (если используется в Bitrix проекте)
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use DeadLarsen\IblockSortFix\Services\SortFixService;

try {
    // Подключаем модуль
    if (!Loader::includeModule('deadlarsen.iblocksortfix')) {
        throw new Exception('Module deadlarsen.iblocksortfix not installed');
    }
    
    // Создаем экземпляр службы
    $sortFixService = new SortFixService();
    
    echo "=== Bitrix SortFix Module Example ===\n\n";
    
    // 1. Получаем общую статистику
    echo "1. Getting statistics...\n";
    $stats = $sortFixService->getElementsStats();
    echo "Total elements: " . $stats['total_elements'] . "\n";
    echo "Iblocks count: " . count($stats['iblock_stats']) . "\n\n";
    
    // 2. Проверяем, нужно ли исправление
    echo "2. Checking if fix is needed...\n";
    $check = $sortFixService->checkSortNeedsFixing();
    
    if ($check['needs_fixing']) {
        echo "⚠️  Fix is needed!\n";
        echo "Duplicates: " . ($check['has_duplicates'] ? 'Yes' : 'No') . "\n";
        echo "Non-standard sort count: " . $check['non_standard_count'] . "\n\n";
        
        // 3. Создаем бекап
        echo "3. Creating backup...\n";
        $backupResult = $sortFixService->createBackup(null, 'example_backup');
        
        if ($backupResult['success']) {
            echo "✅ Backup created: " . $backupResult['backup_name'] . "\n";
            echo "Records backed up: " . $backupResult['records_count'] . "\n\n";
            
            // 4. Исправляем сортировку
            echo "4. Fixing sort...\n";
            $fixResult = $sortFixService->fixElementsSort();
            
            if ($fixResult['success']) {
                echo "✅ Sort fixed! Updated " . $fixResult['updated_count'] . " elements\n\n";
            } else {
                echo "❌ Fix failed: " . $fixResult['message'] . "\n\n";
            }
        } else {
            echo "❌ Backup failed: " . $backupResult['message'] . "\n\n";
        }
        
    } else {
        echo "✅ Sort is already in good condition\n\n";
    }
    
    // 5. Показываем список бекапов
    echo "5. Listing backups...\n";
    $backupsResult = $sortFixService->listBackups();
    
    if ($backupsResult['success'] && !empty($backupsResult['backups'])) {
        echo "Available backups:\n";
        foreach ($backupsResult['backups'] as $backup) {
            echo "- " . $backup['name'] . " (" . $backup['records_count'] . " records, " . $backup['size_mb'] . " MB)\n";
        }
    } else {
        echo "No backups found\n";
    }
    
    echo "\n=== Example completed successfully! ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nMake sure:\n";
    echo "1. Module is installed: composer require deadlarsen/iblocksortfix\n";
    echo "2. Bitrix is properly configured\n";
    echo "3. Database connection is working\n";
}

// Подключаем эпилог Bitrix (если используется)
if (isset($_SERVER['DOCUMENT_ROOT']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';
} 