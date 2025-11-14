<?php
require_once '../includes/config.php';

$pageTitle = 'Calendar - ' . $config['siteName'];
$pageDescription = 'Bootstrap Calendar component examples with days, weeks, months, years, and range selection';
$currentPage = 'calendar';
$currentSection = 'base';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Components', 'url' => ''],
    ['title' => 'Base', 'url' => 'base/'],
    ['title' => 'Calendar']
];

include '../includes/head.php';
?>

<?php include '../includes/sidebar.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include '../includes/header.php'; ?>
      <div class="body flex-grow-1">
        <div class="container-lg px-4">
          <div class="bg-primary bg-opacity-10 border border-2 border-primary rounded mb-4">
            <div class="row d-flex align-items-center p-3 px-xl-4 flex-xl-nowrap">
              <div class="col-xl-auto col-12 d-none d-xl-block p-0"><img class="img-fluid" src="<?= $baseDir ?>assets/img/components.webp" width="160px" height="160px" alt="CoreUI PRO hexagon"></div>
              <div class="col-md col-12 px-lg-4">
                Our Admin Panel isn't just a mix of third-party components. It's <strong>the only Bootstrap dashboard built on a professional, enterprise-grade UI Components Library</strong>.
                This component is part of this library, and we present only the basic usage of it here. To explore extended examples, detailed API documentation, and customization options, refer to our docs.
              </div>
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/calendar/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Calendar</strong><span class="small ms-1">Days</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Select specific days using the Bootstrap Calendar component. The example below shows basic usage.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/calendar/#days" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                    <div class="d-flex justify-content-center">
                      <div class="bg-body border rounded" data-coreui-locale="en-US" data-coreui-start-date="2024/02/13" data-coreui-toggle="calendar"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Calendar</strong><span class="small ms-1">Weeks</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Set the <code>data-coreui-selection-type</code> to <code>week</code> to enable selection of entire week. You can also use <code>data-coreui-show-week-number="true"</code> to show week numbers.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/calendar/#weeks" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                    <div class="d-flex justify-content-center">
                      <div class="bg-body border rounded" data-coreui-locale="en-US" data-coreui-selection-type="week" data-coreui-show-week-number="true" data-coreui-start-date="2024W15" data-coreui-toggle="calendar"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Calendar</strong><span class="small ms-1">Months</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Set the <code>data-coreui-selection-type</code> to <code>month</code> to enable selection of entire months.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/calendar/#months" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                    <div class="d-flex justify-content-center">
                      <div class="bg-body border rounded" data-coreui-locale="en-US" data-coreui-selection-type="month" data-coreui-start-date="2024-2" data-coreui-toggle="calendar"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Calendar</strong><span class="small ms-1">Years</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Set the <code>data-coreui-selection-type</code> to <code>year</code> to enable years range selection.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/calendar/#years" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003">
                    <div class="d-flex justify-content-center">
                      <div class="bg-body border rounded" data-coreui-locale="en-US" data-coreui-selection-type="year" data-coreui-start-date="2024" data-coreui-toggle="calendar"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Calendar</strong><span class="small ms-1">Multiple calendar panels</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Display multiple calendar panels side by side by setting the <code>data-coreui-calendars</code> attribute. This can be useful for selecting ranges or comparing dates across different months.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1004" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/calendar/#multiple-calendar-panels" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1004">
                    <div class="d-flex justify-content-center">
                      <div class="bg-body border rounded" data-coreui-calendars="2" data-coreui-locale="en-US" data-coreui-toggle="calendar"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Calendar</strong><span class="small ms-1">Range selection</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Enable range selection by adding <code>data-coreui-range="true"</code> to allow users to pick a start and end date. This example demonstrates how to configure the Bootstrap 5 Calendar component to handle date ranges.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1005" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/calendar/#multiple-calendar-panels" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1005">
                    <div class="d-flex justify-content-center">
                      <div class="bg-body border rounded" data-coreui-locale="en-US" data-coreui-calendars="2" data-coreui-range="true" data-coreui-start-date="2022/08/23" data-coreui-end-date="2022/09/08" data-coreui-toggle="calendar"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
<?php include '../includes/footer.php'; ?>
    </div>

<?php include '../includes/scripts.php'; ?>