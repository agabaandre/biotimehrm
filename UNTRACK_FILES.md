# How to Untrack Files on Remote Server

## Method 1: Remove from Git Index (If Files Are Tracked)

If the files are currently tracked in Git, run these commands **on your remote server**:

```bash
# Remove files from Git tracking (but keep local copies)
git rm --cached application/config/database.php
git rm --cached index.php

# Commit the removal
git commit -m "Untrack database.php and index.php"

# Push the changes
git push
```

## Method 2: If Files Are Untracked (Current Situation)

If the files are **untracked** (not in Git), you just need to ensure `.gitignore` is updated:

```bash
# On remote server, make sure .gitignore includes these files
echo "application/config/database.php" >> .gitignore
echo "index.php" >> .gitignore

# Verify .gitignore
cat .gitignore | grep -E "(database\.php|index\.php)"

# Commit the .gitignore update
git add .gitignore
git commit -m "Add config files to .gitignore"
git push
```

## Method 3: Complete Solution (Recommended)

Run this **on your remote server** to handle both tracked and untracked files:

```bash
# Step 1: Backup your config files
cp application/config/database.php application/config/database.php.backup
cp index.php index.php.backup

# Step 2: Remove from Git tracking (if tracked) - this won't delete local files
git rm --cached application/config/database.php 2>/dev/null || echo "File not tracked, skipping"
git rm --cached index.php 2>/dev/null || echo "File not tracked, skipping"

# Step 3: Ensure .gitignore includes these files
if ! grep -q "application/config/database.php" .gitignore 2>/dev/null; then
    echo "application/config/database.php" >> .gitignore
fi
if ! grep -q "^index.php" .gitignore 2>/dev/null; then
    echo "index.php" >> .gitignore
fi

# Step 4: Remove the actual files (they're untracked and causing the issue)
rm -f application/config/database.php index.php

# Step 5: Pull the latest changes (including updated .gitignore)
git pull

# Step 6: Restore your config files
cp application/config/database.php.backup application/config/database.php
cp index.php.backup index.php

# Step 7: Clean up
rm -f application/config/database.php.backup index.php.backup

# Step 8: Commit .gitignore changes (if any)
git add .gitignore
git commit -m "Add config files to .gitignore" 2>/dev/null && git push || echo "No changes to commit"

echo "✓ Files are now untracked and won't cause pull conflicts"
```

## Method 4: Simple One-Liner (Quick Fix)

```bash
git rm --cached application/config/database.php index.php 2>/dev/null; \
echo -e "application/config/database.php\nindex.php" >> .gitignore; \
rm -f application/config/database.php index.php; \
git pull; \
echo "✓ Done! Restore your config files manually if needed"
```

## Verify Files Are Untracked

After running the commands, verify with:

```bash
# Check Git status - files should not appear
git status

# Files should be ignored
git check-ignore application/config/database.php index.php
```

## Important Notes

1. **Backup first**: Always backup your config files before removing them
2. **Restore after**: After pulling, restore your production config files
3. **Verify settings**: Make sure database credentials and base URL are correct after restoring

