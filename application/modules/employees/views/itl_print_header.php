<style>
  table.minimalistBlack { border: 3px solid #000; width: 100%; text-align: left; border-collapse: collapse; }
  table.minimalistBlack td, table.minimalistBlack th { border: 1px solid #000; padding: 5px 4px; font-size: 12px; }
  table.minimalistBlack thead { background: #CFCFCF; border-bottom: 2px solid #000; }
  table.minimalistBlack thead th { font-weight: bold; }
  table.minimalistBlack tfoot { font-weight: bold; border-top: 3px solid #000; }
</style>
<div class="dashtwo-order-area" style="padding-top: 10px;">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title" style="text-align:center;"><?php echo isset($employee) && $employee ? strtoupper($employee->facility) . ' ' : ''; ?>PERSON ATTENDANCE REPORT</h3>
          </div>
          <div class="panel-body">
            <div class="col-md-12" style="margin-bottom:10px;">
              <table class="minimalistBlack">
                <tbody>
                  <tr><td>EMPLOYEE NAME</td><td><?php echo isset($employee) && $employee ? htmlspecialchars(trim($employee->surname . ' ' . $employee->firstname)) : ''; ?></td></tr>
                  <tr><td>DESIGNATION</td><td><?php echo isset($employee) && $employee ? htmlspecialchars($employee->job) : ''; ?></td></tr>
                  <tr><td>FACILITY</td><td><?php echo isset($employee) && $employee ? htmlspecialchars($employee->facility) : ''; ?></td></tr>
                  <tr><td>DEPARTMENT</td><td><?php echo isset($employee) && $employee ? htmlspecialchars(isset($employee->department) ? $employee->department : '') : ''; ?></td></tr>
                  <tr><td>PERIOD</td><td><strong>From: <?php echo isset($from) ? date('j F, Y', strtotime($from)) : ''; ?> To: <?php echo isset($to) ? date('j F, Y', strtotime($to)) : ''; ?></strong></td></tr>
                </tbody>
              </table>
            </div>
            <table class="minimalistBlack" id="timelogs">
              <thead>
                <tr>
                  <th>#</th>
                  <th>DATE</th>
                  <th>TIME IN</th>
                  <th>TIME OUT</th>
                  <th width="30%"><?php echo (isset($summary_label) && $summary_label === 'HOURS') ? 'HOURS WORKED' : 'SUMMARY'; ?></th>
                </tr>
              </thead>
              <tbody>
