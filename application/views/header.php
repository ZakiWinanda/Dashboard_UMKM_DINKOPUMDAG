<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta name="googlebot-news" content="nosnippet">
    <meta name="author" content="Cakros Dotcom">
    <title><?=strip_tags($title);?> ~ MONITORING DAN EVALUASI KINERJA PENDAMPING SENTRA WISATA KULINER (SWK)</title>
    <link href="<?=base_url();?>assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/vendor/datatables-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/css/jquery-ui.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/vendor/datatables-responsive/css/responsive.bootstrap4.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/vendor/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/vendor/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/vendor/select2/css/select2.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/vendor/fancybox/jquery.fancybox.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/css/adminlte.min.css" rel="stylesheet">
    <link href="<?=base_url();?>assets/css/skins/_all-skins.min.css" rel="stylesheet">
    <style type="text/css">
    .content-wrapper {
        background-image: linear-gradient(to right, rgba(255,0,0,0), rgb(0 255 200 / 19%));
    }
    .lds-dual-ring {
        display: inline-block;
        width: 80px;
        height: 80px;
    }
    .lds-dual-ring:after {
        content: " ";
        display: block;
        width: 64px;
        height: 64px;
        margin: 8px;
        border-radius: 50%;
        border: 6px solid #333;
        border-color: #333 transparent #333 transparent;
        animation: lds-dual-ring 1.2s linear infinite;
    }
    @keyframes lds-dual-ring {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    .sidebar a, li.nav-header {
        color: #FFFFFF !important;
    }
    .bootstrap-datetimepicker-widget .dropdown-menu {
        z-index: 9999;
        position: absolute;
    }

    /* btn glow */
    .btn-glow {
        animation: glow 0.3s infinite alternate;
    }

    @keyframes glow {
        from {
            box-shadow: 0 0 5px -5px #c0c0c0;
        }
        to {
            box-shadow: 0 0 5px 5px #c0c0c0;
        }
        /* btn glow */
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <?php
    $uri1 = $this->uri->segment(1);
    $uri2 = $this->uri->segment(2);
    ?>
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fa fa-user"></i> <?=$nama; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-xs dropdown-menu-right">
                        <a href="javascript:void(0)" class="dropdown-item" data-toggle="modal" data-target="#modalgantipassword"><i class="fas fa-lock"></i> Ubah Password</a>
                        <a href="javascript:void(0)" onclick="logout()" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
        <aside class="main-sidebar sidebar-dark-primary elevation-4 small">
            <a href="<?=base_url();?>dashboard" class="brand-link">
                <img src="<?=base_url();?>assets/img/logo_pemkot.png" class="logo-xs mr-2 ml-2" style="max-height: 48px;">
                <div class="pl-3 pr-3">
                    <img src="<?=base_url();?>assets/img/logo_pemkot.png" class="brand-text" style="max-height: 48px;">
                    <span>Monev <strong>SWK</strong></span>
                </div>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="<?=base_url('dashboard');?>" class="nav-link <?php if($uri1=='dashboard' || $uri1=='dashboard') echo("active");?>">
                                <i class="nav-icon fa fa-tachometer-alt"></i>
                                <p>DASHBOARD</p>
                            </a>
                        </li>

                        <?php
                        if($is_pimpinan || $is_admin) {
                            print_r('<li class="nav-header">MENU PENDAMPING</li>');
                        }
                        else {
                            print_r('<li class="nav-header">MENU</li>');
                        }
                        ?>
                        <li class="nav-item">
                            <a href="<?=base_url('input_harian');?>" class="nav-link <?php if($uri1=='input_harian') echo('active');?>">
                                <i class="nav-icon fa fa-edit"></i>
                                <p>INPUT OMSET & KUNJUNGAN</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=base_url('capaian_harian');?>" class="nav-link <?php if($uri1=='capaian_harian') echo('active');?>">
                                <i class="nav-icon fa fa-edit"></i>
                                <p>REKAP OMSET & KUNJUNGAN</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=base_url('indikator/entri');?>" class="nav-link <?php if($uri1=='indikator') echo('active');?>">
                                <i class="nav-icon fa fa-edit"></i>
                                <p>ENTRI CAPAIAN INDIKATOR</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?=base_url('laporan');?>" class="nav-link <?php if($uri1=='laporan') echo('active');?>">
                                <i class="nav-icon fa fa-print"></i>
                                <p>LAPORAN</p>
                            </a>
                        </li>

                        <?php
                        if($is_koordinator_pendamping || $is_pimpinan || $is_admin) {
                            ?>
                            <li class="nav-header">MENU KOORDINATOR</li>
                            <li class="nav-item">
                                <a href="<?=base_url('koordinator_pendamping/capaian_harian');?>" class="nav-link <?php if($uri1=='koordinator_pendamping' && $uri2=='capaian_harian') echo('active');?>">
                                    <i class="nav-icon fa fa-edit"></i>
                                    <p>ENTRI OMSET & KUNJUNGAN</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?=base_url('koordinator_pendamping/entri');?>" class="nav-link <?php if($uri1=='koordinator_pendamping' && $uri2=='entri') echo('active');?>">
                                    <i class="nav-icon fa fa-edit"></i>
                                    <p>CAPAIAN INDIKATOR</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?=base_url('koordinator_pendamping/laporan');?>" class="nav-link <?php if($uri1=='koordinator_pendamping' && $uri2=='laporan') echo('active');?>">
                                    <i class="nav-icon fa fa-print"></i>
                                    <p>LAPORAN</p>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                        <?php
                        if($is_admin) {
                            ?>
                            <li class="nav-header">DATA MASTER</li>
                            <li class="nav-item">
                                <a href="<?=base_url('data_master/pengguna');?>" class="nav-link <?php if($uri1=='data_master' && $uri2=='pengguna') echo('active');?>">
                                    <i class="nav-icon fa fa-users"></i>
                                    <p>PENGGUNA</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?=base_url('data_master/pendamping');?>" class="nav-link <?php if($uri1=='data_master' && $uri2=='pendamping') echo('active');?>">
                                    <i class="nav-icon fa fa-user-cog"></i>
                                    <p>PENDAMPING</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?=base_url('data_master/koordinator_pendamping');?>" class="nav-link <?php if($uri1=='data_master' && $uri2=='koordinator_pendamping') echo('active');?>">
                                    <i class="nav-icon fa fa-user-cog"></i>
                                    <p>KOOR. PENDAMPING</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?=base_url('data_master/swk');?>" class="nav-link <?php if($uri1=='data_master' && $uri2=='swk') echo('active');?>">
                                    <i class="nav-icon fa fa-store"></i>
                                    <p>SWK</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?=base_url('data_master/indikator');?>" class="nav-link <?php if($uri1=='data_master' && $uri2=='indikator') echo('active');?>">
                                    <i class="nav-icon fa fa-bullseye"></i>
                                    <p>TARGET</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?=base_url('data_master/omset');?>" class="nav-link <?php if($uri1=='data_master' && $uri2=='omset') echo('active');?>">
                                    <i class="nav-icon fa fa-dollar-sign"></i>
                                    <p>OMSET</p>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </aside>
