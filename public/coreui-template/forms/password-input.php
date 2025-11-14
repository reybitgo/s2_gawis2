<?php
require_once '../includes/config.php';

$pageTitle = 'Password Input - ' . $config['siteName'];
$pageDescription = 'Password input form control component';
$currentPage = 'password-input';
$currentSection = 'forms';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Forms', 'url' => 'forms/'],
    ['title' => 'Password Input']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/forms/password-input/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Password Input</strong></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Use the <code>form-password</code> wrapper to add a visibility toggle button to standard Bootstrap password input fields.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/password-input/#example" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <div class="mb-3">
                          <label class="form-label" for="examplePasswordInput1">Password</label>
                          <div class="form-password">
                            <input class="form-control" id="examplePasswordInput1" type="password" placeholder="Enter your password">
                            <button class="form-password-action" type="button" data-coreui-toggle="password" aria-label="Toggle password visibility"><span class="form-password-action-icon"></span></button>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label" for="examplePasswordInput2">Password with value</label>
                          <div class="form-password">
                            <input class="form-control" id="examplePasswordInput2" type="password" placeholder="Enter your password" value="Top secret">
                            <button class="form-password-action" type="button" data-coreui-toggle="password" aria-label="Toggle password visibility"><span class="form-password-action-icon"></span></button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Password Input</strong><span class="small ms-1">Sizing variants</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small"></p>
                  <p class="text-body-secondary small">Bootstrap Password Input supports different sizes using Bootstrap�s sizing utilities like <code>.form-control-lg</code> and <code>.form-control-sm</code>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/password-input/#sizing-variants" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <div class="form-password mb-2">
                          <input class="form-control form-control-lg" type="password" placeholder="Large password input">
                          <button class="form-password-action" type="button" data-coreui-toggle="password" aria-label="Toggle password visibility"><span class="form-password-action-icon"></span></button>
                        </div>
                        <div class="form-password mb-2">
                          <input class="form-control" type="password" placeholder="Default password input">
                          <button class="form-password-action" type="button" data-coreui-toggle="password" aria-label="Toggle password visibility"><span class="form-password-action-icon"></span></button>
                        </div>
                        <div class="form-password">
                          <input class="form-control form-control-sm" type="password" placeholder="Small password input">
                          <button class="form-password-action" type="button" data-coreui-toggle="password" aria-label="Toggle password visibility"><span class="form-password-action-icon"></span></button>
                        </div>
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