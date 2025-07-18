<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

// Проверяем права доступа
if (!$USER->CanDoOperation('edit_other_settings')) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

// Подключаем модуль
if (!Loader::includeModule('bitrix.sortfix')) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
    echo "Модуль bitrix.sortfix не установлен";
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
    die();
}

$sortFixService = new SortFixService();

// Обработка POST запросов
$message = null;
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (check_bitrix_sessid()) {
        $action = $_POST['action'] ?? '';
        $iblockId = !empty($_POST['iblock_id']) ? (int)$_POST['iblock_id'] : null;
        
        switch ($action) {
            case 'fix_all':
                $result = $sortFixService->fixElementsSort();
                $message = $result['message'];
                $messageType = $result['success'] ? 'OK' : 'ERROR';
                break;
                
            case 'fix_iblock':
                if ($iblockId) {
                    $result = $sortFixService->fixElementsSort($iblockId);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'OK' : 'ERROR';
                } else {
                    $message = 'Не указан ID инфоблока';
                    $messageType = 'ERROR';
                }
                break;
        }
    } else {
        $message = 'Неверная сессия';
        $messageType = 'ERROR';
    }
}

// Получаем статистику
$stats = $sortFixService->getElementsStats();
$needsFixing = $sortFixService->checkSortNeedsFixing();

$APPLICATION->SetTitle("Исправление сортировки элементов инфоблоков");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// Показываем сообщение если есть
if ($message) {
    CAdminMessage::ShowMessage([
        "MESSAGE" => $message,
        "TYPE" => $messageType
    ]);
}
?>

<style>
.sort-fix-container {
    background: #fff;
    padding: 20px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.stats-table th,
.stats-table td {
    padding: 8px 12px;
    border: 1px solid #ddd;
    text-align: left;
}

.stats-table th {
    background-color: #f5f5f5;
    font-weight: bold;
}

.action-buttons {
    margin: 20px 0;
}

.action-buttons .adm-btn {
    margin-right: 10px;
}

.warning-box {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.success-box {
    background-color: #d1f2eb;
    border: 1px solid #a3e4d7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}
</style>

<div class="sort-fix-container">
    <h2>Исправление сортировки элементов инфоблоков</h2>
    
    <?php if ($needsFixing['needs_fixing']): ?>
        <div class="warning-box">
            <strong>Внимание!</strong> Обнаружены проблемы с сортировкой:
            <ul>
                <?php if ($needsFixing['has_duplicates']): ?>
                    <li>Найдено <?= $needsFixing['duplicates_count'] ?> групп элементов с одинаковой сортировкой</li>
                <?php endif; ?>
                <?php if ($needsFixing['non_standard_count'] > 0): ?>
                    <li>Найдено <?= $needsFixing['non_standard_count'] ?> элементов с сортировкой не кратной 100</li>
                <?php endif; ?>
            </ul>
            
            <?php if (!empty($needsFixing['problem_iblocks'])): ?>
                <h4>Проблемные инфоблоки:</h4>
                <table class="stats-table" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Код</th>
                            <th>Элементов</th>
                            <th>Дубликаты</th>
                            <th>Некратные 100</th>
                            <th>Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($needsFixing['problem_iblocks'] as $iblock): ?>
                            <tr style="background-color: #fff3cd;">
                                <td><?= $iblock['id'] ?></td>
                                <td><?= htmlspecialchars($iblock['name']) ?></td>
                                <td><?= htmlspecialchars($iblock['code']) ?></td>
                                <td><?= $iblock['total_elements'] ?></td>
                                <td><?= $iblock['has_duplicates'] ? 'Да' : 'Нет' ?></td>
                                <td>
                                    <?= $iblock['has_non_standard'] ? "Да ({$iblock['non_standard_count']})" : 'Нет' ?>
                                </td>
                                <td>
                                    <form method="post" style="display: inline;">
                                        <?= bitrix_sessid_post() ?>
                                        <input type="hidden" name="action" value="fix_iblock">
                                        <input type="hidden" name="iblock_id" value="<?= $iblock['id'] ?>">
                                        <input type="submit" class="adm-btn adm-btn-save" 
                                               value="Исправить" 
                                               onclick="return confirm('Исправить сортировку для инфоблока \"<?= htmlspecialchars($iblock['name']) ?>\"?')">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="success-box">
            <strong>Отлично!</strong> Сортировка элементов в порядке.
        </div>
    <?php endif; ?>
    
    <h3>Общая статистика</h3>
    <p>Всего элементов в системе: <strong><?= $stats['total_elements'] ?></strong></p>
    
    <h3>Статистика по инфоблокам</h3>
    <table class="stats-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Название инфоблока</th>
                <th>Код</th>
                <th>Элементов</th>
                <th>Min SORT</th>
                <th>Max SORT</th>
                <th>Действие</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stats['iblock_stats'] as $iblock): ?>
                <tr>
                    <td><?= $iblock['IBLOCK_ID'] ?></td>
                    <td><?= htmlspecialchars($iblock['IBLOCK_NAME']) ?></td>
                    <td><?= htmlspecialchars($iblock['IBLOCK_CODE']) ?></td>
                    <td><?= $iblock['element_count'] ?></td>
                    <td><?= $iblock['min_sort'] ?></td>
                    <td><?= $iblock['max_sort'] ?></td>
                    <td>
                        <form method="post" style="display: inline;">
                            <?= bitrix_sessid_post() ?>
                            <input type="hidden" name="action" value="fix_iblock">
                            <input type="hidden" name="iblock_id" value="<?= $iblock['IBLOCK_ID'] ?>">
                            <input type="submit" class="adm-btn adm-btn-save" 
                                   value="Исправить" 
                                   onclick="return confirm('Исправить сортировку для инфоблока \"<?= htmlspecialchars($iblock['IBLOCK_NAME']) ?>\"?')">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="action-buttons">
        <form method="post" style="display: inline;">
            <?= bitrix_sessid_post() ?>
            <input type="hidden" name="action" value="fix_all">
            <input type="submit" class="adm-btn adm-btn-save" 
                   value="Исправить сортировку всех элементов" 
                   onclick="return confirm('Исправить сортировку для ВСЕХ элементов в системе?\\nЭто действие затронет <?= $stats['total_elements'] ?> элементов.')">
        </form>
        
        <input type="button" class="adm-btn" value="Обновить страницу" onclick="window.location.reload()">
    </div>
    
    <h3>Описание</h3>
    <p>
        Этот модуль исправляет поле SORT в таблице b_iblock_element по следующему алгоритму:
    </p>
    <ol>
        <li>Элементы сортируются по возрастанию поля SORT (вторичная сортировка по ID)</li>
        <li>Поле SORT обновляется с шагом 100: первый элемент SORT = 100, следующий SORT = 200, и т.д.</li>
    </ol>
    <p>
        <strong>Внимание:</strong> Операция изменяет данные в базе. Рекомендуется создать резервную копию перед выполнением.
    </p>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?> 