<?php
require_once '../includes/config.php';

$pageTitle = 'Loading Buttons - ' . $config['siteName'];
$pageDescription = 'Bootstrap loading state button components';
$currentPage = 'loading-buttons';
$currentSection = 'buttons';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Buttons', 'url' => 'buttons/'],
    ['title' => 'Loading Buttons']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/loading-buttons/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>CoreUI Loading Buttons</strong><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
            <div class="card-body">
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/loading-buttons/" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                    <div class="row py-4 text-center">
                      <div class="col-md-2">
                        <button class="btn btn-primary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-secondary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-success btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-info btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-danger btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-warning btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                    </div>
                    <div class="row py-4 text-center">
                      <div class="col-md-2">
                        <button class="btn btn-outline-primary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-secondary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-success btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-info btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-danger btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-warning btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                    </div>
                    <div class="row py-4 text-center">
                      <div class="col-md-2">
                        <button class="btn btn-ghost-primary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-secondary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-success btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-info btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-danger btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-warning btn-loading" data-coreui="loading-button" data-coreui-timeout="3000">Submit</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header"><strong>CoreUI Loading Buttons</strong><span class="small ms-1">with grow spinner</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
            <div class="card-body">
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/loading-buttons/#grow" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                    <div class="row py-4 text-center">
                      <div class="col-md-2">
                        <button class="btn btn-primary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-secondary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-success btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-info btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-danger btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-warning btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                    </div>
                    <div class="row py-4 text-center">
                      <div class="col-md-2">
                        <button class="btn btn-outline-primary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-secondary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-success btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-info btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-danger btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-outline-warning btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                    </div>
                    <div class="row py-4 text-center">
                      <div class="col-md-2">
                        <button class="btn btn-ghost-primary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-secondary btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-success btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-info btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-danger btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
                      <div class="col-md-2">
                        <button class="btn btn-ghost-warning btn-loading" data-coreui="loading-button" data-coreui-timeout="3000" data-coreui-spinner-type="grow">Submit</button>
                      </div>
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