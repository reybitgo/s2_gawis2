<?php
// Page configuration
require_once 'includes/config.php';

$pageTitle = 'Dashboard - ' . $config['siteName'];
$pageDescription = 'CoreUI Bootstrap Admin Dashboard';
$currentPage = 'index';
$currentSection = '';

// Additional JavaScript files for this page
$additionalJS = [
    'vendors/chart.js/js/chart.umd.js',
    'vendors/@coreui/chartjs/js/coreui-chartjs.js',
    'vendors/@coreui/utils/js/index.js',
    'js/main.js'
];

// Breadcrumbs (empty for dashboard as it's the home page)
$breadcrumbs = [];

// Include the head
include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>
    <div
      class="sidebar sidebar-light sidebar-lg sidebar-end sidebar-overlaid border-start"
      id="aside"
    >
      <div class="sidebar-header p-0 position-relative">
        <ul class="nav nav-underline-border" role="tablist">
          <li class="nav-item">
            <a
              class="nav-link active"
              data-coreui-toggle="tab"
              href="#timeline"
              role="tab"
            >
              <svg class="icon">
                <use
                  xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-list"
                ></use>
              </svg>
            </a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              data-coreui-toggle="tab"
              href="#messages"
              role="tab"
            >
              <svg class="icon">
                <use
                  xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-speech"
                ></use>
              </svg>
            </a>
          </li>
          <li class="nav-item">
            <a
              class="nav-link"
              data-coreui-toggle="tab"
              href="#settings"
              role="tab"
            >
              <svg class="icon">
                <use
                  xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-settings"
                ></use>
              </svg>
            </a>
          </li>
        </ul>
        <button
          class="btn-close position-absolute top-50 end-0 translate-middle my-0 me-2 btn-close-white d-lg-none"
          type="button"
          aria-label="Close"
          onclick='coreui.Sidebar.getInstance(document.querySelector("#aside")).hide()'
        ></button>
      </div>
      <!-- Tab content-->
      <div class="tab-content">
        <div class="tab-pane active" id="timeline" role="tabpanel">
          <div class="list-group list-group-flush">
            <div
              class="list-group-item border-start-4 border-start-secondary bg-body-tertiary text-center fw-bold text-body-secondary text-uppercase small"
              data-coreui-i18n="today"
            >
              Today
            </div>
            <div class="list-group-item border-start-4 border-start-warning">
              <div class="avatar avatar-lg float-end">
                <img
                  class="avatar-img"
                  src="<?= $baseDir ?>assets/img/avatars/7.jpg"
                  alt="user@email.com"
                />
              </div>
              <div data-coreui-i18n="meetingWithHR">
                Meeting with <strong>Lucas</strong>
              </div>
              <small class="text-body-secondary me-3">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-calendar"
                  ></use>
                </svg>
                &nbsp; 1 - 3pm
              </small>
              <small class="text-body-secondary">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-location-pin"
                  ></use>
                </svg>
                &nbsp; Palo Alto, CA
              </small>
            </div>
            <div class="list-group-item border-start-4 border-start-info">
              <div class="avatar avatar-lg float-end">
                <img
                  class="avatar-img"
                  src="<?= $baseDir ?>assets/img/avatars/4.jpg"
                  alt="user@email.com"
                />
              </div>
              <div data-coreui-i18n="skypeCallWithTHE">
                Skype call with <strong>Megan</strong>
              </div>
              <small class="text-body-secondary me-3">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-calendar"
                  ></use>
                </svg>
                &nbsp; 4 - 5pm
              </small>
              <small class="text-body-secondary">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/brand.svg#cib-skype"
                  ></use>
                </svg>
                &nbsp; On-line
              </small>
            </div>
            <div
              class="list-group-item border-start-4 border-start-secondary bg-body-tertiary text-center fw-bold text-body-secondary text-uppercase small"
              data-coreui-i18n="tomorrow"
            >
              Tomorrow
            </div>
            <div class="list-group-item border-start-4 border-start-danger">
              <div data-coreui-i18n="newUIProjectKickoff">
                New UI Project - <strong>deadline</strong>
              </div>
              <small class="text-body-secondary me-3">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-calendar"
                  ></use>
                </svg>
                &nbsp; 10 - 11pm
              </small>
              <small class="text-body-secondary">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-home"
                  ></use>
                </svg>
                &nbsp; creativeLabs HQ
              </small>
              <div class="avatars-stack mt-2">
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/2.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/3.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/4.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/5.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/6.jpg"
                    alt="user@email.com"
                  />
                </div>
              </div>
            </div>
            <div class="list-group-item border-start-4 border-start-success">
              <div>
                <strong data-coreui-i18n="HappyHour">#10 Startups.Garden</strong>
                Meetup
              </div>
              <small class="text-body-secondary me-3">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-calendar"
                  ></use>
                </svg>
                &nbsp; 1 - 3pm
              </small>
              <small class="text-body-secondary">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-location-pin"
                  ></use>
                </svg>
                &nbsp; Palo Alto, CA
              </small>
            </div>
            <div class="list-group-item border-start-4 border-start-primary">
              <div>
                <strong data-coreui-i18n="teamStandup">Team Standup</strong>
              </div>
              <small class="text-body-secondary me-3">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-calendar"
                  ></use>
                </svg>
                &nbsp; 9 - 10am
              </small>
              <small class="text-body-secondary">
                <svg class="icon">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-location-pin"
                  ></use>
                </svg>
                &nbsp; Palo Alto, CA
              </small>
              <div class="avatars-stack mt-2">
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/2.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/3.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/4.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/5.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/6.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/7.jpg"
                    alt="user@email.com"
                  />
                </div>
                <div class="avatar avatar-xs">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/8.jpg"
                    alt="user@email.com"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-pane p-3" id="messages" role="tabpanel">
          <div class="message">
            <div class="py-3 pb-5 me-3 float-start">
              <div class="avatar">
                <img
                  class="avatar-img"
                  src="<?= $baseDir ?>assets/img/avatars/7.jpg"
                  alt="user@email.com"
                />
                <span class="avatar-status bg-success"></span>
              </div>
            </div>
            <div>
              <small class="text-body-secondary" data-coreui-i18n="lukaszHoleczek"
                >Lukasz Holeczek</small
              >
              <small class="text-body-secondary float-end mt-1">1:52 PM</small>
            </div>
            <div class="text-truncate fw-bold" data-coreui-i18n="loremIpsumDolorSitAmetConsecteturAdipisicing">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt...
            </div>
            <small class="text-body-secondary" data-coreui-i18n="loremIpsumDolorSitAmet">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </small>
          </div>
          <hr />
          <div class="message">
            <div class="py-3 pb-5 me-3 float-start">
              <div class="avatar">
                <img
                  class="avatar-img"
                  src="<?= $baseDir ?>assets/img/avatars/7.jpg"
                  alt="user@email.com"
                />
                <span class="avatar-status bg-success"></span>
              </div>
            </div>
            <div>
              <small class="text-body-secondary" data-coreui-i18n="lukaszHoleczek"
                >Lukasz Holeczek</small
              >
              <small class="text-body-secondary float-end mt-1">1:52 PM</small>
            </div>
            <div class="text-truncate fw-bold" data-coreui-i18n="loremIpsumDolorSitAmetConsecteturAdipisicing">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt...
            </div>
            <small class="text-body-secondary" data-coreui-i18n="loremIpsumDolorSitAmet">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </small>
          </div>
          <hr />
          <div class="message">
            <div class="py-3 pb-5 me-3 float-start">
              <div class="avatar">
                <img
                  class="avatar-img"
                  src="<?= $baseDir ?>assets/img/avatars/7.jpg"
                  alt="user@email.com"
                />
                <span class="avatar-status bg-success"></span>
              </div>
            </div>
            <div>
              <small class="text-body-secondary" data-coreui-i18n="lukaszHoleczek"
                >Lukasz Holeczek</small
              >
              <small class="text-body-secondary float-end mt-1">1:52 PM</small>
            </div>
            <div class="text-truncate fw-bold" data-coreui-i18n="loremIpsumDolorSitAmetConsecteturAdipisicing">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt...
            </div>
            <small class="text-body-secondary" data-coreui-i18n="loremIpsumDolorSitAmet">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </small>
          </div>
          <hr />
          <div class="message">
            <div class="py-3 pb-5 me-3 float-start">
              <div class="avatar">
                <img
                  class="avatar-img"
                  src="<?= $baseDir ?>assets/img/avatars/7.jpg"
                  alt="user@email.com"
                />
                <span class="avatar-status bg-success"></span>
              </div>
            </div>
            <div>
              <small class="text-body-secondary" data-coreui-i18n="lukaszHoleczek"
                >Lukasz Holeczek</small
              >
              <small class="text-body-secondary float-end mt-1">1:52 PM</small>
            </div>
            <div class="text-truncate fw-bold" data-coreui-i18n="loremIpsumDolorSitAmetConsecteturAdipisicing">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt...
            </div>
            <small class="text-body-secondary" data-coreui-i18n="loremIpsumDolorSitAmet">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </small>
          </div>
          <hr />
          <div class="message">
            <div class="py-3 pb-5 me-3 float-start">
              <div class="avatar">
                <img
                  class="avatar-img"
                  src="<?= $baseDir ?>assets/img/avatars/7.jpg"
                  alt="user@email.com"
                />
                <span class="avatar-status bg-success"></span>
              </div>
            </div>
            <div>
              <small class="text-body-secondary" data-coreui-i18n="lukaszHoleczek"
                >Lukasz Holeczek</small
              >
              <small class="text-body-secondary float-end mt-1">1:52 PM</small>
            </div>
            <div class="text-truncate fw-bold" data-coreui-i18n="loremIpsumDolorSitAmetConsecteturAdipisicing">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt...
            </div>
            <small class="text-body-secondary" data-coreui-i18n="loremIpsumDolorSitAmet">
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
              eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </small>
          </div>
        </div>
        <div class="tab-pane p-3" id="settings" role="tabpanel">
          <h6 data-coreui-i18n="settings">Settings</h6>
          <div class="aside-options">
            <div class="clearfix mt-4">
              <small><b data-coreui-i18n="option1">Option 1</b></small>
              <label class="switch switch-label switch-outline-primary switch-pill switch-sm float-end">
                <input class="switch-input" type="checkbox" checked="" />
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
              </label>
            </div>
            <div>
              <small class="text-body-secondary" data-coreui-i18n="loremIpsumDolorSitAmetConsecteturAdipisicingElit">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
                eiusmod tempor incididunt ut labore et dolore magna aliqua.
              </small>
            </div>
          </div>
          <div class="aside-options">
            <div class="clearfix mt-3">
              <small><b data-coreui-i18n="option2">Option 2</b></small>
              <label class="switch switch-label switch-outline-primary switch-pill switch-sm float-end">
                <input class="switch-input" type="checkbox" />
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
              </label>
            </div>
            <div>
              <small class="text-body-secondary" data-coreui-i18n="loremIpsumDolorSitAmetConsecteturAdipisicingElit">
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
                eiusmod tempor incididunt ut labore et dolore magna aliqua.
              </small>
            </div>
          </div>
          <div class="aside-options">
            <div class="clearfix mt-3">
              <small><b data-coreui-i18n="option3">Option 3</b></small>
              <label class="switch switch-label switch-outline-primary switch-pill switch-sm float-end">
                <input class="switch-input" type="checkbox" />
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
              </label>
            </div>
          </div>
          <div class="aside-options">
            <div class="clearfix mt-3">
              <small><b data-coreui-i18n="option4">Option 4</b></small>
              <label class="switch switch-label switch-outline-primary switch-pill switch-sm float-end">
                <input class="switch-input" type="checkbox" checked="" />
                <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
              </label>
            </div>
          </div>
          <hr />
          <h6 data-coreui-i18n="systemUtilization">System Utilization</h6>
          <div class="text-uppercase mb-1 mt-4">
            <small><b>CPU Usage</b></small>
          </div>
          <div class="progress progress-thin">
            <div
              class="progress-bar bg-info"
              role="progressbar"
              style="width: 25%"
              aria-valuenow="25"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
          <small class="text-body-secondary">348 Processes. 1/4 Cores.</small>
          <div class="text-uppercase mb-1 mt-2">
            <small><b>Memory Usage</b></small>
          </div>
          <div class="progress progress-thin">
            <div
              class="progress-bar bg-warning"
              role="progressbar"
              style="width: 70%"
              aria-valuenow="70"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
          <small class="text-body-secondary">11444GB/16384MB</small>
          <div class="text-uppercase mb-1 mt-2">
            <small><b>SSD 1 Usage</b></small>
          </div>
          <div class="progress progress-thin">
            <div
              class="progress-bar bg-danger"
              role="progressbar"
              style="width: 95%"
              aria-valuenow="95"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
          <small class="text-body-secondary">243GB/256GB</small>
          <div class="text-uppercase mb-1 mt-2">
            <small><b>SSD 2 Usage</b></small>
          </div>
          <div class="progress progress-thin">
            <div
              class="progress-bar bg-success"
              role="progressbar"
              style="width: 10%"
              aria-valuenow="10"
              aria-valuemin="0"
              aria-valuemax="100"
            ></div>
          </div>
          <small class="text-body-secondary">25GB/256GB</small>
        </div>
      </div>
    </div>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include 'includes/header.php'; ?>
      <div class="body flex-grow-1">
        <div class="container-lg h-auto px-4">
          <div class="row mb-4">
            <div class="col-sm-6 col-xl-3">
              <div class="card text-white bg-primary-gradient">
                <div
                  class="card-body pb-0 d-flex justify-content-between align-items-start"
                >
                  <div>
                    <div class="fs-4 fw-semibold">
                      26K
                      <span class="fs-6 fw-normal"
                        >(-12.4%
                        <svg class="icon">
                          <use
                            xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-arrow-bottom"
                          ></use>
                        </svg>
                        )</span
                      >
                    </div>
                    <div data-coreui-i18n="users">Users</div>
                  </div>
                  <div class="dropdown">
                    <button
                      class="btn btn-transparent text-white p-0"
                      type="button"
                      data-coreui-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      <svg class="icon">
                        <use
                          xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-options"
                        ></use>
                      </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="action"
                        >Action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="anotherAction"
                        >Another action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="somethingElseHere"
                        >Something else here</a
                      >
                    </div>
                  </div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px">
                  <canvas class="chart" id="card-chart1" height="70"></canvas>
                </div>
              </div>
            </div>
            <!-- /.col-->
            <div class="col-sm-6 col-xl-3">
              <div class="card text-white bg-info-gradient">
                <div
                  class="card-body pb-0 d-flex justify-content-between align-items-start"
                >
                  <div>
                    <div class="fs-4 fw-semibold">
                      $6.200
                      <span class="fs-6 fw-normal"
                        >(40.9%
                        <svg class="icon">
                          <use
                            xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-arrow-top"
                          ></use>
                        </svg>
                        )</span
                      >
                    </div>
                    <div data-coreui-i18n="income">Income</div>
                  </div>
                  <div class="dropdown">
                    <button
                      class="btn btn-transparent text-white p-0"
                      type="button"
                      data-coreui-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      <svg class="icon">
                        <use
                          xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-options"
                        ></use>
                      </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="action"
                        >Action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="anotherAction"
                        >Another action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="somethingElseHere"
                        >Something else here</a
                      >
                    </div>
                  </div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px">
                  <canvas class="chart" id="card-chart2" height="70"></canvas>
                </div>
              </div>
            </div>
            <!-- /.col-->
            <div class="col-sm-6 col-xl-3">
              <div class="card text-white bg-warning-gradient">
                <div
                  class="card-body pb-0 d-flex justify-content-between align-items-start"
                >
                  <div>
                    <div class="fs-4 fw-semibold">
                      9.823
                      <span class="fs-6 fw-normal"
                        >(84.7%
                        <svg class="icon">
                          <use
                            xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-arrow-top"
                          ></use>
                        </svg>
                        )</span
                      >
                    </div>
                    <div data-coreui-i18n="conversion">Conversion Rate</div>
                  </div>
                  <div class="dropdown">
                    <button
                      class="btn btn-transparent text-white p-0"
                      type="button"
                      data-coreui-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      <svg class="icon">
                        <use
                          xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-options"
                        ></use>
                      </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="action"
                        >Action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="anotherAction"
                        >Another action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="somethingElseHere"
                        >Something else here</a
                      >
                    </div>
                  </div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px">
                  <canvas class="chart" id="card-chart3" height="70"></canvas>
                </div>
              </div>
            </div>
            <!-- /.col-->
            <div class="col-sm-6 col-xl-3">
              <div class="card text-white bg-danger-gradient">
                <div
                  class="card-body pb-0 d-flex justify-content-between align-items-start"
                >
                  <div>
                    <div class="fs-4 fw-semibold">
                      2.124
                      <span class="fs-6 fw-normal"
                        >(-22.7%
                        <svg class="icon">
                          <use
                            xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-arrow-bottom"
                          ></use>
                        </svg>
                        )</span
                      >
                    </div>
                    <div data-coreui-i18n="sessions">Sessions</div>
                  </div>
                  <div class="dropdown">
                    <button
                      class="btn btn-transparent text-white p-0"
                      type="button"
                      data-coreui-toggle="dropdown"
                      aria-haspopup="true"
                      aria-expanded="false"
                    >
                      <svg class="icon">
                        <use
                          xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-options"
                        ></use>
                      </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="action"
                        >Action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="anotherAction"
                        >Another action</a
                      ><a
                        class="dropdown-item"
                        href="#"
                        data-coreui-i18n="somethingElseHere"
                        >Something else here</a
                      >
                    </div>
                  </div>
                </div>
                <div class="c-chart-wrapper mt-3 mx-3" style="height: 70px">
                  <canvas class="chart" id="card-chart4" height="70"></canvas>
                </div>
              </div>
            </div>
            <!-- /.col-->
          </div>
          <!-- /.row-->
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title mb-0" data-coreui-i18n="traffic">Traffic</h4>
                  <div class="small text-body-secondary" data-coreui-i18n="january">
                    January - July 2023
                  </div>
                </div>
                <div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
                  <div class="btn-group btn-group-toggle mx-3" data-coreui-toggle="buttons">
                    <input class="btn-check" id="option1" type="radio" name="options" autocomplete="off" />
                    <label class="btn btn-outline-secondary" data-coreui-i18n="day"> Day</label>
                    <input class="btn-check" id="option2" type="radio" name="options" autocomplete="off" checked="" />
                    <label class="btn btn-outline-secondary active" data-coreui-i18n="month"> Month</label>
                    <input class="btn-check" id="option3" type="radio" name="options" autocomplete="off" />
                    <label class="btn btn-outline-secondary" data-coreui-i18n="year"> Year</label>
                  </div>
                  <button class="btn btn-primary" type="button">
                    <svg class="icon">
                      <use
                        xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-cloud-download"
                      ></use>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="c-chart-wrapper" style="height: 300px; margin-top: 40px">
                <canvas class="chart" id="main-chart" height="300"></canvas>
              </div>
            </div>
            <div class="card-footer">
              <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 row-cols-xl-5 g-4 mb-2 text-center">
                <div class="col">
                  <div class="text-body-secondary" data-coreui-i18n="visits">Visits</div>
                  <div class="fw-semibold text-truncate">
                    29.703 Users (40%)
                  </div>
                  <div class="progress progress-thin">
                    <div
                      class="progress-bar bg-success"
                      role="progressbar"
                      style="width: 40%"
                      aria-valuenow="40"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                </div>
                <div class="col">
                  <div class="text-body-secondary" data-coreui-i18n="unique">Unique</div>
                  <div class="fw-semibold text-truncate">
                    24.093 Users (20%)
                  </div>
                  <div class="progress progress-thin">
                    <div
                      class="progress-bar bg-info"
                      role="progressbar"
                      style="width: 20%"
                      aria-valuenow="20"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                </div>
                <div class="col">
                  <div class="text-body-secondary" data-coreui-i18n="pageviews">Pageviews</div>
                  <div class="fw-semibold text-truncate">
                    78.706 Views (60%)
                  </div>
                  <div class="progress progress-thin">
                    <div
                      class="progress-bar bg-warning"
                      role="progressbar"
                      style="width: 60%"
                      aria-valuenow="60"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                </div>
                <div class="col">
                  <div class="text-body-secondary" data-coreui-i18n="newUsers">New Users</div>
                  <div class="fw-semibold text-truncate">
                    22.123 Users (80%)
                  </div>
                  <div class="progress progress-thin">
                    <div
                      class="progress-bar bg-danger"
                      role="progressbar"
                      style="width: 80%"
                      aria-valuenow="80"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                </div>
                <div class="col">
                  <div class="text-body-secondary" data-coreui-i18n="bounceRate">Bounce Rate</div>
                  <div class="fw-semibold text-truncate">
                    40.15%
                  </div>
                  <div class="progress progress-thin">
                    <div
                      class="progress-bar"
                      role="progressbar"
                      style="width: 40%"
                      aria-valuenow="40"
                      aria-valuemin="0"
                      aria-valuemax="100"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /.card.mb-4-->
        </div>
      </div>
<?php include 'includes/footer.php'; ?>
    </div>

<?php include 'includes/scripts.php'; ?>