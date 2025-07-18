#!/usr/bin/env php
<?php

// Определяем корневую директорию
$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__ . "/../../../../");

// Подключаем ядро Bitrix
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

// Подключаем модуль
if (!Loader::includeModule('bitrix.sortfix')) {
    echo "Ошибка: Модуль bitrix.sortfix не установлен\n";
    exit(1);
}

/**
 * Функция для вывода справки
 */
function showHelp() {
    echo "Использование: php sort_fix.php [команда] [опции]\n\n";
    echo "Команды:\n";
    echo "  stats                           - Показать статистику по элементам\n";
    echo "  check [iblock_id]               - Проверить нужно ли исправление (для всех или конкретного инфоблока)\n";
    echo "  fix [iblock_id] [--backup]      - Исправить сортировку (для всех или конкретного инфоблока)\n";
    echo "  backup [iblock_id] [name]       - Создать бекап таблицы (всей или конкретного инфоблока)\n";
    echo "  backup-list                     - Показать список доступных бекапов\n";
    echo "  restore <backup_name> [iblock_id] - Восстановить из бекапа\n";
    echo "  backup-delete <backup_name>     - Удалить бекап\n";
    echo "  help                            - Показать эту справку\n\n";
    echo "Опции:\n";
    echo "  --backup                        - Создать бекап перед исправлением (только для команды fix)\n\n";
    echo "Примеры:\n";
    echo "  php sort_fix.php stats                           # Показать статистику\n";
    echo "  php sort_fix.php check                           # Проверить все инфоблоки\n";
    echo "  php sort_fix.php check 384                       # Проверить инфоблок 384\n";
    echo "  php sort_fix.php fix --backup                    # Исправить с созданием бекапа\n";
    echo "  php sort_fix.php fix 384 --backup                # Исправить инфоблок 384 с бекапом\n";
    echo "  php sort_fix.php backup                          # Создать бекап всей таблицы\n";
    echo "  php sort_fix.php backup 384                      # Создать бекап инфоблока 384\n";
    echo "  php sort_fix.php backup 384 my_backup            # Создать именованный бекап\n";
    echo "  php sort_fix.php backup-list                     # Показать список бекапов\n";
    echo "  php sort_fix.php restore backup_name             # Восстановить всю таблицу\n";
    echo "  php sort_fix.php restore backup_name 384         # Восстановить только инфоблок 384\n";
    echo "  php sort_fix.php backup-delete backup_name       # Удалить бекап\n";
}

/**
 * Функция для вывода статистики
 */
function showStats(SortFixService $service) {
    echo "=== СТАТИСТИКА ЭЛЕМЕНТОВ ИНФОБЛОКОВ ===\n\n";
    
    $stats = $service->getElementsStats();
    
    echo "Всего элементов в системе: " . $stats['total_elements'] . "\n\n";
    
    if (!empty($stats['iblock_stats'])) {
        printf("%-5s %-30s %-20s %-10s %-10s %-10s\n", 
            "ID", "Название", "Код", "Элементов", "Min SORT", "Max SORT");
        echo str_repeat("-", 95) . "\n";
        
        foreach ($stats['iblock_stats'] as $iblock) {
            printf("%-5s %-30s %-20s %-10s %-10s %-10s\n",
                $iblock['IBLOCK_ID'],
                mb_substr($iblock['IBLOCK_NAME'], 0, 30),
                mb_substr($iblock['IBLOCK_CODE'], 0, 20),
                $iblock['element_count'],
                $iblock['min_sort'],
                $iblock['max_sort']
            );
        }
    }
    echo "\n";
}

/**
 * Функция для проверки необходимости исправления
 */
function checkNeedsFixing(SortFixService $service, $iblockId = null) {
    echo "=== ПРОВЕРКА НЕОБХОДИМОСТИ ИСПРАВЛЕНИЯ ===\n\n";
    
    $check = $service->checkSortNeedsFixing($iblockId);
    
    if ($check['needs_fixing']) {
        echo "⚠️  ТРЕБУЕТСЯ ИСПРАВЛЕНИЕ:\n\n";
        
        if ($check['has_duplicates']) {
            echo "   • Найдено {$check['duplicates_count']} групп элементов с одинаковой сортировкой\n";
        }
        
        if ($check['non_standard_count'] > 0) {
            echo "   • Найдено {$check['non_standard_count']} элементов с сортировкой не кратной 100\n";
        }
        
        // Показываем детальную информацию по инфоблокам (только для общей проверки)
        if (!$iblockId && !empty($check['problem_iblocks'])) {
            echo "\n📋 ПРОБЛЕМНЫЕ ИНФОБЛОКИ:\n\n";
            
            printf("%-5s %-30s %-20s %-10s %-15s %-15s\n", 
                "ID", "Название", "Код", "Элементов", "Дубликаты", "Некратные 100");
            echo str_repeat("-", 100) . "\n";
            
            foreach ($check['problem_iblocks'] as $iblock) {
                $duplicates = $iblock['has_duplicates'] ? "Да" : "Нет";
                $nonStandard = $iblock['has_non_standard'] ? "Да ({$iblock['non_standard_count']})" : "Нет";
                
                printf("%-5s %-30s %-20s %-10s %-15s %-15s\n",
                    $iblock['id'],
                    mb_substr($iblock['name'], 0, 30),
                    mb_substr($iblock['code'], 0, 20),
                    $iblock['total_elements'],
                    $duplicates,
                    $nonStandard
                );
            }
            
            echo "\n💡 Для исправления конкретного инфоблока используйте:\n";
            echo "   php sort_fix.php fix <IBLOCK_ID>\n";
        }
    } else {
        echo "✅ Сортировка в порядке\n";
    }
    echo "\n";
}

/**
 * Функция для исправления сортировки
 */
function fixSort(SortFixService $service, $iblockId = null, $createBackup = false) {
    $scope = $iblockId ? "инфоблока ID {$iblockId}" : "всех элементов";
    echo "=== ИСПРАВЛЕНИЕ СОРТИРОВКИ ({$scope}) ===\n\n";
    
    if ($createBackup) {
        echo "📦 Создание бекапа перед исправлением...\n";
    }
    
    echo "Начинаем исправление сортировки...\n";
    
    $result = $service->fixElementsSort($iblockId, $createBackup);
    
    if ($result['success']) {
        echo "✅ " . $result['message'] . "\n";
        
        if (isset($result['backup_created']) && $result['backup_created']) {
            echo "📦 Создан бекап: " . $result['backup_name'] . " ({$result['backup_records']} записей)\n";
        }
    } else {
        echo "❌ " . $result['message'] . "\n";
        exit(1);
    }
    echo "\n";
}

/**
 * Функция для создания бекапа
 */
function createBackup(SortFixService $service, $iblockId = null, $backupName = null) {
    $scope = $iblockId ? "инфоблока ID {$iblockId}" : "всей таблицы";
    echo "=== СОЗДАНИЕ БЕКАПА ({$scope}) ===\n\n";
    
    echo "Создание бекапа...\n";
    
    $result = $service->createBackup($iblockId, $backupName);
    
    if ($result['success']) {
        echo "✅ " . $result['message'] . "\n";
        echo "📦 Имя бекапа: " . $result['backup_name'] . "\n";
        echo "📊 Записей: " . $result['records_count'] . "\n";
    } else {
        echo "❌ " . $result['message'] . "\n";
        exit(1);
    }
    echo "\n";
}

/**
 * Функция для отображения списка бекапов
 */
function listBackups(SortFixService $service) {
    echo "=== СПИСОК БЕКАПОВ ===\n\n";
    
    $result = $service->listBackups();
    
    if (!$result['success']) {
        echo "❌ " . $result['message'] . "\n";
        exit(1);
    }
    
    if (empty($result['backups'])) {
        echo "📭 Бекапы не найдены\n\n";
        return;
    }
    
    printf("%-40s %-10s %-8s %-20s %-20s\n", 
        "Имя бекапа", "Записей", "Размер МБ", "Старейшая запись", "Новейшая запись");
    echo str_repeat("-", 110) . "\n";
    
    foreach ($result['backups'] as $backup) {
        printf("%-40s %-10s %-8s %-20s %-20s\n",
            $backup['name'],
            $backup['records_count'],
            $backup['size_mb'],
            $backup['oldest_record'] ?: 'N/A',
            $backup['newest_record'] ?: 'N/A'
        );
    }
    echo "\n";
}

/**
 * Функция для восстановления из бекапа
 */
function restoreFromBackup(SortFixService $service, $backupName, $iblockId = null) {
    $scope = $iblockId ? "инфоблока ID {$iblockId}" : "всей таблицы";
    echo "=== ВОССТАНОВЛЕНИЕ ИЗ БЕКАПА ({$scope}) ===\n\n";
    
    echo "Восстановление из бекапа {$backupName}...\n";
    
    $result = $service->restoreFromBackup($backupName, $iblockId);
    
    if ($result['success']) {
        echo "✅ " . $result['message'] . "\n";
        echo "📊 Восстановлено записей: " . $result['restored_count'] . "\n";
    } else {
        echo "❌ " . $result['message'] . "\n";
        exit(1);
    }
    echo "\n";
}

/**
 * Функция для удаления бекапа
 */
function deleteBackup(SortFixService $service, $backupName) {
    echo "=== УДАЛЕНИЕ БЕКАПА ===\n\n";
    
    echo "Удаление бекапа {$backupName}...\n";
    
    $result = $service->deleteBackup($backupName);
    
    if ($result['success']) {
        echo "✅ " . $result['message'] . "\n";
    } else {
        echo "❌ " . $result['message'] . "\n";
        exit(1);
    }
    echo "\n";
}

// Основная логика
$service = new SortFixService();

// Получаем аргументы командной строки
$command = $argv[1] ?? 'help';

// Обрабатываем аргументы для разных команд
$args = array_slice($argv, 2);
$options = [];
$params = [];

// Разделяем опции и параметры
foreach ($args as $arg) {
    if (strpos($arg, '--') === 0) {
        $options[] = substr($arg, 2);
    } else {
        $params[] = $arg;
    }
}

// Извлекаем параметры
$iblockId = null;
$backupName = null;
$customBackupName = null;

switch ($command) {
    case 'stats':
        showStats($service);
        break;
        
    case 'check':
        $iblockId = isset($params[0]) && is_numeric($params[0]) ? (int)$params[0] : null;
        checkNeedsFixing($service, $iblockId);
        break;
        
    case 'fix':
        // Обрабатываем параметры для fix
        foreach ($params as $param) {
            if (is_numeric($param)) {
                $iblockId = (int)$param;
            }
        }
        
        $createBackup = in_array('backup', $options);
        
        // Подтверждение для безопасности
        if (!$iblockId) {
            echo "Вы собираетесь исправить сортировку ВСЕХ элементов в системе.\n";
        } else {
            echo "Вы собираетесь исправить сортировку элементов инфоблока ID {$iblockId}.\n";
        }
        
        if ($createBackup) {
            echo "📦 Будет создан бекап перед исправлением.\n";
        }
        
        echo "Продолжить? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            fixSort($service, $iblockId, $createBackup);
        } else {
            echo "Операция отменена.\n";
        }
        break;
        
    case 'backup':
        $iblockId = isset($params[0]) && is_numeric($params[0]) ? (int)$params[0] : null;
        $customBackupName = isset($params[1]) ? $params[1] : null;
        
        // Если первый параметр не числовой, то это имя бекапа
        if (isset($params[0]) && !is_numeric($params[0])) {
            $customBackupName = $params[0];
            $iblockId = null;
        }
        
        createBackup($service, $iblockId, $customBackupName);
        break;
        
    case 'backup-list':
        listBackups($service);
        break;
        
    case 'restore':
        if (empty($params[0])) {
            echo "❌ Не указано имя бекапа для восстановления\n";
            echo "Использование: php sort_fix.php restore <backup_name> [iblock_id]\n";
            exit(1);
        }
        
        $backupName = $params[0];
        $iblockId = isset($params[1]) && is_numeric($params[1]) ? (int)$params[1] : null;
        
        // Подтверждение для безопасности
        $scope = $iblockId ? "инфоблока ID {$iblockId}" : "ВСЕЙ ТАБЛИЦЫ";
        echo "⚠️  Вы собираетесь восстановить данные {$scope} из бекапа {$backupName}.\n";
        echo "Это УДАЛИТ текущие данные и заменит их данными из бекапа!\n";
        echo "Продолжить? (y/N): ";
        
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            restoreFromBackup($service, $backupName, $iblockId);
        } else {
            echo "Операция отменена.\n";
        }
        break;
        
    case 'backup-delete':
        if (empty($params[0])) {
            echo "❌ Не указано имя бекапа для удаления\n";
            echo "Использование: php sort_fix.php backup-delete <backup_name>\n";
            exit(1);
        }
        
        $backupName = $params[0];
        
        // Подтверждение для безопасности
        echo "⚠️  Вы собираетесь удалить бекап {$backupName}.\n";
        echo "Это действие НЕОБРАТИМО!\n";
        echo "Продолжить? (y/N): ";
        
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            deleteBackup($service, $backupName);
        } else {
            echo "Операция отменена.\n";
        }
        break;
        
    case 'help':
    default:
        showHelp();
        break;
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php"; 