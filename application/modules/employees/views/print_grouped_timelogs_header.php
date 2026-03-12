<style>
  body { font-family: Arial; font-size: 11pt; }
  table.gtl-print { border-collapse: collapse; width: 100%; font-size: 10px; }
  table.gtl-print th, table.gtl-print td { border: 0.2mm solid #000; padding: 5px; vertical-align: top; }
  table.gtl-print th { background: #EEEEEE; text-align: center; }
  table.gtl-print tr:nth-child(odd) { background: #e1f4f7; }
</style>
<table class="gtl-print" style="font-size: 12pt; border-collapse: collapse;" cellpadding="8" width="100%">
  <tr>
    <td colspan="2"><?php if (!empty($moh_logo_path) && is_file($moh_logo_path)) { ?><img src="<?php echo $moh_logo_path; ?>" width="100px"><?php } else { ?><img src="<?php echo base_url(); ?>assets/img/MOH.png" width="100px"><?php } ?></td>
    <td colspan="5">
      <h4>MONTHLY TIME LOG REPORT — <?php echo htmlspecialchars($facility_name); ?> — <?php echo htmlspecialchars($date_from); ?> to <?php echo htmlspecialchars($date_to); ?></h4>
    </td>
  </tr>
  <tr>
    <th>#</th>
    <th>Name</th>
    <th>Position</th>
    <th>Facility</th>
    <th>Department</th>
    <th>Date</th>
    <th>Hours Worked</th>
  </tr>
  <tbody>
