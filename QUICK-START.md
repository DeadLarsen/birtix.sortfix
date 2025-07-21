# 🚀 Quick Start - DeadLarsen IblockSortFix

## Установка

```bash
composer require deadlarsen/iblocksortfix
```

## 🎯 Первый запуск после установки

### 1. Проверьте текущее состояние

```bash
# Статистика элементов инфоблоков
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats

# Проверка необходимости исправления
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check
```

### 2. Создайте бекап (рекомендуется)

```bash
# Бекап всей таблицы b_iblock_element
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup

# Или именованный бекап
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup "" "before_sort_fix"
```

### 3. Исправьте сортировку

```bash
# С автоматическим созданием бекапа
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup

# Или сначала отдельный бекап, потом исправление
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php backup
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix
```

### 4. Проверьте результат

```bash
# Убедитесь что все исправлено
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check

# Просмотрите финальную статистику
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats
```

## 🐳 Команды для Docker

Если ваш проект работает в Docker контейнере:

```bash
# Узнайте имя вашего PHP контейнера
docker ps | grep php

# Замените YOUR_PHP_CONTAINER на реальное имя
docker exec YOUR_PHP_CONTAINER php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats
docker exec YOUR_PHP_CONTAINER php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check

# Исправление с автоподтверждением (для автоматизации)
echo "y" | docker exec -i YOUR_PHP_CONTAINER php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
```

## ⚡ Одна команда для всего

Если нужно быстро исправить сортировку с бекапом:

```bash
# Создаст бекап и исправит сортировку
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
```

## 🔍 Основные команды

| Команда | Описание |
|---------|----------|
| `stats` | Статистика элементов по инфоблокам |
| `check` | Проверка необходимости исправления |
| `backup` | Создание бекапа таблицы |
| `fix` | Исправление сортировки |
| `backup-list` | Список созданных бекапов |
| `restore <name>` | Восстановление из бекапа |
| `backup-delete <name>` | Удаление бекапа |
| `help` | Справка по всем командам |

## 🌐 Веб-интерфейс

После установки модуля доступен веб-интерфейс:

- **Путь**: Админ-панель → Настройки → Исправление сортировки
- **URL**: `/local/modules/deadlarsen.iblocksortfix/admin/sort_fix.php`

## 🔧 Программное использование

```php
use DeadLarsen\IblockSortFix\Services\SortFixService;

$service = new SortFixService();

// Получить статистику
$stats = $service->getElementsStats();

// Проверить необходимость исправления
$check = $service->checkSortNeedsFixing();

// Создать бекап
$backup = $service->createBackup();

// Исправить сортировку с бекапом
$result = $service->fixElementsSort(null, true);
```

## 📋 Типичные сценарии

### Первый запуск на новом проекте

```bash
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
```

### Регулярное обслуживание

```bash
# Еженедельная проверка (можно добавить в cron)
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check

# При необходимости исправление
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
```

### Исправление конкретного инфоблока

```bash
# Проверить конкретный инфоблок (например, ID 384)
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check 384

# Исправить только этот инфоблок
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix 384 --backup
```

## 🎯 Упрощение команд

### Wrapper скрипт (самый простой способ)

Вместо длинных команд используйте короткий wrapper:

```bash
# Обычное окружение
./sortfix stats
./sortfix check
./sortfix fix-safe

# Docker окружение
docker exec container_name ./sortfix-docker stats
echo "y" | docker exec -i container_name ./sortfix-docker fix-safe
```

### Composer Scripts

Добавьте в `composer.json` и используйте:

```bash
composer run sortfix:stats
composer run sortfix:check
composer run sortfix:quick
```

### Bash алиасы

Установите алиасы и используйте:

```bash
# Установка
source local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix

# Использование
sfstats  # короткий алиас для stats
sfcheck  # короткий алиас для check
sffix    # короткий алиас для fix-safe
sfquick  # короткий алиас для quick
```

🔗 **Подробнее об алиасах**: [ALIASES.md](ALIASES.md)

---

**Готово! Модуль установлен и готов к работе! 🎉**

Для более подробной информации см.:
- [README.md](README.md) - полная документация
- [EXAMPLES.md](EXAMPLES.md) - примеры использования  
- [ALIASES.md](ALIASES.md) - короткие команды и алиасы 