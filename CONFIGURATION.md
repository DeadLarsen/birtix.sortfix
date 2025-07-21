# 🔧 Конфигурация алиасов DeadLarsen IblockSortFix

## 📍 Проблема множественных путей

Модуль может быть установлен в разных местах в зависимости от способа установки:

1. **`html/vendor/deadlarsen/iblocksortfix/`** - при установке через Composer
2. **`html/local/modules/deadlarsen.iblocksortfix/`** - стандартная установка в Bitrix
3. **`html/bitrix/modules/deadlarsen.iblocksortfix/`** - системная установка

## 🎯 Решение: Автоматическое определение пути

Все алиасы теперь автоматически определяют где находится модуль и используют правильный путь.

---

## 📁 Конфигурационные файлы

### 1. `sortfix.config` - для Bash скриптов и алиасов

```bash
# Автоматические пути (в порядке приоритета)
DOCKER_PATHS=(
    "html/local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
    "html/vendor/deadlarsen/iblocksortfix/cli/sort_fix.php"
    "html/bitrix/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
)

HOST_PATHS=(
    "local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
    "vendor/deadlarsen/iblocksortfix/cli/sort_fix.php"
    "bitrix/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
)

# Принудительные пути (переопределяют автоматические)
CUSTOM_DOCKER_PATH="html/local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
CUSTOM_HOST_PATH="local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"

# Docker контейнер по умолчанию
DEFAULT_CONTAINER="UpGreat.one_php"
```

### 2. `sortfix.make.config` - для Makefile

```makefile
# Makefile-совместимый формат (простые переменные)
CUSTOM_DOCKER_PATH = html/local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
CUSTOM_HOST_PATH = local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
DEFAULT_CONTAINER = UpGreat.one_php
```

---

## 🚀 Как это работает

### 1. Wrapper скрипты

```bash
# ./sortfix автоматически определяет путь
./sortfix check
# 📍 Using module at: local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php

# ./sortfix-docker для Docker окружения  
docker exec container ./sortfix-docker check
# 📍 Using module at: html/local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
```

### 2. Bash алиасы

```bash
# Загрузка с автоопределением путей
source local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix
# ✅ SortFix aliases loaded with auto-path detection!
# 📍 Host path: local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
# 📍 Docker path: html/local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php

# Использование коротких алиасов
sfcheck  # автоматически использует правильный путь
```

### 3. Makefile команды

```bash
make -f Makefile.sortfix help
# 📍 Module paths detected:
#    Host: local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
#    Docker: html/local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
```

---

## ⚙️ Настройка под ваш проект

### Автоматическая настройка (рекомендуется)

Система автоматически найдет модуль в стандартных местах. Не требует настройки.

### Ручная настройка

Если модуль находится в нестандартном месте, создайте/отредактируйте конфигурационные файлы:

#### Для Composer установки

```bash
# sortfix.config
CUSTOM_DOCKER_PATH="html/vendor/deadlarsen/iblocksortfix/cli/sort_fix.php"
CUSTOM_HOST_PATH="vendor/deadlarsen/iblocksortfix/cli/sort_fix.php"
```

#### Для Bitrix modules установки

```bash
# sortfix.config
CUSTOM_DOCKER_PATH="html/bitrix/modules/deadlarsen.iblocksortfix/cli/sort_fix.php" 
CUSTOM_HOST_PATH="bitrix/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
```

#### Для нестандартного пути

```bash
# sortfix.config
CUSTOM_DOCKER_PATH="html/custom/path/to/deadlarsen.iblocksortfix/cli/sort_fix.php"
CUSTOM_HOST_PATH="custom/path/to/deadlarsen.iblocksortfix/cli/sort_fix.php"
```

---

## 🔍 Проверка конфигурации

### Wrapper скрипты

```bash
./sortfix --help
# Покажет путь к модулю в сообщении "📍 Using module at: ..."

docker exec container ./sortfix-docker --help  
# Покажет путь для Docker окружения
```

### Bash алиасы

```bash
sortfix-aliases-help
# 📍 Module paths detected:
#    Host: путь_для_хост_системы
#    Docker: путь_для_docker
```

### Makefile

```bash
make -f Makefile.sortfix help
# 📍 Module paths detected:
#    Host: путь_для_хост_системы  
#    Docker: путь_для_docker
```

---

## 🐛 Устранение неполадок

### Модуль не найден

```bash
❌ Module not found in any of the expected locations:
   - vendor/deadlarsen/iblocksortfix/cli/sort_fix.php
   - local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php  
   - bitrix/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
```

**Решение:**
1. Убедитесь что модуль установлен
2. Проверьте путь к CLI файлу: `ls -la */deadlarsen*/cli/sort_fix.php`
3. Установите правильный путь в `sortfix.config`:
   ```bash
   CUSTOM_HOST_PATH="actual/path/to/cli/sort_fix.php"
   ```

### Неправильная версия модуля

```bash
Ошибка: Модуль bitrix.sortfix не установлен
```

**Решение:**
Система нашла старую версию модуля. Принудительно укажите путь к правильной версии:
```bash
# sortfix.config
CUSTOM_DOCKER_PATH="html/local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
CUSTOM_HOST_PATH="local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php" 
```

### Docker контейнер не найден

```bash
Error response from daemon: No such container: php_container
```

**Решение:**
1. Проверьте имя контейнера: `docker ps`
2. Установите правильное имя:
   ```bash
   # В sortfix.config
   DEFAULT_CONTAINER="ваш_php_контейнер"
   
   # Или переменной окружения
   export SORTFIX_CONTAINER=ваш_php_контейнер
   ```

---

## 📊 Приоритет определения путей

### Для всех способов:

1. **CUSTOM_*_PATH** - принудительно заданный путь (высший приоритет)
2. **Автоматический поиск** в следующем порядке:
   - `local/modules/deadlarsen.iblocksortfix/` (рекомендуется)
   - `vendor/deadlarsen/iblocksortfix/` (Composer)
   - `bitrix/modules/deadlarsen.iblocksortfix/` (системная установка)

### Для Docker:

Добавляется префикс `html/` ко всем путям.

---

## 🎯 Примеры для разных установок

### Проект с Composer установкой

```bash
# sortfix.config
CUSTOM_HOST_PATH="vendor/deadlarsen/iblocksortfix/cli/sort_fix.php"
CUSTOM_DOCKER_PATH="html/vendor/deadlarsen/iblocksortfix/cli/sort_fix.php"
DEFAULT_CONTAINER="my_php_container"
```

### Проект с локальной установкой

```bash
# sortfix.config (по умолчанию работает автоматически)
DEFAULT_CONTAINER="bitrix_php"
```

### Проект с системной установкой

```bash
# sortfix.config
CUSTOM_HOST_PATH="bitrix/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
CUSTOM_DOCKER_PATH="html/bitrix/modules/deadlarsen.iblocksortfix/cli/sort_fix.php"
```

---

## ✅ Результат

Теперь алиасы работают **независимо от способа установки модуля**:

- ✅ Автоматическое определение пути к модулю
- ✅ Поддержка всех 3 возможных местоположений
- ✅ Гибкая конфигурация для нестандартных случаев
- ✅ Четкие сообщения об используемых путях
- ✅ Простое устранение неполадок

**Один набор алиасов работает везде! 🎉** 