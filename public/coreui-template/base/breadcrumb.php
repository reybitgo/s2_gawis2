<?php
// Page configuration
require_once '../includes/config.php';

$pageTitle = 'Breadcrumb - ' . $config['siteName'];
$pageDescription = 'Breadcrumb navigation component examples';
$currentPage = 'breadcrumb';
$currentSection = 'base';

// Additional CSS files specific to this page
$additionalCSS = [
    // No additional CSS needed for breadcrumb page
];

// Additional JavaScript files specific to this page
$additionalJS = [
    // No additional JS needed for breadcrumb page
];

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Base'],
    ['title' => 'Breadcrumb']
];

// Include the head
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/breadcrumb/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Breadcrumb</strong></div>
                <div class="card-body">
                  <p class="text-body-secondary small">The breadcrumb navigation provides links back to each previous page the user navigated through and shows the current location in a website or an application. You don't have to add separators, because they automatically added in CSS through <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/::before"> <code>::before</code></a> and <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/content"> <code>content</code></a>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/breadcrumb/" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item active" aria-current="page">Home</li>
                          </ol>
                        </nav>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Library</li>
                          </ol>
                        </nav>
                        <nav aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Library</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data</li>
                          </ol>
                        </nav>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Breadcrumb</strong><span class="small ms-1">Dividers</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Dividers are automatically added in CSS through <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/::before"><code>::before</code></a> and <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/content"><code>content</code></a>. They can be changed by modifying a local CSS custom property <code>--cui-breadcrumb-divider</code>, or through the <code>$breadcrumb-divider</code> Sass variable — and <code>$breadcrumb-divider-flipped</code> for its RTL counterpart, if needed. We default to our Sass variable, which is set as a fallback to the custom property. This way, you get a global divider that you can override without recompiling CSS at any time.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/breadcrumb/" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <nav style="--cui-breadcrumb-divider: '>';" aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Library</li>
                          </ol>
                        </nav>
                        <nav style="--cui-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='m2.5 0 3 3-3 3-1-1 2-2-2-2 1-1z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Library</li>
                          </ol>
                        </nav>
                        <nav style="--cui-breadcrumb-divider: '';" aria-label="breadcrumb">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Library</li>
                          </ol>
                        </nav>
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