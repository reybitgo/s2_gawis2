<?php
require_once '../includes/config.php';

$pageTitle = 'Carousel - ' . $config['siteName'];
$pageDescription = 'Carousel component examples for creating slideshow components with images, controls, indicators, and captions.';
$currentPage = 'carousel';
$currentSection = 'base';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'Base', 'url' => 'base/'],
    ['title' => 'Carousel']
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
              <div class="col-md-auto col-12 mt-3 mt-lg-0"><a class="btn btn-primary text-nowrap text-white" href="https://coreui.io/bootstrap/docs/components/carousel/" target="_blank" rel="noopener noreferrer">Explore Documentation</a></div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Carousel</strong><span class="small ms-1">Slides only</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Here's a carousel with slides. Note the appearance of the <code>.d-block</code> also, <code>.w-100</code> on carousel images to override browser default image alignment.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/carousel/#slides-only" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                    <div class="carousel slide" id="carouselExampleSlidesOnly" data-coreui-ride="carousel">
                      <div class="carousel-inner">
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: First slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#777"></rect>
                            <text x="50%" y="50%" fill="#555" dy=".3em">First slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Second slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#666"></rect>
                            <text x="50%" y="50%" fill="#444" dy=".3em">Second slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item active">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Third slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#555"></rect>
                            <text x="50%" y="50%" fill="#333" dy=".3em">Third slide</text>
                          </svg>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Carousel</strong><span class="small ms-1">With controls</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Adding in the previous and next controls. We recommend using <code> &lt;button&gt;</code> elements, but you can also use <code> &lt;a&gt;</code> elements with <code>role="button"</code>.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1001" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/carousel/#with-controls" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1001">
                    <div class="carousel slide" id="carouselExampleControls" data-coreui-ride="carousel">
                      <div class="carousel-inner">
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: First slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#777"></rect>
                            <text x="50%" y="50%" fill="#555" dy=".3em">First slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Second slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#666"></rect>
                            <text x="50%" y="50%" fill="#444" dy=".3em">Second slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item active">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Third slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#555"></rect>
                            <text x="50%" y="50%" fill="#333" dy=".3em">Third slide</text>
                          </svg>
                        </div>
                      </div>
                      <button class="carousel-control-prev" type="button" data-coreui-target="#carouselExampleControls" data-coreui-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                      <button class="carousel-control-next" type="button" data-coreui-target="#carouselExampleControls" data-coreui-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Carousel</strong><span class="small ms-1">With indicators</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">You can attach the indicators to the carousel, lengthwise the controls, too.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1002" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/carousel/#with-indicators" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1002">
                    <div class="carousel slide" id="carouselExampleIndicators" data-coreui-ride="carousel">
                      <div class="carousel-indicators">
                        <button type="button" data-coreui-target="#carouselExampleIndicators" data-coreui-slide-to="0" aria-label="Slide 1"></button>
                        <button type="button" data-coreui-target="#carouselExampleIndicators" data-coreui-slide-to="1" aria-label="Slide 2"></button>
                        <button class="active" type="button" data-coreui-target="#carouselExampleIndicators" data-coreui-slide-to="2" aria-label="Slide 3" aria-current="true"></button>
                      </div>
                      <div class="carousel-inner">
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: First slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#777"></rect>
                            <text x="50%" y="50%" fill="#555" dy=".3em">First slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Second slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#666"></rect>
                            <text x="50%" y="50%" fill="#444" dy=".3em">Second slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item active">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Third slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#555"></rect>
                            <text x="50%" y="50%" fill="#333" dy=".3em">Third slide</text>
                          </svg>
                        </div>
                      </div>
                      <button class="carousel-control-prev" type="button" data-coreui-target="#carouselExampleIndicators" data-coreui-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                      <button class="carousel-control-next" type="button" data-coreui-target="#carouselExampleIndicators" data-coreui-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Carousel</strong><span class="small ms-1">With captions</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">You can add captions to slides with the <code>.carousel-caption</code> element within any <code>.carousel-item</code>. They can be immediately hidden on smaller viewports, as shown below, with optional <a href="https://coreui.io/bootstrap/docs/utilities/display/">display utilities</a>. We hide them with <code>.d-none</code> and draw them back on medium-sized devices with <code>.d-md-block</code>.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1003" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/carousel/#with-captions" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1003">
                    <div class="carousel slide" id="carouselExampleCaptions" data-coreui-ride="carousel">
                      <div class="carousel-indicators">
                        <button type="button" data-coreui-target="#carouselExampleCaptions" data-coreui-slide-to="0" aria-label="Slide 1"></button>
                        <button type="button" data-coreui-target="#carouselExampleCaptions" data-coreui-slide-to="1" aria-label="Slide 2"></button>
                        <button class="active" type="button" data-coreui-target="#carouselExampleCaptions" data-coreui-slide-to="2" aria-label="Slide 3" aria-current="true"></button>
                      </div>
                      <div class="carousel-inner">
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: First slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#777"></rect>
                            <text x="50%" y="50%" fill="#555" dy=".3em">First slide</text>
                          </svg>
                          <div class="carousel-caption d-none d-md-block">
                            <h5>First slide label</h5>
                            <p>Some representative placeholder content for the first slide.</p>
                          </div>
                        </div>
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Second slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#666"></rect>
                            <text x="50%" y="50%" fill="#444" dy=".3em">Second slide</text>
                          </svg>
                          <div class="carousel-caption d-none d-md-block">
                            <h5>Second slide label</h5>
                            <p>Some representative placeholder content for the second slide.</p>
                          </div>
                        </div>
                        <div class="carousel-item active">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Third slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#555"></rect>
                            <text x="50%" y="50%" fill="#333" dy=".3em">Third slide</text>
                          </svg>
                          <div class="carousel-caption d-none d-md-block">
                            <h5>Third slide label</h5>
                            <p>Some representative placeholder content for the third slide.</p>
                          </div>
                        </div>
                      </div>
                      <button class="carousel-control-prev" type="button" data-coreui-target="#carouselExampleCaptions" data-coreui-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                      <button class="carousel-control-next" type="button" data-coreui-target="#carouselExampleCaptions" data-coreui-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Carousel</strong><span class="small ms-1">Crossfade</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Add <code>.carousel-fade</code> to your carousel to animate slides with a fade transition instead of a slide.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1004" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/carousel/#crossfade" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1004">
                    <div class="carousel slide carousel-fade" id="carouselExampleFade" data-coreui-ride="carousel">
                      <div class="carousel-inner">
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: First slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#777"></rect>
                            <text x="50%" y="50%" fill="#555" dy=".3em">First slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Second slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#666"></rect>
                            <text x="50%" y="50%" fill="#444" dy=".3em">Second slide</text>
                          </svg>
                        </div>
                        <div class="carousel-item active">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Third slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#555"></rect>
                            <text x="50%" y="50%" fill="#333" dy=".3em">Third slide</text>
                          </svg>
                        </div>
                      </div>
                      <button class="carousel-control-prev" type="button" data-coreui-target="#carouselExampleFade" data-coreui-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                      <button class="carousel-control-next" type="button" data-coreui-target="#carouselExampleFade" data-coreui-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card mb-4">
            <div class="card-header"><strong>Carousel</strong><span class="small ms-1">Dark variant</span></div>
            <div class="card-body">
              <p class="text-body-secondary small">Add <code>.carousel-dark</code> to the <code>.carousel</code> for darker controls, indicators, and captions. Controls have been inverted from their default white fill with the <code>filter</code> CSS property. Captions and controls have additional Sass variables that customize the <code>color</code> and <code>background-color</code>.</p>
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1005" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://coreui.io/bootstrap/docs/components/carousel/#dark-variant" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1005">
                    <div class="carousel carousel-dark slide" id="carouselExampleDark" data-coreui-ride="carousel">
                      <div class="carousel-indicators">
                        <button type="button" data-coreui-target="#carouselExampleDark" data-coreui-slide-to="0" aria-label="Slide 1"></button>
                        <button type="button" data-coreui-target="#carouselExampleDark" data-coreui-slide-to="1" aria-label="Slide 2"></button>
                        <button class="active" type="button" data-coreui-target="#carouselExampleDark" data-coreui-slide-to="2" aria-label="Slide 3" aria-current="true"></button>
                      </div>
                      <div class="carousel-inner">
                        <div class="carousel-item" data-coreui-interval="10000">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: First slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#f5f5f5"></rect>
                            <text x="50%" y="50%" fill="#aaa" dy=".3em">First slide</text>
                          </svg>
                          <div class="carousel-caption d-none d-md-block">
                            <h5>First slide label</h5>
                            <p>Some representative placeholder content for the first slide.</p>
                          </div>
                        </div>
                        <div class="carousel-item" data-coreui-interval="2000">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Second slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#eee"></rect>
                            <text x="50%" y="50%" fill="#bbb" dy=".3em">Second slide</text>
                          </svg>
                          <div class="carousel-caption d-none d-md-block">
                            <h5>Second slide label</h5>
                            <p>Some representative placeholder content for the second slide.</p>
                          </div>
                        </div>
                        <div class="carousel-item active">
                          <svg class="docs-placeholder-img docs-placeholder-img-lg d-block w-100" width="800" height="400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: Third slide" preserveaspectratio="xMidYMid slice" focusable="false">
                            <title>Placeholder</title>
                            <rect width="100%" height="100%" fill="#e5e5e5"></rect>
                            <text x="50%" y="50%" fill="#999" dy=".3em">Third slide</text>
                          </svg>
                          <div class="carousel-caption d-none d-md-block">
                            <h5>Third slide label</h5>
                            <p>Some representative placeholder content for the third slide.</p>
                          </div>
                        </div>
                      </div>
                      <button class="carousel-control-prev" type="button" data-coreui-target="#carouselExampleDark" data-coreui-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></button>
                      <button class="carousel-control-next" type="button" data-coreui-target="#carouselExampleDark" data-coreui-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
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