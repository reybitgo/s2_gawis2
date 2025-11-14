<?php
require_once '../includes/config.php';

$pageTitle = 'Collapse - ' . $config['siteName'];
$pageDescription = 'Collapse component examples for creating collapsible content sections with toggle functionality.';
$currentPage = 'collapse';
$currentSection = 'base';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Base', 'url' => 'base/'],
    ['title' => 'Collapse']
];

include '../includes/head.php';
?>

<?php include '../includes/sidebar.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include '../includes/header.php'; ?>
        <div class="container-lg px-4">
          <div class="bg-primary bg-opacity-10 border border-2 border-primary rounded mb-4">
            <div class="row d-flex align-items-center p-3 px-xl-4 flex-xl-nowrap">
              <div class="col-xl-auto col-12 d-none d-xl-block p-0"><img class="img-fluid" src="<?= $baseDir ?>assets/img/components.webp" width="160px" height="160px" alt="CoreUI PRO hexagon"></div>
              <div class="col-md col-12 px-lg-4">
                Our Admin Panel isn't just a mix of third-party components. It's <strong>the only Bootstrap dashboard built on a professional, enterprise-grade UI Components Library</strong>.
                This component is part of this library, and we present only the basic usage of it here. To explore extended examples, detailed API documentation, and customization options, refer to our docs.
              </div>
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/collapse/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Collapse</strong></div>
            <div class="card-body">
              <p class="text-body-secondary small">You can use a link with the <code>href</code> attribute, or a button with the <code>data-coreui-target</code> attribute. In both samples, the <code>data-coreui-toggle="collapse""</code> is required.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/collapse/#example" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                    <p><a class="btn btn-primary" data-coreui-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">Link with href</a>
                      <button class="btn btn-primary" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">Button with data-target</button>
                    </p>
                    <div class="collapse" id="collapseExample">
                      <div class="card mb-4 card-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Collapse</strong><span class="small ms-1">Horizontal</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">The collapse plugin also supports horizontal collapsing. Add the <code>.collapse-horizontal</code> modifier class to transition the <code>width</code> instead of <code>height</code> and set a <code>width</code> on the immediate child element. Feel free to write your own custom Sass, use inline styles, or use our <a href="/docs/utilities/sizing/">width utilities</a>.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/collapse/#horizontal" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                    <p>
                      <button class="btn btn-primary" type="button" data-coreui-toggle="collapse" data-coreui-target="#collapseWidthExample" aria-expanded="false" aria-controls="collapseWidthExample">Toggle width collapse</button>
                    </p>
                    <div style="min-height: 120px;">
                      <div class="collapse collapse-horizontal" id="collapseWidthExample">
                        <div class="card card-body" style="width: 300px;">This is some placeholder content for a horizontal collapse. It's hidden by default and shown when triggered.</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Collapse</strong><span class="small ms-1">multiple targets</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">A <code>&lt;button&gt;</code> or <code>&lt;a&gt;</code> can show and hide multiple elements by referencing them with a selector in its <code>href</code> or <code>data-coreui-target</code> attribute. Multiple <code>&lt;button&gt;</code> or <code>&lt;a&gt;</code> can show and hide an element if they each reference it with their <code>href</code> or <code>data-coreui-target</code> attribute</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/collapse/#multiple-targets" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                    <p><a class="btn btn-primary" data-coreui-toggle="collapse" href="#multiCollapseExample1" aria-expanded="false" aria-controls="multiCollapseExample1">Toggle first element</a>
                      <button class="btn btn-primary" type="button" data-coreui-toggle="collapse" data-coreui-target="#multiCollapseExample2" aria-expanded="false" aria-controls="multiCollapseExample1">Toggle second element</button>
                      <button class="btn btn-primary" type="button" data-coreui-toggle="collapse" data-coreui-target=".multi-collapse" aria-expanded="false" aria-controls="multiCollapseExample1 multiCollapseExample2">Toggle both elements</button>
                    </p>
                    <div class="row">
                      <div class="col-sm-12 col-md-6">
                        <div class="collapse multi-collapse" id="multiCollapseExample1">
                          <div class="card mb-4 card-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.</div>
                        </div>
                      </div>
                      <div class="col-sm-12 col-md-6">
                        <div class="collapse multi-collapse" id="multiCollapseExample2">
                          <div class="card mb-4 card-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.</div>
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