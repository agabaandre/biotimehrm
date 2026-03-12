<?php
$year = isset($year) ? $year : '';
$base = base_url();
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
$facility_name = isset($_SESSION['facility_name']) ? $_SESSION['facility_name'] : '';
?>
<style>
	#averageHoursTable th.ah-num-col,
	#averageHoursTable td.ah-num-col { width: 120px; text-align: center; }
	#averageHoursTable thead th { padding: 8px 6px; }
</style>
<section class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="callout callout-success">
					<form id="averageHoursFiltersForm" class="form-horizontal" style="padding-bottom: 2em;" action="<?php echo $base; ?>reports/average_hours" method="get">
						<input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>">
						<div class="row align-items-end">
							<div class="form-group col-md-4 mb-2 mb-md-0">
								<label class="mb-1">Year</label>
								<select class="form-control select2" name="year" id="ah_year">
									<option value="">All years</option>
									<?php
									$cy = (int) date('Y');
									for ($i = -5; $i <= 25; $i++) {
										$y = 2017 + $i;
										$sel = ($year !== '' && (string) $y === (string) $year) ? ' selected' : '';
										echo '<option value="' . $y . '"' . $sel . '>' . $y . '</option>';
									}
									?>
								</select>
							</div>
							<div class="form-group col-md-4 mb-2 mb-md-0">
								<button type="button" id="ah_apply" class="btn bg-gray-dark color-pale"><i class="fa fa-filter"></i> Apply</button>
								<?php
								$print_url = $base . 'reports/print_average?' . ($year !== '' ? 'year=' . rawurlencode($year) : '');
								?>
								<a href="<?php echo htmlspecialchars($print_url); ?>" id="ah_print_link" class="btn bg-gray-dark color-pale" target="_blank" rel="noopener" style="margin-left: 8px;"><i class="fa fa-print"></i> Print</a>
							</div>
						</div>
					</form>
				</div>
				<div class="panel-body">
					<p style="font-size: 16px; font-weight: bold; margin: 0 auto;">
						MONTHLY STAFF AVERAGE WORKING HOURS — <?php echo htmlspecialchars($facility_name); ?>
					</p>
					<div class="table-responsive" style="margin-top: 10px;">
						<table id="averageHoursTable" class="table table-striped table-bordered" style="width: 100%;">
							<thead>
								<tr>
									<th>#</th>
									<th>Month and Year</th>
									<th class="ah-num-col">Average Hours</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script type="text/javascript">
(function() {
	var baseUrl = '<?php echo addslashes($base); ?>';
	var csrfTokenName = '<?php echo addslashes($csrf_name); ?>';
	var csrfTokenHash = '<?php echo addslashes($csrf_hash); ?>';
	var defaultYear = '<?php echo addslashes($year); ?>';

	function getYear() {
		var y = $('#ah_year').val();
		return y !== null && y !== undefined ? y : defaultYear;
	}

	function updatePrintLink() {
		var y = getYear();
		var qs = y ? '?year=' + encodeURIComponent(y) : '';
		$('#ah_print_link').attr('href', baseUrl + 'reports/print_average' + qs);
	}

	$(document).ready(function() {
		if (typeof $.fn.DataTable !== 'function') {
			console.error('DataTables not loaded.');
			return;
		}
		var table = $('#averageHoursTable').DataTable({
			processing: true,
			serverSide: true,
			searching: true,
			ordering: true,
			order: [[1, 'desc']],
			pageLength: 20,
			lengthMenu: [[10, 20, 50, 100, 200], [10, 20, 50, 100, 200]],
			autoWidth: false,
			ajax: {
				url: baseUrl + 'reports/average_hours_ajax',
				type: 'POST',
				data: function(d) {
					d[csrfTokenName] = csrfTokenHash;
					d.year = getYear();
				}
			},
			columns: [
				{ data: 0, orderable: false, width: '50px' },
				{ data: 1 },
				{ data: 2, className: 'ah-num-col', width: '120px' }
			],
			dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
				 '<"row"<"col-sm-12"tr>>' +
				 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
			language: { processing: '<i class="fa fa-spinner fa-spin"></i> Loading...' }
		});

		$('#averageHoursFiltersForm').on('submit', function(e) {
			e.preventDefault();
			return false;
		});

		$('#ah_apply').on('click', function() {
			updatePrintLink();
			table.ajax.reload();
		});

		$('#ah_print_link').on('click', function(e) {
			updatePrintLink();
			var href = $(this).attr('href');
			if (href && href !== '#') {
				e.preventDefault();
				window.open(href, '_blank', 'noopener');
			}
		});

		$('#ah_year').on('change', function() {
			updatePrintLink();
		});

		updatePrintLink();
	});
})();
</script>
