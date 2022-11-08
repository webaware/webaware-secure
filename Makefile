PKG_NAME			= webaware-secure
PKG_VERSION			= $(shell sed -rn 's/^Version: (.*)/\1/p' $(PKG_NAME).php)

ZIP					= .dist/$(PKG_NAME)-$(PKG_VERSION).zip
FIND_PHP			= find . -path ./vendor -prune -o -path ./node_modules -prune -o -path './.*' -o -name '*.php'
LINT_PHP			= $(FIND_PHP) -exec php -l '{}' \; >/dev/null
SNIFF_PHP			= vendor/bin/phpcs -ps
SRC_PHP				= $(shell $(FIND_PHP) -print)

all:
	@echo please see Makefile for available builds / commands

.PHONY: all lint lint-php zip changelog

# release product

zip: $(ZIP)

$(ZIP): $(SRC_PHP) static/js/*
	rm -rf .dist
	mkdir .dist
	git archive HEAD --prefix=$(PKG_NAME)/ --format=zip -9 -o $(ZIP)

# changelog HTML for copying to the website changelog

changelog: /tmp/c.html

/tmp/c.html: changelog.md
	pandoc -f markdown-auto_identifiers -o $@ $<

# code linters

lint: lint-php

lint-php:
	@echo PHP lint...
	@$(LINT_PHP)
	@$(SNIFF_PHP)
