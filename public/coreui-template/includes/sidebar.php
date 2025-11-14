    <div class="sidebar sidebar-fixed border-end" id="sidebar">
      <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
          <img
            class="sidebar-brand-full"
            src="<?= $baseDir ?>assets/brand/gawis_logo.png"
            width="110"
            height="39"
            alt="CoreUI Logo"
          />
        </div>
        <button
          class="btn-close d-lg-none"
          type="button"
          aria-label="Close"
          onclick='coreui.Sidebar.getInstance(document.querySelector("#sidebar")).toggle()'
        ></button>
      </div>
      <ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('index') ? ' active' : '' ?>" href="<?= $baseDir ?>index.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-speedometer"
              ></use>
            </svg>
            <span data-coreui-i18n="dashboard">Dashboard</span>
          </a>
        </li>
        <li class="nav-title" data-coreui-i18n="theme">Theme</li>
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('colors') ? ' active' : '' ?>" href="<?= $baseDir ?>colors.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-drop"
              ></use>
            </svg>
            <span data-coreui-i18n="colors">Colors</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('typography') ? ' active' : '' ?>" href="<?= $baseDir ?>typography.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-pencil"
              ></use>
            </svg>
            <span data-coreui-i18n="typography">Typography</span>
          </a>
        </li>
        <li class="nav-title" data-coreui-i18n="components">Components</li>
        <li class="nav-group">
          <a class="nav-link nav-group-toggle<?= isActiveSection('base') ? ' active' : '' ?>" href="#">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-puzzle"
              ></use>
            </svg>
            <span data-coreui-i18n="base">Base</span>
          </a>
          <ul class="nav-group-items compact">
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('accordion') ? ' active' : '' ?>" href="<?= $baseDir ?>base/accordion.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Accordion
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('breadcrumb') ? ' active' : '' ?>" href="<?= $baseDir ?>base/breadcrumb.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Breadcrumb
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('cards') ? ' active' : '' ?>" href="<?= $baseDir ?>base/cards.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Cards
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('carousel') ? ' active' : '' ?>" href="<?= $baseDir ?>base/carousel.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Carousel
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('collapse') ? ' active' : '' ?>" href="<?= $baseDir ?>base/collapse.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Collapse
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('list-group') ? ' active' : '' ?>" href="<?= $baseDir ?>base/list-group.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                List Group
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('navs-tabs') ? ' active' : '' ?>" href="<?= $baseDir ?>base/navs-tabs.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Navs & Tabs
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('pagination') ? ' active' : '' ?>" href="<?= $baseDir ?>base/pagination.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Pagination
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('placeholders') ? ' active' : '' ?>" href="<?= $baseDir ?>base/placeholders.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Placeholders
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('popovers') ? ' active' : '' ?>" href="<?= $baseDir ?>base/popovers.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Popovers
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('progress') ? ' active' : '' ?>" href="<?= $baseDir ?>base/progress.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Progress
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('spinners') ? ' active' : '' ?>" href="<?= $baseDir ?>base/spinners.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Spinners
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('tables') ? ' active' : '' ?>" href="<?= $baseDir ?>base/tables.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Tables
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('tooltips') ? ' active' : '' ?>" href="<?= $baseDir ?>base/tooltips.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Tooltips
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-group">
          <a class="nav-link nav-group-toggle<?= isActiveSection('buttons') ? ' active' : '' ?>" href="#">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-cursor"
              ></use>
            </svg>
            <span data-coreui-i18n="buttons">Buttons</span>
          </a>
          <ul class="nav-group-items compact">
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('buttons') ? ' active' : '' ?>" href="<?= $baseDir ?>buttons/buttons.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Buttons
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('button-group') ? ' active' : '' ?>" href="<?= $baseDir ?>buttons/button-group.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Button Group
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('dropdowns') ? ' active' : '' ?>" href="<?= $baseDir ?>buttons/dropdowns.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Dropdowns
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('loading-buttons') ? ' active' : '' ?>" href="<?= $baseDir ?>buttons/loading-buttons.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Loading Buttons
                <span class="badge bg-danger-gradient ms-auto">PRO</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-group">
          <a class="nav-link nav-group-toggle<?= isActiveSection('forms') ? ' active' : '' ?>" href="#">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-notes"
              ></use>
            </svg>
            <span data-coreui-i18n="forms">Forms</span>
          </a>
          <ul class="nav-group-items compact">
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('form-control') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/form-control.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Form Control
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('select') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/select.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Select
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('checks-radios') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/checks-radios.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Checks & Radios
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('range') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/range.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Range
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('input-group') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/input-group.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Input Group
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('floating-labels') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/floating-labels.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Floating Labels
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('layout') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/layout.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                <span data-coreui-i18n="layout">Layout</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('validation') ? ' active' : '' ?>" href="<?= $baseDir ?>forms/validation.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                <span data-coreui-i18n="validation">Validation</span>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('widgets') ? ' active' : '' ?>" href="<?= $baseDir ?>widgets.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-calculator"
              ></use>
            </svg>
            <span data-coreui-i18n="widgets">Widgets</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('charts') ? ' active' : '' ?>" href="<?= $baseDir ?>charts.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-chart-pie"
              ></use>
            </svg>
            <span data-coreui-i18n="charts">Charts</span>
          </a>
        </li>
        <li class="nav-group">
          <a class="nav-link nav-group-toggle<?= isActiveSection('icons') ? ' active' : '' ?>" href="#">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-star"
              ></use>
            </svg>
            <span data-coreui-i18n="icons">Icons</span>
          </a>
          <ul class="nav-group-items compact">
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('coreui-icons-free') ? ' active' : '' ?>" href="<?= $baseDir ?>icons/coreui-icons-free.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                CoreUI Icons
                <span class="badge bg-success ms-auto" data-coreui-i18n="free">Free</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('coreui-icons-brand') ? ' active' : '' ?>" href="<?= $baseDir ?>icons/coreui-icons-brand.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                CoreUI Icons - Brand
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('coreui-icons-flag') ? ' active' : '' ?>" href="<?= $baseDir ?>icons/coreui-icons-flag.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                CoreUI Icons - Flag
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-group">
          <a class="nav-link nav-group-toggle<?= isActiveSection('notifications') ? ' active' : '' ?>" href="#">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-bell"
              ></use>
            </svg>
            <span data-coreui-i18n="notifications">Notifications</span>
          </a>
          <ul class="nav-group-items compact">
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('alerts') ? ' active' : '' ?>" href="<?= $baseDir ?>notifications/alerts.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Alerts
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('badge') ? ' active' : '' ?>" href="<?= $baseDir ?>notifications/badge.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Badge
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('modals') ? ' active' : '' ?>" href="<?= $baseDir ?>notifications/modals.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Modals
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link<?= isActivePage('toasts') ? ' active' : '' ?>" href="<?= $baseDir ?>notifications/toasts.php">
                <span class="nav-icon">
                  <span class="nav-icon-bullet"></span>
                </span>
                Toasts
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-title" data-coreui-i18n="extras">Extras</li>
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('calendar') ? ' active' : '' ?>" href="<?= $baseDir ?>calendar.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-calendar"
              ></use>
            </svg>
            Calendar
            <span class="badge bg-danger-gradient ms-auto">PRO</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('datatables') ? ' active' : '' ?>" href="<?= $baseDir ?>datatables.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-list-numbered"
              ></use>
            </svg>
            DataTables
            <span class="badge bg-danger-gradient ms-auto">PRO</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link<?= isActivePage('maps') ? ' active' : '' ?>" href="<?= $baseDir ?>maps.php">
            <svg class="nav-icon">
              <use
                xlink:href="<?= $baseDir ?>vendors/@coreui/icons/svg/free.svg#cil-map"
              ></use>
            </svg>
            OpenStreetMap
            <span class="badge bg-danger-gradient ms-auto">PRO</span>
          </a>
        </li>
      </ul>
      <div class="sidebar-footer border-top d-none d-lg-flex">
        <button
          class="sidebar-toggler"
          type="button"
          data-coreui-toggle="unfoldable"
        ></button>
      </div>
    </div>