<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extended Migration Library
 * 
 * Fixes the is_callable() issue for instance methods
 */
class MY_Migration extends CI_Migration {

	/**
	 * Validates the migration file structure
	 * 
	 * Override to fix is_callable() issue with instance methods
	 */
	protected function _validate_migration($file, $method)
	{
		include_once($file);
		$class = 'Migration_'.ucfirst(strtolower($this->_get_migration_name(basename($file, '.php'))));

		// Validate the migration file structure
		if ( ! class_exists($class, FALSE))
		{
			$this->_error_string = sprintf($this->lang->line('migration_class_doesnt_exist'), $class);
			return FALSE;
		}
		// Fix: Use method_exists() instead of is_callable() for instance methods
		elseif ( ! method_exists($class, $method))
		{
			$this->_error_string = sprintf($this->lang->line('migration_missing_'.$method.'_method'), $class);
			return FALSE;
		}

		return array($class, $method);
	}
}

