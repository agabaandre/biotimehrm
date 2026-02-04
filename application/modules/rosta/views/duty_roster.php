<?php
function isWeekend($date)
{
	$day = intval(date('N', strtotime($date)));
	if ($day >= 6) {
		return 'yes';
	};
	return 'no';
}
function dayState($day, $scheduled)
{
	$user = $_SESSION['role'];
	$state = ""; // Initialize state
	
	//its today or day in the past
	if (strtotime($day) < strtotime(date('Y-m-d')) && !empty($scheduled) && $user !== 'sadmin') {
	$state = "disabled";
	 } else if (strtotime($day) < strtotime(date('Y-m-d')) && empty($scheduled) && $user !== 'sadmin') {
		$state = "";
	}
	//if they are scheduled to work
	if (strtotime($day) > strtotime(date('Y-m-d'))) {
		 $state = "disabled";
	}
	
	return $state; // Return the state
} //color

// Show performance monitor for large datasets
if (isset($duties) && count($duties) > 0) {
	// Safely get filters - use passed data or default to empty array
	$view_filters = isset($filters) ? $filters : array();
	$total_employees = $this->rosta_model->count_tabs_optimized($view_filters, '');
	if ($total_employees > 100) {
		include('performance_monitor.php');
	}
}
?>
<?php
// Initialize $state variable
$state = "";

$pv = $this->input->post('year').'-'.$this->input->post('month');

$posted_date = date('Y-m', strtotime($pv));
$current_value = date('Y-m');
$posted_timestamp = strtotime($posted_date);
$current_timestamp = strtotime('first day of +2 months');

if ($posted_timestamp > $current_timestamp) {
    $state = "disabled";
}


//print_r($state);

?>
<div class="card">
	<div class="">
	</div>
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body" style="overflow-x: scroll;">
						<div class="callout callout-success" style="margin-bottom: 10px;">
							<form id="tabularFiltersForm" class="form-horizontal" style="padding-bottom: 1em; margin-bottom: 0;" action="javascript:void(0);" method="post">
								<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
								<div class="row">
									<div class="col-md-3">
										<div class="control-group">
											<input type="hidden" id="month" value="<?php echo $month; ?>">
											<select class="form-control select2" name="month" id="tabular_month">
												<option value="<?php echo $month; ?>"><?php echo strtoupper(date('F', mktime(0, 0, 0, $month, 10))) . "(Showing below)"; ?></option>
												<option value="01">JANUARY</option>
												<option value="02">FEBRUARY</option>
												<option value="03">MARCH</option>
												<option value="04">APRIL</option>
												<option value="05">MAY</option>
												<option value="06">JUNE</option>
												<option value="07">JULY</option>
												<option value="08">AUGUST</option>
												<option value="09">SEPTEMBER</option>
												<option value="10">OCTOBER</option>
												<option value="11">NOVEMBER</option>
												<option value="12">DECEMBER</option>
											</select>
										</div>
									</div>
												<div class="col-md-3">
					   <div class="control-group">
						<input type="hidden" id="year" value="<?php echo $year; ?>">
														<select class="form-control select2" name="year" id="tabular_year">
															<option><?php echo $year; ?></option>
															<?php
															$currentYear = date("Y");
															// Descending order: current year first, then previous years
															for ($i = 0; $i >= -5; $i--) {
																$yearToAdd = $currentYear + $i;
																if ($yearToAdd != $year) {
																?>
																<option><?php echo $yearToAdd; ?></option>
																<?php } ?>
															<?php } ?>
														</select>
													</div>
												</div>
									<div class="col-md-3">
										<div class="control-group">
											<?php
											$facility = $this->session->userdata['facility'];
											$employees = Modules::run("employees/get_employees"); ?>
											<select class="form-control select2" name="empid" id="tabular_empid" select2>
												<option value="">Select Employee</option>
												<?php 
												$selected_employee = isset($selected_employee) ? $selected_employee : '';
												foreach ($employees as $emp) {  
													$selected = ($selected_employee == $emp->ihris_pid) ? 'selected' : '';
												?>
													<option value="<?php echo $emp->ihris_pid ?>" <?php echo $selected; ?>><?php echo $emp->surname . ' ' . $emp->firstname . ' ' . $emp->othername; ?></option>
												<?php }  ?>
											</select>
										</div>
									</div>
									<div class="col-md-3">
										<div class="control-group">
											<button type="button" id="tabular_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-tasks" aria-hidden="true"></i>Apply</button>
											<button type="button" id="sync_all_data" class="btn btn-warning" style="font-size:12px; margin-left:5px;" title="Sync all pending offline data"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Sync All Data</button>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="callout callout-success" style="margin-bottom: 10px;">
							<p class="" style="text-align: center; margin-top: 5px; font-weight:bold; font-size: 1rem;"> Duty Roster Key</p>
							<hr style="color:#15b178;">
							<?php $colors = Modules::run('schedules/getrosterKey'); ?>
							<div class="col-lg-12" style="text-align:center;">
								<?php foreach ($colors as $color) { ?>
									<button type="button" class="btn btn-sm btnkey bg-gray-dark color-pale"><?php echo $color->schedule; ?> (<?php echo $color->letter; ?>)
									</button>
								<?php  } ?>
							</div>
						</div>
						<style>
							.btnkey {
								min-width: 100px;
								;
								color: #fff;
								margin: 2px;
								font-size: 11px;
								overflow: hidden;
							}

							.tabtable {
								zoom: 85%;
							}

							@media only screen and (max-width: 600px) {
								.btnkey {
									width: 100%;
								}
							}
						</style>
						<style>
							#tabular_table {
								table-layout: fixed !important;
								width: 100% !important;
								border-collapse: separate !important;
								border-spacing: 0 !important;
							}
							#tabular_table thead {
								display: table-header-group !important;
							}
							#tabular_table tbody {
								display: table-row-group !important;
							}
							#tabular_table thead th {
								padding: 4px 2px !important;
								margin: 0 !important;
								overflow: hidden !important;
								white-space: nowrap !important;
								text-align: center !important;
								position: relative !important;
							}
							#tabular_table tbody td {
								padding: 2px !important;
								margin: 0 !important;
								vertical-align: middle !important;
								overflow: hidden !important;
								text-align: center !important;
								position: relative !important;
							}
							#tabular_table tbody td.text-left {
								text-align: left !important;
							}
							#tabular_table thead th.text-left {
								text-align: left !important;
							}
							#tabular_table input[type="text"] {
								margin: 0 !important;
								padding: 1px 2px !important;
								border: 1px solid #ddd !important;
								width: 100% !important;
								max-width: 100% !important;
								box-sizing: border-box !important;
							}
							#tabular_table input[type="text"]:focus {
								border: 1px solid #5bc0de !important;
								outline: none !important;
							}
							#tabular_table input[type="text"].pending-sync {
								border: 2px solid #f0ad4e !important;
								background-color: #fff3cd !important;
							}
							.dataTables_wrapper {
								overflow-x: auto !important;
							}

							/* Mobile: make inputs tap-friendly + allow horizontal scroll for many day columns */
							@media (max-width: 768px) {
								.dataTables_wrapper {
									-webkit-overflow-scrolling: touch;
								}
								#tabular_table {
									/* Let the table grow wider than the viewport so columns stay readable */
									width: max-content !important;
								}
								#tabular_table thead th {
									padding: 8px 4px !important;
								}
								#tabular_table tbody td {
									padding: 4px !important;
								}
								#tabular_table input[type="text"] {
									min-height: 34px !important;
									font-size: 16px !important; /* avoids iOS zoom + improves readability */
								}
							}
						</style>
						<?php
						?>
						<div class="row" style="margin-bottom: 5px;">
							<div class="col-md-12">
								<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px" style="float:left;">
							</div>
							<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0; margin:0 auto;">
								<p id="duty_roster_title" style="text-align:center; font-weight:bold; font-size:20px; margin-bottom: 5px;" data-facility-name="<?php echo htmlspecialchars(isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Ministry of Health'); ?>">
									MONTHLY DUTY ROSTER FOR - <?php echo htmlspecialchars(isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : 'Ministry of Health'); ?> <?php echo date('F, Y', strtotime($year . "-" . $month)); ?>
								</p>
							</div>
						</div>
						<table id="tabular_table" class="table table-bordered table-striped table-condensed" style="width:100%; font-size:11px; margin-top: 0; border-collapse: collapse;"></table>
						<div id="editing_locked_message" style="display:none; text-align:center; margin-top:10px;">
							<h4><font color='red'>Editing is locked, please contact the Admin</font></h4>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<script type="text/javascript">
	var url = window.location.href;
	if (url == '<?php echo base_url(); ?>rosta/tabular') {
		$('.fixed-top').addClass('mini-navbar');
	}

		var tabSchedules = {};
		var isSadmin = false;
		var isFutureMonth = false;
		var baseUrl = '<?php echo base_url(); ?>';

		// Helper function to show notification with auto-dismiss
		function showSyncNotification(message, type, delay) {
			delay = delay || 2000;
			var notify = $.notify(message, type);
			setTimeout(function() {
				try {
					if (notify && typeof notify.remove === 'function') {
						notify.remove();
					} else if (notify && notify.$el && typeof notify.$el.remove === 'function') {
						notify.$el.remove();
					}
				} catch(e) {
					// Notification will auto-dismiss based on default delay
				}
			}, delay);
			return notify;
		}

		// Offline Storage Manager
		var OfflineStorage = {
			storageKey: 'rosta_pending_operations',
			syncInProgress: false,
			
			// Check if online
			isOnline: function() {
				return navigator.onLine;
			},
			
			// Save operation to local storage
			saveOperation: function(operation) {
				try {
					var operations = this.getPendingOperations();
					operation.id = 'op_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
					operation.timestamp = new Date().toISOString();
					operation.retries = 0;
					operations.push(operation);
					localStorage.setItem(this.storageKey, JSON.stringify(operations));
					this.updateSyncIndicator();
					return operation.id;
				} catch (e) {
					console.error('Failed to save to local storage:', e);
					return null;
				}
			},
			
			// Get all pending operations
			getPendingOperations: function() {
				try {
					var stored = localStorage.getItem(this.storageKey);
					return stored ? JSON.parse(stored) : [];
				} catch (e) {
					console.error('Failed to read from local storage:', e);
					return [];
				}
			},
			
			// Remove operation after successful sync
			removeOperation: function(operationId) {
				try {
					var operations = this.getPendingOperations();
					operations = operations.filter(function(op) {
						return op.id !== operationId;
					});
					localStorage.setItem(this.storageKey, JSON.stringify(operations));
					this.updateSyncIndicator();
				} catch (e) {
					console.error('Failed to remove operation:', e);
				}
			},
			
			// Sync all pending operations
			sync: function() {
				if (this.syncInProgress) {
					console.log('Sync already in progress');
					return;
				}
				
				if (!this.isOnline()) {
					console.log('Not online, cannot sync');
					showSyncNotification("You are offline. Cannot sync.", "warn", 3000);
					return;
				}
				
				var operations = this.getPendingOperations();
				console.log('Starting sync for', operations.length, 'operations');
				
				if (operations.length === 0) {
					this.updateSyncIndicator();
					return;
				}
				
				this.syncInProgress = true;
				this.updateSyncIndicator('syncing');
				
				var self = this;
				var syncPromises = [];
				var totalOperations = operations.length;
				
				// Show initial sync notification
				showSyncNotification("Syncing " + totalOperations + " item" + (totalOperations > 1 ? 's' : '') + "...", "info", 2000);
				
				// Sync operations sequentially to avoid overwhelming the server
				var syncSequentially = function(index) {
					if (index >= operations.length) {
						// All operations processed
						self.syncInProgress = false;
						self.updateSyncIndicator();
						// Update button state
						if (typeof updateSyncButton === 'function') {
							updateSyncButton();
						}
						// Reload table if any operations were synced
						var syncedCount = totalOperations - self.getPendingOperations().length;
						console.log('Sync complete. Synced:', syncedCount, 'of', totalOperations);
						if (syncedCount > 0) {
							tabularTable.ajax.reload(null, false);
						}
						return;
					}
					
					var operation = operations[index];
					self.syncOperation(operation).then(function(result) {
						console.log('Operation synced successfully:', operation.id);
						// Continue with next operation
						setTimeout(function() {
							syncSequentially(index + 1);
						}, 100); // Small delay between operations
					}).catch(function(error) {
						console.error('Operation sync failed:', operation.id, error);
						// Continue with next operation even if this one failed
						setTimeout(function() {
							syncSequentially(index + 1);
						}, 100);
					});
				};
				
				// Start syncing from first operation
				syncSequentially(0);
			},
			
			// Sync a single operation
			syncOperation: function(operation) {
				var self = this;
				return new Promise(function(resolve, reject) {
					var url = baseUrl + (operation.type === 'add' ? 'rosta/addEvent' : 'rosta/updateEvent');
					
					// Create a copy of the data to avoid modifying the original
					var data = $.extend({}, operation.data);
					
					// Get CSRF token from the form (same way as regular save operations)
					var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
					var csrfToken = $('input[name="' + csrfTokenName + '"]').val() || 
					                 $('meta[name="csrf-token"]').attr('content') || 
					                 '<?php echo $this->security->get_csrf_hash(); ?>';
					
					// Add CSRF token to data (same format as $.post)
					data[csrfTokenName] = csrfToken;
					
					console.log('Syncing operation:', operation.id);
					console.log('Type:', operation.type);
					console.log('URL:', url);
					console.log('Data:', JSON.stringify(data));
					
					// Use $.post instead of $.ajax to match the format used in regular saves
					$.post(url, data)
						.done(function(result) {
							console.log('Sync success for operation:', operation.id, 'Result:', result);
							
							// Trim whitespace and newlines from result
							var trimmedResult = (result || '').toString().trim();
							
							// For addEvent, result should be a number (affected rows)
							// For updateEvent, result should be '1' or '0'
							var isSuccess = false;
							if (operation.type === 'add') {
								// addEvent returns number of affected rows (0 or 1)
								// Even if 0, it might be because entry already exists (INSERT IGNORE)
								var rowsAffected = parseInt(trimmedResult) || 0;
								isSuccess = (rowsAffected >= 0); // Accept 0 as success (entry might already exist)
							} else {
								// updateEvent returns '1' for success, '0' for failure
								// But if entry doesn't exist, we should try to add it instead
								if (trimmedResult === '0' || trimmedResult === '') {
									console.log('Update returned 0, entry might not exist. Trying to add instead...');
									// Try to add the entry if update failed (entry might not exist yet)
									var addData = $.extend({}, operation.data);
									// For add, we need 'start' date, but update doesn't have it
									// Extract date from entry_id if possible
									var entryId = operation.data.id || '';
									var dateMatch = entryId.match(/^(\d{4}-\d{2}-\d{2})/);
									if (dateMatch) {
										addData.start = dateMatch[1];
										addData.end = "";
										delete addData.id; // Remove id for add operation
										
										// Get CSRF token
										var csrfTokenName = '<?php echo $this->security->get_csrf_token_name(); ?>';
										var csrfToken = $('input[name="' + csrfTokenName + '"]').val() || 
										                 $('meta[name="csrf-token"]').attr('content') || 
										                 '<?php echo $this->security->get_csrf_hash(); ?>';
										addData[csrfTokenName] = csrfToken;
										
										// Try to add the entry
										$.post(baseUrl + 'rosta/addEvent', addData)
											.done(function(addResult) {
												var addRows = parseInt(addResult) || 0;
												if (addRows >= 0) {
													console.log('Successfully added entry after update failed');
													self.removeOperation(operation.id);
													showSyncNotification("Scheduled Synced", "success", 2000);
													resolve(addResult);
												} else {
													console.warn('Add also failed after update failed');
													showSyncNotification("Sync failed: Could not update or add entry", "error", 3000);
													reject('Update and add both failed');
												}
											})
											.fail(function(xhr, status, error) {
												console.error('Add failed after update failed:', error);
												showSyncNotification("Sync failed: Could not update or add entry", "error", 3000);
												reject(error);
											});
										return; // Exit early, will resolve/reject in add callback
									} else {
										// Can't extract date, treat as failure
										isSuccess = false;
									}
								} else {
									isSuccess = (trimmedResult === '1' || trimmedResult === 1);
								}
							}
							
							if (isSuccess) {
								self.removeOperation(operation.id);
								
								// Show success notification similar to save notifications
								if (operation.type === 'add') {
									showSyncNotification("Scheduled Synced", "success", 2000);
								} else {
									showSyncNotification("Update Synced", "info", 2000);
								}
								
								resolve(trimmedResult);
							} else {
								console.warn('Sync returned unsuccessful result:', trimmedResult);
								showSyncNotification("Sync failed: Invalid response from server", "error", 3000);
								reject('Invalid response: ' + trimmedResult);
							}
						})
						.fail(function(xhr, status, error) {
							console.error('Sync error for operation:', operation.id);
							console.error('Status:', status);
							console.error('Error:', error);
							console.error('Response:', xhr.responseText);
							console.error('Status Code:', xhr.status);
							
							operation.retries = (operation.retries || 0) + 1;
							
							// Check for CSRF token error
							if (xhr.status === 403 || (xhr.responseText && xhr.responseText.toLowerCase().indexOf('csrf') !== -1)) {
								console.error('CSRF token error - may need to refresh page');
								showSyncNotification("Sync failed: Session expired. Please refresh the page.", "error", 4000);
							} else if (xhr.status === 0) {
								console.error('Network error - connection failed');
								showSyncNotification("Sync failed: Network error. Check your connection.", "error", 3000);
							} else {
								showSyncNotification("Sync failed: " + (xhr.responseText || error || 'Unknown error'), "error", 3000);
							}
							
							// Remove if too many retries
							if (operation.retries >= 5) {
								self.removeOperation(operation.id);
								console.error('Operation failed after 5 retries:', operation.id);
								showSyncNotification("Sync failed after multiple attempts", "error", 3000);
								reject(error);
							} else {
								// Update operation with retry count
								var operations = self.getPendingOperations();
								var index = operations.findIndex(function(op) {
									return op.id === operation.id;
								});
								if (index !== -1) {
									operations[index] = operation;
									localStorage.setItem(self.storageKey, JSON.stringify(operations));
									console.log('Updated operation retry count:', operation.retries);
								}
								reject(error);
							}
						});
				});
			},
			
			// Update sync indicator in UI (removed - button shows status instead)
			updateSyncIndicator: function(status) {
				// Indicator removed - button shows sync status instead
				// This function kept as stub for compatibility
			},
			
			// Clear all pending operations (for testing/debugging)
			clear: function() {
				localStorage.removeItem(this.storageKey);
				this.updateSyncIndicator();
			}
		};

		$(document).ready(function() {
		// baseUrl is already defined at top level
		var month = '<?php echo $month; ?>';
		var year = '<?php echo $year; ?>';
		var monthDays = <?php echo (int)cal_days_in_month(CAL_GREGORIAN, $month, $year); ?>;
		var isMobile = (window.matchMedia && window.matchMedia('(max-width: 768px)').matches) || (window.innerWidth && window.innerWidth <= 768);

		// Days in month for selected month/year (1-12, full year)
		function getDaysInMonth(monthNum, yearNum) {
			var m = parseInt(monthNum, 10);
			var y = parseInt(yearNum, 10);
			return new Date(y, m, 0).getDate();
		}

		function isWeekend(dateStr) {
			var date = new Date(dateStr);
			var day = date.getDay();
			return (day === 0 || day === 6); // Sunday = 0, Saturday = 6
		}

		function escapeHtml(val) {
			if (val == null || val === '') return '';
			var s = String(val);
			return s.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}

		function updateDutyRosterTitle(monthVal, yearVal) {
			var facilityName = $('#duty_roster_title').attr('data-facility-name') || 'Ministry of Health';
			var dateObj = new Date(parseInt(yearVal, 10), parseInt(monthVal, 10) - 1, 1);
			var monthName = dateObj.toLocaleString('en-US', { month: 'long' });
			$('#duty_roster_title').text('MONTHLY DUTY ROSTER FOR - ' + facilityName + ' ' + monthName + ', ' + yearVal);
		}

		function buildColumns(monthVal, yearVal, daysInMonth) {
			var cols = [];
			cols.push({ data: 'rownum', title: '#', className: 'text-center', width: isMobile ? '50px' : '40px', orderable: false });
			cols.push({ data: 'fullname', title: 'Name', className: 'text-left', width: isMobile ? '200px' : '120px', orderable: false });
			cols.push({ data: 'job', title: 'Position', className: 'text-left', width: isMobile ? '180px' : '120px', orderable: false });

			for (var d = 1; d <= daysInMonth; d++) {
				var dayStr = (d < 10) ? '0' + d : d.toString();
				var ymd = yearVal + '-' + monthVal + '-' + dayStr;
				var isWeekendDay = isWeekend(ymd);
				var headerClass = isWeekendDay ? 'text-center weekend-header' : 'text-center';
				var headerStyle = isWeekendDay ? 'background-color:red; color:#FFFFFF;' : '';

				(function(dayNum) {
					cols.push({
						data: 'd' + dayNum,
						title: '<span style="' + headerStyle + '">' + dayNum + '</span>',
						className: headerClass,
						width: isMobile ? '52px' : '35px',
						orderable: false,
						render: function(data, type, row) {
							if (type === 'display') {
								var entryId = row['entry_id_' + dayNum] || '';
								var pid = row['ihris_pid'] || '';
								var disabled = row['disabled_' + dayNum] === true || row['disabled_' + dayNum] === 'true';
								var disabledAttr = disabled ? 'disabled' : '';
								var inputClass = data ? 'update duty' : 'new duty';
								var fontSize = isMobile ? '16px' : '13px';
								var padding = isMobile ? '6px 2px' : '1px 2px';
								var height = isMobile ? '34px' : 'auto';
								var displayVal = escapeHtml(data || '');
								
								return '<input type="text" style="padding:' + padding + '; height:' + height + '; margin:0; text-align: center; width:100%; max-width:100%; box-sizing:border-box; font-size:' + fontSize + '; font-weight:bold; border:1px solid #ddd; border-radius:4px;" ' +
									'class="' + inputClass + '" ' +
									'id="' + entryId + '" ' +
									'day="' + dayNum + '" ' +
									'pid="' + pid + '" ' +
									'pattern="[A-Za-z]+" ' +
									'maxlength="1" ' +
									'title="Letters only for Duty" ' +
									'value="' + displayVal + '" ' +
									disabledAttr + '>';
							}
							return data || '';
						}
					});
				})(d);
			}
			return cols;
		}

		var tableColumns = buildColumns(month, year, monthDays);
		
		// Build columnDefs with explicit widths
		var columnDefs = tableColumns.map(function(col, index) {
			return {
				targets: index,
				width: col.width || '35px',
				className: col.className || '',
				orderable: col.orderable !== undefined ? col.orderable : false
			};
		});
		
		var tabularTable = $('#tabular_table').DataTable({
			processing: true,
			serverSide: true,
			searching: false,
			ordering: false,
			autoWidth: false,
			pageLength: 20,
			lengthChange: true,
			lengthMenu: [[20, 25, 50, 100, 200], [20, 25, 50, 100, 200]],
			pagingType: 'simple_numbers',
			dom: '<"top"lp>rt<"bottom"ip><"clear">',
			columnDefs: columnDefs,
			ajax: {
				url: baseUrl + 'rosta/tabularAjax',
				type: 'POST',
				data: function(d) {
					d.month = $('#tabular_month').val() || month;
					d.year = $('#tabular_year').val() || year;
					d.empid = $('#tabular_empid').val() || '';
					d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
				},
				dataSrc: function(json) {
					tabSchedules = json.tab_schedules || {};
					isSadmin = json.is_sadmin || false;
					isFutureMonth = json.is_future_month || false;
					
					if (isFutureMonth && !isSadmin) {
						$('#editing_locked_message').show();
					} else {
						$('#editing_locked_message').hide();
					}
					
					return json.data;
				}
			},
			columns: tableColumns,
			initComplete: function() {
				// Force column width synchronization after initial load
				var api = this.api();
				setTimeout(function() {
					forceColumnAlignment(api);
				}, 50);
			},
			drawCallback: function() {
				var api = this.api();
				// Force column alignment after each draw
				setTimeout(function() {
					forceColumnAlignment(api);
				}, 10);
				// Re-attach event handlers after table redraw
				attachDutyHandlers();
			}
		});

		function forceColumnAlignment(api) {
			// Use DataTables API to get columns and force alignment
			api.columns().every(function() {
				var column = this;
				var header = $(column.header());
				var nodes = column.nodes();
				
				if (nodes.length > 0) {
					// Get the defined width from column definition
					var colIndex = column.index();
					var definedWidth = tableColumns[colIndex] ? parseInt(tableColumns[colIndex].width.replace('px', '')) : 35;
					
					// Measure actual width of first body cell
					var firstCell = $(nodes[0]);
					var actualWidth = firstCell.outerWidth();
					
					// Use the larger of defined or actual width
					var targetWidth = Math.max(definedWidth, actualWidth);
					
					// Apply to header
					header.css({
						'width': targetWidth + 'px',
						'min-width': targetWidth + 'px',
						'max-width': targetWidth + 'px',
						'box-sizing': 'border-box'
					});
					
					// Apply to all cells in this column
					$(nodes).each(function() {
						$(this).css({
							'width': targetWidth + 'px',
							'min-width': targetWidth + 'px',
							'max-width': targetWidth + 'px',
							'box-sizing': 'border-box'
						});
					});
				}
			});
			
			// Additional pass: ensure headers match body cells exactly
			var table = $('#tabular_table');
			var headerCells = table.find('thead th');
			var firstRowCells = table.find('tbody tr:first td');
			
			if (headerCells.length === firstRowCells.length) {
				firstRowCells.each(function(index) {
					var bodyCell = $(this);
					var headerCell = $(headerCells[index]);
					var bodyWidth = bodyCell.outerWidth();
					
					headerCell.css({
						'width': bodyWidth + 'px',
						'min-width': bodyWidth + 'px',
						'max-width': bodyWidth + 'px'
					});
					
					bodyCell.css({
						'width': bodyWidth + 'px',
						'min-width': bodyWidth + 'px',
						'max-width': bodyWidth + 'px'
					});
				});
			}
		}

		function attachDutyHandlers() {
			var $newInputs = $('.new');
			var $updateInputs = $('.update');
			
			$newInputs.off('keyup').on('keyup', function(event) {
				var $input = $(this);
		if (event.keyCode == 13) {
					var textboxes = $("input.duty");
					var currentBoxNumber = textboxes.index(this);
			if (textboxes[currentBoxNumber + 1] != null) {
						var nextBox = textboxes[currentBoxNumber + 1];
				nextBox.focus();
				nextBox.select();
			}
			event.preventDefault();
			return false;
				} else {
					var letter = $input.val();
					if (letter !== "") {
						var hpid = $input.attr('pid');
						var entry_id = $input.attr('id');
						var day = $input.attr('day');
						
						letter = letter.replace(/\s/g, '');
						letter = letter.toUpperCase();
						var duty = tabSchedules["'" + letter + "'"];
						
						if (typeof duty == "undefined") {
							$.notify("Warning: That letter doesn't represent any schedule", "warn");
							$input.val('');
						} else {
							var color = pickColor(duty);
							var dayStr = (parseInt(day) < 10) ? "0" + day : day;
							var selMonth = $('#tabular_month').val() || month;
							var selYear = $('#tabular_year').val() || year;
							var start = selYear + "-" + selMonth + "-" + dayStr;
							
							$input.val(letter);
							var $self = $input;
							
							// Prepare data
							var postData = {
						hpid: hpid,
						duty: duty,
						color: color,
						start: start,
								end: ""
							};
							
							// Try to save online first
							if (OfflineStorage.isOnline()) {
								$.post(baseUrl + 'rosta/addEvent', $.extend({}, postData, {
						'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
								}), function(result) {
						console.log(result);
									$self.prop('id', entry_id);
									$self.addClass('update duty');
									$self.removeClass('new');
						showSyncNotification("Scheduled Saved", "success", 2000);
									tabularTable.ajax.reload(null, false);
								}).fail(function(xhr, status, error) {
									// Network error - save to local storage
									console.log('Network error, saving to local storage');
									var opId = OfflineStorage.saveOperation({
										type: 'add',
										data: postData
									});
									if (opId) {
										$self.prop('id', entry_id);
										$self.addClass('update duty');
										$self.removeClass('new');
										$self.addClass('pending-sync');
										showSyncNotification("Saved offline - will sync when online", "info", 2000);
									} else {
										showSyncNotification("Failed to save - please try again", "error", 3000);
									}
								});
							} else {
								// Offline - save to local storage
								console.log('Offline, saving to local storage');
								var opId = OfflineStorage.saveOperation({
									type: 'add',
									data: postData
								});
								if (opId) {
									$self.prop('id', entry_id);
									$self.addClass('update duty');
									$self.removeClass('new');
									$self.addClass('pending-sync');
									showSyncNotification("Saved offline - will sync when online", "info", 2000);
								} else {
									showSyncNotification("Failed to save - please try again", "error", 3000);
								}
							}
						}
					}
				}
			});
			
			$updateInputs.off('keyup').on('keyup', function(event) {
				var $input = $(this);
		if (event.keyCode == 13) {
					var textboxes = $("input.duty");
					var currentBoxNumber = textboxes.index(this);
			if (textboxes[currentBoxNumber + 1] != null) {
						var nextBox = textboxes[currentBoxNumber + 1];
				nextBox.focus();
				nextBox.select();
			}
			event.preventDefault();
			return false;
				} else {
					var letter = $input.val();
					if (letter !== "") {
						var id = $input.attr('id');
						var hpid = $input.attr('pid');
						
						letter = letter.replace(/\s/g, '');
						letter = letter.toUpperCase();
						var duty = tabSchedules["'" + letter + "'"];
						
						if (typeof duty == "undefined") {
							$.notify("Warning: That letter doesn't represent any schedule", "warn");
							$input.val('');
						} else {
							var color = pickColor(duty);
							$input.val(letter);
							
							// Prepare data
							var postData = {
						id: id,
						hpid: hpid,
						duty: duty,
								color: color
							};
							
							// Try to save online first
							if (OfflineStorage.isOnline()) {
								$.post(baseUrl + 'rosta/updateEvent', $.extend({}, postData, {
						'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
								}), function(result) {
						console.log(result);
									$input.removeClass('pending-sync');
									showSyncNotification("Update Finished", "info", 2000);
									tabularTable.ajax.reload(null, false);
								}).fail(function(xhr, status, error) {
									// Network error - save to local storage
									console.log('Network error, saving to local storage');
									var opId = OfflineStorage.saveOperation({
										type: 'update',
										data: postData
									});
									if (opId) {
										$input.addClass('pending-sync');
										showSyncNotification("Saved offline - will sync when online", "info", 2000);
									} else {
										showSyncNotification("Failed to save - please try again", "error", 3000);
									}
								});
							} else {
								// Offline - save to local storage
								console.log('Offline, saving to local storage');
								var opId = OfflineStorage.saveOperation({
									type: 'update',
									data: postData
								});
								if (opId) {
									$input.addClass('pending-sync');
									showSyncNotification("Saved offline - will sync when online", "info", 2000);
								} else {
									showSyncNotification("Failed to save - please try again", "error", 3000);
								}
							}
						}
					}
				}
			});
		}

		function applyTabularFilter() {
			var selMonth = $('#tabular_month').val() || month;
			var selYear = $('#tabular_year').val() || year;
			updateDutyRosterTitle(selMonth, selYear);
			var newDays = getDaysInMonth(selMonth, selYear);
			var monthOrYearChanged = (selMonth !== month || selYear !== year);
			if (monthOrYearChanged) {
				month = selMonth;
				year = selYear;
				monthDays = newDays;
				if (tabularTable && $.fn.DataTable.isDataTable('#tabular_table')) {
					tabularTable.destroy();
					$('#tabular_table').empty();
				}
				tableColumns = buildColumns(month, year, monthDays);
				columnDefs = tableColumns.map(function(col, index) {
					return {
						targets: index,
						width: col.width || '35px',
						className: col.className || '',
						orderable: col.orderable !== undefined ? col.orderable : false
					};
				});
				tabularTable = $('#tabular_table').DataTable({
					processing: true,
					serverSide: true,
					searching: false,
					ordering: false,
					autoWidth: false,
					pageLength: 20,
					lengthChange: true,
					lengthMenu: [[20, 25, 50, 100, 200], [20, 25, 50, 100, 200]],
					pagingType: 'simple_numbers',
					dom: '<"top"lp>rt<"bottom"ip><"clear">',
					columnDefs: columnDefs,
					ajax: {
						url: baseUrl + 'rosta/tabularAjax',
						type: 'POST',
						data: function(d) {
							d.month = $('#tabular_month').val() || month;
							d.year = $('#tabular_year').val() || year;
							d.empid = $('#tabular_empid').val() || '';
							d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
						},
						dataSrc: function(json) {
							tabSchedules = json.tab_schedules || {};
							isSadmin = json.is_sadmin || false;
							isFutureMonth = json.is_future_month || false;
							if (isFutureMonth && !isSadmin) {
								$('#editing_locked_message').show();
							} else {
								$('#editing_locked_message').hide();
							}
							return json.data;
						}
					},
					columns: tableColumns,
					initComplete: function() {
						var api = this.api();
						setTimeout(function() { forceColumnAlignment(api); }, 50);
					},
					drawCallback: function() {
						var api = this.api();
						setTimeout(function() { forceColumnAlignment(api); }, 10);
						attachDutyHandlers();
					}
				});
			} else {
				month = selMonth;
				year = selYear;
				tabularTable.ajax.reload();
			}
		}

		$('#tabular_apply').on('click', function(e) {
			e.preventDefault();
			applyTabularFilter();
		});

		$('#tabular_month, #tabular_year').on('change', function() {
			applyTabularFilter();
		});
		$('#tabular_empid').on('change', function() {
			tabularTable.ajax.reload();
		});
		
		// Initialize sync indicator
		OfflineStorage.updateSyncIndicator();
		
		// Listen for online/offline events
		$(window).on('online', function() {
			console.log('Network is online, syncing pending operations...');
			OfflineStorage.sync();
		});
		
		$(window).on('offline', function() {
			console.log('Network is offline');
			OfflineStorage.updateSyncIndicator();
		});
		
		// Periodic sync check (every 30 seconds)
		setInterval(function() {
			if (OfflineStorage.isOnline() && !OfflineStorage.syncInProgress) {
				var pending = OfflineStorage.getPendingOperations();
				if (pending.length > 0) {
					OfflineStorage.sync();
				}
			}
		}, 30000);
		
		// Manual sync button - use event delegation to ensure it works
		$(document).on('click', '#sync_all_data', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var pendingCount = OfflineStorage.getPendingOperations().length;
			
			if (pendingCount === 0) {
				showSyncNotification("No pending data to sync", "info", 2000);
				updateSyncButton();
				return;
			}
			
			if (!OfflineStorage.isOnline()) {
				showSyncNotification("You are offline. Please check your connection.", "warn", 3000);
				return;
			}
			
			if (OfflineStorage.syncInProgress) {
				showSyncNotification("Sync already in progress...", "info", 2000);
				return;
			}
			
			// Disable button during sync
			$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Syncing...');
			
			// Start sync - this will show individual sync notifications for each operation
			OfflineStorage.sync();
			
			// Monitor sync completion and update button
			var checkInterval = setInterval(function() {
				if (!OfflineStorage.syncInProgress) {
					clearInterval(checkInterval);
					var newPendingCount = OfflineStorage.getPendingOperations().length;
					var syncedCount = pendingCount - newPendingCount;
					
					updateSyncButton();
					
					// Show summary notification
					if (newPendingCount === 0 && syncedCount > 0) {
						showSyncNotification("All " + syncedCount + " item" + (syncedCount > 1 ? 's' : '') + " synced successfully", "success", 2000);
					}
					
					$btn.prop('disabled', false);
					if (newPendingCount > 0) {
						$btn.html('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Sync All Data (' + newPendingCount + ')');
					} else {
						$btn.html('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Sync All Data');
					}
				}
			}, 500);
		});
		
		// Update sync button state based on pending operations
		function updateSyncButton() {
			var pendingCount = OfflineStorage.getPendingOperations().length;
			var $btn = $('#sync_all_data');
			
			if (pendingCount > 0) {
				$btn.removeClass('btn-warning').addClass('btn-warning');
				$btn.html('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Sync All Data (' + pendingCount + ')');
				$btn.prop('disabled', false);
			} else {
				$btn.removeClass('btn-warning').addClass('btn-success');
				$btn.html('<i class="fa fa-check" aria-hidden="true"></i> All Synced');
				setTimeout(function() {
					$btn.removeClass('btn-success').addClass('btn-warning');
					$btn.html('<i class="fa fa-cloud-upload" aria-hidden="true"></i> Sync All Data');
				}, 3000);
			}
		}
		
		// Update button when sync operations change
		// Wrapper to update button whenever sync indicator would have been updated
		var originalUpdateSyncIndicator = OfflineStorage.updateSyncIndicator;
		OfflineStorage.updateSyncIndicator = function(status) {
			originalUpdateSyncIndicator.call(this, status);
			updateSyncButton();
		};
		
		// Initialize button on page load
		updateSyncButton();
		
		// Try to sync on page load if online
		if (OfflineStorage.isOnline()) {
			setTimeout(function() {
				var pending = OfflineStorage.getPendingOperations();
				if (pending.length > 0) {
					OfflineStorage.sync();
				}
			}, 1000);
		}
	});
	//color picking function
	function pickColor(duty) {
		if (duty == '14') {
			var kala = '#297bb2';
		} else
		if (duty == '15') {
			//even
			var kala = '#245270';
		} else
			//night
			if (duty == '16') {
				var kala = '#2f446b';
			}
		else
			//off
			if (duty == '17') {
				var kala = '#d1a110';
			}
		else
			//annual leave
			if (duty == '18') {
				var kala = '#B22222';
			}
		else
			//study leave
			if (duty == '19') {
				var kala = '#FF8C00';
			}
		else
			//maternity leave
			if (duty == '20') {
				var kala = '#9ACD32';
			}
		else
			//other
			if (duty == '21') {
				var kala = '#32CD32';
			}
		return kala;
	}
</script>