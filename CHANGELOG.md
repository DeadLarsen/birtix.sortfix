# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2025-01-29

### Added
- 💾 **Backup System** - Complete backup functionality for `b_iblock_element` table
- 🔄 **Data Restoration** - Restore data from backups (full table or specific iblocks)
- 📋 **Backup Management** - List, create, and delete backups via CLI and web interface
- ⚡ **Automatic Backups** - Option to create backup before fix operations
- 🏷️ **Named Backups** - Create custom-named backups for easy identification
- 📊 **Backup Statistics** - View backup size, record count, and date information

### Enhanced
- 🖥️ **Web Interface** - Added backup management section to admin panel
- 💻 **CLI Commands** - New backup commands: `backup`, `backup-list`, `restore`, `backup-delete`
- 🔧 **Fix Operations** - Added `--backup` option to create backup before fixing
- 📚 **Documentation** - Updated README and examples with backup functionality

### Security
- 🛡️ **Safe Operations** - All backup operations include confirmation prompts
- 🔒 **Validation** - Backup name validation and existence checks

## [1.0.0] - 2025-01-29

### Added
- Initial release of Bitrix SortFix module
- Core service for fixing SORT field in `b_iblock_element` table
- Web interface in Bitrix admin panel for managing sort fixes
- CLI script for command-line operations
- Support for fixing all elements or specific iblock elements
- 100-step SORT algorithm (100, 200, 300, etc.)
- Transaction support for safe database operations
- Detailed statistics and problem detection
- Migration for module installation via Phinx
- Comprehensive documentation and examples

### Features
- **Web Interface**: Admin panel page with statistics and fix buttons
- **CLI Commands**:
  - `stats` - Show statistics for all iblocks
  - `check [iblock_id]` - Check if sorting needs fixing (with detailed problem analysis)
  - `fix [iblock_id]` - Fix sorting for all or specific iblock
  - `help` - Show usage information
- **Safety**: All operations wrapped in database transactions
- **Flexibility**: Works with individual iblocks or all elements
- **Monitoring**: Detects duplicate SORT values and non-standard values (not divisible by 100)

### Technical Details
- Compatible with 1C-Bitrix CMS (any edition)
- PHP 7.4+ required
- MySQL 5.7+ support
- PSR-4 autoloading
- Comprehensive error handling and logging 