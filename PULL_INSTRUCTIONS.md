# Instructions for Pulling on Remote Server

When you get the error about untracked files being overwritten, use one of these solutions:

## Option 1: Stash the Local Files (Recommended)
This backs up your local config files before pulling:

```bash
# Backup the files
cp application/config/database.php application/config/database.php.backup
cp index.php index.php.backup

# Remove them temporarily
rm application/config/database.php
rm index.php

# Pull the changes
git pull

# Restore your config files
cp application/config/database.php.backup application/config/database.php
cp index.php.backup index.php

# Clean up backups
rm application/config/database.php.backup index.php.backup
```

## Option 2: Force Overwrite (Use with Caution)
This will overwrite your local config files with the remote versions:

```bash
# Remove local files
rm application/config/database.php index.php

# Pull changes
git pull

# Restore your config from backup (if you have one)
# Or manually reconfigure
```

## Option 3: Use Git Stash (If files are tracked)
If the files are tracked in your local repo but not in remote:

```bash
# Stash local changes
git stash

# Pull changes
git pull

# Restore your config (if needed)
git stash pop
```

## After Pulling
Since these files are now in `.gitignore`, they won't cause conflicts in future pulls.
Make sure your production config files are properly configured after pulling.

