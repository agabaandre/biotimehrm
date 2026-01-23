<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migrations - Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .migration-status {
            margin-top: 20px;
        }
        .version-badge {
            font-size: 1.2em;
            padding: 10px 20px;
        }
        .migration-item {
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #ddd;
        }
        .migration-item.current {
            border-left-color: #28a745;
            background-color: #d4edda;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h1>Database Migrations</h1>
                
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $this->session->flashdata('success'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card migration-status">
                    <div class="card-header">
                        <h3>Migration Status</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Current Version:</strong>
                                <span class="badge bg-primary version-badge"><?php echo $current_version; ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Latest Version:</strong>
                                <span class="badge bg-success version-badge"><?php echo $latest_version; ?></span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h4>Available Migrations</h4>
                        <div class="list-group">
                            <?php if (empty($migrations)): ?>
                                <div class="alert alert-info">No migrations found.</div>
                            <?php else: ?>
                                <?php foreach ($migrations as $migration): ?>
                                    <div class="migration-item list-group-item <?php echo ($migration['version'] == $current_version) ? 'current' : ''; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo $migration['name']; ?></strong>
                                                <br>
                                                <small class="text-muted">Version: <?php echo $migration['version']; ?></small>
                                            </div>
                                            <?php if ($migration['version'] == $current_version): ?>
                                                <span class="badge bg-success">Current</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h3>Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="<?php echo base_url('migrations/migrate'); ?>" class="btn btn-primary" onclick="return confirm('Run all pending migrations?');">
                                Run Migrations
                            </a>
                            <a href="<?php echo base_url('migrations/rollback/0'); ?>" class="btn btn-warning" onclick="return confirm('Rollback all migrations? This will reset the database!');">
                                Rollback All
                            </a>
                            <a href="<?php echo base_url('migrations/status'); ?>" class="btn btn-secondary">
                                Refresh Status
                            </a>
                        </div>
                        
                        <hr>
                        
                        <h5>Create New Migration</h5>
                        <form method="post" action="<?php echo base_url('migrations/create'); ?>" class="form-inline">
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="migration_name" required>
                                <button type="submit" class="btn btn-success">Create Migration</button>
                            </div>
                        </form>
                        <small class="text-muted">Example: add_users_table, update_departments_schema</small>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h3>CLI Commands</h3>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3"><code># Check status
php index.php migrations status

# Run all migrations
php index.php migrations migrate

# Rollback to version 0
php index.php migrations rollback 0

# Create new migration
php index.php migrations create add_users_table</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

