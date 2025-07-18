# Bitrix SortFix

🔧 **Professional module for fixing SORT field in 1C-Bitrix iblock elements**

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://www.php.net)
[![1C-Bitrix](https://img.shields.io/badge/1C--Bitrix-Compatible-green.svg)](https://www.1c-bitrix.ru)

## Quick Install

```bash
composer require bitrix/sortfix
```

The module will be automatically installed to `local/modules/bitrix.sortfix/` and registered in your Bitrix system.

## Features

- 🔧 **Automatic SORT field fixing** in `b_iblock_element` table
- 📊 **Detailed statistics** for iblock elements  
- 🎯 **Selective fixing** for specific iblocks
- 💾 **Backup system** - automatic backup creation
- 🔄 **Data restoration** from backups
- 🖥️ **Web interface** in Bitrix admin panel
- 💻 **CLI interface** for automation
- 🔒 **Transaction safety** - all operations use database transactions

## Quick Usage

### Web Interface
Go to Bitrix admin panel → **Settings → Sort Fix**

### CLI Commands
```bash
# Check current state
php local/modules/bitrix.sortfix/cli/sort_fix.php check

# Create backup  
php local/modules/bitrix.sortfix/cli/sort_fix.php backup

# Fix with automatic backup
php local/modules/bitrix.sortfix/cli/sort_fix.php fix --backup
```

### Programmatic Usage
```php
use Bitrix\Main\Loader;
use Bitrix\SortFix\Services\SortFixService;

if (Loader::includeModule('bitrix.sortfix')) {
    $service = new SortFixService();
    
    // Get statistics
    $stats = $service->getElementsStats();
    
    // Fix with backup
    $result = $service->fixElementsSort(null, true);
}
```

## Algorithm

1. Sort elements by SORT ASC, ID ASC
2. Update SORT field with 100-step increment: 100, 200, 300, etc.

## Requirements

- PHP >= 7.4
- 1C-Bitrix (any current version)
- MySQL/MariaDB

## Documentation

- **Full Documentation**: [README.md](https://github.com/bitrix-community/sortfix/blob/main/README.md)
- **Installation Guide**: [INSTALL.md](https://github.com/bitrix-community/sortfix/blob/main/INSTALL.md)  
- **Usage Examples**: [EXAMPLES.md](https://github.com/bitrix-community/sortfix/blob/main/EXAMPLES.md)
- **Changelog**: [CHANGELOG.md](https://github.com/bitrix-community/sortfix/blob/main/CHANGELOG.md)

## Support

- 🐛 **Issues**: [GitHub Issues](https://github.com/bitrix-community/sortfix/issues)
- 📖 **Documentation**: [GitHub Repository](https://github.com/bitrix-community/sortfix)
- 💬 **Community**: [Discussions](https://github.com/bitrix-community/sortfix/discussions)

---

**Note**: Currently works only with Iblock 1.0 (classic iblocks). Iblock 2.0 support coming soon. 