      <header class="header header-sticky p-0 mb-4">
        <div class="container-fluid px-4 border-bottom">
          <button
            class="header-toggler"
            type="button"
            onclick='coreui.Sidebar.getInstance(document.querySelector("#sidebar")).toggle()'
            style="margin-inline-start: -14px"
          >
            <svg class="icon icon-lg">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-menu"
              ></use>
            </svg>
          </button>
          <ul class="header-nav d-none d-md-flex">
            <li class="nav-item">
              <a class="nav-link" href="<?= $baseDir ?>index.php" data-coreui-i18n="dashboard">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" data-coreui-i18n="users">Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" data-coreui-i18n="settings">Settings</a>
            </li>
          </ul>
          <ul class="header-nav ms-auto">
            <li class="nav-item">
              <div class="dropdown">
                <button
                  class="btn btn-link nav-link"
                  type="button"
                  aria-expanded="false"
                  data-coreui-toggle="dropdown"
                >
                  <svg class="icon icon-lg theme-icon-active">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-contrast"
                    ></use>
                  </svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="--cui-dropdown-min-width: 8rem">
                  <li>
                    <button
                      class="dropdown-item d-flex align-items-center"
                      type="button"
                      data-coreui-theme-value="light"
                    >
                      <svg class="icon icon-lg me-3">
                        <use
                          xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-sun"
                        ></use>
                      </svg>
                      <span data-coreui-i18n="light">Light</span>
                    </button>
                  </li>
                  <li>
                    <button
                      class="dropdown-item d-flex align-items-center"
                      type="button"
                      data-coreui-theme-value="dark"
                    >
                      <svg class="icon icon-lg me-3">
                        <use
                          xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-moon"
                        ></use>
                      </svg>
                      <span data-coreui-i18n="dark">Dark</span>
                    </button>
                  </li>
                  <li>
                    <button
                      class="dropdown-item d-flex align-items-center"
                      type="button"
                      data-coreui-theme-value="auto"
                    >
                      <svg class="icon icon-lg me-3">
                        <use
                          xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-contrast"
                        ></use>
                      </svg>
                      <span data-coreui-i18n="auto">Auto</span>
                    </button>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item py-1">
              <div class="vr h-100 mx-2 text-body text-opacity-75"></div>
            </li>
            <li class="nav-item dropdown">
              <button
                class="btn btn-link nav-link py-2 pe-2"
                type="button"
                aria-expanded="false"
                data-coreui-toggle="dropdown"
              >
                <svg class="icon icon-lg">
                  <use
                    xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-bell"
                  ></use>
                </svg>
              </button>
              <div class="dropdown-menu dropdown-menu-end pt-0 w-auto">
                <div
                  class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2"
                  data-coreui-i18n="youHaveCountNotifications"
                  data-coreui-i18n-values='{"count": 5}'
                >
                  You have 5 notifications
                </div>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2 text-success">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-user-follow"
                    ></use>
                  </svg>
                  New user registered
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2 text-danger">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-user-unfollow"
                    ></use>
                  </svg>
                  User deleted
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2 text-info">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-chart"
                    ></use>
                  </svg>
                  Sales report is ready
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2 text-success">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-basket"
                    ></use>
                  </svg>
                  New client
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2 text-warning">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-speedometer"
                    ></use>
                  </svg>
                  Server overloaded
                </a>
                <div
                  class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2"
                  data-coreui-i18n="server"
                >
                  Server
                </div>
                <a class="dropdown-item" href="#">
                  <div class="text-uppercase mb-1">
                    <small><b>CPU Usage</b></small>
                  </div>
                  <span class="fw-semibold">25%</span>
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
                </a>
                <a class="dropdown-item" href="#">
                  <div class="text-uppercase mb-1">
                    <small><b>Memory Usage</b></small>
                  </div>
                  <span class="fw-semibold">70%</span>
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
                </a>
                <a class="dropdown-item" href="#">
                  <div class="text-uppercase mb-1">
                    <small><b>SSD 1 Usage</b></small>
                  </div>
                  <span class="fw-semibold">95%</span>
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
                </a>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a
                class="nav-link py-0 pe-0"
                data-coreui-toggle="dropdown"
                href="#"
                role="button"
                aria-haspopup="true"
                aria-expanded="false"
              >
                <div class="avatar avatar-md">
                  <img
                    class="avatar-img"
                    src="<?= $baseDir ?>assets/img/avatars/8.jpg"
                    alt="user@email.com"
                  />
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-end pt-0 w-auto">
                <div
                  class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2"
                  data-coreui-i18n="account"
                >
                  Account
                </div>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-bell"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="updates">Updates</span>
                  <span class="badge badge-sm bg-info ms-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-envelope-open"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="messages">Messages</span>
                  <span class="badge badge-sm bg-success ms-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-task"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="tasks">Tasks</span>
                  <span class="badge badge-sm bg-danger ms-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-comment-square"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="comments">Comments</span>
                  <span class="badge badge-sm bg-warning ms-auto">42</span>
                </a>
                <div
                  class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2"
                  data-coreui-i18n="settings"
                >
                  Settings
                </div>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-user"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="profile">Profile</span>
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-settings"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="settings">Settings</span>
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-credit-card"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="payments">Payments</span>
                  <span class="badge badge-sm bg-secondary ms-auto">42</span>
                </a>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-file"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="projects">Projects</span>
                  <span class="badge badge-sm bg-primary ms-auto">42</span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-lock-locked"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="lockAccount">Lock Account</span>
                </a>
                <a class="dropdown-item" href="<?= $baseDir ?>login.php">
                  <svg class="icon me-2">
                    <use
                      xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-account-logout"
                    ></use>
                  </svg>
                  <span data-coreui-i18n="logout">Logout</span>
                </a>
              </div>
            </li>
          </ul>
        </div>
        <div class="container-fluid px-4">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb my-0">
              <li class="breadcrumb-item">
                <a href="<?= $baseDir ?>index.php" data-coreui-i18n="home">Home</a>
              </li>
              <?php if (!empty($breadcrumbs)): ?>
                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                  <?php if (isset($breadcrumb['url'])): ?>
                    <li class="breadcrumb-item">
                      <a href="<?= $baseDir ?><?= $breadcrumb['url'] ?>"><?= $breadcrumb['title'] ?></a>
                    </li>
                  <?php else: ?>
                    <li class="breadcrumb-item active">
                      <span><?= $breadcrumb['title'] ?></span>
                    </li>
                  <?php endif; ?>
                <?php endforeach; ?>
              <?php endif; ?>
            </ol>
          </nav>
        </div>
      </header>