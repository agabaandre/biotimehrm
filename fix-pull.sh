#!/bin/bash
# Script to handle untracked files before pulling on remote server
# Run this script on your production/online server

set -e  # Exit on error

echo "========================================="
echo "Handling untracked files before pull..."
echo "========================================="

# Check if files exist
if [ -f "application/config/database.php" ]; then
    echo "✓ Found database.php - backing up..."
    cp application/config/database.php application/config/database.php.backup
    echo "  Backup saved to: application/config/database.php.backup"
else
    echo "⚠ database.php not found, skipping backup"
fi

if [ -f "index.php" ]; then
    echo "✓ Found index.php - backing up..."
    cp index.php index.php.backup
    echo "  Backup saved to: index.php.backup"
else
    echo "⚠ index.php not found, skipping backup"
fi

echo ""
echo "Removing untracked files to allow pull..."
rm -f application/config/database.php index.php

echo ""
echo "Pulling latest changes from repository..."
if git pull; then
    echo "✓ Pull successful!"
    
    echo ""
    echo "Restoring your config files..."
    if [ -f "application/config/database.php.backup" ]; then
        cp application/config/database.php.backup application/config/database.php
        echo "✓ Restored database.php"
    fi
    
    if [ -f "index.php.backup" ]; then
        cp index.php.backup index.php
        echo "✓ Restored index.php"
    fi
    
    echo ""
    echo "Cleaning up backup files..."
    rm -f application/config/database.php.backup index.php.backup
    
    echo ""
    echo "========================================="
    echo "✓ Done! Config files restored."
    echo "========================================="
    echo "⚠ IMPORTANT: Please verify your config files are correct!"
    echo "   - Check database.php has correct database credentials"
    echo "   - Check index.php has correct base URL"
    echo "========================================="
else
    echo "✗ Pull failed! Restoring backups..."
    if [ -f "application/config/database.php.backup" ]; then
        cp application/config/database.php.backup application/config/database.php
    fi
    if [ -f "index.php.backup" ]; then
        cp index.php.backup index.php
    fi
    echo "Backups restored. Please check the error above."
    exit 1
fi

