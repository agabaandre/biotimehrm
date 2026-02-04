<?php
function isWeekend($date)
{
	$day = intval(date('N', strtotime($date)));
	if ($day >= 6) {
		return 'yes';
	};
	return 'no';
}
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
							<form id="actualsFiltersForm" class="form-horizontal" style="padding-bottom: 1em; margin-bottom: 0;" action="javascript:void(0);" method="post">
								<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
								<div class="row">
									<div class="col-md-3">
										<div class="control-group">
											<input type="hidden" id="month" value="<?php echo $month; ?>">
											<select class="form-control select2" name="month" id="actuals_month">
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
											<select class="form-control select2" name="year" id="actuals_year">
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
											<select class="form-control select2" name="empid" id="actuals_empid" select2>
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
											<button type="button" id="actuals_apply" class="btn bg-gray-dark color-pale" style="font-size:12px;"><i class="fa fa-tasks" aria-hidden="true"></i>Apply</button>
											<button type="button" id="sync_all_data" class="btn btn-warning" style="font-size:12px; margin-left:5px;" title="Sync all pending offline data"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Sync All Data</button>
										</div>
									</div>
								</div>
							</form>
						</div>
						<div class="callout callout-success" style="margin-bottom: 10px;">
							<p class="" style="text-align: center; margin-top: 5px; font-weight:bold; font-size: 1rem;"> Attendance Key</p>
							<hr style="color:#15b178;">
							<?php $colors = Modules::run('schedules/getattSchedules'); ?>
							<div class="col-lg-12" style="text-align:center;">
								<p style="text-align:center; font-weight:bold; font:14rem;"></p>
								<?php foreach ($colors as $color) { ?>
									<button type="button" class="btn btn-sm btnkey bg-gray-dark color-pale"><?php echo $color->schedule; ?> (<?php echo $color->letter; ?>)
									</button>
								<?php  } ?>
							</div>
						</div>
						<style>
							.btnkey {
								min-width: 100px;
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
							#actuals_table {
								table-layout: fixed !important;
								width: 100% !important;
								border-collapse: separate !important;
								border-spacing: 0 !important;
							}
							#actuals_table thead {
								display: table-header-group !important;
							}
							#actuals_table tbody {
								display: table-row-group !important;
							}
							#actuals_table thead th {
								padding: 4px 2px !important;
								margin: 0 !important;
								overflow: hidden !important;
								white-space: nowrap !important;
								text-align: center !important;
								position: relative !important;
							}
							#actuals_table tbody td {
								padding: 2px !important;
								margin: 0 !important;
								vertical-align: middle !important;
								overflow: hidden !important;
								text-align: center !important;
								position: relative !important;
							}
							#actuals_table tbody td.text-left {
								text-align: left !important;
							}
							#actuals_table thead th.text-left {
								text-align: left !important;
							}
							#actuals_table input[type="text"] {
								margin: 0 !important;
								padding: 1px 2px !important;
								border: 1px solid #ddd !important;
								width: 100% !important;
								max-width: 100% !important;
								box-sizing: border-box !important;
								text-transform: uppercase !important;
							}
							#actuals_table input[type="text"]:focus {
								border: 1px solid #5bc0de !important;
								outline: none !important;
							}
							#actuals_table input[type="text"].pending-sync {
								border: 2px solid #f0ad4e !important;
								background-color: #fff3cd !important;
							}
							.dataTables_wrapper {
								overflow-x: auto !important;
							}
						</style>
						<?php
						?>
						<div class="row" style="margin-bottom: 5px;">
							<div class="col-md-12">
								<img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px" style="float:left;">
							</div>
							<div class="col-md-12" style="border-right: 0; border-left: 0; border-top: 0; margin:0 auto;">
								<p id="actuals_title" style="text-align:center; font-weight:bold; font-size:20px; margin-bottom: 5px;" data-facility-name="<?php echo htmlspecialchars($_SESSION['facility_name'] ?? 'Ministry of Health'); ?>">
									MONTHLY ATTENDANCE FOR - <?php echo htmlspecialchars($_SESSION['facility_name'] ?? 'Ministry of Health'); ?> <?php echo date('F, Y', strtotime($year . "-" . $month)); ?>
								</p>
							</div>
						</div>
						<table id="actuals_table" class="table table-bordered table-striped table-condensed" style="width:100%; font-size:11px; margin-top: 0; border-collapse: collapse;"></table>
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
	if (url == '<?php echo base_url(); ?>rosta/actuals' || url == '<?php echo base_url(); ?>rosta/actuals#') {
		$('.fixed-top').addClass('mini-navbar');
	}

	var actualSchedules = {};
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
		storageKey: 'actuals_pending_operations',
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
						actualsTable.ajax.reload(null, false);
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
				var url = baseUrl + 'rosta/saveActual';
				
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
				console.log('URL:', url);
				console.log('Data:', JSON.stringify(data));
				
				// Use $.post instead of $.ajax to match the format used in regular saves
				$.post(url, data)
					.done(function(result) {
						console.log('Sync success for operation:', operation.id, 'Result:', result);
						
						// saveActual returns "Actual Saved" or "Update Finished" or "Failed"
						var trimmedResult = (result || '').toString().trim();
						var isSuccess = (trimmedResult.indexOf('Saved') !== -1 || trimmedResult.indexOf('Finished') !== -1);
						
						if (isSuccess) {
							self.removeOperation(operation.id);
							showSyncNotification("Actual Synced", "success", 2000);
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
		var currentMonth = '<?php echo $month; ?>';
		var currentYear = '<?php echo $year; ?>';
		var actualsTable = null;
		var tableColumns = null;

		function isWeekend(dateStr) {
			var date = new Date(dateStr);
			var day = date.getDay();
			return (day === 0 || day === 6); // Sunday = 0, Saturday = 6
		}

		function getDaysInMonth(month, year) {
			return new Date(parseInt(year, 10), parseInt(month, 10), 0).getDate();
		}

		function updateActualsTitle(month, year) {
			var facilityName = $('#actuals_title').attr('data-facility-name') || 'Ministry of Health';
			var dateObj = new Date(parseInt(year, 10), parseInt(month, 10) - 1, 1);
			var monthName = dateObj.toLocaleString('en-US', { month: 'long' });
			$('#actuals_title').text('MONTHLY ATTENDANCE FOR - ' + facilityName + ' ' + monthName + ', ' + year);
		}

		function buildColumns(month, year) {
			var monthDays = getDaysInMonth(month, year);
			var cols = [];
			cols.push({ data: 'rownum', title: '#', className: 'text-center', width: '40px', orderable: false });
			cols.push({ data: 'fullname', title: 'Name', className: 'text-left', width: '150px', orderable: false });
			cols.push({ data: 'job', title: 'Position', className: 'text-left', width: '150px', orderable: false });

			for (var d = 1; d <= monthDays; d++) {
				var dayStr = (d < 10) ? '0' + d : d.toString();
				var ymd = year + '-' + (month.length === 1 ? '0' + month : month) + '-' + dayStr;
				var isWeekendDay = isWeekend(ymd);
				var headerClass = isWeekendDay ? 'text-center weekend-header' : 'text-center';
				var headerStyle = isWeekendDay ? 'background-color:red; color:#FFFFFF;' : '';

				(function(dayNum) {
					cols.push({
						data: 'd' + dayNum,
						title: '<span style="' + headerStyle + '">' + dayNum + '</span>',
						className: headerClass,
						width: '35px',
						orderable: false,
						render: function(data, type, row) {
							if (type === 'display') {
								var entryId = row['entry_id_' + dayNum];
								var pid = row['ihris_pid'];
								var dateStr = row['date_' + dayNum];
								var disabled = row['disabled_' + dayNum] === true || row['disabled_' + dayNum] === 'true';
								var disabledAttr = disabled ? 'disabled' : '';
								var inputClass = data ? 'update actual' : 'actual';
								
								return '<input type="text" style="padding:1px 2px; margin:0; text-align: center; width:100%; max-width:100%; box-sizing:border-box; font-size:13px; font-weight:bold; border:1px solid #ddd; text-transform:uppercase;" ' +
									'class="' + inputClass + '" ' +
									'id="' + entryId + '" ' +
									'day="' + dayNum + '" ' +
									'did="' + dateStr + '" ' +
									'pid="' + pid + '" ' +
									'pattern="[PROLXH]+" ' +
									'maxlength="1" ' +
									'title="P, O, R, L, X, H only" ' +
									'value="' + (data || '') + '" ' +
									disabledAttr + '>';
							}
							return data || '';
						}
					});
				})(d);
			}
			return cols;
		}

		function initActualsTable(month, year) {
			currentMonth = month;
			currentYear = year;
			tableColumns = buildColumns(month, year);
			
			var columnDefs = tableColumns.map(function(col, index) {
				return {
					targets: index,
					width: col.width || '35px',
					className: col.className || '',
					orderable: col.orderable !== undefined ? col.orderable : false
				};
			});
			
			actualsTable = $('#actuals_table').DataTable({
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
					url: baseUrl + 'rosta/actualsAjax',
					type: 'POST',
					data: function(d) {
						d.month = $('#actuals_month').val() || currentMonth;
						d.year = $('#actuals_year').val() || currentYear;
						d.empid = $('#actuals_empid').val() || '';
						d['<?php echo $this->security->get_csrf_token_name(); ?>'] = '<?php echo $this->security->get_csrf_hash(); ?>';
					},
					dataSrc: function(json) {
						return json.data;
					}
				},
				columns: tableColumns,
				initComplete: function() {
					var api = this.api();
					setTimeout(function() {
						forceColumnAlignment(api);
					}, 50);
				},
				drawCallback: function() {
					var api = this.api();
					setTimeout(function() {
						forceColumnAlignment(api);
					}, 10);
					attachActualHandlers();
				}
			});
		}

		// Initial table build
		initActualsTable(currentMonth, currentYear);

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
			var table = $('#actuals_table');
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

		function attachActualHandlers() {
			var $newInputs = $('.actual').not('.update');
			var $updateInputs = $('.update.actual');
			
			$newInputs.off('keyup').on('keyup', function(event) {
				var $input = $(this);
				if (event.keyCode == 13) {
					var textboxes = $("input.actual");
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
						var date = $input.attr('did');
						var day = $input.attr('day');
						
						letter = letter.replace(/\s/g, '');
						letter = letter.toUpperCase();
						
						// Validate letter (P, R, O, L, X, H only)
						if (letter !== "P" && letter !== "R" && letter !== "O" && letter !== "L" && letter !== "X" && letter !== "H") {
							showSyncNotification("Warning: Letter not recognised. Use P, R, O, L, X, or H only", "warn", 2000);
							$input.val('');
							return;
						}
						
						var color = pickColor(letter);
						$input.val(letter);
						var $self = $input;
						
						// Prepare data
						var postData = {
							hpid: hpid,
							date: date,
							duty: letter,
							color: color
						};
						
						// Try to save online first
						if (OfflineStorage.isOnline()) {
							$.post(baseUrl + 'rosta/saveActual', $.extend({}, postData, {
								'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
							}), function(result) {
								console.log(result);
								$self.removeClass('actual');
								$self.addClass('update actual');
								showSyncNotification("Data Saved", "success", 2000);
								actualsTable.ajax.reload(null, false);
							}).fail(function(xhr, status, error) {
								// Network error - save to local storage
								console.log('Network error, saving to local storage');
								var opId = OfflineStorage.saveOperation({
									type: 'save',
									data: postData
								});
								if (opId) {
									$self.removeClass('actual');
									$self.addClass('update actual');
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
								type: 'save',
								data: postData
							});
							if (opId) {
								$self.removeClass('actual');
								$self.addClass('update actual');
								$self.addClass('pending-sync');
								showSyncNotification("Saved offline - will sync when online", "info", 2000);
							} else {
								showSyncNotification("Failed to save - please try again", "error", 3000);
							}
						}
					}
				}
			});
			
			$updateInputs.off('keyup').on('keyup', function(event) {
				var $input = $(this);
		if (event.keyCode == 13) {
					var textboxes = $("input.actual");
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
						var date = $input.attr('did');
						
						letter = letter.replace(/\s/g, '');
						letter = letter.toUpperCase();
						
						// Validate letter (P, R, O, L, X, H only)
						if (letter !== "P" && letter !== "R" && letter !== "O" && letter !== "L" && letter !== "X" && letter !== "H") {
							showSyncNotification("Warning: Letter not recognised. Use P, R, O, L, X, or H only", "warn", 2000);
							$input.val('');
							return;
						}
						
						var color = pickColor(letter);
						$input.val(letter);
						
						// Prepare data
						var postData = {
							hpid: hpid,
							date: date,
							duty: letter,
							color: color
						};
						
						// Try to save online first
						if (OfflineStorage.isOnline()) {
							$.post(baseUrl + 'rosta/saveActual', $.extend({}, postData, {
								'<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
							}), function(result) {
							console.log(result);
								$input.removeClass('pending-sync');
								showSyncNotification("Data Saved", "success", 2000);
								actualsTable.ajax.reload(null, false);
							}).fail(function(xhr, status, error) {
								// Network error - save to local storage
								console.log('Network error, saving to local storage');
								var opId = OfflineStorage.saveOperation({
									type: 'save',
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
								type: 'save',
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
			});
		}

		function applyActualsFilter() {
			var selMonth = $('#actuals_month').val() || currentMonth;
			var selYear = $('#actuals_year').val() || currentYear;
			updateActualsTitle(selMonth, selYear);
			var monthOrYearChanged = (selMonth !== currentMonth || selYear !== currentYear);
			if (monthOrYearChanged) {
				currentMonth = selMonth;
				currentYear = selYear;
				if (actualsTable && $.fn.DataTable.isDataTable('#actuals_table')) {
					actualsTable.destroy();
					$('#actuals_table').empty();
				}
				initActualsTable(selMonth, selYear);
			} else {
				currentMonth = selMonth;
				currentYear = selYear;
				if (actualsTable) {
					actualsTable.ajax.reload();
				}
			}
		}

		$('#actuals_apply').on('click', function(e) {
			e.preventDefault();
			applyActualsFilter();
		});

		$('#actuals_month, #actuals_year').on('change', function() {
			applyActualsFilter();
		});

		$('#actuals_empid').on('change', function() {
			if (actualsTable) {
				actualsTable.ajax.reload();
			}
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
		if (duty == 'P') {
			var kala = '#4169E1';
		} else if (duty == 'O') {
			var kala = '#d1a110';
		} else if (duty == 'R') {
				var kala = '#008B8B';
		} else if (duty == 'L') {
				var kala = '#29910d';
		} else if (duty == 'X') {
			var kala = '#DC143C';
		} else if (duty == 'H') {
			var kala = '#C71585';
		}
		return kala;
	}
</script>
