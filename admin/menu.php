<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

// Не выполняем в CLI режиме
if (php_sapi_name() === 'cli') {
    return [];
}

// Проверяем права доступа
global $USER;
if (!$USER || !$USER->CanDoOperation('edit_other_settings')) {
    return [];
}

$aMenu = [
    [
        "parent_menu" => "global_menu_settings",
        "sort" => 550,
        "text" => "Исправление сортировки",
        "title" => "Исправление поля SORT в элементах инфоблоков",
        "url" => "/local/modules/deadlarsen.iblocksortfix/admin/sort_fix.php",
        "icon" => "iblock_menu_icon_types",
        "page_icon" => "iblock_page_icon_types",
        "items_id" => "menu_deadlarsen_iblocksortfix",
    ]
];

return $aMenu; 