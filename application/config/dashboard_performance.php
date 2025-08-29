<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Performance Configuration
 * Adjust these settings to optimize dashboard performance
 */

$config['dashboard'] = array(
    
    // Cache settings
    'cache_enabled' => TRUE,
    'cache_duration' => 900, // 15 minutes in seconds
    'essential_cache_duration' => 600, // 10 minutes for essential data
    
    // Database query optimization
    'max_query_timeout' => 30, // Maximum seconds for database queries
    'enable_query_logging' => FALSE, // Log slow queries for debugging
    
    // Progressive loading settings
    'load_essential_first' => TRUE, // Load critical data first
    'load_calendars_async' => TRUE, // Load calendars asynchronously
    'enable_lazy_loading' => TRUE, // Enable lazy loading for non-critical data
    
    // UI optimization
    'fade_animations' => TRUE, // Enable fade animations for data updates
    'show_loading_indicators' => TRUE, // Show loading spinners
    'enable_error_handling' => TRUE, // Show user-friendly error messages
    
    // Data refresh settings
    'auto_refresh_interval' => 300000, // Auto-refresh every 5 minutes (milliseconds)
    'enable_real_time_updates' => FALSE, // Enable WebSocket updates (if available)
    
    // Memory optimization
    'max_memory_usage' => '256M', // Maximum memory usage for dashboard
    'enable_data_compression' => TRUE, // Compress JSON responses
    
    // Monitoring and debugging
    'log_performance_metrics' => TRUE, // Log dashboard load times
    'enable_performance_monitoring' => TRUE, // Monitor dashboard performance
    'debug_mode' => FALSE // Enable debug information
);

// Database connection pool settings (if using connection pooling)
$config['database_pool'] = array(
    'min_connections' => 5,
    'max_connections' => 20,
    'connection_timeout' => 30,
    'idle_timeout' => 300
);

// Cache configuration for different data types
$config['cache_strategy'] = array(
    'counts' => array(
        'enabled' => TRUE,
        'duration' => 1800, // 30 minutes
        'priority' => 'high'
    ),
    'dates' => array(
        'enabled' => TRUE,
        'duration' => 900, // 15 minutes
        'priority' => 'medium'
    ),
    'attendance' => array(
        'enabled' => TRUE,
        'duration' => 300, // 5 minutes
        'priority' => 'high'
    ),
    'calendars' => array(
        'enabled' => TRUE,
        'duration' => 3600, // 1 hour
        'priority' => 'low'
    )
);
