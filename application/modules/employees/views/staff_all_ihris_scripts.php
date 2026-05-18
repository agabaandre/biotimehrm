<script>
(function() {
    function whenPluginsReady(cb) {
        if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.select2) {
            cb(jQuery);
            return;
        }
        setTimeout(function() { whenPluginsReady(cb); }, 50);
    }

    var baseUrl = '<?php echo base_url(); ?>';
    var filterReloadTimer = null;
    var suppressFilterReload = false;
    var filtersSelect2Ready = false;

    function ihrisSelect2Options() {
        return {
            theme: 'bootstrap4',
            width: '100%',
            minimumResultsForSearch: 6
        };
    }

    function initIhrisSelect2($el) {
        if (!$el.length) {
            return;
        }
        if ($el.data('select2')) {
            return;
        }
        try {
            $el.select2(ihrisSelect2Options());
        } catch (err) {
            console.error('Select2 init failed for', $el.attr('id'), err);
        }
    }

    function setFilterVal($el, val) {
        suppressFilterReload = true;
        $el.val(val || '');
        if ($el.data('select2')) {
            $el.trigger('change.select2');
        }
        suppressFilterReload = false;
    }

    function scheduleTableReload() {
        if (suppressFilterReload || !window.ihrisStaffTable) {
            return;
        }
        clearTimeout(filterReloadTimer);
        filterReloadTimer = setTimeout(function() {
            window.ihrisStaffTable.ajax.reload(null, false);
        }, 250);
    }

    function populateFacilityOptions(facilities) {
        var $facility = jQuery('#filterFacility');
        suppressFilterReload = true;
        $facility.empty().append('<option value="">All</option>');
        (facilities || []).forEach(function(o) {
            if (!o || !o.value) {
                return;
            }
            $facility.append(new Option(o.label || o.value, o.value, false, false));
        });
        setFilterVal($facility, '');
        suppressFilterReload = false;
    }

    function loadFacilitiesForDistrict(district, done) {
        var params = { filters: '1' };
        if (district) {
            params.district = district;
        }
        jQuery.getJSON(baseUrl + 'employees/all_ihris_staff', params)
            .done(function(data) {
                populateFacilityOptions(data && data.facilities ? data.facilities : []);
            })
            .fail(function() {
                populateFacilityOptions([]);
            })
            .always(function() {
                if (typeof done === 'function') {
                    done();
                }
            });
    }

    function initFilterSelect2($) {
        if (filtersSelect2Ready) {
            return;
        }
        filtersSelect2Ready = true;
        suppressFilterReload = true;

        initIhrisSelect2($('#filterDistrict'));
        initIhrisSelect2($('#filterFacility'));
        initIhrisSelect2($('#filterInstitutionType'));
        initIhrisSelect2($('#filterFacilityType'));
        // Job list is large — init last so the first table request is not blocked.
        window.setTimeout(function() {
            initIhrisSelect2($('#filterJob'));
            suppressFilterReload = false;
        }, 0);
    }

    function bindFilterHandlers($) {
        $('#filterForm').off('change.ihrisFilters', '.ihris-filter-s2').on('change.ihrisFilters', '.ihris-filter-s2', function() {
            if (suppressFilterReload) {
                return;
            }
            var id = this.id;
            if (id === 'filterDistrict') {
                loadFacilitiesForDistrict($(this).val() || '', function() {
                    scheduleTableReload();
                });
                return;
            }
            scheduleTableReload();
        });
    }

    whenPluginsReady(function($) {
        $(function() {
            var canMarkDisabled = <?php echo !empty($can_mark_disabled) ? 'true' : 'false'; ?>;
            var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
            var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

            var table = $('#staffTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                ajax: {
                    url: baseUrl + 'employees/all_ihris_staff',
                    type: 'POST',
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    data: function(d) {
                        d.globalSearch = $('#globalSearch').val();
                        d.includeInactive = $('#includeInactive').val() || 0;
                        d.district = $('#filterDistrict').val() || '';
                        d.facility = $('#filterFacility').val() || '';
                        d.job = $('#filterJob').val() || '';
                        d.institution_type = $('#filterInstitutionType').val() || '';
                        d.facility_type = $('#filterFacilityType').val() || '';
                        d[csrfName] = csrfHash;
                    },
                    error: function(xhr) {
                        console.error('Staff table load failed', xhr.status, (xhr.responseText || '').substring(0, 500));
                        var msg = 'Could not load staff data. Try refreshing the page.';
                        if (xhr.status === 403 || xhr.status === 404) {
                            msg = 'Session may have expired. Please refresh and log in again.';
                        }
                        $('#showingInfo').text(msg).addClass('text-danger');
                    }
                },
                columns: [
                    { data: 'serial', className: 'text-center' },
                    { data: 'ihris_pid', className: 'text-center' },
                    { data: 'nin', className: 'text-center' },
                    { data: 'fullname', render: function(data, type, row) {
                        if (type !== 'display') return data;
                        var pid = row && row.ihris_pid ? String(row.ihris_pid) : '';
                        var safe = (data || '').replace(/</g,'&lt;').replace(/"/g,'&quot;');
                        if (!pid) return safe;
                        var personId = pid.indexOf('person|') === 0 ? pid : 'person|' + pid;
                        return '<a href="' + baseUrl + 'employees/employeeTimeLogs/' + encodeURIComponent(personId) + '">' + safe + '</a>';
                    }},
                    { data: 'gender', className: 'text-center' },
                    { data: 'birth_date', className: 'text-center' },
                    { data: 'phone', className: 'text-center' },
                    { data: 'email' },
                    { data: 'district', defaultContent: '' },
                    { data: 'facility' },
                    { data: 'department' },
                    { data: 'job' },
                    { data: 'employment_terms', className: 'text-center' },
                    { data: 'card_number', className: 'text-center' },
                    { data: 'status_label', className: 'text-center', render: function(data, type, row) {
                        var label = data || (row.status === 0 ? 'Former Staff' : 'Active');
                        var badge = row.status === 0 ? 'badge-secondary' : 'badge-success';
                        return '<span class="badge ' + badge + '">' + String(label).replace(/</g,'&lt;') + '</span>';
                    }},
                    { data: null, className: 'text-center', orderable: false, responsivePriority: 1, render: function(data, type, row) {
                        var pid = row.ihris_pid || '';
                        var pidEnc = (pid.indexOf('person|') === 0) ? pid : ('person|' + pid);
                        var inchargeHtml = (row.is_incharge == 1)
                            ? '<span class="badge badge-success">Already Incharge</span>'
                            : '<button type="button" class="btn btn-xs btn-info assign-incharge" data-staff=\'' + JSON.stringify(row).replace(/'/g, '&#39;') + '\'><i class="fas fa-user-plus"></i> Assign</button>';
                        var statusHtml = '';
                        if (canMarkDisabled) {
                            if (row.status === 0) {
                                statusHtml = ' <button type="button" class="btn btn-xs btn-outline-success btn-mark-enabled" data-ihris-pid="' + String(pidEnc).replace(/"/g,'&quot;') + '"><i class="fas fa-user-check"></i> Active</button>';
                            } else {
                                statusHtml = ' <button type="button" class="btn btn-xs btn-outline-warning btn-mark-disabled" data-ihris-pid="' + String(pidEnc).replace(/"/g,'&quot;') + '"><i class="fas fa-user-minus"></i> Disable</button>';
                            }
                        }
                        return inchargeHtml + statusHtml;
                    }}
                ],
                order: [[3, 'asc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                responsive: true,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                initComplete: function() {
                    initFilterSelect2($);
                    bindFilterHandlers($);
                },
                drawCallback: function() {
                    var info = table.page.info();
                    var total = info.recordsDisplay !== undefined ? info.recordsDisplay : info.recordsTotal;
                    var start = total ? info.start + 1 : 0;
                    var end = total ? Math.min(info.start + info.length, total) : 0;
                    $('#showingInfo').removeClass('text-danger').text('Showing ' + start + '–' + end + ' of ' + total);
                },
                language: {
                    processing: 'Loading...',
                    search: 'Search:',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                    infoEmpty: 'No staff',
                    zeroRecords: 'No matching staff',
                    emptyTable: 'No staff found'
                }
            });

            window.ihrisStaffTable = table;

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $('#resetFilters').on('click', function() {
                suppressFilterReload = true;
                $('#globalSearch').val('');
                setFilterVal($('#filterDistrict'), '');
                setFilterVal($('#filterJob'), '');
                setFilterVal($('#filterInstitutionType'), '');
                setFilterVal($('#filterFacilityType'), '');
                populateFacilityOptions([]);
                suppressFilterReload = false;
                $('#includeInactive').val('0');
                $('#includeInactiveToggle').removeClass('btn-success').addClass('btn-secondary').attr('aria-checked', 'false');
                $('#includeInactiveToggle .toggle-off').removeClass('d-none');
                $('#includeInactiveToggle .toggle-on').addClass('d-none');
                table.ajax.reload();
            });

            function setIncludeInactiveValue(on) {
                $('#includeInactive').val(on ? '1' : '0');
                $('#includeInactiveToggle').attr('aria-checked', on ? 'true' : 'false').toggleClass('btn-secondary', !on).toggleClass('btn-success', on);
                $('#includeInactiveToggle .toggle-off').toggleClass('d-none', on);
                $('#includeInactiveToggle .toggle-on').toggleClass('d-none', !on);
            }
            $('#includeInactiveToggle').on('click', function() {
                setIncludeInactiveValue($('#includeInactive').val() !== '1');
                table.ajax.reload();
            });

            $(document).on('click', '.assign-incharge', function() {
                var staffJson = $(this).attr('data-staff');
                try {
                    var staffData = typeof staffJson === 'object' ? staffJson : JSON.parse(staffJson);
                    $('.staff-name').text(staffData.fullname || '');
                    $('.staff-job').text(staffData.job || '');
                    $('.staff-facility').text(staffData.facility || '');
                    $('input[name="name"]').val(staffData.fullname || '');
                    $('input[name="username"]').val(staffData.ihris_pid || '');
                    $('input[name="email"]').val(staffData.email || '');
                    $('input[name="ihris_pid"]').val(staffData.ihris_pid || '');
                    $('input[name="district_id"]').val(staffData.district_id || '');
                    $('input[name="facility_id[]"]').val((staffData.facility_id || '') + '_' + (staffData.facility || ''));
                    $('input[name="department_id"]').val(staffData.department_id || '');
                    $('input[name="password"]').val('<?php echo Modules::run("svariables/getSettings")->default_password; ?>');
                    $('#inchargeModal').modal('show');
                } catch (e) {}
            });

            $('#inchargeForm').on('submit', function(e) {
                e.preventDefault();
                var btn = $('#inchargeForm button[type="submit"]');
                var orig = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                $.ajax({
                    url: baseUrl + 'auth/addUser',
                    method: 'POST',
                    data: $('#inchargeForm').serialize() + '&' + csrfName + '=' + encodeURIComponent(csrfHash),
                    success: function() {
                        $.notify('Incharge assigned.', 'success');
                        $('#inchargeModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function() { $.notify('Error assigning incharge.', 'error'); },
                    complete: function() { btn.prop('disabled', false).html(orig); }
                });
            });

            function parseJsonRes(res) { if (typeof res === 'string') { try { return JSON.parse(res); } catch(e) { return {}; } } return res || {}; }
            $('#staffTable').on('click', '.btn-mark-disabled', function() {
                var btn = $(this), pid = btn.data('ihris-pid');
                if (!pid) return;
                btn.prop('disabled', true);
                $.post(baseUrl + 'employees/setStaffDisabled', { ihris_pid: pid, [csrfName]: csrfHash }).done(function(res) {
                    var d = parseJsonRes(res);
                    if (d.success) {
                        var row = btn.closest('tr'), rowData = table.row(row).data();
                        if (rowData) { rowData.status = 0; rowData.status_label = 'Former Staff'; table.row(row).data(rowData).draw(false); }
                        $.notify(d.message || 'Marked as Former Staff.', 'success');
                    } else {
                        $.notify(d.message || 'Failed', 'error');
                    }
                }).fail(function() { $.notify('Request failed', 'error'); }).always(function() { btn.prop('disabled', false); });
            });
            $('#staffTable').on('click', '.btn-mark-enabled', function() {
                var btn = $(this), pid = btn.data('ihris-pid');
                if (!pid) return;
                btn.prop('disabled', true);
                $.post(baseUrl + 'employees/setStaffEnabled', { ihris_pid: pid, [csrfName]: csrfHash }).done(function(res) {
                    var d = parseJsonRes(res);
                    if (d.success) {
                        var row = btn.closest('tr'), rowData = table.row(row).data();
                        if (rowData) { rowData.status = 1; rowData.status_label = 'Active'; table.row(row).data(rowData).draw(false); }
                        $.notify(d.message || 'Marked as Active.', 'success');
                    } else {
                        $.notify(d.message || 'Failed', 'error');
                    }
                }).fail(function() { $.notify('Request failed', 'error'); }).always(function() { btn.prop('disabled', false); });
            });
        });
    });
})();
</script>
