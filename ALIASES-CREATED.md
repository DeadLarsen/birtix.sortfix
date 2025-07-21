# ✅ Созданы алиасы и короткие команды

## 📅 Обновление: 21 июля 2025 г.

Созданы 4 способа использования коротких команд вместо длинного пути `php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php`.

## 🎯 Созданные файлы

### ✅ 1. Wrapper скрипты
- **`sortfix`** - wrapper для обычного окружения
- **`sortfix-docker`** - wrapper для Docker окружения

### ✅ 2. Composer интеграция
- **`composer-scripts.json`** - готовые скрипты для добавления в composer.json

### ✅ 3. Makefile команды
- **`Makefile.sortfix`** - полный набор Make команд

### ✅ 4. Bash алиасы
- **`.bash_aliases_sortfix`** - файл с алиасами и функциями

### ✅ 5. Документация
- **`ALIASES.md`** - полное руководство по всем способам использования

## 🚀 Готовые команды

### Wrapper скрипт (рекомендуется)
```bash
# Вместо: php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats
./sortfix stats

# Вместо: echo "y" | docker exec -i container php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
echo "y" | docker exec -i container ./sortfix-docker fix-safe
```

### Composer Scripts
```bash
# Вместо: php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check
composer run sortfix:check

# Вместо: php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
composer run sortfix:fix-safe
```

### Makefile команды
```bash
# Вместо: php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats
make -f Makefile.sortfix sortfix-stats

# Вместо: docker exec container php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check
make -f Makefile.sortfix sortfix-docker-check CONTAINER=container_name
```

### Bash алиасы
```bash
# После установки: source local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix

# Вместо: php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats
sfstats

# Вместо: php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
sffix
```

## 📊 Возможности каждого способа

### 🎯 Wrapper скрипт
✅ **Преимущества:**
- Самый простой в использовании
- Отдельные версии для Docker и обычного окружения
- Проверка существования модуля
- Справка по командам
- Валидация параметров

**Использование:**
```bash
chmod +x sortfix sortfix-docker
./sortfix quick
docker exec container_name ./sortfix-docker quick
```

### 📦 Composer Scripts
✅ **Преимущества:**
- Интеграция с существующим workflow
- Стандартный способ для PHP проектов
- Последовательные команды (chain)
- Работает везде где есть Composer

**Использование:**
```bash
composer run sortfix:stats
composer run sortfix:quick  # check + fix-safe
```

### 🔨 Makefile
✅ **Преимущества:**
- Мощная система автоматизации
- Параметры через переменные
- Зависимости между задачами
- Интеграция с CI/CD

**Использование:**
```bash
make -f Makefile.sortfix sortfix-quick
make -f Makefile.sortfix sortfix-fix-iblock ID=384
make -f Makefile.sortfix sortfix-docker-fix CONTAINER=php_container
```

### 💻 Bash алиасы
✅ **Преимущества:**
- Максимально короткие команды
- Автодополнение (tab completion)
- Функции с параметрами
- Переменные окружения
- Интеграция с shell

**Использование:**
```bash
# Короткие команды
sfstats
sfcheck  
sffix
sfquick

# Функции
sortfix-check-iblock 384
sortfix-restore backup_name

# Docker с переменной окружения
export SORTFIX_CONTAINER=php_container
sfdfix
```

## 🎯 Рекомендации по использованию

| Сценарий | Рекомендуемый способ | Команда |
|----------|---------------------|---------|
| **Первый запуск** | Wrapper скрипт | `./sortfix quick` |
| **Docker проект** | Wrapper скрипт | `docker exec container ./sortfix-docker quick` |
| **CI/CD пайплайн** | Composer scripts | `composer run sortfix:check` |
| **Ежедневная работа** | Bash алиасы | `sfquick` |
| **Автоматизация** | Makefile | `make -f Makefile.sortfix sortfix-quick` |
| **Команды с параметрами** | Bash функции | `sortfix-fix-iblock 384` |

## 🛠️ Быстрая установка

### Wrapper (минимум настройки)
```bash
chmod +x sortfix sortfix-docker
./sortfix stats
```

### Composer (стандартный способ)
```bash
# Добавить в composer.json и использовать
composer run sortfix:stats
```

### Bash алиасы (для удобства)
```bash
source local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix
sfstats
```

## 📈 Результат

Теперь вместо:
```bash
php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup
```

Можно использовать:
```bash
./sortfix fix-safe              # wrapper
composer run sortfix:fix-safe   # composer  
make -f Makefile.sortfix sortfix-fix-safe  # make
sffix                           # bash алиас
```

**Экономия времени: до 90% символов! 🎉**

---

**Все способы готовы к использованию!**

Выберите подходящий способ или используйте несколько одновременно. Подробная документация: [ALIASES.md](ALIASES.md) 