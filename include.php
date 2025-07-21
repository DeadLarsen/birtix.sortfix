<?php

use Bitrix\Main\Loader;

// Автозагрузка классов модуля
Loader::registerAutoLoadClasses('deadlarsen.iblocksortfix', [
    'DeadLarsen\\IblockSortFix\\Services\\SortFixService' => 'lib/Services/SortFixService.php',
]);

// Добавляем пункт в административное меню
if (file_exists(__DIR__ . '/admin/menu.php')) {
    include_once __DIR__ . '/admin/menu.php';
} 