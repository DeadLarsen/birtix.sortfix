# 🚀 Алиасы и короткие команды для DeadLarsen IblockSortFix

Вместо длинных команд `php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php` можно использовать короткие алиасы.

## 📋 Все способы использования коротких команд

### 1. 🎯 Wrapper скрипт (рекомендуется)

Скачайте и используйте готовый wrapper скрипт:

```bash
# Для обычного окружения
chmod +x sortfix
./sortfix stats
./sortfix check
./sortfix fix-safe

# Для Docker окружения  
chmod +x sortfix-docker
docker exec container_name ./sortfix-docker stats
echo "y" | docker exec -i container_name ./sortfix-docker fix-safe
```

### 2. 📦 Composer Scripts

Добавьте в ваш `composer.json`:

```json
{
    "scripts": {
        "sortfix:stats": "php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php stats",
        "sortfix:check": "php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php check",
        "sortfix:fix-safe": "php local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php fix --backup",
        "sortfix:quick": ["@sortfix:check", "@sortfix:fix-safe"]
    }
}
```

Использование:
```bash
composer run sortfix:stats
composer run sortfix:check
composer run sortfix:fix-safe
composer run sortfix:quick
```

### 3. 🔨 Makefile команды

Используйте готовый Makefile:

```bash
# Основные команды
make -f Makefile.sortfix sortfix-stats
make -f Makefile.sortfix sortfix-check
make -f Makefile.sortfix sortfix-fix-safe
make -f Makefile.sortfix sortfix-quick

# Docker команды
make -f Makefile.sortfix sortfix-docker-stats CONTAINER=your_php_container
make -f Makefile.sortfix sortfix-docker-fix CONTAINER=your_php_container

# Для конкретного инфоблока
make -f Makefile.sortfix sortfix-check-iblock ID=384
make -f Makefile.sortfix sortfix-fix-iblock ID=384
```

### 4. 💻 Bash алиасы

Установите bash алиасы:

```bash
# Установка
source local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix

# Или добавьте в ~/.bashrc:
echo "source $(pwd)/local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix" >> ~/.bashrc
source ~/.bashrc
```

Использование:
```bash
# Полные алиасы
sortfix-stats
sortfix-check
sortfix-fix-safe
sortfix-quick

# Короткие алиасы
sfstats
sfcheck
sffix
sfquick

# Docker алиасы (установите SORTFIX_CONTAINER=your_container)
export SORTFIX_CONTAINER=UpGreat.one_php
sfdstats
sfdcheck
sfdfix

# Функции
sortfix-check-iblock 384
sortfix-fix-iblock 384
sortfix-restore backup_name
```

## 📊 Сравнение способов

| Способ | Простота | Гибкость | Docker | Автодополнение |
|--------|----------|----------|---------|----------------|
| **Wrapper скрипт** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |
| **Composer Scripts** | ⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐ | ⭐⭐ |
| **Makefile** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐ |
| **Bash алиасы** | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |

## 🎯 Рекомендуемые команды

### Быстрый старт
```bash
# Wrapper (самый простой способ)
./sortfix quick

# Composer
composer run sortfix:quick

# Make
make -f Makefile.sortfix sortfix-quick

# Bash алиас
sfquick
```

### Docker проекты
```bash
# Docker wrapper
docker exec container_name ./sortfix-docker quick

# Bash алиас с переменной окружения
export SORTFIX_CONTAINER=your_php_container
sfdfix  # короткий алиас для fix-safe
```

### Регулярные проверки
```bash
# Проверка состояния
./sortfix check           # wrapper
composer run sortfix:check     # composer
make -f Makefile.sortfix sortfix-check  # make
sfcheck                   # bash алиас

# Исправление с бекапом при необходимости
./sortfix fix-safe        # wrapper
composer run sortfix:fix-safe   # composer  
make -f Makefile.sortfix sortfix-fix-safe  # make
sffix                     # bash алиас
```

## 🛠️ Настройка

### Wrapper скрипты
```bash
# Скачать и настроить
chmod +x sortfix sortfix-docker

# Создать симлинк для глобального доступа (опционально)
sudo ln -s $(pwd)/sortfix /usr/local/bin/sortfix
sudo ln -s $(pwd)/sortfix-docker /usr/local/bin/sortfix-docker
```

### Composer Scripts
Скопируйте содержимое `composer-scripts.json` в секцию `scripts` вашего `composer.json`.

### Makefile
```bash
# Скопировать Makefile в корень проекта (опционально)
cp Makefile.sortfix Makefile
make sortfix-stats  # теперь можно без -f
```

### Bash алиасы
```bash
# Для bash
echo "source $(pwd)/local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix" >> ~/.bashrc
source ~/.bashrc

# Для zsh
echo "source $(pwd)/local/modules/deadlarsen.iblocksortfix/.bash_aliases_sortfix" >> ~/.zshrc
source ~/.zshrc

# Настройка Docker контейнера
echo "export SORTFIX_CONTAINER=your_php_container" >> ~/.bashrc
```

## 🔍 Справка по командам

### Получить помощь
```bash
# Wrapper скрипты
./sortfix --help
./sortfix help               # справка модуля
docker exec container_name ./sortfix-docker --help

# Composer
composer run-script --list | grep sortfix

# Makefile
make -f Makefile.sortfix help

# Bash алиасы
sortfix-aliases-help
```

### Доступные команды во всех способах

| Команда | Описание |
|---------|----------|
| `stats` | Статистика элементов инфоблоков |
| `check` | Проверка необходимости исправления |
| `backup` | Создание бекапа таблицы |
| `fix` | Исправление сортировки |
| `fix-safe` | Исправление с автоматическим бекапом |
| `backup-list` | Список созданных бекапов |
| `restore <name>` | Восстановление из бекапа |
| `backup-delete <name>` | Удаление бекапа |
| `quick` | Быстрая проверка и исправление |
| `help` | Справка по командам |

## 💡 Советы

1. **Для новичков**: Используйте wrapper скрипт `./sortfix`
2. **Для CI/CD**: Используйте Composer scripts
3. **Для сложной автоматизации**: Используйте Makefile
4. **Для ежедневной работы**: Установите bash алиасы
5. **Для Docker проектов**: Используйте `./sortfix-docker` или настройте переменную `SORTFIX_CONTAINER`

## 🐛 Устранение неполадок

### Wrapper скрипт не работает
```bash
# Проверьте права
chmod +x sortfix sortfix-docker

# Проверьте путь к модулю
ls -la local/modules/deadlarsen.iblocksortfix/cli/sort_fix.php
```

### Bash алиасы не загружаются
```bash
# Проверьте что файл подключен
grep "bash_aliases_sortfix" ~/.bashrc

# Перезагрузите shell
source ~/.bashrc
```

### Docker команды не работают
```bash
# Проверьте контейнер
docker ps | grep php

# Установите переменную окружения
export SORTFIX_CONTAINER=your_actual_container_name
```

---

**Выберите удобный способ и начинайте использовать короткие команды! 🚀** 