
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $setting->title ?> - <?php echo (!empty($title) ? $title : null) ?></title>
  <!-- Favicon and touch icons -->
  <link rel="shortcut icon" href="<?php echo base_url(!empty($settings->favicon) ? $settings->favicon : "assets/images/MOH.png"); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="HRM iHRIS Employee Attendance Tracking System">
  <meta name="keywords" content="Ministry of Health, Health Attendance Uganda, Ministry of Health Uganda Attendance Tracking, Uganda Attenance, Agaba Andrew Attendance, Biometric Attendance, HRM Attend, iHRIS Attendance, Ismail Wadembere iHRIS, Agaba Andrew iHRIS, Patrick Lubwama iHRIS, iHRIS Uganda, iHRIS, IntraHealth iHRIS, Health Attendance, Attendance Tracking System Uganda">
  <meta name="author" content="Agaba Andrew +256702787688">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo base_url() ?>assets/css/responsive2.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/fontawesome-free/css/all.min.css">
  <!-- fullCalendar -->
  <link rel="stylesheet" href="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>assets/bower_components/fullcalendar/dist/fullcalendar.print.min.css" media="print">
  <link rel="stylesheet" href="<?php echo base_url() ?>assets/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>assets/bootstrap-datepicker/css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="<?php echo base_url() ?>node_modules/ionicons-npm/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/dist/css/adminlte.min.css">
  <script src="<?php echo base_url() ?>node_modules/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo base_url() ?>node_modules/highcharts/highcharts.js"></script>
  <script>
    /**
     * Highcharts loader (hardened)
     * Fixes intermittent "Highcharts module loaded before core" crashes by:
     * - waiting for a compatible core (v10+ exposes Highcharts._modules)
     * - loading modules sequentially and only once (idempotent)
     */
    (function loadHighchartsModules() {
      window.__hcLoaderState = window.__hcLoaderState || {
        started: false,
        loaded: false,
        tries: 0
      };

      if (window.__hcLoaderState.loaded) {
        return;
      }

      // Core must exist and be compatible with the module bundle we ship (v10+)
      var hasCore = (typeof window.Highcharts !== 'undefined' &&
        typeof window.Highcharts.setOptions === 'function' &&
        typeof window.Highcharts._modules === 'object');

      if (!hasCore) {
        window.__hcLoaderState.tries++;
        // Give up after ~10s to avoid infinite loops; pages should still work without charts
        if (window.__hcLoaderState.tries > 200) {
          console.warn('Highcharts core not ready/compatible; skipping module loading.');
          return;
        }
        setTimeout(loadHighchartsModules, 50);
        return;
      }

      if (window.__hcLoaderState.started) {
        return;
      }
      window.__hcLoaderState.started = true;

      var moduleScripts = [
        '<?php echo base_url() ?>node_modules/highcharts/highcharts-more.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/solid-gauge.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/exporting.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/export-data.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/accessibility.js'
      ];

      function loadScript(src) {
        return new Promise(function(resolve) {
          // Prevent double-inserting the same script
          if (document.querySelector('script[data-hc-module="' + src + '"]')) {
            resolve();
            return;
          }

          var script = document.createElement('script');
          script.src = src;
          script.async = false;
          script.setAttribute('data-hc-module', src);
          script.onload = function() { resolve(); };
          script.onerror = function() {
            console.error('Failed to load Highcharts module:', src);
            resolve(); // continue so one bad module doesn't block the rest
          };
          document.head.appendChild(script);
        });
      }

      // Load sequentially
      moduleScripts.reduce(function(p, src) {
        return p.then(function() { return loadScript(src); });
      }, Promise.resolve()).then(function() {
        window.__hcLoaderState.loaded = true;
      });
    })();
  </script>
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- Theme style -->
  <link href="<?php echo base_url(); ?>assets/css/fullcalendar.css" rel="stylesheet">
  <script>
    (function() {
      function hidePreloader() {
        try {
          // Only hide if present
          var $status = $('#status');
          var $preloader = $('#preloader');
          if ($status.length) $status.stop(true, true).fadeOut(300);
          if ($preloader.length) $preloader.stop(true, true).fadeOut(400);

          // Clean up rare leftover backdrops when no modal is open
          if (!$('.modal.show').length) {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
          }
        } catch (e) {
          // ignore
        }
      }

      // Run on DOM ready, window load, and as a hard timeout fallback.
      $(function() { setTimeout(hidePreloader, 500); });
      $(window).on('load', function() { setTimeout(hidePreloader, 500); });
      setTimeout(hidePreloader, 8000);
    })();
  </script>
  <style>
    @media (max-width: 767px) {
      .hidden-mobile {
        display: none;
      }
    }

    body {
      font-family: 'Arial', 'Cambria', 'Helvetica Neue', 'Source Sans Pro', 'Helvetica', 'sans-serif';
      overflow-x: hidden;
      overflow-y: auto;
    }

    .callout.callout-success {
      border-left-color: #207597 !important;
    }

    page-item.active .page-link {
      z-index: 3;
      color: #fff;
      background-color: #007bff;
      border-color: #007bff;
    }

    .select2-close-mask {
      z-index: 2099;
    }

    .select2-dropdown {
      z-index: 3051;
    }

    .dash-icon {
      color: #37989d;
      font-size: 15px;
      margin-right: 4px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
      background-color: #37989d;
      border-color: #37989d;
      color: #fff;
      padding: 0 10px;
      margin-top: 0.31rem;
    }

    .fa-circle {
      color: #37989d;
      font-size: 6px !important;
    }

    .nav-drop {
      font-size: 10px;
      font-weight: 560;
      text-overflow: ellipsis;
      overflow: hidden;
    }

    .nav-item {
      font-weight: 570;
    }

    body::-webkit-scrollbar-track {
      -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
      box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
      background-color: #F5F5F5;
    }

    body::-webkit-scrollbar {
      width: 0.5em;
      background-color: #F5F5F5;
    }

    body::-webkit-scrollbar-thumb {
      background-color: #d2d4dc;
      height: 70%;
      border: 1px solid #555555;
      border-radius: 4px;
      scrollbar-width: thin;
    }

    .sido {
      clear: both;
      overflow: auto;
      background: #222d32;
    }

    .sido::-webkit-scrollbar-track {
      -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3) !important;
      box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
      background-color: #F5F5F5;
      scrollbar-width: thin;
    }

    .btn {
      font-size: 12px !important;
    }

    .buttons-html5 {
      font-size: 12px !important;
      background: #343a40 !important;
      margin: 6px;
      border-radius: 4px;
    }

    .buttons-page-length {
      font-size: 12px !important;
      background: #343a40 !important;
      margin: 6px;
      border-radius: 4px;
    }

    .page-item.active .page-link {
      z-index: 3;
      color: #fff;
      background-color: #17a2b8;
      border-color: #17a2b8;
    }

    .sido::-webkit-scrollbar {
      width: 0.5em;
      background-color: #F5F5F5 !important;
      scrollbar-width: thin;
    }

    .sido::-webkit-scrollbar-thumb {
      background-color: #d2d4dc;
      border: 1px solid #555555 !important;
      border-radius: 4px;
    }

    .btnkey {
      min-width: 98px;
      ;
      color: #fff !important;
      margin: 2px;
      border-radius: 2px;
    }

    .rbtnkey {
      min-width: 148px;
      ;
      color: #fff !important;
      margin: 2px;
      border-radius: 2px;
    }

    .fc-content {
      color: #fff !important;
      font-size: 13px;

    }

    .fc-center {
      font-size: 14px !important;
      ;
    }

    .highcharts-title {
      font-size: 15px !important;
    }

    #preloader {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #E4E5E5;
      z-index: 200;
    }

    #status {
      width: 80px;
      height: 80px;
      position: absolute;
      left: 60%;
      top: 50%;
      background-image: url("<?php echo base_url() ?>assets/images/loader1.gif");
      z-index: 9999;
      background-repeat: no-repeat;
      background-position: center;
      background-size: cover;
      margin: -50px 0 0 -50px;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
  <!-- Site wrapper -->
  <div class="wrapper">