<?php
require_once '../includes/config.php';

$pageTitle = 'Stepper - ' . $config['siteName'];
$pageDescription = 'Stepper form control component';
$currentPage = 'stepper';
$currentSection = 'forms';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Forms', 'url' => 'forms/'],
    ['title' => 'Stepper']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/forms/stepper/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Stepper</strong></div>
                <div class="card-body">
                  <p class="text-body-secondary small">A simple multi-step form built with the Bootstrap Stepper. Each step displays form content and controls navigation through buttons (<code>next</code>, <code>prev</code>, <code>finish</code>).</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/stepper/#example" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <div class="stepper" id="myStepper" data-coreui-toggle="stepper">
                          <ol class="stepper-steps">
                            <li class="stepper-step">
                              <button class="stepper-step-button" type="button" data-coreui-toggle="step" data-coreui-target="#step-1"><span class="stepper-step-indicator">1</span><span class="stepper-step-label">Step 1</span></button>
                            </li>
                            <li class="stepper-step">
                              <button class="stepper-step-button active" type="button" data-coreui-toggle="step" data-coreui-target="#step-2"><span class="stepper-step-indicator">2</span><span class="stepper-step-label">Step 2</span></button>
                            </li>
                            <li class="stepper-step">
                              <button class="stepper-step-button" type="button" data-coreui-toggle="step" data-coreui-target="#step-3"><span class="stepper-step-indicator">3</span><span class="stepper-step-label">Step 3</span></button>
                            </li>
                          </ol>
                          <div class="stepper-content">
                            <div class="stepper-pane" id="step-1">
                              <form class="row g-3 mb-4">
                                <div class="col-md-4">
                                  <label class="form-label" for="horizontalStepper01">First name</label>
                                  <input class="form-control" id="horizontalStepper01" type="text" value="Lukasz">
                                  <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-4">
                                  <label class="form-label" for="horizontalStepper02">Last name</label>
                                  <input class="form-control" id="horizontalStepper02" type="text" value="Holeczek">
                                  <div class="valid-feedback">Looks good!</div>
                                </div>
                                <div class="col-md-4">
                                  <label class="form-label" for="horizontalStepperUsername">Username</label>
                                  <div class="input-group has-validation"><span class="input-group-text" id="inputGroupPrepend">@</span>
                                    <input class="form-control" id="horizontalStepperUsername" type="text" aria-describedby="inputGroupPrepend" required="">
                                    <div class="invalid-feedback">Please choose a username.</div>
                                  </div>
                                </div>
                              </form>
                              <button class="btn btn-primary" data-coreui-stepper-action="next">Next</button>
                            </div>
                            <div class="stepper-pane active show" id="step-2">
                              <form class="row g-3 mb-4">
                                <div class="col-md-6">
                                  <label class="form-label" for="horizontalStepper03">City</label>
                                  <input class="form-control" id="horizontalStepper03" type="text">
                                  <div class="invalid-feedback">Please provide a valid city.</div>
                                </div>
                                <div class="col-md-3">
                                  <label class="form-label" for="horizontalStepper04">State</label>
                                  <select class="form-select" id="horizontalStepper04">
                                    <option selected="" disabled="" value="">Choose...</option>
                                    <option>...</option>
                                  </select>
                                  <div class="invalid-feedback">Please select a valid state.</div>
                                </div>
                                <div class="col-md-3">
                                  <label class="form-label" for="horizontalStepper05">Zip</label>
                                  <input class="form-control" id="horizontalStepper05" type="text">
                                  <div class="invalid-feedback">Please provide a valid zip.</div>
                                </div>
                              </form>
                              <button class="btn btn-secondary" data-coreui-stepper-action="prev">Previous</button>
                              <button class="btn btn-primary" data-coreui-stepper-action="next">Next</button>
                            </div>
                            <div class="stepper-pane" id="step-3">
                              <form class="row g-3 mb-4">
                                <div class="col-md-6">
                                  <label class="form-label" for="horizontalStepper06">Email</label>
                                  <input class="form-control" id="horizontalStepper06" type="email">
                                </div>
                                <div class="col-md-6">
                                  <label class="form-label" for="horizontalStepper07">Password</label>
                                  <input class="form-control" id="horizontalStepper07" type="password">
                                </div>
                                <div class="col-12">
                                  <div class="form-check">
                                    <input class="form-check-input" id="horizontalStepper08" type="checkbox">
                                    <label class="form-check-label" for="horizontalStepper08">Check me out</label>
                                  </div>
                                </div>
                              </form>
                              <button class="btn btn-secondary" data-coreui-stepper-action="prev">Previous</button>
                              <button class="btn btn-success" data-coreui-stepper-action="finish">Finish</button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Stepper</strong><span class="small ms-1">Vertical indicator layout</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Display step indicators vertically above labels using the <code>.vertical</code> modifier. This provides a compact and stylish layout for narrower screens or vertical designs.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/stepper/#vertical-indicator-layout" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <div class="stepper" data-coreui-toggle="stepper">
                          <ol class="stepper-steps">
                            <li class="stepper-step vertical">
                              <button class="stepper-step-button active" type="button" data-coreui-toggle="step"><span class="stepper-step-indicator">1</span><span class="stepper-step-label">Step 1</span></button>
                            </li>
                            <li class="stepper-step vertical">
                              <button class="stepper-step-button" type="button" data-coreui-toggle="step"><span class="stepper-step-indicator">2</span><span class="stepper-step-label">Step 2</span></button>
                            </li>
                            <li class="stepper-step vertical">
                              <button class="stepper-step-button" type="button" data-coreui-toggle="step"><span class="stepper-step-indicator">3</span><span class="stepper-step-label">Step 3</span></button>
                            </li>
                          </ol>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card mb-4">
                <div class="card-header"><strong>Stepper</strong><span class="small ms-1">Vertical layout</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Use the <code>.stepper-vertical</code> class to build a full vertical stepper, where both indicators and step content stack vertically. Ideal for mobile-first designs or top-to-bottom flows.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/stepper/#vertical-layout" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                        <div class="stepper stepper-vertical" data-coreui-toggle="stepper">
                          <ol class="stepper-steps">
                            <li class="stepper-step">
                              <button class="stepper-step-button active" type="button" data-coreui-toggle="step"><span class="stepper-step-indicator">1</span><span class="stepper-step-label">Step 1</span></button>
                              <div class="stepper-step-content">
                                <div class="py-3">
                                  <form class="row g-3 mb-4">
                                    <div class="col-md-4">
                                      <label class="form-label" for="horizontalStepper201">First name</label>
                                      <input class="form-control" id="horizontalStepper201" type="text" value="Lukasz">
                                      <div class="valid-feedback">Looks good!</div>
                                    </div>
                                    <div class="col-md-4">
                                      <label class="form-label" for="horizontalStepper202">Last name</label>
                                      <input class="form-control" id="horizontalStepper202" type="text" value="Holeczek">
                                      <div class="valid-feedback">Looks good!</div>
                                    </div>
                                    <div class="col-md-4">
                                      <label class="form-label" for="horizontalStepper2Username">Username</label>
                                      <div class="input-group has-validation"><span class="input-group-text" id="inputGroupPrepend">@</span>
                                        <input class="form-control" id="horizontalStepper2Username" type="text" aria-describedby="inputGroupPrepend">
                                        <div class="invalid-feedback">Please choose a username.</div>
                                      </div>
                                    </div>
                                  </form>
                                  <button class="btn btn-primary" data-coreui-stepper-action="next">Next</button>
                                </div>
                              </div>
                            </li>
                            <li class="stepper-step">
                              <button class="stepper-step-button" type="button" data-coreui-toggle="step"><span class="stepper-step-indicator">2</span><span class="stepper-step-label">Step 2</span></button>
                              <div class="stepper-step-content">
                                <div class="py-3">
                                  <form class="row g-3 mb-4">
                                    <div class="col-md-6">
                                      <label class="form-label" for="horizontalStepper203">City</label>
                                      <input class="form-control" id="horizontalStepper203" type="text">
                                      <div class="invalid-feedback">Please provide a valid city.</div>
                                    </div>
                                    <div class="col-md-3">
                                      <label class="form-label" for="horizontalStepper204">State</label>
                                      <select class="form-select" id="horizontalStepper204">
                                        <option selected="" disabled="" value="">Choose...</option>
                                        <option>...</option>
                                      </select>
                                      <div class="invalid-feedback">Please select a valid state.</div>
                                    </div>
                                    <div class="col-md-3">
                                      <label class="form-label" for="horizontalStepper205">Zip</label>
                                      <input class="form-control" id="horizontalStepper205" type="text">
                                      <div class="invalid-feedback">Please provide a valid zip.</div>
                                    </div>
                                  </form>
                                  <button class="btn btn-secondary" data-coreui-stepper-action="prev">Previous</button>
                                  <button class="btn btn-primary" data-coreui-stepper-action="next">Next</button>
                                </div>
                              </div>
                            </li>
                            <li class="stepper-step">
                              <button class="stepper-step-button" type="button" data-coreui-toggle="step"><span class="stepper-step-indicator">3</span><span class="stepper-step-label">Step 3</span></button>
                              <div class="stepper-step-content">
                                <div class="pt-3">
                                  <form class="row g-3 mb-4">
                                    <div class="col-md-6">
                                      <label class="form-label" for="horizontalStepper206">Email</label>
                                      <input class="form-control" id="horizontalStepper206" type="email">
                                    </div>
                                    <div class="col-md-6">
                                      <label class="form-label" for="horizontalStepper207">Password</label>
                                      <input class="form-control" id="horizontalStepper207" type="password">
                                    </div>
                                    <div class="col-12">
                                      <div class="form-check">
                                        <input class="form-check-input" id="horizontalStepper208" type="checkbox">
                                        <label class="form-check-label" for="horizontalStepper208">Check me out</label>
                                      </div>
                                    </div>
                                  </form>
                                  <button class="btn btn-secondary" data-coreui-stepper-action="prev">Previous</button>
                                  <button class="btn btn-success" data-coreui-stepper-action="finish">Finish</button>
                                </div>
                              </div>
                            </li>
                          </ol>
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