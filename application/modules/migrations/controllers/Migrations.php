<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration Management Controller
 * 
 * Provides web and CLI interface for managing database migrations
 * 
 * Usage:
 *   Web: http://localhost/attend/migrations/status
 *   CLI: php index.php migrations migrate
 */
class Migrations extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Load migration library
        $this->load->library('migration');
        
        // For CLI access, allow without authentication
        // For web access, you may want to add authentication
        if (!$this->input->is_cli_request()) {
            // Check if user is logged in (optional - remove if you want public access)
            if (!$this->session->userdata('isLoggedIn')) {
                show_error('You must be logged in to access migrations.', 403);
                return;
            }
        }
    }

    /**
     * Show migration status
     */
    public function status() {
        $this->load->library('migration');
        
        $data = array(
            'current_version' => $this->migration->current(),
            'latest_version' => $this->migration->latest(),
            'migrations' => $this->getMigrationFiles()
        );
        
        if ($this->input->is_cli_request()) {
            echo "Current Migration Version: " . $data['current_version'] . "\n";
            echo "Latest Migration Version: " . $data['latest_version'] . "\n";
            echo "\nAvailable Migrations:\n";
            foreach ($data['migrations'] as $migration) {
                echo "  - " . $migration['file'] . " (Version: " . $migration['version'] . ")\n";
            }
        } else {
            $this->load->view('migrations/status', $data);
        }
    }

    /**
     * Run migrations to latest version
     */
    public function migrate($version = null) {
        $this->load->library('migration');
        
        if ($version !== null) {
            $result = $this->migration->version($version);
        } else {
            $result = $this->migration->latest();
        }
        
        if ($result === FALSE) {
            $error = $this->migration->error_string();
            
            if ($this->input->is_cli_request()) {
                echo "Migration failed!\n";
                echo "Error: " . $error . "\n";
            } else {
                $this->session->set_flashdata('error', 'Migration failed: ' . $error);
                redirect('migrations/status');
            }
        } else {
            if ($this->input->is_cli_request()) {
                echo "Migration successful!\n";
                echo "Current version: " . $this->migration->current() . "\n";
            } else {
                $this->session->set_flashdata('success', 'Migration completed successfully!');
                redirect('migrations/status');
            }
        }
    }

    /**
     * Rollback to a specific version
     */
    public function rollback($version = 0) {
        $this->load->library('migration');
        
        $result = $this->migration->version($version);
        
        if ($result === FALSE) {
            $error = $this->migration->error_string();
            
            if ($this->input->is_cli_request()) {
                echo "Rollback failed!\n";
                echo "Error: " . $error . "\n";
            } else {
                $this->session->set_flashdata('error', 'Rollback failed: ' . $error);
                redirect('migrations/status');
            }
        } else {
            if ($this->input->is_cli_request()) {
                echo "Rollback successful!\n";
                echo "Current version: " . $this->migration->current() . "\n";
            } else {
                $this->session->set_flashdata('success', 'Rollback completed successfully!');
                redirect('migrations/status');
            }
        }
    }

    /**
     * Create a new migration file
     */
    public function create($name = null) {
        // Get name from POST or CLI argument
        if ($name === null) {
            $name = $this->input->post('name');
        }
        
        if ($name === null || empty($name)) {
            if ($this->input->is_cli_request()) {
                echo "Usage: php index.php migrations create migration_name\n";
            } else {
                $this->session->set_flashdata('error', 'Migration name is required.');
                redirect('migrations/status');
            }
            return;
        }
        
        // Sanitize migration name
        $name = preg_replace('/[^a-z0-9_]+/i', '_', $name);
        $name = strtolower($name);
        
        // Generate timestamp
        $timestamp = date('YmdHis');
        $filename = $timestamp . '_' . $name . '.php';
        $filepath = APPPATH . 'migrations/' . $filename;
        
        // Migration template
        $template = <<<'PHP'
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_{CLASS_NAME} extends CI_Migration {

    public function up()
    {
        // Add your migration code here
        // Example:
        // $this->dbforge->add_field(array(
        //     'id' => array(
        //         'type' => 'INT',
        //         'constraint' => 11,
        //         'unsigned' => TRUE,
        //         'auto_increment' => TRUE
        //     ),
        //     'name' => array(
        //         'type' => 'VARCHAR',
        //         'constraint' => '255',
        //     ),
        // ));
        // $this->dbforge->add_key('id', TRUE);
        // $this->dbforge->create_table('example_table');
    }

    public function down()
    {
        // Add your rollback code here
        // Example:
        // $this->dbforge->drop_table('example_table');
    }
}
PHP;
        
        // CodeIgniter expects: Migration_ + ucfirst(strtolower(name))
        // So for "add_auto_increment_id" it becomes "Migration_Add_auto_increment_id"
        $class_name = ucfirst(strtolower($name));
        $template = str_replace('{CLASS_NAME}', $class_name, $template);
        
        if (file_put_contents($filepath, $template)) {
            if ($this->input->is_cli_request()) {
                echo "Migration file created: " . $filename . "\n";
                echo "Path: " . $filepath . "\n";
            } else {
                $this->session->set_flashdata('success', 'Migration file created: ' . $filename);
                redirect('migrations/status');
            }
        } else {
            if ($this->input->is_cli_request()) {
                echo "Failed to create migration file.\n";
            } else {
                $this->session->set_flashdata('error', 'Failed to create migration file.');
                redirect('migrations/status');
            }
        }
    }

    /**
     * Get list of migration files
     */
    private function getMigrationFiles() {
        $migrations = array();
        $path = APPPATH . 'migrations/';
        
        if (is_dir($path)) {
            $files = glob($path . '*.php');
            foreach ($files as $file) {
                $filename = basename($file);
                if (preg_match('/^(\d+)_(.+)\.php$/', $filename, $matches)) {
                    $migrations[] = array(
                        'file' => $filename,
                        'version' => $matches[1],
                        'name' => $matches[2]
                    );
                }
            }
        }
        
        return $migrations;
    }
}

