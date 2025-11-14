<?php
require_once '../includes/config.php';

$pageTitle = 'Tooltips - ' . $config['siteName'];
$pageDescription = 'Bootstrap tooltip component for hover information';
$currentPage = 'tooltips';
$currentSection = 'base';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Base', 'url' => 'base/'],
    ['title' => 'Tooltips']
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
                Our Admin Panel isn’t just a mix of third-party components. It’s <strong>the only Bootstrap dashboard built on a professional, enterprise-grade UI Components Library</strong>.
                This component is part of this library, and we present only the basic usage of it here. To explore extended examples, detailed API documentation, and customization options, refer to our docs.
              </div>
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/tooltips/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Tooltips</strong></div>
                <div class="card-body">
                  <p>Hover over the links below to see tooltips:</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/tooltips/#examples" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <p>Placeholder text to demonstrate some <a href="#" data-coreui-toggle="tooltip" data-coreui-original-title="Default tooltip">inline links</a> with tooltips. This is now just filler, no killer. Content placed here just to mimic the presence of <a href="#" data-coreui-toggle="tooltip" data-coreui-original-title="Another tooltip">real text</a>. And all that just to give you an idea of how tooltips would look when used in real-world situations. So hopefully you've now seen how <a href="#" data-coreui-toggle="tooltip" data-coreui-original-title="Another one here too">these tooltips on links</a> can work in practice, once you use them on <a href="#" data-coreui-toggle="tooltip" title="The last tip!">your own</a> site or project.</p>
                      </div>
                    </div>
                  </div>
                  <hr>
                  <p>Hover over the buttons below to see the four tooltips directions: top, right, bottom, and left. Directions are mirrored when using Bootstrap in RTL.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/tooltips/#examples" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <button class="btn btn-secondary" type="button" data-coreui-toggle="tooltip" data-coreui-placement="top" data-coreui-original-title="Tooltip on top">Tooltip on top</button>
                        <button class="btn btn-secondary" type="button" data-coreui-toggle="tooltip" data-coreui-placement="right" data-coreui-original-title="Tooltip on right">Tooltip on right</button>
                        <button class="btn btn-secondary" type="button" data-coreui-toggle="tooltip" data-coreui-placement="bottom" data-coreui-original-title="Tooltip on bottom">Tooltip on bottom</button>
                        <button class="btn btn-secondary" type="button" data-coreui-toggle="tooltip" data-coreui-placement="left" data-coreui-original-title="Tooltip on left">Tooltip on left</button>
                        <button class="btn btn-secondary" type="button" data-coreui-toggle="tooltip" data-coreui-html="true" data-coreui-original-title="<em>Tooltip</em> <u>with</u> <b>HTML</b>">Tooltip with HTML</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Tooltips</strong><span class="small ms-1"> Disabled elements</span></div>
                <div class="card-body">
                  <p>Elements with the <code>disabled</code> attribute aren’t interactive, meaning users cannot focus, hover, or click them to trigger a tooltip (or popover). As a workaround, you’ll want to trigger the tooltip from a wrapper <code> &lt;div&gt;</code> or <code>&lt;span&gt;</code>, ideally made keyboard-focusable using <code>tabindex="0"</code>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/tooltips/#disabled-elements" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002"><span class="d-inline-block" tabindex="0" data-coreui-toggle="tooltip" data-coreui-original-title="Disabled tooltip">
                          <button class="btn btn-primary" type="button" disabled="">Disabled button</button></span>
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