<?php
require_once '../includes/config.php';

$pageTitle = 'Badge - ' . $config['siteName'];
$pageDescription = 'Bootstrap badge notification components';
$currentPage = 'badge';
$currentSection = 'notifications';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Notifications', 'url' => 'notifications/'],
    ['title' => 'Badge']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/badge/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header"><strong>Badges</strong></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Bootstrap badge scale to suit the size of the parent element by using relative font sizing and <code>em</code> units.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/badge/#headings" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <h1>Example heading<span class="badge bg-secondary">New</span></h1>
                        <h2>Example heading<span class="badge bg-secondary">New</span></h2>
                        <h3>Example heading<span class="badge bg-secondary">New</span></h3>
                        <h4>Example heading<span class="badge bg-secondary">New</span></h4>
                        <h5>Example heading<span class="badge bg-secondary">New</span></h5>
                        <h6>Example heading<span class="badge bg-secondary">New</span></h6>
                      </div>
                    </div>
                  </div>
                  <p class="text-body-secondary small">Badges can be used as part of links or buttons to provide a counter.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/badge/#headings" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <button class="btn btn-primary" type="button">Notifications<span class="badge bg-secondary">4</span></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card mb-4">
                <div class="card-header"><strong>Badges</strong><span class="small ms-1">Contextual variations</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Add any of the below-mentioned classes to modify the presentation of a badge. Please note that when using Bootstrap�s default <code>.bg-light</code>, you�ll likely need a text color utility like <code>.text-dark</code> for proper styling. This is because background utilities do not set anything but <code>background-color</code>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/badge/#contextual-variations" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002"><span class="badge me-1 bg-primary">Primary</span>
                        <span class="badge me-1 bg-secondary">Secondary</span>
                        <span class="badge me-1 bg-success">Success</span>
                        <span class="badge me-1 bg-danger">Danger</span>
                        <span class="badge me-1 bg-warning">Warning</span>
                        <span class="badge me-1 bg-info">Info</span>
                        <span class="badge me-1 bg-light text-dark">Light</span>
                        <span class="badge me-1 bg-dark">Dark</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Badges</strong><span class="small ms-1">Pill badges</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Apply the <code>.rounded-pill</code> modifier class to make badges rounded.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/badge/#pill-badges" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003"><span class="badge me-1 rounded-pill bg-primary">Primary</span>
                        <span class="badge me-1 rounded-pill bg-secondary">Secondary</span>
                        <span class="badge me-1 rounded-pill bg-success">Success</span>
                        <span class="badge me-1 rounded-pill bg-danger">Danger</span>
                        <span class="badge me-1 rounded-pill bg-warning">Warning</span>
                        <span class="badge me-1 rounded-pill bg-info">Info</span>
                        <span class="badge me-1 rounded-pill bg-light text-dark">Light</span>
                        <span class="badge me-1 rounded-pill bg-dark">Dark</span>
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