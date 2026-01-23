#!/bin/bash
# Script to untrack database.php and index.php on remote server

set -e

echo "========================================="
echo "Untracking config files..."
echo "========================================="

# Step 1: Backup files
echo "Step 1: Backing up config files..."
if [ -f "application/config/database.php" ]; then
    cp application/config/database.php application/config/database.php.backup
    echo "✓ Backed up database.php"
fi
if [ -f "index.php" ]; then
    cp index.php index.php.backup
    echo "✓ Backed up index.php"
fi

# Step 2: Remove from Git tracking (if tracked)
echo ""
echo "Step 2: Removing from Git tracking..."
git rm --cached application/config/database.php 2>/dev/null && echo "✓ Removed database.php from tracking" || echo "⚠ database.php not tracked, skipping"
git rm --cached index.php 2>/dev/null && echo "✓ Removed index.php from tracking" || echo "⚠ index.php not tracked, skipping"

# Step 3: Update .gitignore
echo ""
echo "Step 3: Updating .gitignore..."
if ! grep -q "application/config/database.php" .gitignore 2>/dev/null; then
    echo "application/config/database.php" >> .gitignore
    echo "✓ Added database.php to .gitignore"
fi
if ! grep -q "^index.php$" .gitignore 2>/dev/null; then
    echo "index.php" >> .gitignore
    echo "✓ Added index.php to .gitignore"
fi

# Step 4: Remove untracked files
echo ""
echo "Step 4: Removing untracked files..."
rm -f application/config/database.php index.php
echo "✓ Removed local files"

# Step 5: Pull latest changes
echo ""
echo "Step 5: Pulling latest changes..."
if git pull; then
    echo "✓ Pull successful"
else
    echo "⚠ Pull had issues, but continuing..."
fi

# Step 6: Restore config files
echo ""
echo "Step 6: Restoring config files..."
if [ -f "application/config/database.php.backup" ]; then
    cp application/config/database.php.backup application/config/database.php
    echo "✓ Restored database.php"
fi
if [ -f "index.php.backup" ]; then
    cp index.php.backup index.php
    echo "✓ Restored index.php"
fi

# Step 7: Commit .gitignore if changed
echo ""
echo "Step 7: Committing .gitignore changes..."
if git diff --staged .gitignore > /dev/null 2>&1 || git diff .gitignore > /dev/null 2>&1; then
    git add .gitignore
    git commit -m "Add config files to .gitignore" 2>/dev/null && echo "✓ Committed .gitignore" || echo "⚠ Could not commit .gitignore"
fi

# Step 8: Clean up
echo ""
echo "Step 8: Cleaning up..."
rm -f application/config/database.php.backup index.php.backup
echo "✓ Cleanup complete"

echo ""
echo "========================================="
echo "✓ Done! Files are now untracked"
echo "========================================="
echo ""
echo "Verify files are untracked:"
echo "  git status"
echo ""
echo "⚠ IMPORTANT: Verify your config files are correct!"
echo "========================================="

