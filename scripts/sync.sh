#!/bin/bash

######################################################################
# 
# Script to synchronize files and databases between the production
# server and the local development environment.
#
# Usage: ./scripts/sync.sh
#
# NOTE: Run this script from the project root directory.
#
# WARNING: This script will DROP the local database before importing
#          the remote database. Ensure you have backups if necessary.
#
######################################################################

# Project root
PROJECT_ROOT="${PWD}"

# Local directory path to sync with remote server
LOCAL_SYNC_DIR="${PROJECT_ROOT}/web/assets/"

# Hostname or IP to sync from
SYNC_FROM="71.19.243.203"

# Remote user for SSH
REMOTE_USER="www-data"

# Remote root directory
REMOTE_ROOT="/var/www/montreal2027.ca/"

# Directory to sync
REMOTE_DIR="${REMOTE_ROOT}/web/assets/"


# Sync files using rsync
echo "Starting file synchronization of assets from ${SYNC_FROM}..."
rsync -avz --delete -e 'ssh -i ~/.ssh/montreal2027 -p 345'  --exclude 'php/*' --progress "${REMOTE_USER}@${SYNC_FROM}:${REMOTE_DIR}" "${LOCAL_SYNC_DIR}"

read -p "Drop local database and import remote database? (y/N): " confirm
if [[ "$confirm" =~ ^[Nn]$ ]]
then
    echo "Database synchronization aborted."
    exit 0
fi

cd "${PROJECT_ROOT}"

echo "Downloading remote database..."
ssh "${REMOTE_USER}@${SYNC_FROM}" -i ~/.ssh/montreal2027 -p 345 "(cd ${REMOTE_ROOT} && drush sql:dump)" > dump.sql

echo "Backing up local database..."
./vendor/bin/drush sql:dump > ../backups/m2707-$(date +%Y-%m-%d_%H:%M).sql

echo "Dropping local database..."
./vendor/bin/drush sql:drop -y

echo "Importing remote database into local database..."
$(./vendor/bin/drush sql:connect) < dump.sql

# Rebuild Drupal cache
./vendor/bin/drush cr

# Clean up
echo "Cleaning up temporary files..."
rm dump.sql

echo "Synchronization complete."

