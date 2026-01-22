<?php
/**
 * Performance Monitor View
 * This view shows performance statistics for the duty roster
 */
?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tachometer-alt"></i> Performance Monitor
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info">
                        <i class="fas fa-users"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Employees</span>
                        <span class="info-box-number" id="totalEmployees">-</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success">
                        <i class="fas fa-calendar-check"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Duties</span>
                        <span class="info-box-number" id="totalDuties">-</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Query Time</span>
                        <span class="info-box-number" id="queryTime">-</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary">
                        <i class="fas fa-calendar"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Current Month</span>
                        <span class="info-box-number" id="currentMonth">-</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="button" class="btn btn-primary" onclick="createIndexes()">
                    <i class="fas fa-database"></i> Create Performance Indexes
                </button>
                <button type="button" class="btn btn-info" onclick="refreshStats()">
                    <i class="fas fa-sync"></i> Refresh Stats
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function refreshStats() {
    $.ajax({
        url: '<?php echo base_url("rosta/getPerformanceStats"); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#totalEmployees').text(data.total_employees);
            $('#totalDuties').text(data.total_duties);
            $('#queryTime').text(data.query_execution_time + 'ms');
            $('#currentMonth').text(data.current_month);
        },
        error: function() {
            console.error('Failed to fetch performance stats');
        }
    });
}

function createIndexes() {
    if (confirm('This will create database indexes to improve performance. Continue?')) {
        $.ajax({
            url: '<?php echo base_url("rosta/createIndexes"); ?>',
            type: 'GET',
            success: function(response) {
                alert('Indexes created successfully: ' + response);
                refreshStats();
            },
            error: function() {
                alert('Failed to create indexes');
            }
        });
    }
}

// Load stats on page load
$(document).ready(function() {
    refreshStats();
});
</script>
