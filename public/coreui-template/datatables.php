<?php
require_once 'includes/config.php';

$pageTitle = 'DataTables - ' . $config['siteName'];
$pageDescription = 'DataTables plugin for advanced table functionality';
$currentPage = 'datatables';
$currentSection = '';

$additionalCSS = [];
$additionalJS = [];

$breadcrumbs = [
    ['title' => 'DataTables']
];

include 'includes/head.php';
?>

<?php include 'includes/sidebar.php'; ?>
    <div class="wrapper d-flex flex-column min-vh-100">
<?php include 'includes/header.php'; ?>
      <div class="body flex-grow-1">
        <div class="container-lg px-4">
          <div class="card mb-4">
            <div class="card-header"> DataTables<a class="badge bg-danger-gradient ms-2 text-decoration-none" href="https://coreui.io/pricing/?framework=bootstrap">CoreUI Pro Integration</a></div>
            <div class="card-body">
              <div class="example">
                <ul class="nav nav-underline-border" role="tablist">
                  <li class="nav-item"><a class="nav-link active" data-coreui-toggle="tab" href="#preview-1000" role="tab">
                      <svg class="icon me-2">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-media-play"></use>
                      </svg>Preview</a></li>
                  <li class="nav-item"><a class="nav-link" href="https://datatables.net/manual/" target="_blank">
                      <svg class="icon me-2">
                        <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-code"></use>
                      </svg>Code</a></li>
                </ul>
                <div class="tab-content rounded-bottom">
                  <div class="tab-pane p-3 active preview" role="tabpanel" id="preview-1000">
                    <table class="table table-striped border datatable">
                      <thead>
                        <tr>
                          <th>Username</th>
                          <th>Date registered</th>
                          <th>Role</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr class="align-middle">
                          <td>Anton Phunihel</td>
                          <td>2012/01/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Alphonse Ivo</td>
                          <td>2012/01/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Thancmar Theophanes</td>
                          <td>2012/01/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Walerian Khwaja</td>
                          <td>2012/01/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Clemens Janko</td>
                          <td>2012/02/01</td>
                          <td>Staff</td>
                          <td><span class="badge bg-danger-gradient">Banned</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Chidubem Gottlob</td>
                          <td>2012/02/01</td>
                          <td>Staff</td>
                          <td><span class="badge bg-danger-gradient">Banned</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Hristofor Sergio</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Tadhg Griogair</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Pollux Beaumont</td>
                          <td>2012/01/21</td>
                          <td>Staff</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Adam Alister</td>
                          <td>2012/01/21</td>
                          <td>Staff</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Carlito Roffe</td>
                          <td>2012/08/23</td>
                          <td>Staff</td>
                          <td><span class="badge bg-danger-gradient">Banned</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Sana Amrin</td>
                          <td>2012/08/23</td>
                          <td>Staff</td>
                          <td><span class="badge bg-danger-gradient">Banned</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Adinah Ralph</td>
                          <td>2012/06/01</td>
                          <td>Admin</td>
                          <td><span class="badge bg-dark-gradient">Inactive</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Dederick Mihail</td>
                          <td>2012/06/01</td>
                          <td>Admin</td>
                          <td><span class="badge bg-dark-gradient">Inactive</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Hip�lito Andr�s</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Fricis Arieh</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Scottie Maximilian</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Bao Gaspar</td>
                          <td>2012/01/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Tullio Luka</td>
                          <td>2012/02/01</td>
                          <td>Staff</td>
                          <td><span class="badge bg-danger-gradient">Banned</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Felice Arseniy</td>
                          <td>2012/02/01</td>
                          <td>Admin</td>
                          <td><span class="badge bg-dark-gradient">Inactive</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Finlay Alf</td>
                          <td>2012/02/01</td>
                          <td>Admin</td>
                          <td><span class="badge bg-dark-gradient">Inactive</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Theophilus Nala</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Sullivan Robert</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Krist�f Filiberto</td>
                          <td>2012/01/21</td>
                          <td>Staff</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Kuzma Edvard</td>
                          <td>2012/01/21</td>
                          <td>Staff</td>
                          <td><span class="badge bg-success-gradient">Active</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>B�nyamin Kasper</td>
                          <td>2012/08/23</td>
                          <td>Staff</td>
                          <td><span class="badge bg-danger-gradient">Banned</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Crofton Arran</td>
                          <td>2012/08/23</td>
                          <td>Staff</td>
                          <td><span class="badge bg-danger-gradient">Banned</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Bernhard Shelah</td>
                          <td>2012/06/01</td>
                          <td>Admin</td>
                          <td><span class="badge bg-dark-gradient">Inactive</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Grahame Miodrag</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Innokentiy Celio</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Kostandin Warinhari</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                        <tr class="align-middle">
                          <td>Ajith Hristijan</td>
                          <td>2012/03/01</td>
                          <td>Member</td>
                          <td><span class="badge bg-warning-gradient">Pending</span></td>
                          <td><a class="btn btn-success me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-magnifying-glass"></use>
                              </svg></a><a class="btn btn-info me-2" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-description"></use>
                              </svg></a><a class="btn btn-danger" href="#">
                              <svg class="icon">
                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-trash"></use>
                              </svg></a></td>
                        </tr>
                      </tbody>
                    </table>
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