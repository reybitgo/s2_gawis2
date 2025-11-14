<?php
require_once '../includes/config.php';

$pageTitle = 'Form Control - ' . $config['siteName'];
$pageDescription = 'Bootstrap form control components';
$currentPage = 'form-control';
$currentSection = 'forms';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Forms', 'url' => 'forms/'],
    ['title' => 'Form Control']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/forms/form-control/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">Basic example</span></div>
                <div class="card-body">
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                        <div class="mb-3">
                          <label class="form-label" for="exampleFormControlInput1">Email address</label>
                          <input class="form-control" id="exampleFormControlInput1" type="email" placeholder="name@example.com">
                        </div>
                        <div class="mb-3">
                          <label class="form-label" for="exampleFormControlTextarea1">Example textarea</label>
                          <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">Sizing</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Set heights using classes like <code>.form-control-lg</code> and <code>.form-control-sm</code>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#sizing" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                        <input class="form-control form-control-lg" type="text" placeholder=".form-control-lg" aria-label=".form-control-lg example">
                        <input class="form-control" type="text" placeholder="Default input" aria-label="default input example">
                        <input class="form-control form-control-sm" type="text" placeholder=".form-control-sm" aria-label=".form-control-sm example">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">Disabled</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Add the <code>disabled</code> boolean attribute on an input to give it a grayed out appearance and remove pointer events.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#disabled" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                        <input class="form-control" type="text" placeholder="Disabled input" aria-label="Disabled input example" disabled="">
                        <input class="form-control" type="text" placeholder="Disabled readonly input" aria-label="Disabled input example" disabled="" readonly="">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">Readonly</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Add the <code>readonly</code> boolean attribute on an input to prevent modification of the input’s value. Read-only inputs appear lighter (just like disabled inputs), but retain the standard cursor.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#readonly" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003">
                        <input class="form-control" type="text" placeholder="Readonly input here..." aria-label="readonly input example" readonly="">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">Readonly plain text</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">If you want to have <code>&lt;input readonly&gt;</code> elements in your form styled as plain text, use the <code>.form-control-plaintext</code> class to remove the default form field styling and preserve the correct margin and padding.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1004" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#readonly-plain-text" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1004">
                        <div class="mb-3 row">
                          <label class="col-sm-2 col-form-label" for="staticEmail">Email</label>
                          <div class="col-sm-10">
                            <input class="form-control-plaintext" id="staticEmail" type="text" readonly="" value="email@example.com">
                          </div>
                        </div>
                        <div class="mb-3 row">
                          <label class="col-sm-2 col-form-label" for="inputPassword">Password</label>
                          <div class="col-sm-10">
                            <input class="form-control" id="inputPassword" type="password">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1005" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#readonly-plain-text" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1005">
                        <form class="row g-3">
                          <div class="col-auto">
                            <label class="visually-hidden" for="staticEmail2">Email</label>
                            <input class="form-control-plaintext" id="staticEmail2" type="text" readonly="" value="email@example.com">
                          </div>
                          <div class="col-auto">
                            <label class="visually-hidden" for="inputPassword2">Password</label>
                            <input class="form-control" id="inputPassword2" type="password" placeholder="Password">
                          </div>
                          <div class="col-auto"></div>
                        </form>
                        <button class="btn btn-primary mb-3" type="submit">Confirm identity</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">File input</span></div>
                <div class="card-body">
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1006" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#file-input" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1006">
                        <div class="mb-3">
                          <label class="form-label" for="formFile">Default file input example</label>
                          <input class="form-control" id="formFile" type="file">
                        </div>
                        <div class="mb-3">
                          <label class="form-label" for="formFileMultiple">Multiple files input example</label>
                          <input class="form-control" id="formFileMultiple" type="file" multiple="">
                        </div>
                        <div class="mb-3">
                          <label class="form-label" for="formFileDisabled">Disabled file input example</label>
                          <input class="form-control" id="formFileDisabled" type="file" disabled="">
                        </div>
                        <div class="mb-3">
                          <label class="form-label" for="formFileSm">Small file input example</label>
                          <input class="form-control form-control-sm" id="formFileSm" type="file">
                        </div>
                        <div>
                          <label class="form-label" for="formFileLg">Large file input example</label>
                          <input class="form-control form-control-lg" id="formFileLg" type="file">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">Color</span></div>
                <div class="card-body">
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1007" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#color" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1007">
                        <label class="form-label" for="exampleColorInput">Color picker</label>
                        <input class="form-control form-control-color" id="exampleColorInput" type="color" value="#563d7c" title="Choose your color">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card mb-4">
                <div class="card-header"><strong>Form control</strong><span class="small ms-1">Datalists</span></div>
                <div class="card-body">
                  <p class="text-body-secondary small">Datalists allow you to create a group of <code>&lt;option&gt;</code>s that can be accessed (and autocompleted) from within an <code>&lt;input&gt;</code>. These are similar to <code>&lt;select&gt;</code> elements, but come with more menu styling limitations and differences. While most browsers and operating systems include some support for <code>&lt;datalist&gt;</code> elements, their styling is inconsistent at best.</p>
                  <p class="text-body-secondary small">Learn more about <a href="https://caniuse.com/datalist">support for datalist elements</a>.</p>
                  <div class="example">
                    <ul class="nav nav-underline-border" role="tablist">
                      <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1008" role="tab">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                          </svg>Preview</a></li>
                      <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/forms/form-control/#datalists" target="_blank">
                          <svg class="icon me-2">
                            <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                          </svg>Code</a></li>
                    </ul>
                    <div class="tab-content rounded-bottom">
                      <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1008">
                        <label class="form-label" for="exampleDataList">Datalist example</label>
                        <input class="form-control" id="exampleDataList" list="datalistOptions" placeholder="Type to search...">
                        <datalist id="datalistOptions">
                          <option value="San Francisco"></option>
                          <option value="New York"></option>
                          <option value="Seattle"></option>
                          <option value="Los Angeles"></option>
                          <option value="Chicago"></option>
                        </datalist>
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