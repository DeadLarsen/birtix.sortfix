# Contributing to DeadLarsen IblockSortFix

First off, thank you for considering contributing to DeadLarsen IblockSortFix! It's people like you that make this project a great tool for the 1C-Bitrix community.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

* **Use a clear and descriptive title**
* **Describe the exact steps which reproduce the problem**
* **Provide specific examples to demonstrate the steps**
* **Describe the behavior you observed after following the steps**
* **Explain which behavior you expected to see instead and why**
* **Include details about your configuration and environment:**
  * 1C-Bitrix version
  * PHP version
  * MySQL version
  * Operating system

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

* **Use a clear and descriptive title**
* **Provide a step-by-step description of the suggested enhancement**
* **Provide specific examples to demonstrate the steps**
* **Describe the current behavior and explain which behavior you expected to see instead**
* **Explain why this enhancement would be useful to most users**

### Pull Requests

* Fill in the required template
* Do not include issue numbers in the PR title
* Include screenshots and animated GIFs in your pull request whenever possible
* Follow the PHP coding standards (PSR-12)
* Include thoughtfully-worded, well-structured tests
* Document new code based on the Documentation Styleguide
* End all files with a newline

## Development Setup

### Prerequisites

* PHP 7.4 or higher
* 1C-Bitrix CMS
* Composer
* Git

### Local Development

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/sortfix.git`
3. Install dependencies: `composer install`
4. Create a feature branch: `git checkout -b feature/amazing-feature`
5. Make your changes
6. Run tests: `composer test`
7. Check code style: `composer cs-check`
8. Fix code style if needed: `composer cs-fix`
9. Commit your changes: `git commit -m 'Add some amazing feature'`
10. Push to the branch: `git push origin feature/amazing-feature`
11. Open a Pull Request

### Testing

We use PHPUnit for testing. Run the test suite with:

```bash
composer test
```

When adding new features, please include appropriate tests.

### Code Style

We follow PSR-12 coding standard. You can check your code style with:

```bash
composer cs-check
```

And fix issues with:

```bash
composer cs-fix
```

## Project Structure

```
bitrix.sortfix/
├── admin/                  # Admin interface files
│   ├── menu.php           # Admin menu integration
│   └── sort_fix.php       # Main admin page
├── cli/                   # Command line scripts
│   └── sort_fix.php       # Main CLI script
├── install/               # Installation files
│   └── index.php          # Module installer
├── lib/                   # Main library files
│   └── Services/          # Service classes
│       └── SortFixService.php
├── tests/                 # Test files (add as needed)
├── composer.json          # Composer configuration
├── README.md              # Main documentation
├── CHANGELOG.md           # Version history
└── CONTRIBUTING.md        # This file
```

## Coding Guidelines

### PHP Code

* Use PSR-4 autoloading
* Follow PSR-12 coding standard
* Use type hints where possible
* Write meaningful docblocks
* Handle exceptions appropriately
* Use transactions for database operations

### Commit Messages

* Use the present tense ("Add feature" not "Added feature")
* Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
* Limit the first line to 72 characters or less
* Reference issues and pull requests liberally after the first line

### Branch Naming

* Use descriptive names: `feature/new-sorting-algorithm`
* For bug fixes: `bugfix/fix-duplicate-detection`
* For documentation: `docs/update-readme`

## Release Process

1. Update version in `composer.json` and `install/index.php`
2. Update `CHANGELOG.md` with new version
3. Create a pull request
4. After merge, create a GitHub release with tag

## Questions?

Don't hesitate to ask questions by creating an issue with the "question" label.

Thank you for contributing! 🎉 