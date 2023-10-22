<div class="table-responsive" id="staff-container">
	<!-- Table content will be dynamically loaded here -->
</div>
<script>
	
		function fetchDataAndUpdate() {
			$.ajax({
				url: '<?php echo base_url() ?>dashboard/get_dashboard',
				type: 'GET',
				dataType: 'json', // Expecting JSON response
				success: function(response) {
					if (response.html) {
						// Use html() to replace the content
						$('#staff-container').html(response.html);
					} else {
						console.error('No HTML content found in the response.');
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX Error:', status, error);
				}
			});
		}

		// Fetch data immediately when the page loads
		fetchDataAndUpdate();

		// Update data every 30 minutes (30 * 60 * 1000 milliseconds)
		setInterval(fetchDataAndUpdate, 30 * 60 * 1000);

</script>
