# Database Migrations

This directory contains database migration files for managing schema changes.

## Usage

### Web Interface
Visit: `http://localhost/attend/migrations/status`

### CLI Commands

#### Check Migration Status
```bash
php index.php migrations status
```

#### Run All Pending Migrations
```bash
php index.php migrations migrate
```

#### Rollback to Specific Version
```bash
php index.php migrations rollback 0
```

#### Create New Migration
```bash
php index.php migrations create add_users_table
```

### Using the Helper Script
```bash
./migrate status
./migrate migrate
./migrate rollback 0
./migrate create migration_name
```

## Migration File Format

Migration files should be named using timestamp format: `YYYYMMDDHHIISS_description.php`

Example: `20260122220000_fix_biotime_departments_table.php`

## Migration Class Structure

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Your_migration_name extends CI_Migration {

    public function up()
    {
        // Add your migration code here
    }

    public function down()
    {
        // Add your rollback code here
    }
}
```

## Common Operations

### Create Table
```php
$this->dbforge->add_field(array(
    'id' => array(
        'type' => 'INT',
        'constraint' => 11,
        'unsigned' => TRUE,
        'auto_increment' => TRUE
    ),
    'name' => array(
        'type' => 'VARCHAR',
        'constraint' => '255',
    ),
));
$this->dbforge->add_key('id', TRUE);
$this->dbforge->create_table('table_name');
```

### Add Column
```php
$fields = array(
    'new_column' => array(
        'type' => 'VARCHAR',
        'constraint' => '100',
    ),
);
$this->dbforge->add_column('table_name', $fields);
```

### Drop Column
```php
$this->dbforge->drop_column('table_name', 'column_name');
```

### Modify Column
```php
$fields = array(
    'column_name' => array(
        'type' => 'VARCHAR',
        'constraint' => '255',
    ),
);
$this->dbforge->modify_column('table_name', $fields);
```

## Best Practices

1. Always test migrations on a development database first
2. Write rollback code in the `down()` method
3. Use descriptive migration names
4. One migration per logical change
5. Never modify existing migration files after they've been run in production
6. Always backup your database before running migrations

