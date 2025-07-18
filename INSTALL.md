# Установка модуля Bitrix SortFix

Модуль `bitrix/sortfix` можно установить несколькими способами в зависимости от ваших потребностей.

## 🚀 Установка через Composer (рекомендуется)

### Быстрая установка

```bash
composer require bitrix/sortfix
```

После установки модуль автоматически будет скопирован в `local/modules/bitrix.sortfix/` и при возможности установлен в Bitrix.

### Ручная активация (если автоустановка не сработала)

1. **Через админ-панель:**
   - Зайдите в админ-панель Bitrix
   - Перейдите в "Настройки" → "Модули"
   - Найдите модуль "Sort Fix" и нажмите "Установить"

2. **Через миграцию:**
   ```bash
   vendor/bin/phinx migrate -c database/phinx.php
   ```

3. **Через CLI:**
   ```bash
   php local/modules/bitrix.sortfix/install/index.php
   ```

## 📁 Ручная установка

### 1. Скачивание файлов

```bash
# Клонирование репозитория
git clone https://github.com/bitrix-community/sortfix.git local/modules/bitrix.sortfix

# Или скачивание архива
wget https://github.com/bitrix-community/sortfix/archive/main.zip
unzip main.zip -d local/modules/
mv local/modules/sortfix-main local/modules/bitrix.sortfix
```

### 2. Установка модуля

Выберите один из способов:

**Способ 1: Через админ-панель**
1. Зайдите в админ-панель Bitrix
2. Перейдите в "Настройки" → "Модули"
3. Найдите модуль "Sort Fix" и нажмите "Установить"

**Способ 2: Через миграцию**
```bash
# Скопируйте миграцию
cp local/modules/bitrix.sortfix/database/migrations/20250129120000_install_bitrix_sortfix_module.php database/migrations/

# Выполните миграцию
vendor/bin/phinx migrate
```

**Способ 3: Программная установка**
```php
<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

use Bitrix\Main\Loader;

// Подключаем и устанавливаем модуль
if (Loader::includeModule('bitrix.sortfix')) {
    echo "Module already installed\n";
} else {
    $installer = new Bitrix\SortFix\Install\Index();
    $installer->DoInstall();
    echo "Module installed\n";
}
```

## 🐳 Установка в Docker

### Dockerfile

```dockerfile
FROM your-bitrix-image

# Установка через Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Или ручная установка
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/local/modules/bitrix.sortfix
```

### docker-compose.yml

```yaml
version: '3.8'
services:
  bitrix:
    build: .
    volumes:
      - ./local/modules/bitrix.sortfix:/var/www/html/local/modules/bitrix.sortfix
    environment:
      - COMPOSER_ALLOW_SUPERUSER=1
    command: |
      bash -c "
        composer require bitrix/sortfix &&
        php local/modules/bitrix.sortfix/cli/sort_fix.php stats
      "
```

## ⚙️ Настройка после установки

### 1. Проверка установки

```bash
# Через CLI
php local/modules/bitrix.sortfix/cli/sort_fix.php stats

# Через веб-интерфейс
# Перейдите в админ-панель → Настройки → Исправление сортировки
```

### 2. Первый запуск

```bash
# Проверить состояние
php local/modules/bitrix.sortfix/cli/sort_fix.php check

# Создать бекап
php local/modules/bitrix.sortfix/cli/sort_fix.php backup

# Исправить сортировку с бекапом
php local/modules/bitrix.sortfix/cli/sort_fix.php fix --backup
```

## 🔧 Настройка автозагрузки (для Composer)

Если вы используете собственный автозагрузчик, добавьте в ваш `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "Bitrix\\SortFix\\": "vendor/bitrix/sortfix/lib/"
        }
    }
}
```

## 🚨 Устранение неполадок

### Проблема: Модуль не устанавливается автоматически

**Решение:**
```bash
# Проверьте права доступа
chmod -R 755 local/modules/bitrix.sortfix/

# Установите вручную через админ-панель
# или выполните миграцию
```

### Проблема: CLI команды не работают

**Решение:**
```bash
# Проверьте путь к PHP
which php

# Проверьте DOCUMENT_ROOT
cd /path/to/bitrix/root
php local/modules/bitrix.sortfix/cli/sort_fix.php help
```

### Проблема: Нет доступа к админ-панели

**Решение:**
```bash
# Установите через CLI
php -d register_argc_argv=On local/modules/bitrix.sortfix/install/index.php

# Или выполните миграцию
vendor/bin/phinx migrate
```

## 📋 Требования

- PHP >= 7.4
- 1C-Bitrix (любая актуальная версия)
- MySQL/MariaDB
- Доступ к файловой системе для записи

## 🔗 Полезные ссылки

- [Документация](README.md)
- [Примеры использования](EXAMPLES.md)
- [Журнал изменений](CHANGELOG.md)
- [Участие в разработке](CONTRIBUTING.md) 