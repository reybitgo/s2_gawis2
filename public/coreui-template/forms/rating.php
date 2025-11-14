<?php
require_once '../includes/config.php';

$pageTitle = 'Rating - ' . $config['siteName'];
$pageDescription = 'Rating form control component';
$currentPage = 'rating';
$currentSection = 'forms';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Forms', 'url' => 'forms/'],
    ['title' => 'Rating']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/forms/rating/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Rating</strong></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Embed the Rating component in your HTML by using a <code>div</code> with <code>data-coreui-toggle="rating"</code>. Set the initial value with <code>data-coreui-value</code>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#how-to-use-rating-component" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <div data-coreui-toggle="rating" data-coreui-value="3"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Rating</strong><span class="small ms-1">Readonly</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Set the Rating component to read-only by adding <code>data-coreui-read-only="true"</code>. This disables interaction, preventing users from changing the displayed rating value.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#readonly" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <div data-coreui-read-only="true" data-coreui-toggle="rating" data-coreui-value="3"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Rating</strong><span class="small ms-1">Disabled</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Add the <code>data-coreui-disabled="true"</code> boolean attribute to give it a grayed out appearance, remove pointer events, and prevent focusing.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#disabled" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                        <div data-coreui-disabled="true" data-coreui-toggle="rating" data-coreui-value="3"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Rating</strong><span class="small ms-1">Precision</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Adjust the granularity of the Rating component by setting <code>data-coreui-precision</code>. This attribute allows for fractional ratings, such as quarter values, to provide more precise feedback.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#precision" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003">
                        <div data-coreui-precision="0.25" data-coreui-toggle="rating" data-coreui-value="1.5"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Rating</strong><span class="small ms-1">Number of items</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Control the total number of rating items displayed by using <code>data-coreui-item-count</code>. You can create a Rating component with a custom scale, be it larger for detailed assessments or smaller for simplicity.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1004" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#number-of-items" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1004">
                        <div class="mb-3" data-coreui-item-count="20" data-coreui-toggle="rating" data-coreui-value="5"></div>
                        <div data-coreui-item-count="3" data-coreui-toggle="rating" data-coreui-value="1"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Rating</strong><span class="small ms-1">Tooltips</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Enable descriptive text on hover by setting <code>data-coreui-tooltips</code> to <code>true</code>. This provides immediate feedback or guidance as the user interacts with the rating items.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1005" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#tooltips" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1005">
                        <div data-coreui-toggle="rating" data-coreui-tooltips="true" data-coreui-value="3"></div>
                      </div>
                    </div>
                  </div>
                  <p class="text-body-secondary small">For custom messages, provide a comma-separated list of tooltips corresponding to each rating value to enhance the user�s understanding of each rating level.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1006" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#tooltips" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1006">
                        <div data-coreui-toggle="rating" data-coreui-tooltips="Very bad, Bad, Meh, Good, Very good" data-coreui-value="3"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Rating</strong><span class="small ms-1">Sizes</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Larger or smaller rating component? Add <code>data-coreui-size="lg"</code> or <code>data-coreui-size="sm"</code> for additional sizes.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1007" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/rating/#sizes" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1007">
                        <div data-coreui-size="sm" data-coreui-toggle="rating" data-coreui-value="3"></div>
                        <div data-coreui-toggle="rating" data-coreui-value="3"></div>
                        <div data-coreui-size="lg" data-coreui-toggle="rating" data-coreui-value="3"></div>
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