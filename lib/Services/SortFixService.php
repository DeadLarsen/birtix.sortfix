<?php

namespace Bitrix\SortFix\Services;

use Bitrix\Main\DB\Result;
use Bitrix\Main\Application;

class SortFixService
{
    private const SORT_STEP = 100;
    
    /**
     * Исправляет поле SORT в таблице b_iblock_element
     * 
     * @param int|null $iblockId Если указан, то исправляет только элементы конкретного инфоблока
     * @return array Результат выполнения операции
     */
    public function fixElementsSort(?int $iblockId = null): array
    {
        $connection = Application::getConnection();
        
        try {
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
            
            return [
                'success' => true,
                'message' => "Успешно обновлено {$updatedCount} элементов",
                'updated_count' => $updatedCount,
                'iblock_id' => $iblockId
            ];
            
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