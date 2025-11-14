<?php
require_once '../includes/config.php';

$pageTitle = 'Alerts - ' . $config['siteName'];
$pageDescription = 'Bootstrap alert notification components';
$currentPage = 'alerts';
$currentSection = 'notifications';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Notifications', 'url' => 'notifications/'],
    ['title' => 'Alerts']
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
                Our Admin Panel isn�t just a mix of third-party components. It�s <strong>the only Bootstrap dashboard built on a professional, enterprise-grade UI Components Library</strong>.
                This component is part of this library, and we present only the basic usage of it here. To explore extended examples, detailed API documentation, and customization options, refer to our docs.
              </div>
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/alerts/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header"><strong>Alerts</strong></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Bootstrap alert is prepared for any length of text, as well as an optional close button. For a styling, use one of the <strong>required</strong> contextual classes (e.g.,<code>.alert-success</code>).</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/alerts/#examples" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <div class="alert alert-primary" role="alert">This is a primary alert�check it out!</div>
                        <div class="alert alert-secondary" role="alert">This is a secondary alert�check it out!</div>
                        <div class="alert alert-success" role="alert">This is a success alert�check it out!</div>
                        <div class="alert alert-danger" role="alert">This is a danger alert�check it out!</div>
                        <div class="alert alert-warning" role="alert">This is a warning alert�check it out!</div>
                        <div class="alert alert-info" role="alert">This is a info alert�check it out!</div>
                        <div class="alert alert-light" role="alert">This is a light alert�check it out!</div>
                        <div class="alert alert-dark" role="alert">This is a dark alert�check it out!</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header"><strong>Alerts</strong><span class="small ms-1">Link color</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Use the <code>.alert-link</code> utility class to immediately give matching colored links inside any alert.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/alerts/#link-color" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <div class="alert alert-primary" role="alert">This is a primary alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                        <div class="alert alert-secondary" role="alert">This is a secondary alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                        <div class="alert alert-success" role="alert">This is a success alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                        <div class="alert alert-danger" role="alert">This is a danger alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                        <div class="alert alert-warning" role="alert">This is a warning alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                        <div class="alert alert-info" role="alert">This is a info alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                        <div class="alert alert-light" role="alert">This is a light alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                        <div class="alert alert-dark" role="alert">This is a dark alert with <a class="alert-link" href="#">an example link</a>. Give it a click if you like.</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.col-->
          </div>
          <!-- /.row-->
          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header"><strong>Alerts</strong><span class="small ms-1">Additional content</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Alert can also incorporate supplementary HTML elements like heading, paragraph, and divider.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/alerts/#additional-content" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                        <div class="alert alert-success" role="alert">
                          <h4 class="alert-heading">Well done!</h4>
                          <p>Aww yeah, you successfully read this important alert message. This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.</p>
                          <hr>
                          <p class="mb-0">Whenever you need to, be sure to use margin utilities to keep things nice and tidy.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- /.col-->
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header"><strong>Alerts</strong><span class="small ms-1">Dismissing</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Using the JavaScript plugin, it�s possible to remove any alert.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/alerts/#dismissing" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003">
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                          <div class="fw-semibold">Holy guacamole!</div> You should check in on some of those fields below.
                          <button class="btn-close" type="button" data-coreui-dismiss="alert" aria-label="Close"></button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /.row-->
        </div>
      </div>
<?php include '../includes/footer.php'; ?>
    </div>

<?php include '../includes/scripts.php'; ?>