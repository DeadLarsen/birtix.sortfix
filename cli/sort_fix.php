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
    echo "  stats                    - Показать статистику по элементам\n";
    echo "  check [iblock_id]        - Проверить нужно ли исправление (для всех или конкретного инфоблока)\n";
    echo "  fix [iblock_id]          - Исправить сортировку (для всех или конкретного инфоблока)\n";
    echo "  help                     - Показать эту справку\n\n";
    echo "Примеры:\n";
    echo "  php sort_fix.php stats           # Показать статистику\n";
    echo "  php sort_fix.php check           # Проверить все инфоблоки с детальной информацией\n";
    echo "  php sort_fix.php check 384       # Проверить конкретный инфоблок 384\n";
    echo "  php sort_fix.php fix             # Исправить сортировку всех элементов\n";
    echo "  php sort_fix.php fix 384         # Исправить сортировку элементов инфоблока 384\n";
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
function fixSort(SortFixService $service, $iblockId = null) {
    $scope = $iblockId ? "инфоблока ID {$iblockId}" : "всех элементов";
    echo "=== ИСПРАВЛЕНИЕ СОРТИРОВКИ ({$scope}) ===\n\n";
    
    echo "Начинаем исправление сортировки...\n";
    
    $result = $service->fixElementsSort($iblockId);
    
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
$iblockId = isset($argv[2]) && is_numeric($argv[2]) ? (int)$argv[2] : null;

switch ($command) {
    case 'stats':
        showStats($service);
        break;
        
    case 'check':
        checkNeedsFixing($service, $iblockId);
        break;
        
    case 'fix':
        // Подтверждение для безопасности
        if (!$iblockId) {
            echo "Вы собираетесь исправить сортировку ВСЕХ элементов в системе.\n";
        } else {
            echo "Вы собираетесь исправить сортировку элементов инфоблока ID {$iblockId}.\n";
        }
        
        echo "Продолжить? (y/N): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 'y') {
            fixSort($service, $iblockId);
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