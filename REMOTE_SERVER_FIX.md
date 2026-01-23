# Fix for Remote Server Pull Error

## The Problem
When pulling on your remote/production server, you get:
```
error: The following untracked working tree files would be overwritten by merge:
        application/config/database.php
        index.php
```

## Solution: Run This on Your Remote Server

### Option 1: Use the Script (Easiest)

1. **Upload the `fix-pull.sh` script to your remote server** (or create it there)
2. **Make it executable:**
   ```bash
   chmod +x fix-pull.sh
   ```
3. **Run it:**
   ```bash
   ./fix-pull.sh
   ```

### Option 2: Manual Commands

Run these commands **on your remote server**:

```bash
# Step 1: Backup your config files
cp application/config/database.php application/config/database.php.backup
cp index.php index.php.backup

# Step 2: Remove the untracked files
rm application/config/database.php index.php

# Step 3: Pull the changes
git pull

# Step 4: Restore your config files
cp application/config/database.php.backup application/config/database.php
cp index.php.backup index.php

# Step 5: Clean up backups
rm application/config/database.php.backup index.php.backup

# Step 6: IMPORTANT - Verify your config files are correct!
# Check database.php has correct database credentials
# Check index.php has correct base URL
```

### Option 3: One-Liner (Quick Fix)

```bash
cp application/config/database.php application/config/database.php.backup && \
cp index.php index.php.backup && \
rm application/config/database.php index.php && \
git pull && \
cp application/config/database.php.backup application/config/database.php && \
cp index.php.backup index.php && \
rm application/config/database.php.backup index.php.backup && \
echo "Done! Verify your config files."
```

## After Fixing

Once you've pulled successfully:
1. **Verify your config files** - Make sure `database.php` and `index.php` have the correct settings for production
2. **Future pulls** - Since these files are now in `.gitignore`, future pulls won't have this issue

## Why This Happens

- These files exist on your remote server as **untracked files**
- The repository has these files **tracked** (or they're being added)
- Git won't overwrite untracked files during a pull
- By removing them first, Git can pull the tracked versions, then you restore your local config

## Prevention

The `.gitignore` file has been updated to ignore these files. After pulling, make sure your remote server has the updated `.gitignore` so this won't happen again.

