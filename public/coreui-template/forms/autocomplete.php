<?php
require_once '../includes/config.php';

$pageTitle = 'Autocomplete - ' . $config['siteName'];
$pageDescription = 'Form autocomplete input component';
$currentPage = 'autocomplete';
$currentSection = 'forms';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Forms', 'url' => 'forms/'],
    ['title' => 'Autocomplete']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/forms/autocomplete/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>CoreUI Autocomplete</strong><span class="small ms-1">Basic example</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <p class="text-body-secondary small">A straightforward demonstration of how to implement a basic Bootstrap Autocomplete input field, highlighting essential attributes and configurations.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/autocomplete/#basic-example" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <div class="mb-3">
                          <label class="form-label" for="autocomplete1">Programming Languages</label>
                          <div id="autocomplete1" data-coreui-toggle="autocomplete" data-coreui-cleaner="true" data-coreui-highlight-options-on-search="true" data-coreui-indicator="true" data-coreui-options="JavaScript, TypeScript, Python, Java, C#, C++, Go, Rust, Swift, Kotlin" data-coreui-placeholder="Type to search...." data-coreui-search="global" data-coreui-search-no-results-label="No results found" data-coreui-show-hints="true" data-coreui-value="React.js"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>CoreUI Autocomplete</strong><span class="small ms-1">With hints</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Enable hints by adding <code>data-coreui-show-hints="true"</code> to show completion suggestions as you type.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/autocomplete/#show-hints" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <div class="mb-3">
                          <label class="form-label" for="autocomplete2">Frameworks</label>
                          <div id="autocomplete2" data-coreui-toggle="autocomplete" data-coreui-options="Angular, Bootstrap, React.js, Vue.js, Svelte, Next.js, Nuxt.js, jQuery" data-coreui-placeholder="Search frameworks..." data-coreui-show-hints="true"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>CoreUI Autocomplete</strong><span class="small ms-1">With cleaner button</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Add a clear button by setting <code>data-coreui-cleaner="true"</code>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/autocomplete/#cleaner-functionality" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                        <div class="mb-3">
                          <label class="form-label" for="autocomplete3">Countries</label>
                          <div id="autocomplete3" data-coreui-toggle="autocomplete" data-coreui-options="United States, Canada, United Kingdom, Germany, France, Italy, Spain, Poland, Netherlands, Belgium" data-coreui-placeholder="Select country..." data-coreui-cleaner="true"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>CoreUI Autocomplete</strong><span class="small ms-1">Highlight matching text</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Enable text highlighting in dropdown options with <code>data-coreui-highlight-options-on-search="true"</code>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/autocomplete/#highlight-matching-text" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003">
                        <div class="mb-3">
                          <label class="form-label" for="autocomplete4">Cities</label>
                          <div id="autocomplete4" data-coreui-toggle="autocomplete" data-coreui-options="New York, Los Angeles, Chicago, Houston, Phoenix, Philadelphia, San Antonio, San Diego, Dallas, San Jose" data-coreui-placeholder="Find city..." data-coreui-highlight-options-on-search="true"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>CoreUI Autocomplete</strong><span class="small ms-1">Multiple configurations</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Combine multiple features for enhanced functionality.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1004" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/autocomplete/" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1004">
                        <form>
                          <div class="row">
                            <div class="col-md-6 mb-3">
                              <label class="form-label" for="autocomplete6">Technologies (with hints &amp; cleaner)</label>
                              <div id="autocomplete6" data-coreui-toggle="autocomplete" data-coreui-options="HTML, CSS, JavaScript, TypeScript, React, Vue.js, Angular, Node.js, Express, MongoDB, PostgreSQL, Redis" data-coreui-placeholder="Choose technology..." data-coreui-show-hints="true" data-coreui-cleaner="true" data-coreui-highlight-options-on-search="true"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                              <label class="form-label" for="autocomplete7">Colors</label>
                              <div id="autocomplete7" data-coreui-toggle="autocomplete" data-coreui-options="Red, Green, Blue, Yellow, Orange, Purple, Pink, Cyan, Magenta, Lime" data-coreui-placeholder="Pick a color..." data-coreui-cleaner="true"></div>
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
                <div class="card-header"><strong>CoreUI Autocomplete</strong><span class="small ms-1">Validation states</span><a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI PRO Component</a></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Apply validation states using Bootstrap's validation classes.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1005" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/autocomplete/" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1005">
                        <form>
                          <div class="row">
                            <div class="col-md-4 mb-3">
                              <label class="form-label" for="autocomplete-valid">Valid input</label>
                              <div class="is-valid" id="autocomplete-valid" data-coreui-toggle="autocomplete" data-coreui-options="Option 1, Option 2, Option 3, Option 4" data-coreui-placeholder="Valid state..." data-coreui-valid="true"></div>
                              <div class="valid-feedback">Valid choice!</div>
                            </div>
                            <div class="col-md-4 mb-3">
                              <label class="form-label" for="autocomplete-invalid">Invalid input</label>
                              <div class="is-invalid" id="autocomplete-invalid" data-coreui-toggle="autocomplete" data-coreui-options="Option 1, Option 2, Option 3, Option 4" data-coreui-placeholder="Invalid state..." data-coreui-invalid="true"></div>
                              <div class="invalid-feedback">Please select a valid option.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                              <label class="form-label" for="autocomplete-disabled">Disabled</label>
                              <div id="autocomplete-disabled" data-coreui-toggle="autocomplete" data-coreui-options="Option 1, Option 2, Option 3, Option 4" data-coreui-placeholder="Disabled state..." data-coreui-disabled="true"></div>
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