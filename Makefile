PLUGIN_SLUG := worknoon-chat
DIST_DIR ?= dist
ZIP_FILE := $(DIST_DIR)/$(PLUGIN_SLUG).zip

.PHONY: help
help:
	@echo "Worknoon chat WordPress plugin commands"
	@echo ""
	@echo "  make lint       Run syntax checks for PHP files"
	@echo "  make package    Create distributable plugin zip"
	@echo "  make clean      Remove generated package output"

.PHONY: lint
lint:
	find . -path './.git' -prune -o -name '*.php' -print -exec php -l {} \;

.PHONY: package
package:
	mkdir -p $(DIST_DIR)
	zip -r $(ZIP_FILE) . -x '.git/*' '$(DIST_DIR)/*'

.PHONY: clean
clean:
	rm -rf $(DIST_DIR)
