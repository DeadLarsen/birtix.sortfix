# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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