
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
  <script>
    /**
     * Highcharts loader (production-safe)
     *
     * The online crash you reported happens when Highcharts modules (highcharts-more/solid-gauge/accessibility)
     * execute before the Highcharts core is available.
     *
     * This loader guarantees order:
     * - load Highcharts core FIRST (with fallbacks)
     * - then load modules sequentially
     * - never load modules unless the core is actually present
     *
     * It is also idempotent (won't insert duplicates).
     */
    (function highchartsLoader() {
      window.__hcLoaderState = window.__hcLoaderState || {
        started: false,
        loaded: false
      };

      if (window.__hcLoaderState.loaded || window.__hcLoaderState.started) return;
      window.__hcLoaderState.started = true;

      function hasHighchartsCore() {
        return (typeof window.Highcharts !== 'undefined' &&
          typeof window.Highcharts.setOptions === 'function' &&
          typeof window.Highcharts.chart === 'function');
      }

      function loadScriptOnce(src, attrName) {
        return new Promise(function(resolve, reject) {
          var selector = 'script[' + attrName + '="' + src + '"]';
          if (document.querySelector(selector)) {
            resolve();
            return;
          }
          var script = document.createElement('script');
          script.src = src;
          script.async = false;
          script.setAttribute(attrName, src);
          script.onload = function() { resolve(); };
          script.onerror = function() { reject(new Error('Failed to load ' + src)); };
          document.head.appendChild(script);
        });
      }

      // Core fallbacks: keep node_modules first (dev), but allow production to swap without breaking.
      var coreCandidates = [
        '<?php echo base_url() ?>node_modules/highcharts/highcharts.js',
        // If you later decide to host Highcharts under assets/, this will start working automatically.
        '<?php echo base_url() ?>assets/plugins/highcharts/highcharts.js'
      ];

      function loadCoreWithFallbacks() {
        // If already present, skip loading.
        if (hasHighchartsCore()) return Promise.resolve();

        // Try candidates in order.
        return coreCandidates.reduce(function(p, src) {
          return p.catch(function() {
            return loadScriptOnce(src, 'data-hc-core').then(function() {
              // Some servers may return HTML for missing files; verify we actually got Highcharts.
              if (!hasHighchartsCore()) {
                throw new Error('Highcharts core did not initialize from ' + src);
              }
            });
          });
        }, Promise.reject(new Error('init')));
      }

      var moduleScripts = [
        '<?php echo base_url() ?>node_modules/highcharts/highcharts-more.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/solid-gauge.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/exporting.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/export-data.js',
        '<?php echo base_url() ?>node_modules/highcharts/modules/accessibility.js',
        // Optional future asset fallbacks (won't run unless you host them)
        'https://code.highcharts.com/highcharts.js',
        'https://code.highcharts.com/highcharts-more.js',
        'https://code.highcharts.com/modules/exporting.js',
        'https://code.highcharts.com/modules/export-data.js',
        'https://code.highcharts.com/modules/bullet.js',
        'https://code.highcharts.com/modules/accessibility.js'
      ];

      function loadModulesSequentially() {
        // Only load the *first* set that exists; if node_modules is missing in prod, the asset fallbacks can be used.
        var primary = moduleScripts.slice(0, 5);
        var secondary = moduleScripts.slice(5);

        function loadList(list) {
          return list.reduce(function(p, src) {
            return p.then(function() {
              if (!hasHighchartsCore()) throw new Error('Highcharts core missing while loading modules');
              return loadScriptOnce(src, 'data-hc-module').catch(function(e) {
                // If a module fails, keep going; missing optional modules should not kill the page.
                console.warn(e.message);
              });
            });
          }, Promise.resolve());
        }

        return loadList(primary).then(function() {
          // If core exists but some modules couldn't load from node_modules, attempt asset fallback list.
          return loadList(secondary);
        });
      }

      loadCoreWithFallbacks()
        .then(loadModulesSequentially)
        .then(function() {
          window.__hcLoaderState.loaded = true;
        })
        .catch(function(e) {
          console.warn('Highcharts loader failed:', e && e.message ? e.message : e);
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
    /* Global: DataTables Buttons toolbar - keep buttons on one line */
    .dt-buttons {
      display: inline-flex !important;
      flex-wrap: nowrap !important;
      align-items: center !important;
      gap: 6px !important;
      white-space: nowrap !important;
      overflow-x: auto;
      padding-bottom: 2px;
    }

    .dt-buttons .btn {
      flex: 0 0 auto;
      margin: 0 !important;
    }

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