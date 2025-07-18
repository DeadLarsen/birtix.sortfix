# Примеры использования Bitrix SortFix

В этом документе представлены практические примеры использования модуля Bitrix SortFix для различных сценариев.

## 📋 Оглавление

- [Базовые операции](#базовые-операции)
- [Автоматизация через CLI](#автоматизация-через-cli)
- [Программное использование](#программное-использование)
- [Мониторинг и диагностика](#мониторинг-и-диагностика)
- [Производственные сценарии](#производственные-сценарии)

## Базовые операции

### Первичная диагностика проекта

```bash
# Получить общую статистику
php local/modules/bitrix.sortfix/cli/sort_fix.php stats

# Проверить, есть ли проблемы с сортировкой
php local/modules/bitrix.sortfix/cli/sort_fix.php check
```

**Пример вывода:**
```
=== СТАТИСТИКА ЭЛЕМЕНТОВ ИНФОБЛОКОВ ===

Всего элементов в системе: 1542

ID    Название               Код               Элементов Min SORT   Max SORT  
-----------------------------------------------------------------------------------------------
1     Новости               news                 245        100       24500     
2     Товары                catalog              890        500       500       
3     Статьи                articles             125        1         999       
```

### Исправление проблемного инфоблока

```bash
# Проверить конкретный инфоблок
php local/modules/bitrix.sortfix/cli/sort_fix.php check 3

# Исправить сортировку
echo "y" | php local/modules/bitrix.sortfix/cli/sort_fix.php fix 3
```

## Автоматизация через CLI

### Скрипт для регулярного обслуживания

Создайте скрипт `maintenance.sh`:

```bash
#!/bin/bash

# Путь к корню Bitrix
BITRIX_ROOT="/var/www/html"
CLI_SCRIPT="$BITRIX_ROOT/local/modules/bitrix.sortfix/cli/sort_fix.php"
LOG_FILE="/var/log/sortfix-maintenance.log"

echo "=== SortFix Maintenance $(date) ===" >> $LOG_FILE

# Проверка состояния
php $CLI_SCRIPT check >> $LOG_FILE 2>&1

# Если есть проблемы, исправляем автоматически (в промышленной среде осторожно!)
if php $CLI_SCRIPT check | grep -q "ТРЕБУЕТСЯ ИСПРАВЛЕНИЕ"; then
    echo "Обнаружены проблемы с сортировкой, исправляем..." >> $LOG_FILE
    echo "y" | php $CLI_SCRIPT fix >> $LOG_FILE 2>&1
    echo "Исправление завершено" >> $LOG_FILE
else
    echo "Сортировка в порядке" >> $LOG_FILE
fi

echo "=== Maintenance completed ===" >> $LOG_FILE
```

### Cron задача

```bash
# Добавить в crontab (crontab -e)
# Проверка каждый день в 3:00
0 3 * * * /path/to/maintenance.sh

# Или только проверка состояния каждые 4 часа
0 */4 * * * php /var/www/html/local/modules/bitrix.sortfix/cli/sort_fix.php check
```

## Программное использование

### В обработчике событий

```php
<?php
// В файле /local/php_interface/init.php

use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

// Автоматическое исправление при добавлении новых элементов
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "checkAndFixSortOnAdd");

function checkAndFixSortOnAdd(&$arFields)
{
    if (!Loader::includeModule('bitrix.sortfix')) {
        return;
    }
    
    $iblockId = $arFields['IBLOCK_ID'];
    $sortFixService = new SortFixService();
    
    // Проверяем только добавленный инфоблок
    $check = $sortFixService->checkSortNeedsFixing($iblockId);
    
    if ($check['needs_fixing']) {
        // Логируем событие
        AddMessage2Log(
            "Автоматическое исправление сортировки для инфоблока {$iblockId}",
            "SORTFIX_AUTO"
        );
        
        // Исправляем
        $result = $sortFixService->fixElementsSort($iblockId);
        
        if ($result['success']) {
            AddMessage2Log(
                "Исправлено {$result['updated_count']} элементов в инфоблоке {$iblockId}",
                "SORTFIX_AUTO"
            );
        }
    }
}
```

### В административном компоненте

```php
<?php
// В компоненте админки

use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

if (Loader::includeModule('bitrix.sortfix')) {
    $sortFixService = new SortFixService();
    
    // Получаем статистику для отображения
    $stats = $sortFixService->getElementsStats();
    $needsFixing = $sortFixService->checkSortNeedsFixing();
    
    // Показываем предупреждение админу
    if ($needsFixing['needs_fixing']) {
        $message = "Внимание! Обнаружены проблемы с сортировкой элементов. ";
        $message .= "Проблемных инфоблоков: " . count($needsFixing['problem_iblocks']);
        
        CAdminMessage::ShowMessage([
            "MESSAGE" => $message,
            "TYPE" => "ERROR",
            "HTML" => true
        ]);
    }
}
```

### В REST API

```php
<?php
// Создание REST-endpoint для проверки состояния

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

class SortFixController extends Controller
{
    public function getStatusAction()
    {
        if (!Loader::includeModule('bitrix.sortfix')) {
            return ['error' => 'Module not installed'];
        }
        
        $sortFixService = new SortFixService();
        
        return [
            'stats' => $sortFixService->getElementsStats(),
            'issues' => $sortFixService->checkSortNeedsFixing()
        ];
    }
    
    public function fixIblockAction($iblockId)
    {
        if (!Loader::includeModule('bitrix.sortfix')) {
            return ['error' => 'Module not installed'];
        }
        
        $sortFixService = new SortFixService();
        return $sortFixService->fixElementsSort($iblockId);
    }
}
```

## Мониторинг и диагностика

### Скрипт мониторинга для Nagios/Zabbix

```bash
#!/bin/bash
# check_sortfix.sh - скрипт для мониторинга

BITRIX_ROOT="/var/www/html"
CLI_SCRIPT="$BITRIX_ROOT/local/modules/bitrix.sortfix/cli/sort_fix.php"

# Проверяем состояние
OUTPUT=$(php $CLI_SCRIPT check 2>&1)

if echo "$OUTPUT" | grep -q "Сортировка в порядке"; then
    echo "OK - Сортировка элементов в норме"
    exit 0
elif echo "$OUTPUT" | grep -q "ТРЕБУЕТСЯ ИСПРАВЛЕНИЕ"; then
    # Извлекаем количество проблемных инфоблоков
    PROBLEM_COUNT=$(echo "$OUTPUT" | grep -c "Да")
    echo "WARNING - Обнаружены проблемы с сортировкой в $PROBLEM_COUNT инфоблоках"
    exit 1
else
    echo "CRITICAL - Ошибка при проверке сортировки"
    exit 2
fi
```

### Создание отчетов

```php
<?php
// Генерация еженедельного отчета

use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

if (Loader::includeModule('bitrix.sortfix')) {
    $sortFixService = new SortFixService();
    $stats = $sortFixService->getElementsStats();
    $issues = $sortFixService->checkSortNeedsFixing();
    
    $report = "=== ЕЖЕНЕДЕЛЬНЫЙ ОТЧЕТ SORTFIX ===\n\n";
    $report .= "Дата: " . date('Y-m-d H:i:s') . "\n";
    $report .= "Всего элементов: " . $stats['total_elements'] . "\n";
    $report .= "Всего инфоблоков: " . count($stats['iblock_stats']) . "\n\n";
    
    if ($issues['needs_fixing']) {
        $report .= "⚠️ ТРЕБУЕТСЯ ВНИМАНИЕ:\n";
        $report .= "Проблемных инфоблоков: " . count($issues['problem_iblocks']) . "\n\n";
        
        foreach ($issues['problem_iblocks'] as $iblock) {
            $report .= sprintf(
                "- %s (ID: %d): %s\n",
                $iblock['name'],
                $iblock['id'],
                $iblock['has_duplicates'] ? 'дубликаты' : 'некратные 100'
            );
        }
    } else {
        $report .= "✅ Все в порядке - проблем не обнаружено\n";
    }
    
    // Отправка отчета по email
    mail(
        'admin@site.com',
        'SortFix Weekly Report',
        $report,
        'From: sortfix@site.com'
    );
}
```

## Производственные сценарии

### Массовая миграция

```bash
#!/bin/bash
# Скрипт для массовой миграции большого проекта

BITRIX_ROOT="/var/www/html"
CLI_SCRIPT="$BITRIX_ROOT/local/modules/bitrix.sortfix/cli/sort_fix.php"
BACKUP_DIR="/backup/sortfix"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "=== Начало массовой миграции ==="

# 1. Создаем бэкап базы
echo "Создание резервной копии..."
mysqldump -u root -p database_name b_iblock_element > "$BACKUP_DIR/b_iblock_element_$TIMESTAMP.sql"

# 2. Получаем список проблемных инфоблоков
echo "Анализ проблемных инфоблоков..."
php $CLI_SCRIPT check > "$BACKUP_DIR/check_result_$TIMESTAMP.txt"

# 3. Исправляем по одному инфоблоку с паузами
IBLOCK_IDS=(1 2 3 5 8 13 21)  # Список проблемных инфоблоков

for iblock_id in "${IBLOCK_IDS[@]}"; do
    echo "Обработка инфоблока $iblock_id..."
    
    # Исправляем
    echo "y" | php $CLI_SCRIPT fix $iblock_id
    
    # Проверяем результат
    if php $CLI_SCRIPT check $iblock_id | grep -q "Сортировка в порядке"; then
        echo "✅ Инфоблок $iblock_id обработан успешно"
    else
        echo "❌ Ошибка при обработке инфоблока $iblock_id"
        exit 1
    fi
    
    # Пауза между обработкой (снижаем нагрузку)
    sleep 5
done

echo "=== Миграция завершена ==="
```

### Интеграция с CI/CD

```yaml
# .github/workflows/deploy.yml
name: Deploy with SortFix

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Deploy to server
      run: |
        # ... код деплоя ...
        
    - name: Run SortFix check
      run: |
        ssh user@server "cd /var/www/html && php local/modules/bitrix.sortfix/cli/sort_fix.php check"
        
    - name: Auto-fix if needed
      run: |
        ssh user@server "cd /var/www/html && echo 'y' | php local/modules/bitrix.sortfix/cli/sort_fix.php fix"
```

### Мониторинг производительности

```php
<?php
// Мониторинг времени выполнения операций

use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

if (Loader::includeModule('bitrix.sortfix')) {
    $sortFixService = new SortFixService();
    
    // Замеряем время проверки
    $startTime = microtime(true);
    $issues = $sortFixService->checkSortNeedsFixing();
    $checkTime = microtime(true) - $startTime;
    
    // Логируем производительность
    AddMessage2Log(
        sprintf("SortFix check completed in %.3f seconds", $checkTime),
        "SORTFIX_PERFORMANCE"
    );
    
    // Если проверка занимает слишком много времени
    if ($checkTime > 10.0) {
        AddMessage2Log(
            "SortFix check is slow, consider optimizing database",
            "SORTFIX_WARNING"
        );
    }
}
```

Эти примеры покрывают основные сценарии использования модуля в реальных проектах. 