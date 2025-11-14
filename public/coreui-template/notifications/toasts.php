<?php
require_once '../includes/config.php';

$pageTitle = 'Toasts - ' . $config['siteName'];
$pageDescription = 'Bootstrap toast notification components';
$currentPage = 'toasts';
$currentSection = 'notifications';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Notifications', 'url' => 'notifications/'],
    ['title' => 'Toasts']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/toasts/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Toast</strong><span class="small ms-1">Basic</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Toasts are as flexible as you need and have very little required markup. At a minimum, we require a single element to contain your �toasted� content and strongly encourage a dismiss button.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/toasts/#basic" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                    <div class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="toast-header">
                        <svg class="docs-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveaspectratio="xMidYMid slice" focusable="false">
                          <rect width="100%" height="100%" fill="#007aff"></rect>
                        </svg><strong class="me-auto">Bootstrap</strong><small>11 mins ago</small>
                        <button class="btn-close" type="button" data-coreui-dismiss="toast" aria-label="Close"></button>
                      </div>
                      <div class="toast-body">Hello, world! This is a toast message.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Toast</strong><span class="small ms-1">Live example</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Click the button the below to show as toast (positioning with our utilities in the lower right corner) that has been hidden by default with <code>.hide</code>.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/toasts/#live-example" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                    <div class="toaster position-fixed bottom-0 end-0 p-3" style="z-index: 5">
                      <div class="toast hide" id="liveToast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                          <svg class="docs-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveaspectratio="xMidYMid slice" focusable="false">
                            <rect width="100%" height="100%" fill="#007aff"></rect>
                          </svg><strong class="me-auto">Bootstrap</strong><small>11 mins ago</small>
                          <button class="btn-close" type="button" data-coreui-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">Hello, world! This is a toast message.</div>
                      </div>
                    </div>
                    <button class="btn btn-primary" id="liveToastBtn" type="button">Show live toast</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Toast</strong><span class="small ms-1">Translucent</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Toasts are slightly translucent, too, so they blend over whatever they might appear over.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/toasts/#translucent" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom bg-dark">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                    <div class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="toast-header">
                        <svg class="docs-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveaspectratio="xMidYMid slice" focusable="false">
                          <rect width="100%" height="100%" fill="#007aff"></rect>
                        </svg><strong class="me-auto">Bootstrap</strong><small class="text-body-secondary">11 mins ago</small>
                        <button class="btn-close" type="button" data-coreui-dismiss="toast" aria-label="Close"></button>
                      </div>
                      <div class="toast-body">Hello, world! This is a toast message.</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Toast</strong><span class="small ms-1">Stacking</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">You can stack toasts by wrapping them in a toast container, which will vertically add some spacing.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/toasts/#stacking" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003">
                    <div class="toast-container position-static">
                      <div class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                          <svg class="docs-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveaspectratio="xMidYMid slice" focusable="false">
                            <rect width="100%" height="100%" fill="#007aff"></rect>
                          </svg><strong class="me-auto">Bootstrap</strong><small class="text-body-secondary">just now</small>
                          <button class="btn-close" type="button" data-coreui-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">See? Just like this.</div>
                      </div>
                      <div class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                          <svg class="docs-placeholder-img rounded me-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" preserveaspectratio="xMidYMid slice" focusable="false">
                            <rect width="100%" height="100%" fill="#007aff"></rect>
                          </svg><strong class="me-auto">Bootstrap</strong><small class="text-body-secondary">2 seconds ago</small>
                          <button class="btn-close" type="button" data-coreui-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">Heads up, toasts will stack automatically</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Toast</strong><span class="small ms-1">Custom content</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Customize your toasts by removing sub-components, tweaking with <a href="https://coreui.io/bootstrap/docs/utilities/api/">utilities</a>, or adding your own markup. Here we�ve created a simpler toast by removing the default <code>.toast-header</code>, adding a custom hide icon from <a href="https://coreui.io/icons/">CoreUI Icons</a>, and using some <a href="https://coreui.io/bootstrap/docs/utilities/flex/">flexbox utilities</a> to adjust the layout.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1004" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/toasts/#custom-content" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1004">
                    <div class="toast align-items-center fade show" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="d-flex">
                        <div class="toast-body">Hello, world! This is a toast message.</div>
                        <button class="btn-close me-2 m-auto" type="button" data-coreui-dismiss="toast" aria-label="Close"></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <p class="text-body-secondary small">Alternatively, you can also add additional controls and components to toasts.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1005" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/toasts/#custom-content" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1005">
                    <div class="toast fade show" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="toast-body">Hello, world! This is a toast message.
                        <div class="mt-2 pt-2 border-top">
                          <button class="btn btn-primary btn-sm" type="button">Take action</button>
                          <button class="btn btn-secondary btn-sm" type="button" data-coreui-dismiss="toast">Close</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Toast</strong><span class="small ms-1">Color schemes</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Building on the above example, you can create different toast color schemes with our <a href="https://coreui.io/bootstrap/docs/utilities/colors/">color</a> and <a href="https://coreui.io/bootstrap/docs/utilities/background/">background</a> utilities. Here we�ve added <code>.bg-primary</code> and <code>.text-white</code> to the <code>.toast</code>, and then added <code>.btn-close-white</code> to our close button. For a crisp edge, we remove the default border with <code>.border-0</code>.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1006" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/toasts/#color-schemes" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1006">
                    <div class="toast align-items-center text-white bg-primary border-0 fade show" role="alert" aria-live="assertive" aria-atomic="true">
                      <div class="d-flex">
                        <div class="toast-body">Hello, world! This is a toast message.</div>
                        <button class="btn-close btn-close-white me-2 m-auto" type="button" data-coreui-dismiss="toast" aria-label="Close"></button>
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