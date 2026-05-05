#!/bin/bash

######################################################################
# Drupal Config Export Pre-Commit Validation
#
# This script checks if Drupal configuration is in sync before
# allowing a commit. It ensures developers don't forget to export
# config after making changes via the admin UI.
#
# Installation (git hook):
#   ln -s ../../.github/hooks/pre-commit-config-check.sh .git/hooks/pre-commit
#   chmod +x .github/hooks/pre-commit-config-check.sh
#
# Manual check:
#   ./.github/hooks/pre-commit-config-check.sh
######################################################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Detect which drush command to use
# Priority: Docker (if running) > Local drush
DRUSH=""

# Check if Docker containers are running
if docker-compose ps web 2>/dev/null | grep -q "Up"; then
    DRUSH="docker-compose exec -T web drush"
    echo -e "${YELLOW}Using Docker environment${NC}"
elif command -v drush &> /dev/null; then
    DRUSH="drush"
    echo -e "${YELLOW}Using local Drush${NC}"
else
    echo -e "${RED}Error: Cannot find Drush${NC}"
    echo "Please ensure either:"
    echo "  1. Docker containers are running (docker-compose up -d), or"
    echo "  2. Drush is installed locally"
    exit 1
fi

echo -e "${YELLOW}Checking Drupal configuration status...${NC}"

# Check if there are any staged Drupal config files
STAGED_CONFIG=$(git diff --cached --name-only | grep "^config/sync/" || true)

# Check if there are uncommitted config changes in Drupal
# This returns 0 if config is in sync, non-zero if not
if $DRUSH config:status --state=Different 2>/dev/null | grep -q "Different"; then
    echo -e "${RED}Error: Drupal configuration is out of sync!${NC}"
    echo -e "${YELLOW}Some configuration changes have not been exported.${NC}"
    echo ""
    echo "Changed configuration:"
    $DRUSH config:status --state=Different
    echo ""
    echo -e "${YELLOW}Please export configuration before committing:${NC}"
    if [[ "$DRUSH" == *"docker-compose"* ]]; then
        echo "  docker-compose exec web drush cex"
    else
        echo "  drush cex"
    fi
    echo ""
    echo "Then stage the updated config files:"
    echo "  git add config/sync/"
    echo ""
    echo -e "${RED}Commit aborted.${NC}"
    exit 1
fi

# If staging config files, remind about potential dependencies
if [ -n "$STAGED_CONFIG" ]; then
    echo -e "${GREEN}✓ Configuration files found in commit${NC}"
    echo -e "${YELLOW}Reminder: Config changes may require update hooks or README updates${NC}"
fi

echo -e "${GREEN}✓ Drupal configuration is in sync${NC}"
exit 0
