</div>
<!-- /.content-wrapper -->
<footer class="main-footer text-muted" style=" text-align:center;">
    <div class="col-lg-12">
        <div class="footer-copy-right">
            <!-- <!-- <img src="https://upload.wikimedia.org/wikipedia/commons/1/17/USAID-Identity.svg" style="width:180px; height:50px;"> -->
                          <a href="http://health.go.ug" target="_blank"> <img src="https://upload.wikimedia.org/wikipedia/commons/7/7c/Coat_of_arms_of_Uganda.svg" style="width:80px; height:50px;"> </a>
            <p>&copy; <?php echo date('Y'); ?>, Ministry of Health -Uganda. <strong>All Rights Reserved</strong></p>
        </div>
    </div>
</footer>
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
<!-- jQuery -->
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- Bootstrap 4 -->


<script src="<?php echo base_url() ?>assets/plugins/summernote/summernote-bs4.min.js"></script>
<!-- AdminLTE App -->
<!-- Select2 -->
<script src="<?php echo base_url() ?>assets/plugins/select2/js/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>assets/dist/js/adminlte.min.js"></script>
<!-- <script src="<?php echo base_url(); ?>assets/dist/js/dashboard.js"></script> -->
<!-- AdminLTE for demo purposes -->
<!-- <script src="<?php echo base_url(); ?>assets/dist/js/demo.js"></script> -->

<script src="<?php echo base_url(); ?>assets/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?php echo base_url(); ?>assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- date-range-picker -->
<script src="<?php echo base_url() ?>assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="<?php echo base_url() ?>assets/plugins/summernote/summernote-bs4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/select2/js/select2.full.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/notify.min.js"></script>
<!-- fullCalendar 2.2.5 -->
<script src="<?php echo base_url() ?>assets/plugins/moment/moment.min.js"></script>
<script src="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.min.js"></script>
<!-- counterup JS
		============================================ -->
<!-- DataTables  & Plugins -->
<script src="<?php echo base_url(); ?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/jszip/jszip.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
<script src="<?php echo base_url(); ?>assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<div class="control-sidebar-bg"></div>
</div>

<script>
    $(document).ready(function() {
        $.fn.datepicker.defaults.format = "yyyy-mm-dd";
        $('.datepicker').datepicker({
            todayHighlight: true,
            autoclose: true,
        });
    });
</script>

<!-- Session Keep Alive Script -->
<?php if ($this->session->userdata('isLoggedIn')): ?>
<script>
(function() {
    'use strict';
    
    // Session keep-alive configuration
    const SESSION_CHECK_INTERVAL = 10 * 60 * 1000; // Check every 10 minutes
    const SESSION_EXTEND_INTERVAL = 45 * 60 * 1000; // Extend every 45 minutes
    const SESSION_DURATION = 6 * 60 * 60 * 1000; // 6 hours total
    const WARNING_TIME = 10 * 60 * 1000; // Show warning 10 minutes before expiry
    
    let lastActivity = Date.now();
    let sessionCheckTimer = null;
    let sessionExtendTimer = null;
    let warningTimer = null;
    let logoutTimer = null;
    let warningShown = false;
    
    // Track user activity
    function updateActivity() {
        lastActivity = Date.now();
        resetTimers();
        warningShown = false; // Reset warning flag on activity
    }
    
    // Reset all timers
    function resetTimers() {
        if (warningTimer) clearTimeout(warningTimer);
        if (logoutTimer) clearTimeout(logoutTimer);
        
        // Set warning 10 minutes before session expires
        warningTimer = setTimeout(showSessionWarning, SESSION_DURATION - WARNING_TIME);
        
        // Set logout when session expires
        logoutTimer = setTimeout(logoutUser, SESSION_DURATION);
    }
    
    // Check session status
    function checkSession() {
        fetch('<?php echo base_url("auth/checkSession"); ?>', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'expired') {
                // Session expired, redirect to login
                console.log('Session expired, redirecting to login');
                window.location.href = '<?php echo base_url("auth"); ?>';
            } else if (data.status === 'active') {
                console.log('Session is active, expires in', data.expires_in, 'seconds');
            }
        })
        .catch(error => {
            console.error('Session check failed:', error);
            // Don't redirect on network errors, just log them
        });
    }
    
    // Extend session
    function extendSession() {
        fetch('<?php echo base_url("auth/extendSession"); ?>', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                console.log('Session extended successfully');
                resetTimers();
                warningShown = false; // Reset warning flag
            }
        })
        .catch(error => {
            console.error('Session extension failed:', error);
            // Don't redirect on network errors, just log them
        });
    }
    
    // Show session warning modal
    function showSessionWarning() {
        if (warningShown) return; // Prevent multiple warnings
        
        warningShown = true;
        
        // Create a modal-style warning
        const warningModal = document.createElement('div');
        warningModal.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; padding: 30px; border-radius: 8px; max-width: 500px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                    <div style="color: #f39c12; font-size: 48px; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 style="color: #333; margin-bottom: 15px;">Session Timeout Warning</h3>
                    <p style="color: #666; margin-bottom: 25px; line-height: 1.5;">
                        Your session will expire in <strong>10 minutes</strong> due to inactivity.<br>
                        Do you want to continue working?
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center;">
                        <button id="extendSessionBtn" style="background: #28a745; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 16px;">
                            <i class="fas fa-check"></i> Continue Working
                        </button>
                        <button id="logoutNowBtn" style="background: #dc3545; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; font-size: 16px;">
                            <i class="fas fa-sign-out-alt"></i> Logout Now
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(warningModal);
        
        // Add event listeners
        document.getElementById('extendSessionBtn').addEventListener('click', () => {
            warningModal.remove();
            extendSession();
        });
        
        document.getElementById('logoutNowBtn').addEventListener('click', () => {
            warningModal.remove();
            logoutUser();
        });
        
        // Auto-logout after 2 minutes if user doesn't respond
        setTimeout(() => {
            if (warningModal.parentNode) {
                warningModal.remove();
                logoutUser();
            }
        }, 2 * 60 * 1000);
    }
    
    // Logout user
    function logoutUser() {
        window.location.href = '<?php echo base_url("auth/logout"); ?>';
    }
    
    // Initialize session keep-alive
    function initSessionKeepAlive() {
        // Set up activity tracking
        const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        activityEvents.forEach(event => {
            document.addEventListener(event, updateActivity, true);
        });
        
        // Set up timers
        sessionCheckTimer = setInterval(checkSession, SESSION_CHECK_INTERVAL);
        sessionExtendTimer = setInterval(extendSession, SESSION_EXTEND_INTERVAL);
        
        // Start session timers
        resetTimers();
        
        // Check session immediately
        checkSession();
        
        console.log('Session keep-alive initialized with 6-hour duration');
        console.log('Session check interval:', SESSION_CHECK_INTERVAL / 1000, 'seconds');
        console.log('Session extend interval:', SESSION_EXTEND_INTERVAL / 1000, 'seconds');
        console.log('Session duration:', SESSION_DURATION / 1000 / 60, 'minutes');
        console.log('Warning time:', WARNING_TIME / 1000 / 60, 'minutes before expiry');
    }
    
    // Clean up timers
    function cleanup() {
        if (sessionCheckTimer) {
            clearInterval(sessionCheckTimer);
        }
        if (sessionExtendTimer) {
            clearInterval(sessionExtendTimer);
        }
        if (warningTimer) {
            clearTimeout(warningTimer);
        }
        if (logoutTimer) {
            clearTimeout(logoutTimer);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSessionKeepAlive);
    } else {
        initSessionKeepAlive();
    }
    
    // Clean up on page unload
    window.addEventListener('beforeunload', cleanup);
    
    // Handle visibility change (tab switching)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, pause timers
            cleanup();
        } else {
            // Page is visible, resume timers
            updateActivity();
            initSessionKeepAlive();
        }
    });
    
    // Expose functions for manual control
    window.SessionKeepAlive = {
        checkSession: checkSession,
        extendSession: extendSession,
        updateActivity: updateActivity,
        cleanup: cleanup
    };
    
})();
</script>
<?php endif; ?>
<script>
    // Radialize the colors
    $(document).ready(function() {
        Highcharts.setOptions({
            colors: Highcharts.getOptions().colors.map(function(color) {
                return {
                    radialGradient: {
                        cx: 0.5,
                        cy: 0.3,
                        r: 0.7
                    },
                    stops: [
                        [0, color],
                        [1, Highcharts.color(color).brighten(-0.3).get('rgb')] // darken
                    ]
                };
            })
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('.mytable').DataTable({
            dom: 'Bfrtip',
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
            lengthMenu: [
                [25, 50, 100, 150, -1],
                ['25', '50', '100', '150', '200', 'Show all']
            ],
            buttons: [
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'pageLength',
            ]
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#timelogs').DataTable({
            "paging": false,
            "lengthChange": true,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        // $.notify("Hello","success");
        var isPassChanged = <?php echo $pass_changed = $this->session->userdata('changed'); ?>;

        if (isPassChanged != 1) {
            console.log(isPassChanged);
            // $('#changepassword').modal('show');
        }
        var url = "<?php echo $this->uri->segment(2); ?>";
        if (url == "tabular" || url == "actuals" || url == "fetch_report" || url == "actualsreport" || url == "tabular#" || url == "timesheet" || url == "attfrom_report") {
            $('body').addClass('sidebar-collapse');
            $('#sidebar').toggleClass('active');
        };
    });
</script>
<!-- ./wrapper -->
<?php
$uri = $_SERVER['REQUEST_URI'];
$uri; // Outputs: URI
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$linkquery = $url; // Outputs: Full URL
// Outputs: Query String
?>
<!-- Modal -->
<div class="modal fade" id="switch" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Switch Facility</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form form-horizontal" action="<?php echo base_url(); ?>departments/switchDepartment" method="post">
                    <div class="row">
                        <div class="col-md-12">
                            <label>District</label>
                            <select class="sdistrict form-control select2dist" id="district" name="district" onChange="getFacs($(this).val());" <?php if (!(in_array('34', $permissions))) {
                                                                                                                                                    echo "disabled";
                                                                                                                                                } ?>>
                                <option value="<?php echo urlencode(str_replace(" ", "", ($_SESSION['district_id']))) . "_" . urlencode(str_replace(" ", "", $_SESSION['district'])); ?>"><?php echo $_SESSION['district']; ?></option>
                                <?php
                                $districts = Modules::run("lists/switch_districts");
                                foreach ($districts as $district) {
                                ?>
                                    <option value="<?php echo urlencode(str_replace(" ", "", $district->district_id)) . "_" . urlencode(str_replace(" ", "", $district->district)); ?>"><?php echo ucwords($district->district); ?></option>
                                <?php }   ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Facility</label>
                                <select name="facility" onChange="getDeps($(this).val());" class="sfacility form-control select2dist" required>
                                    <option value="" disabled>All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Department</label>
                                <select name="department" onChange="getDivisions($(this).val());" class="sdepartment form-control select2dist">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="direct" value="<?php echo $linkquery; ?>">
                        <div class="col-md-12" style="display:none;">
                            <div class="form-group">
                                <label>Division</label>
                                <select id="division" class="sdivison form-control select2dist" onChange="getUnits($(this).val());" name="division">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="display:none;">
                        <div class="col-md-6">
                            <!-- < needs fixing> -->
                            <div class="form-group">
                                <label>Section</label>
                                <select id="section" class="ssection form-control select2dist" onChange="getUnits($(this).val());" name="section">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unit</label>
                                <select id="unit" name="unit" onchange="this.form.submit()" class="sunit form-control select2dist">
                                    <option value="">All</option>
                                </select>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info"><i class="fa fa-paper-plane" aria-hidden="true">Switch</i></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times">Close</i></button>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal -->
<div id="changepassword" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Change Password</h4>
                <br />

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="changed" style="color:#005662;"></p>
                <form method="post" id="change_pass">

                    <div class="form-group">
                        <label>Old Password</label>
                        <input type="password" class="form-control" name="oldpass" id="old">
                    </div>


                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" class="form-control" name="newpass" id="new" onkeyup="checker();" required>
                        <p class="help-block error"></p>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="hidden" value='1' name="changed">
                        <input type="hidden" value='<?php echo $this->session->userdata('user_id'); ?>' name="user_id">
                        <input type="password" class="form-control" name="confirm" id="confirm" onkeyup="checker();" required>
                        <p class="help-block error"></p>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-info">Change Password</button>
            </div>
            </form>
        </div>
    </div>
</div>




</body>

</html>


<script>
    $(function() {
        $('.select2').select2()
        $('.select2dist').select2({
            dropdownParent: "#switch"
        });
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    });
</script>
<script>
    $("document").ready(function() {
        $(".sdistrict").change();
        //$(".sfacility").change();
        // console.log(time);
    });

    function getFacs(val) {
        $.ajax({
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_facilities",
            data: 'dist_data=' + val,
            success: function(data) {
                //alert(data);
                $(".sfacility").html(data);
            }
        });
    }

    function getuserDeps(val) {
        $.ajax({
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_departments",
            data: 'fac_data=' + val,
            success: function(data) {
                //alert(data);
                $(".userdepartment").html(data);
            }
            //  console.log('iwioowiiwoow');
        });
    }

    function getDivisions(val) {
        $.ajax({
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_divisions",
            data: 'depart_data=' + val,
            success: function(data) {
                // alert(data);
                $(".sdivision").html(data);
            }
        });
    }

    function getUnits(val) {
        $.ajax({
            method: "GET",
            url: "<?php echo base_url(); ?>departments/get_units",
            data: 'division=' + val,
            success: function(data) {
                //alert(data);
                $(".sunit").html(data);
            }
        });
    }

    //change Password
    function checker() {
        $first = $('#new').val();
        $confirm = $('#confirm').val();
        if (($first !== $confirm) && $first !== "") {
            $('.error').html('<font color="red">Passwords Do not Match</font>');
        } else {
            $('.error').html('<font color="green">Passwords Match</font>');
        }
    } //checker
    $('#change_pass').submit(function(e) {
        e.preventDefault();
        var data = $(this).serialize();
        var url = '<?php echo base_url(); ?>auth/changePass'
        console.log(data);
        $.ajax({
            url: url,
            method: "post",
            data: data,
            success: function(res) {
                if (res == "OK") {
                    $('.changed').html("<center><font color='green'>Password change effective</font></center>");
                } else {
                    $('.changed').html("<center>" + res + "</center>");
                }
                console.log(res);
            } //success
        }); // ajax
    }); //form submit
</script>


<script>
    // Function to handle form submission
    function submitForm() {
        // Get the form data
        var formData = $("#biotimesync").serialize();

        // Make the AJAX request
        $.ajax({
            type: "POST",
            url: "<?php echo base_url('biotimejobs/custom_logs'); ?>",
            data: formData,
            success: function(response) {
                // Handle the response if needed
                console.log("Form submitted successfully.");
                console.log(response);
            },
            error: function(xhr, status, error) {
                // Handle the error if needed
                console.error("Error submitting form:", error);
            }
        });

        // Close the modal after form submission
        $("#syncModal").modal("hide");
    }

    // Trigger the form submission when the "Sync" button is clicked
    $(document).on("submit", "#biotimesync", function(event) {
        event.preventDefault(); // Prevent the default form submission behavior
        submitForm();
    });
</script>