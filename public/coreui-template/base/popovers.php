<?php
require_once '../includes/config.php';

$pageTitle = 'Popovers - ' . $config['siteName'];
$pageDescription = 'Bootstrap popover component for contextual information';
$currentPage = 'popovers';
$currentSection = 'base';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Base', 'url' => 'base/'],
    ['title' => 'Popovers']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/popovers/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Popover</strong><span class="small ms-1">Basic example</span></div>
                <div class="card-body">
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/popovers/#example" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <button class="btn btn-lg btn-danger" type="button" data-coreui-toggle="popover" title="" data-coreui-content="And here's some amazing content. It's very engaging. Right?" data-coreui-original-title="Popover title">Click to toggle popover</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Popover</strong><span class="small ms-1">Four directions</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Four options are available: top, right, bottom, and left aligned. Directions are mirrored when using Bootstrap in RTL.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/popovers/#four-directions" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <button class="btn btn-secondary" type="button" data-coreui-container="body" data-coreui-toggle="popover" data-coreui-placement="top" data-coreui-content="Top popover" data-coreui-original-title="" title="">Popover on top</button>
                        <button class="btn btn-secondary" type="button" data-coreui-container="body" data-coreui-toggle="popover" data-coreui-placement="right" data-coreui-content="Right popover" data-coreui-original-title="" title="">Popover on right</button>
                        <button class="btn btn-secondary" type="button" data-coreui-container="body" data-coreui-toggle="popover" data-coreui-placement="bottom" data-coreui-content="Bottom popover" data-coreui-original-title="" title="">Popover on bottom</button>
                        <button class="btn btn-secondary" type="button" data-coreui-container="body" data-coreui-toggle="popover" data-coreui-placement="left" data-coreui-content="Left popover" data-coreui-original-title="" title="">Popover on left</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Popover</strong><span class="small ms-1">Dismiss on next click</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Use the <code>focus</code> trigger to dismiss popovers on the user’s next click of a different element than the toggle element.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/popovers/#dismiss-on-next-click" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002"><a class="btn btn-lg btn-danger" tabindex="0" role="button" data-coreui-toggle="popover" data-coreui-trigger="focus" title="" data-coreui-content="And here's some amazing content. It's very engaging. Right?" data-coreui-original-title="Dismissible popover">Dismissible popover</a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12"></div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Popover</strong><span class="small ms-1">Disabled elements</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Elements with the <code>disabled</code> attribute aren’t interactive, meaning users cannot hover or click them to trigger a popover (or tooltip). As a workaround, you’ll want to trigger the popover from a wrapper <code> &lt;div&gt;</code> or <code> &lt;span&gt;</code>, ideally made keyboard-focusable using <code>tabindex="0"</code>.</p>
                  <p class="text-body-secondary small">For disabled popover triggers, you may also prefer <code>data-coreui-trigger="hover focus"</code> so that the popover appears as immediate visual feedback to your users as they may not expect to <em>click</em> on a disabled element.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/popovers/#disabled-elements" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003"><span class="d-inline-block" tabindex="0" data-coreui-toggle="popover" data-coreui-trigger="hover focus" data-coreui-content="Disabled popover" data-coreui-original-title="" title="">
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