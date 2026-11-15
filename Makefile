# Makefile for phpwcms-dev development tasks

.PHONY: phpstan-analyse phpstan-update stacklit-update check-php help \
	i18n-check i18n-coverage i18n-sync i18n-prune release-check

# Default PHP executable (local php is the default)
# You can override this on the command line if your local php version is incompatible,
# e.g., make phpstan-analyse PHP=/path/to/php8/bin/php
PHP ?= php
PHPSTAN = $(PHP) include/vendor/bin/phpstan
STACKLIT ?= stacklit
I18N = $(PHP) bin/i18n.php

# Check PHP version compatibility (PHP >= 8.2 required)
PHP_VERSION_CHECK := $(shell $(PHP) -r "exit(PHP_VERSION_ID >= 80200 ? 0 : 1);" 2>/dev/null && echo "OK" || echo "FAIL")

# Default target
all: help

help:
	@echo "Available commands:"
	@echo "  make phpstan-analyse - Run phpstan analysis"
	@echo "  make phpstan-update  - Update phpstan baseline file"
	@echo "  make stacklit-update - Update stacklit index and CLAUDE.md map"
	@echo "  make i18n-check      - Verify that backend strings used by the code exist in English"
	@echo "  make i18n-coverage   - Print the language coverage table"
	@echo "  make i18n-sync LANG=de - Add missing strings to a language file (DRY=1 to preview)"
	@echo "  make i18n-prune LANG=de - Report unused strings of a language file"
	@echo "  make release-check   - Checks a release has to pass"

check-php:
ifeq ($(PHP_VERSION_CHECK),FAIL)
	@echo "Error: PHP >= 8.2 is required to run PHPStan."
	@echo "The current PHP executable ($(PHP)) is incompatible."
	@echo "Please override it on the command line, for example:"
	@echo "  make phpstan-analyse PHP=/path/to/php8/bin/php"
	@exit 1
endif

phpstan-analyse: check-php
	$(PHPSTAN) analyse -c .phpstan/phpstan.neon --memory-limit=2G

phpstan-update: check-php
	$(PHPSTAN) analyse -c .phpstan/phpstan.neon --generate-baseline=.phpstan/phpstan-baseline.neon --memory-limit=2G

stacklit-update:
	$(STACKLIT) generate
	$(STACKLIT) derive --inject claude

i18n-check: check-php
	$(I18N) check

i18n-coverage: check-php
	$(I18N) coverage

i18n-sync: check-php
	@test -n "$(LANG)" || { echo "Usage: make i18n-sync LANG=de [DRY=1]"; exit 1; }
	$(I18N) sync --lang=$(LANG) $(if $(DRY),--dry-run,)

i18n-prune: check-php
	@test -n "$(LANG)" || { echo "Usage: make i18n-prune LANG=en"; exit 1; }
	$(I18N) prune --lang=$(LANG)

# Gate a release has to pass before it is packaged
release-check: check-php i18n-check
	@echo "release checks passed"
