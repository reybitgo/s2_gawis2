<?php
require_once '../includes/config.php';

$pageTitle = 'Multi Select - ' . $config['siteName'];
$pageDescription = 'Multi-select form control component';
$currentPage = 'multi-select';
$currentSection = 'forms';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Forms', 'url' => 'forms/'],
    ['title' => 'Multi Select']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/forms/multi-select/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>CoreUI Multi Select</strong><span class="small ms-1">with Checkbox (Default Style)</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/multi-select/" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <form>
                          <div class="row">
                            <div class="col-12 col-lg-6 col-xl-3 mb-3 mb-xl-0">
                              <label>Single Select</label>
                              <select class="form-multi-select" data-coreui-multiple="false" data-coreui-search="true">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 mb-3 mb-xl-0">
                              <label>Multiple Select - text selection</label>
                              <select class="form-multi-select" data-coreui-search="true" data-coreui-selection-type="text">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 mb-3 mb-lg-0">
                              <label>Multiple Select - tag selection</label>
                              <select class="form-multi-select" data-coreui-search="true" data-coreui-selection-type="tags">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3">
                              <label>Multiple Select - counter selection</label>
                              <select class="form-multi-select" data-coreui-search="true" data-coreui-selection-type="counter">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>CoreUI Multi Select</strong><span class="small ms-1">with Text </span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/multi-select/" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <form>
                          <div class="row">
                            <div class="col-12 col-lg-6 col-xl-3 mb-3 mb-xl-0">
                              <label>Single Select</label>
                              <select class="form-multi-select" data-coreui-multiple="false" data-coreui-search="true" data-coreui-options-style="text">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 mb-3 mb-xl-0">
                              <label>Multiple Select - text selection</label>
                              <select class="form-multi-select" data-coreui-search="true" data-coreui-selection-type="text" data-coreui-options-style="text">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3 mb-3 mb-lg-0">
                              <label>Multiple Select - tag selection</label>
                              <select class="form-multi-select" data-coreui-search="true" data-coreui-selection-type="tags" data-coreui-options-style="text">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                            <div class="col-12 col-lg-6 col-xl-3">
                              <label>Multiple Select - counter selection</label>
                              <select class="form-multi-select" data-coreui-search="true" data-coreui-selection-type="counter" data-coreui-options-style="text">
                                <option value="0">enhancement</option>
                                <option value="1">bug</option>
                                <option value="2">duplicate</option>
                                <option value="3">invalid</option>
                                <optgroup label="group">
                                  <option value="4">enhancement2</option>
                                  <option value="5">bug2</option>
                                </optgroup>
                              </select>
                            </div>
                          </div>
                        </form>
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