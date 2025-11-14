<?php
require_once 'includes/config.php';

$pageTitle = 'OpenStreetMap - ' . $config['siteName'];
$pageDescription = 'OpenStreetMap integration and components';
$currentPage = 'maps';
$currentSection = '';

$additionalCSS = [
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
];
$additionalJS = [
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    'js/openstreetmap.js'
];

$breadcrumbs = [
    ['title' => 'OpenStreetMap']
];

include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include 'includes/header.php'; ?>
      <div class="body flex-grow-1">
        <div class="container-lg px-4">
          <div class="card mb-4">
            <div class="card-header">
              OpenStreetMap<a
                class="badge bg-danger-gradient ms-2 text-decoration-none"
                href="https://coreui.io/pricing/?framework=bootstrap"
                >CoreUI Pro Integration</a
              >
            </div>
            <div class="card-body">
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item">
                    <a
                      class="nav-link active"
                      data-coreui-toggle="tab"
                      href="#preview-1000"
                      role="tab"
                    >
                      <svg class="icon me-2">
                        <use
                          xlink:href="vendors/@coreui/icons/svg/free.svg#cil-media-play"
                        ></use></svg
                      >Preview</a
                    >
                  </li>
                  <li class="nav-item">
                    <a
                      class="nav-link"
                      href="https://leafletjs.com/examples.html"
                      target="_blank"
                    >
                      <svg class="icon me-2">
                        <use
                          xlink:href="vendors/@coreui/icons/svg/free.svg#cil-code"
                        ></use></svg
                      >Code</a
                    >
                  </li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div
                    class="tab-pane p-3 active preview"
                    role="tabpanel"
                    id="preview-1000"
                  >
                    <div id="map" style="height: 560px">
                      <div
                        style="
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          height: 100%;
                          background-color: #f8f9fa;
                          border: 1px dashed #dee2e6;
                        "
                      >
                        <div style="text-align: center; color: #6c757d">
                          <div
                            class="spinner-border text-primary"
                            role="status"
                          >
                            <span class="visually-hidden">Loading...</span>
                          </div>
                          <p class="mt-3">Loading OpenStreetMap...</p>
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
<?php include 'includes/footer.php'; ?>
    </div>

<?php include 'includes/scripts.php'; ?>