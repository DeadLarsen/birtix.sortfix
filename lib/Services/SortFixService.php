<?php

namespace Bitrix\SortFix\Services;

use Bitrix\Main\DB\Result;
use Bitrix\Main\Application;

class SortFixService
{
    private const SORT_STEP = 100;
    private const BACKUP_PREFIX = 'b_iblock_element_backup_';
    
    /**
     * Создает бекап таблицы b_iblock_element
     * 
     * @param int|null $iblockId Если указан, то бекап только элементов конкретного инфоблока
     * @param string|null $customName Пользовательское имя бекапа
     * @return array Результат создания бекапа
     */
    public function createBackup(?int $iblockId = null, ?string $customName = null): array
    {
        $connection = Application::getConnection();
        
        try {
            // Генерируем имя таблицы бекапа
            $timestamp = date('Y_m_d_H_i_s');
            $iblockSuffix = $iblockId ? "_iblock_{$iblockId}" : "";
            $backupTableName = self::BACKUP_PREFIX . $timestamp . $iblockSuffix;
            
            if ($customName) {
                $backupTableName = self::BACKUP_PREFIX . $customName;
            }
            
            // Проверяем, что таблица с таким именем не существует
            if ($this->tableExists($backupTableName)) {
                return [
                    'success' => false,
                    'message' => "Таблица {$backupTableName} уже существует"
                ];
            }
            
            // Создаем структуру таблицы бекапа
            $createTableQuery = "
                CREATE TABLE `{$backupTableName}` LIKE `b_iblock_element`
            ";
            $connection->query($createTableQuery);
            
            // Копируем данные
            $whereClause = $iblockId ? "WHERE IBLOCK_ID = {$iblockId}" : "";
            $insertQuery = "
                INSERT INTO `{$backupTableName}` 
                SELECT * FROM `b_iblock_element` {$whereClause}
            ";
            $connection->query($insertQuery);
            
            // Получаем количество скопированных записей
            $countQuery = "SELECT COUNT(*) as cnt FROM `{$backupTableName}`";
            $countResult = $connection->query($countQuery);
            $count = $countResult->fetch()['cnt'];
            
            return [
                'success' => true,
                'message' => "Бекап создан успешно",
                'backup_name' => $backupTableName,
                'records_count' => $count,
                'iblock_id' => $iblockId
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка при создании бекапа: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Получает список доступных бекапов
     * 
     * @return array
     */
    public function listBackups(): array
    {
        $connection = Application::getConnection();
        
        try {
            $query = "
                SHOW TABLES LIKE '" . self::BACKUP_PREFIX . "%'
            ";
            $result = $connection->query($query);
            $tables = $result->fetchAll();
            
            $backups = [];
            foreach ($tables as $table) {
                $tableName = array_values($table)[0];
                
                // Получаем информацию о таблице
                $infoQuery = "
                    SELECT 
                        COUNT(*) as records_count,
                        MIN(TIMESTAMP_X) as oldest_record,
                        MAX(TIMESTAMP_X) as newest_record
                    FROM `{$tableName}`
                ";
                $infoResult = $connection->query($infoQuery);
                $info = $infoResult->fetch();
                
                // Получаем размер таблицы
                $sizeQuery = "
                    SELECT 
                        ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                    FROM information_schema.TABLES 
                    WHERE table_schema = DATABASE() AND table_name = '{$tableName}'
                ";
                $sizeResult = $connection->query($sizeQuery);
                $size = $sizeResult->fetch()['size_mb'];
                
                $backups[] = [
                    'name' => $tableName,
                    'records_count' => $info['records_count'],
                    'size_mb' => $size,
                    'oldest_record' => $info['oldest_record'],
                    'newest_record' => $info['newest_record']
                ];
            }
            
            // Сортируем по дате создания (по имени таблицы)
            usort($backups, function($a, $b) {
                return strcmp($b['name'], $a['name']);
            });
            
            return [
                'success' => true,
                'backups' => $backups
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка при получении списка бекапов: ' . $e->getMessage(),
                'backups' => []
            ];
        }
    }
    
    /**
     * Восстанавливает данные из бекапа
     * 
     * @param string $backupName Имя таблицы бекапа
     * @param int|null $iblockId Если указан, то восстанавливает только элементы конкретного инфоблока
     * @return array
     */
    public function restoreFromBackup(string $backupName, ?int $iblockId = null): array
    {
        $connection = Application::getConnection();
        
        try {
            // Проверяем, что таблица бекапа существует
            if (!$this->tableExists($backupName)) {
                return [
                    'success' => false,
                    'message' => "Таблица бекапа {$backupName} не существует"
                ];
            }
            
            $connection->startTransaction();
            
            // Если указан конкретный инфоблок, удаляем только его элементы
            if ($iblockId) {
                $deleteQuery = "DELETE FROM b_iblock_element WHERE IBLOCK_ID = {$iblockId}";
                $connection->query($deleteQuery);
                
                $insertQuery = "
                    INSERT INTO b_iblock_element 
                    SELECT * FROM `{$backupName}` WHERE IBLOCK_ID = {$iblockId}
                ";
            } else {
                // Полная замена таблицы
                $deleteQuery = "DELETE FROM b_iblock_element";
                $connection->query($deleteQuery);
                
                $insertQuery = "
                    INSERT INTO b_iblock_element 
                    SELECT * FROM `{$backupName}`
                ";
            }
            
            $connection->query($insertQuery);
            
            // Получаем количество восстановленных записей
            $whereClause = $iblockId ? "WHERE IBLOCK_ID = {$iblockId}" : "";
            $countQuery = "SELECT COUNT(*) as cnt FROM b_iblock_element {$whereClause}";
            $countResult = $connection->query($countQuery);
            $count = $countResult->fetch()['cnt'];
            
            $connection->commitTransaction();
            
            return [
                'success' => true,
                'message' => "Данные восстановлены из бекапа {$backupName}",
                'restored_count' => $count,
                'iblock_id' => $iblockId
            ];
            
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
            
            return [
                'success' => false,
                'message' => 'Ошибка при восстановлении из бекапа: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Удаляет таблицу бекапа
     * 
     * @param string $backupName
     * @return array
     */
    public function deleteBackup(string $backupName): array
    {
        $connection = Application::getConnection();
        
        try {
            // Проверяем, что это действительно таблица бекапа
            if (strpos($backupName, self::BACKUP_PREFIX) !== 0) {
                return [
                    'success' => false,
                    'message' => 'Недопустимое имя таблицы бекапа'
                ];
            }
            
            if (!$this->tableExists($backupName)) {
                return [
                    'success' => false,
                    'message' => "Таблица бекапа {$backupName} не существует"
                ];
            }
            
            $dropQuery = "DROP TABLE `{$backupName}`";
            $connection->query($dropQuery);
            
            return [
                'success' => true,
                'message' => "Бекап {$backupName} удален"
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка при удалении бекапа: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Проверяет существование таблицы
     * 
     * @param string $tableName
     * @return bool
     */
    private function tableExists(string $tableName): bool
    {
        $connection = Application::getConnection();
        
        try {
            $query = "SHOW TABLES LIKE '{$tableName}'";
            $result = $connection->query($query);
            return $result->getSelectedRowsCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Исправляет поле SORT в таблице b_iblock_element
     * 
     * @param int|null $iblockId Если указан, то исправляет только элементы конкретного инфоблока
     * @param bool $createBackup Создавать ли бекап перед исправлением
     * @param string|null $backupName Пользовательское имя бекапа
     * @return array Результат выполнения операции
     */
    public function fixElementsSort(?int $iblockId = null, bool $createBackup = false, ?string $backupName = null): array
    {
        $connection = Application::getConnection();
        
        try {
            // Создаем бекап при необходимости
            if ($createBackup) {
                $backupResult = $this->createBackup($iblockId, $backupName);
                if (!$backupResult['success']) {
                    return [
                        'success' => false,
                        'message' => 'Ошибка при создании бекапа: ' . $backupResult['message'],
                        'updated_count' => 0
                    ];
                }
            }
            
            // Начинаем транзакцию
            $connection->startTransaction();
            
            // Получаем элементы отсортированные по SORT ASC, ID ASC
            $whereClause = $iblockId ? "WHERE IBLOCK_ID = {$iblockId}" : "";
            
            $query = "
                SELECT ID, IBLOCK_ID, NAME, SORT 
                FROM b_iblock_element 
                {$whereClause}
                ORDER BY SORT ASC, ID ASC
            ";
            
            $result = $connection->query($query);
            $elements = $result->fetchAll();
            
            if (empty($elements)) {
                $connection->rollbackTransaction();
                return [
                    'success' => false,
                    'message' => 'Элементы не найдены',
                    'updated_count' => 0
                ];
            }
            
            $updatedCount = 0;
            $newSort = self::SORT_STEP;
            
            // Обновляем SORT для каждого элемента
            foreach ($elements as $element) {
                $updateQuery = "
                    UPDATE b_iblock_element 
                    SET SORT = {$newSort} 
                    WHERE ID = {$element['ID']}
                ";
                
                $connection->query($updateQuery);
                $updatedCount++;
                $newSort += self::SORT_STEP;
            }
            
            // Подтверждаем транзакцию
            $connection->commitTransaction();
            
            $result = [
                'success' => true,
                'message' => "Успешно обновлено {$updatedCount} элементов",
                'updated_count' => $updatedCount,
                'iblock_id' => $iblockId
            ];
            
            // Добавляем информацию о бекапе, если он был создан
            if ($createBackup && isset($backupResult)) {
                $result['backup_created'] = true;
                $result['backup_name'] = $backupResult['backup_name'];
                $result['backup_records'] = $backupResult['records_count'];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $connection->rollbackTransaction();
            
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении: ' . $e->getMessage(),
                'updated_count' => 0
            ];
        }
    }
    
    /**
     * Получает статистику по элементам инфоблоков
     * 
     * @return array
     */
    public function getElementsStats(): array
    {
        $connection = Application::getConnection();
        
        // Общая статистика
        $totalQuery = "SELECT COUNT(*) as total FROM b_iblock_element";
        $totalResult = $connection->query($totalQuery);
        $total = $totalResult->fetch()['total'];
        
        // Статистика по инфоблокам
        $iblockStatsQuery = "
            SELECT 
                ie.IBLOCK_ID,
                ib.NAME as IBLOCK_NAME,
                ib.CODE as IBLOCK_CODE,
                COUNT(*) as element_count,
                MIN(ie.SORT) as min_sort,
                MAX(ie.SORT) as max_sort
            FROM b_iblock_element ie
            LEFT JOIN b_iblock ib ON ie.IBLOCK_ID = ib.ID
            GROUP BY ie.IBLOCK_ID, ib.NAME, ib.CODE
            ORDER BY ie.IBLOCK_ID
        ";
        
        $iblockStatsResult = $connection->query($iblockStatsQuery);
        $iblockStats = $iblockStatsResult->fetchAll();
        
        return [
            'total_elements' => $total,
            'iblock_stats' => $iblockStats
        ];
    }
    
    /**
     * Проверяет, нужно ли исправление сортировки
     * 
     * @param int|null $iblockId
     * @return array
     */
    public function checkSortNeedsFixing(?int $iblockId = null): array
    {
        $connection = Application::getConnection();
        
        $whereClause = $iblockId ? "WHERE ie.IBLOCK_ID = {$iblockId}" : "";
        
        // Проверяем есть ли элементы с одинаковым SORT (только внутри инфоблоков)
        if ($iblockId) {
            // Для конкретного инфоблока проверяем дубликаты внутри него
            $duplicatesQuery = "
                SELECT SORT, COUNT(*) as cnt
                FROM b_iblock_element 
                WHERE IBLOCK_ID = {$iblockId}
                GROUP BY SORT 
                HAVING COUNT(*) > 1
            ";
            $duplicatesResult = $connection->query($duplicatesQuery);
            $duplicates = $duplicatesResult->fetchAll();
        } else {
            // Для общей проверки считаем количество инфоблоков с дубликатами
            $duplicatesQuery = "
                SELECT IBLOCK_ID, COUNT(DISTINCT SORT) as unique_sorts, COUNT(*) as total_elements
                FROM b_iblock_element 
                GROUP BY IBLOCK_ID
                HAVING total_elements > unique_sorts
            ";
            $duplicatesResult = $connection->query($duplicatesQuery);
            $duplicates = $duplicatesResult->fetchAll();
        }
        
        // Проверяем есть ли элементы с SORT не кратным 100
        $andClause = $iblockId ? "AND" : "WHERE";
        $nonStandardQuery = "
            SELECT COUNT(*) as cnt
            FROM b_iblock_element 
            " . ($iblockId ? "WHERE IBLOCK_ID = {$iblockId}" : "") . "
            {$andClause} SORT % 100 != 0
        ";
        
        $nonStandardResult = $connection->query($nonStandardQuery);
        $nonStandard = $nonStandardResult->fetch()['cnt'];
        
        // Если запрос для конкретного инфоблока, возвращаем простой результат
        if ($iblockId) {
            return [
                'has_duplicates' => !empty($duplicates),
                'duplicates_count' => count($duplicates),
                'non_standard_count' => $nonStandard,
                'needs_fixing' => !empty($duplicates) || $nonStandard > 0
            ];
        }
        
        // Получаем детальную информацию по каждому инфоблоку
        $iblockProblemsQuery = "
            SELECT 
                ie.IBLOCK_ID,
                ib.NAME as IBLOCK_NAME,
                ib.CODE as IBLOCK_CODE,
                COUNT(*) as total_elements,
                SUM(CASE WHEN ie.SORT % 100 != 0 THEN 1 ELSE 0 END) as non_standard_count
            FROM b_iblock_element ie
            LEFT JOIN b_iblock ib ON ie.IBLOCK_ID = ib.ID
            GROUP BY ie.IBLOCK_ID, ib.NAME, ib.CODE
            HAVING non_standard_count > 0
            ORDER BY ie.IBLOCK_ID
        ";
        
        $iblockProblemsResult = $connection->query($iblockProblemsQuery);
        $iblockProblems = $iblockProblemsResult->fetchAll();
        
        // Проверяем дубликаты по инфоблокам (только внутри каждого инфоблока)
        $duplicateIblocksQuery = "
            SELECT 
                ie.IBLOCK_ID,
                ib.NAME as IBLOCK_NAME,
                ib.CODE as IBLOCK_CODE,
                COUNT(DISTINCT ie.SORT) as unique_sorts,
                COUNT(*) as total_elements
            FROM b_iblock_element ie
            LEFT JOIN b_iblock ib ON ie.IBLOCK_ID = ib.ID
            GROUP BY ie.IBLOCK_ID, ib.NAME, ib.CODE
            HAVING total_elements > unique_sorts
            ORDER BY ie.IBLOCK_ID
        ";
        
        $duplicateIblocksResult = $connection->query($duplicateIblocksQuery);
        $duplicateIblocks = $duplicateIblocksResult->fetchAll();
        
        // Объединяем проблемные инфоблоки
        $problemIblocks = [];
        
        // Добавляем инфоблоки с некратными SORT
        foreach ($iblockProblems as $problem) {
            $problemIblocks[$problem['IBLOCK_ID']] = [
                'id' => $problem['IBLOCK_ID'],
                'name' => $problem['IBLOCK_NAME'],
                'code' => $problem['IBLOCK_CODE'],
                'has_non_standard' => true,
                'non_standard_count' => $problem['non_standard_count'],
                'has_duplicates' => false,
                'total_elements' => $problem['total_elements']
            ];
        }
        
        // Добавляем инфоблоки с дубликатами
        foreach ($duplicateIblocks as $duplicate) {
            $iblockId = $duplicate['IBLOCK_ID'];
            if (isset($problemIblocks[$iblockId])) {
                $problemIblocks[$iblockId]['has_duplicates'] = true;
            } else {
                $problemIblocks[$iblockId] = [
                    'id' => $duplicate['IBLOCK_ID'],
                    'name' => $duplicate['IBLOCK_NAME'],
                    'code' => $duplicate['IBLOCK_CODE'],
                    'has_non_standard' => false,
                    'non_standard_count' => 0,
                    'has_duplicates' => true,
                    'total_elements' => $duplicate['total_elements']
                ];
            }
        }
        
        return [
            'has_duplicates' => !empty($duplicates),
            'duplicates_count' => count($duplicates),
            'non_standard_count' => $nonStandard,
            'needs_fixing' => !empty($duplicates) || $nonStandard > 0,
            'problem_iblocks' => array_values($problemIblocks)
        ];
    }
} 