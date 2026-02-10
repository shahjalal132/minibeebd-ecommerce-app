<!DOCTYPE html>
<html lang="en">
@php
    /** Load site info once */
    $info = \App\Models\Information::first();

    /** Build logo URL with safe fallback */
    $logoUrl = ($info && !empty($info->site_logo))
        ? asset('uploads/img/'.$info->site_logo)
        : asset('backend/img/default-logo.svg'); // নিজের fallback ফাইল দিন
@endphp
<head>
    <meta charset="utf-8" />
    <title>{{ $info->site_name ?? 'Admin' }} Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="Admin Panel" name="description" />
    <meta name="author" content="Coderthemes" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ $logoUrl }}">

    <!-- Vendor / Theme CSS -->
    <link href="{{ asset('backend/css/vendor/jquery-jvectormap-1.2.2.css')}}" rel="stylesheet" />
    <link href="{{ asset('backend/css/icons.min.css')}}" rel="stylesheet" />
    <link href="{{ asset('backend/css/app-creative.min.css')}}" rel="stylesheet" id="app-style" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

    <!-- Layout overrides -->
    <style>
        :root{ --ls-w:220px; } /* Desktop sidebar width */

        /* Print */
        @media print{ .no-print,.no-print *{ display:none !important; } }

        /* Light-mode text (dark mode নষ্ট হবে না) */
        [data-layout-color="light"] .content-page,
        [data-layout-color="light"] .content-page *{ color:#111; }

        .ml-12{
            margin-left: 17% !important;
        }
        .ml-12{
            margin-left: 17% !important;
        }

        /* Desktop: content push so nothing gets cut */
        @media (min-width: 992px){
            body.with-sidebar{ padding-left: var(--ls-w); }
        }

        /* Sidebar sizing - FIXED to prevent expansion */
        .leftside-menu.leftside-menu-detached{
            width: var(--ls-w) !important;
            min-width: var(--ls-w) !important;
            max-width: var(--ls-w) !important;
            position: fixed !important;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1000;
        }

        /* Content page positioning */
        .content-page{
            margin-left: var(--ls-w);
            width: calc(100% - var(--ls-w));
        }

        /* Mobile: sidebar becomes a full-width bar (no overlay/backdrop) */
        @media (max-width: 991.98px){
            .leftside-menu.leftside-menu-detached{
                position: relative !important;
                width: 100% !important; 
                min-width: 100% !important; 
                max-width: 100% !important;
                height: auto;
                border-right: 0; 
                box-shadow: none;
            }
            body.with-sidebar{ padding-left:0 !important; }
            .content-page{
                margin-left: 0;
                width: 100%;
            }
        }

        /* ===== Topbar logo OFF ===== */
        .topnav-logo{ display: none !important; }

        /* ===== Sidebar logo CENTER ===== */
        .leftbar-user{ text-align:center; padding: 14px 10px; }
        .leftbar-user a{ display:block; text-decoration:none; }
        .sidebar-logo{
            height: 64px; width:auto; display:block; margin: 50px auto 8px;
            object-fit:contain;
        }
        .leftbar-user-name{
            color:#fff; font-weight:600; font-size:15px; margin-top:6px;
            display:block;
        }

        /* User avatar (if ever used) */
        .leftbar-user img.rounded-circle{ width:42px; height:42px; object-fit:cover; }

        /* Subtle content padding */
        .content-page .content{ padding-top: 14px; }

        /* Footer border */
        .footer{ border-top: 1px solid rgba(0,0,0,.05); }
    </style>

    @stack('css')
</head>

<body class="loading with-sidebar" data-layout="detached" data-layout-color="light" data-rightbar-onstart="true">

    <!-- Topbar Start -->
    <div class="navbar-custom topnav-navbar topnav-navbar-dark">
        <div class="container-fluid">

            {{-- Topbar logo intentionally hidden by CSS (.topnav-logo {display:none}) --}}
            {{-- <a href="{{ route('admin.dashboard')}}" class="topnav-logo">
                <span class="topnav-logo-lg"><img src="{{ $logoUrl }}" alt=""></span>
                <span class="topnav-logo-sm"><img src="{{ $logoUrl }}" alt="" style="height:16px"></span>
            </a> --}}

            <ul class="list-unstyled topbar-menu float-end mb-0">
                <li class="dropdown notification-list d-xl-none">
                    <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <i class="dripicons-search noti-icon"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-animated dropdown-lg p-0">
                        <form class="p-3"><input type="text" class="form-control" placeholder="Search ..." aria-label="Search"></form>
                    </div>
                </li>

                <li class="notification-list">
                    <a class="nav-link" href="{{ route('front.home') }}" target="_blank" aria-label="View Site">
                        <i class="dripicons-home noti-icon"></i>
                    </a>
                </li>

                <li class="dropdown notification-list">
                    <a class="nav-link dropdown-toggle nav-user arrow-none me-0" data-bs-toggle="dropdown" id="topbar-userdrop" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <span class="account-user-avatar">
                            <img src="{{ getImage('uploads/img', Auth::user()->image) }}" alt="user-image" class="rounded-circle">
                        </span>
                        <span><span class="account-user-name">{{ auth()->user()->first_name }}</span></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown" aria-labelledby="topbar-userdrop">
                        <div class="dropdown-header noti-title"><h6 class="m-0">Welcome !</h6></div>

                        <a href="{{ route('admin.profile') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-account-circle me-1"></i><span>My Account</span>
                        </a>
                        <a href="{{ route('admin.password') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-shield-lock me-1"></i><span>Change Password</span>
                        </a>

                        @can('product.delete')
                        <a href="{{ route('admin.settings.index') }}" class="dropdown-item notify-item">
                            <i class="mdi mdi-cog me-1"></i><span>Settings</span>
                        </a>
                        @endcan

                        <a class="dropdown-item notify-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout me-1"></i><span>Logout</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </div>
                </li>
            </ul>

            <!-- Theme’s built-in button (ignore if not needed) -->
            <a class="button-menu-mobile disable-btn" aria-label="Toggle Sidebar">
                <div class="lines"><span></span><span></span><span></span></div>
            </a>
        </div>
    </div>
    <!-- end Topbar -->

    <!-- Start Content-->
    <div class="container-fluid">
        <div class="wrapper">

            <!-- ========== Left Sidebar Start ========== -->
            <div class="leftside-menu leftside-menu-detached">
                <!-- Sidebar logo centered -->
                <div class="leftbar-user">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ $logoUrl }}" class="sidebar-logo" alt="{{ $info->site_name ?? 'Admin' }} Logo">
                    </a>
                    <span class="leftbar-user-name">{{ auth()->user()->name }}</span>
                </div>

                <!--- Sidemenu -->
                @include('backend.partials.navbar')
                <!-- End Sidebar -->

                <div class="clearfix"></div>
            </div>
            <!-- Left Sidebar End -->

            <div class="content-page ml-12">
                <div class="content">
                    @yield('content')
                </div> <!-- End Content -->

                <!-- Footer -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6"></div>
                            <!--
                            <div class="col-md-6">
                                <div class="text-md-end footer-links d-none d-md-block">
                                    <a href="javascript:void(0);">About</a>
                                    <a href="javascript:void(0);">Support</a>
                                    <a href="javascript:void(0);">Contact Us</a>
                                </div>
                            </div>
                            -->
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->
            </div> <!-- content-page -->

        </div> <!-- wrapper -->
    </div>
    <!-- END Container -->

    <!-- Right Sidebar (theme demo) -->
    <div class="end-bar">
        <div class="rightbar-title">
            <a href="javascript:void(0);" class="end-bar-toggle float-end">
                <i class="dripicons-cross noti-icon"></i>
            </a>
            <h5 class="m-0 text-light">Settings</h5>
        </div>

        <div class="rightbar-content h-100" data-simplebar>
            <div class="p-3">
                <h5 class="mt-3">Color Scheme</h5>
                <hr class="mt-1" />
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="color-scheme-mode" value="light" id="light-mode-check" checked />
                    <label class="form-check-label" for="light-mode-check">Light Mode</label>
                </div>
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="color-scheme-mode" value="dark" id="dark-mode-check" />
                    <label class="form-check-label" for="dark-mode-check">Dark Mode</label>
                </div>

                <h5 class="mt-4">Width</h5>
                <hr class="mt-1" />
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="width" value="fluid" id="fluid-check" checked />
                    <label class="form-check-label" for="fluid-check">Fluid</label>
                </div>
                <div class="form-check form-switch mb-1">
                    <input type="checkbox" class="form-check-input" name="width" value="boxed" id="boxed-check" />
                    <label class="form-check-label" for="boxed-check">Boxed</label>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="common_modal" tabindex="-1" aria-hidden="true"></div>
    <div class="rightbar-overlay"></div>

    @include('backend.partials.js')
</body>
</html>
