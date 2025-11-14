<?php
require_once 'includes/config.php';

$pageTitle = 'Calendar - ' . $config['siteName'];
$pageDescription = 'Calendar component and functionality';
$currentPage = 'calendar';
$currentSection = '';

$additionalCSS = [];
$additionalJS = [
    'vendors/fullcalendar/js/index.global.min.js',
    'js/calendar.js'
];

$breadcrumbs = [
    ['title' => 'Calendar']
];

include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include 'includes/header.php'; ?>
      <div class="body flex-grow-1">
        <div class="container-lg px-4">
          <div class="card mb-4">
            <div class="card-header"> FullCalendar<a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI Pro Integration</a></div>
            <div class="card-body">
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://fullcalendar.io/docs" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                    <div class="bg-body p-3 rounded" id="calendar"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
<?php include 'includes/footer.php'; ?>
    </div>

<?php include 'includes/scripts.php'; ?>