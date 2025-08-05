# DeadLarsen IblockSortFix - Модуль исправления сортировки для 1C-Bitrix

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net)
[![1C-Bitrix](https://img.shields.io/badge/1C--Bitrix-Compatible-green.svg)](https://www.1c-bitrix.ru)
[![Packagist Version](https://img.shields.io/packagist/v/deadlarsen/iblocksortfix)](https://packagist.org/packages/deadlarsen/iblocksortfix)
[![Packagist Downloads](https://img.shields.io/packagist/dt/deadlarsen/iblocksortfix)](https://packagist.org/packages/deadlarsen/iblocksortfix)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

🔧 **Профессиональный модуль для исправления поля SORT в элементах инфоблоков 1C-Bitrix**


Модуль `deadlarsen.iblocksortfix` предназначен для исправления поля SORT в таблице `b_iblock_element` в системе 1C-Bitrix с помощью эффективного алгоритма с шагом 100.

## ✨ Особенности

- 🚀 **Быстрая установка** - установка одной командой через ~~Composer~~ или миграцию
- 🎯 **Точное исправление** - алгоритм с шагом 100 для оптимальной сортировки
- 🛡️ **Безопасность** - все операции выполняются в транзакциях
- 🖥️ **Веб-интерфейс** - удобная панель в админке Bitrix
- ⚡ **CLI команды** - автоматизация через командную строку
- 📊 **Детальная аналитика** - полная статистика по инфоблокам
- 🔍 **Интеллектуальная диагностика** - обнаружение проблем с сортировкой
- 💾 **Система бекапов** - автоматическое создание резервных копий
- 🔄 **Восстановление данных** - быстрое восстановление из бекапов

## 🚀 Быстрый старт

~~### Установка через Composer (рекомендуется)~~

```bash
composer require deadlarsen/iblocksortfix
```

Модуль автоматически будет установлен в `local/modules/deadlarsen.iblocksortfix/` и зарегистрирован в системе.

### 🚀 Команды после установки через Composer

**📍 Поддержка множественных путей:** Алиасы автоматически определяют где установлен модуль (`vendor/`, `local/modules/`, или `bitrix/modules/`) и используют правильный путь. [Подробнее →](CONFIGURATION.md)

После установки `composer require deadlarsen/iblocksortfix` выполните:

```bash
# 1. Проверьте статистику элементов
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats

# 2. Проверьте нужно ли исправление
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check

# 3. Создайте бекап (рекомендуется)
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup

# 4. Исправьте сортировку с автоматическим бекапом
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup

# 5. Убедитесь что исправление прошло успешно
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check
```

### Ручная установка

1. Скачайте модуль в `local/modules/deadlarsen.iblocksortfix/`
2. Установите через админ-панель или выполните миграцию
3. Откройте **Настройки → Исправление сортировки**

Подробные инструкции: [INSTALL.md](INSTALL.md)  
Быстрый старт: [QUICK-START.md](QUICK-START.md)  
Короткие команды: [ALIASES.md](ALIASES.md)  
Конфигурация: [CONFIGURATION.md](CONFIGURATION.md)

## 📋 Содержание

- [Особенности](#-особенности)
- [Быстрый старт](#-быстрый-старт)
- [Команды после установки](#-команды-после-установки-через-composer)
- [Описание проблемы](#-описание-проблемы)
- [Алгоритм работы](#-алгоритм-работы)
- [Установка](#-установка)
- [Использование](#-использование)
- [Структура модуля](#-структура-модуля)
- [Безопасность](#-безопасность)
- [Примеры использования](#-примеры-использования)
- [Техническая информация](#-техническая-информация)
- [Содействие проекту](#-содействие-проекту)
- [Лицензия](#-лицензия)
- [Поддержка](#-поддержка)

## 🎯 Описание проблемы

В процессе работы с инфоблоками могут возникать ситуации, когда:
- Несколько элементов имеют одинаковое значение поля SORT
- Значения SORT не соответствуют стандарту (не кратны 100)
- Порядок сортировки нарушен

## ⚙️ Алгоритм работы

Модуль исправляет сортировку по следующему алгоритму:

1. Получает все элементы инфоблоков, отсортированные по полю SORT (по возрастанию)
2. При одинаковых значениях SORT применяется вторичная сортировка по ID
3. Обновляет поле SORT с шагом 100:
   - Первый элемент: SORT = 100
   - Второй элемент: SORT = 200  
   - Третий элемент: SORT = 300
   - И так далее...

## 📦 Установка

### 1. Установка модуля

Модуль уже размещен в директории `local/modules/deadlarsen.iblocksortfix/`.

Для установки выполните:

```bash
# Через административный интерфейс
# Настройки → Marketplace → Установленные решения → Найти "Sort Fix" → Установить

# Или через создание миграции
```

### 2. Создание миграции для установки

Создайте миграцию для автоматической установки модуля:

```php
<?php
// database/migrations/YYYYMMDD_HHMMSS_install_bitrix_sortfix_module.php

use Bitrix\Main\ModuleManager;

class install_bitrix_sortfix_module
{
    public function up()
    {
        if (!ModuleManager::isModuleInstalled('bitrix.sortfix')) {
            ModuleManager::registerModule('bitrix.sortfix');
        }
    }

    public function down()
    {
        if (ModuleManager::isModuleInstalled('bitrix.sortfix')) {
            ModuleManager::unRegisterModule('bitrix.sortfix');
        }
    }
}
```

## 💻 Использование

### Через административный интерфейс

1. Войдите в административную панель Bitrix
2. Перейдите в **Настройки → Исправление сортировки**
3. На странице вы увидите:
   - Статистику по элементам инфоблоков
   - Предупреждения о проблемах с сортировкой
   - Кнопки для исправления сортировки отдельных инфоблоков или всех элементов

### Через командную строку

CLI-скрипт находится в `local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php`

#### Показать статистику:
```bash
cd /path/to/bitrix/root
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats
```

#### Проверить необходимость исправления:
```bash
# Проверить все инфоблоки с детальной информацией
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check

# Проверить конкретный инфоблок
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check 384
```

#### Исправить сортировку всех элементов:
```bash
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix
```

#### Исправить сортировку конкретного инфоблока:
```bash
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix 384
```

#### Управление бекапами:

```bash
# Создать бекап всей таблицы
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup

# Создать бекап конкретного инфоблока
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup 384

# Создать именованный бекап
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup 384 my_backup

# Показать список бекапов
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup-list

# Восстановить из бекапа (всю таблицу)
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php restore backup_name

# Восстановить из бекапа (только конкретный инфоблок)
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php restore backup_name 384

# Удалить бекап
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup-delete backup_name

# Исправить сортировку с созданием бекапа
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix 384 --backup
```

#### Показать справку:
```bash
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php help
```

### Программное использование

```php
use Bitrix\Main\Loader;
use DeadLarsen\IblockSortFix\Services\SortFixService;

if (Loader::includeModule('bitrix.sortfix')) {
    $sortFixService = new SortFixService();
    
    // Получить статистику
    $stats = $sortFixService->getElementsStats();
    
    // Проверить необходимость исправления
    $needsFixing = $sortFixService->checkSortNeedsFixing();
    
    // Исправить сортировку всех элементов
    $result = $sortFixService->fixElementsSort();
    
    // Исправить сортировку конкретного инфоблока
    $result = $sortFixService->fixElementsSort(384);
    
    // Создать бекап перед исправлением
    $result = $sortFixService->fixElementsSort(null, true); // с бекапом
    
    // Работа с бекапами
    
    // Создать бекап всей таблицы
    $backupResult = $sortFixService->createBackup();
    
    // Создать бекап конкретного инфоблока
    $backupResult = $sortFixService->createBackup(384);
    
    // Создать именованный бекап
    $backupResult = $sortFixService->createBackup(384, 'my_backup');
    
    // Получить список бекапов
    $backups = $sortFixService->listBackups();
    
    // Восстановить из бекапа
    $restoreResult = $sortFixService->restoreFromBackup('backup_name');
    
    // Восстановить только конкретный инфоблок
    $restoreResult = $sortFixService->restoreFromBackup('backup_name', 384);
    
    // Удалить бекап
    $deleteResult = $sortFixService->deleteBackup('backup_name');
}
```

## 📁 Структура модуля

```
local/modules/deadlarsen.iblocksortfix/
├── admin/
│   ├── menu.php                 # Добавление пункта в админ-меню
│   └── sort_fix.php            # Административная страница
├── cli/
│   └── sort_fix.php            # CLI-скрипт
├── install/
│   └── index.php               # Установщик модуля
├── lib/
│   └── Services/
│       └── SortFixService.php  # Основной сервис
├── include.php                 # Подключение модуля
└── README.md                   # Документация
```

## 🛡️ Безопасность

- Все операции выполняются в транзакциях
- Перед выполнением операций рекомендуется создать резервную копию базы данных
- CLI-скрипт требует подтверждения перед выполнением операций изменения
- Административный интерфейс требует права `edit_other_settings`

## 📚 Примеры использования

### Пример 1: Проверка и исправление через CLI

```bash
# Проверяем текущее состояние
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats

# Проверяем нужно ли исправление
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check

# Если нужно исправление, запускаем
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix
```

### Пример 2: Автоматизация через cron

```bash
# Добавить в crontab для еженедельной проверки и исправления
0 2 * * 0 cd /var/www/html && php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix > /var/log/sort_fix.log 2>&1
```

### Пример 3: Использование в коде

```php
// В обработчике события или в компоненте
$sortFixService = new \DeadLarsen\IblockSortFix\Services\SortFixService();

// Проверяем инфоблок новостей (ID = 392)
$check = $sortFixService->checkSortNeedsFixing(392);

if ($check['needs_fixing']) {
    // Исправляем автоматически
    $result = $sortFixService->fixElementsSort(392);
    
    if ($result['success']) {
        AddMessage2Log("Сортировка инфоблока 392 исправлена: " . $result['message']);
    }
}
```

## 🔧 Техническая информация

- **Версия модуля**: 1.0.0
- **Совместимость**: 1C-Bitrix любой редакции
- **Требования**: PHP 7.4+, MySQL 5.7+
- **Транзакции**: Да, все операции выполняются в транзакциях
- **Логирование**: Результаты операций логируются в стандартный лог Bitrix

## 🤝 Содействие проекту

Мы приветствуем вклад сообщества! Перед тем как внести изменения, пожалуйста, прочтите наше [руководство по содействию](CONTRIBUTING.md).

### Как помочь проекту

- 🐛 Сообщить о баге
- 💡 Предложить улучшение
- 📝 Улучшить документацию
- 🔧 Внести изменения в код
- ⭐ Поставить звезду проекту

## 📄 Лицензия

Этот проект лицензирован под лицензией MIT - см. файл [LICENSE](LICENSE) для деталей.

## 💬 Поддержка

При возникновении проблем с модулем:

### 🆘 Получить помощь

- 📖 Изучите [документацию](README.md)
- 🐛 [Создайте issue](https://github.com/deadlarsen/iblocksortfix/issues) для багов
- 💬 [Обсуждения](https://github.com/deadlarsen/iblocksortfix/discussions) для вопросов

### 🔍 Диагностика

1. Проверьте логи Bitrix (`/bitrix/modules/main/classes/general/event_log.php`)
2. Убедитесь, что модуль установлен корректно
3. Проверьте права доступа к файлам модуля
4. Создайте резервную копию перед выполнением операций

### 🗑️ Удаление модуля

Для удаления модуля:

1. Через административный интерфейс: **Настройки → Marketplace → Установленные решения → Sort Fix → Удалить**
2. Или создайте миграцию для удаления:

```php
ModuleManager::unRegisterModule('bitrix.sortfix');
```

---

<div align="center">

**⭐ Понравился проект? Поставьте звезду!**

**💝 Спасибо за использование DeadLarsen IblockSortFix!**

Made with ❤️ by [Bitrix Community](https://github.com/bitrix)

</div> 
