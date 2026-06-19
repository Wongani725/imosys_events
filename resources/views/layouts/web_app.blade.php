<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Web App') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }

        .sidebar {
            background-color: #006198;
            color: white;
            width: 250px;
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: white;
        }

        .sidebar .nav-link:hover {
            color: #ccc;
        }

        .navbar-brand {
            font-size: 1rem;
        }

        .main-content {
            padding: 1rem;
        }

        @media (min-width: 992px) {
            .main-content {
                padding: 2rem;
            }
        }

        .navbar .d-inline-flex {
            display: flex;
            align-items: center;
        }

        .navbar-text {
            font-size: 0.8rem;
        }

        .card-img-top {
            max-height: 180px;
            object-fit: cover;
        }

    </style>

    @stack('styles')
</head>
<body>
@php
    $memberUser = Auth::guard('member')->user();
    $incompleteProfile = $memberUser && (empty($memberUser->phone_number) || empty($memberUser->address));
@endphp

<div class="d-lg-flex min-vh-100">
    <!-- Sidebar for large screens -->
    <div class="sidebar d-none d-lg-block p-3">
        <div class="text-center mb-3">
            <img src="{{ asset('images/trans-logo.PNG') }}" alt="IIA Malawi" style="height:60px;width:auto;display:block;margin:0 auto 8px;">
            <h4 class="text-white mb-0">{{ config('app.name', 'Web App') }}</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link" href="{{ route('member-dashboard') }}">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item mb-2 position-relative">
                <a class="nav-link" href="{{ route('member.notifications') }}">
                    <i class="fas fa-bell me-2"></i>Notifications
                    @if(Auth::guard('member')->check())
                        @php
                            $notifCount = \App\Models\NotificationRecipient::where('member_id', Auth::guard('member')->id())->whereNull('read_at')->count();
                        @endphp
                        @if($notifCount > 0)
                            <span class="badge bg-danger rounded-pill position-absolute top-0 end-0">{{ $notifCount }}</span>
                        @endif
                    @endif
                </a>
            </li>
            <li class="nav-item mb-2 position-relative">
                <a class="nav-link" href="{{ route('password.view') }}">
                    <i class="fas fa-user-edit me-2"></i>Update Profile
                    @if($incompleteProfile)
                        <span class="badge bg-danger rounded-pill position-absolute top-0 end-0" title="Complete your profile">!</span>
                    @endif
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

        <!-- Main content area -->
        <div class="container-fluid main-content text-center py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif


            @yield('content')
        </div>
    </div>
</div>

<!-- Offcanvas Sidebar for Small Screens -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="background-color:#006198;">
        <div class="offcanvas-header text-white flex-column align-items-center" style="background-color:#006198;">
            <img src="{{ asset('images/trans-logo.PNG') }}" alt="IIA Malawi" style="height:40px;width:auto;display:block;margin-bottom:4px;">
            <h5 class="offcanvas-title text-center" id="mobileSidebarLabel">{{ config('app.name', 'Web App') }}</h5>
        </div>
        <div class="offcanvas-body sidebar">
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a class="nav-link" href="{{ route('member-dashboard') }}">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link" href="{{ route('member.notifications') }}">
                        <i class="fas fa-bell me-2"></i>Notifications
                        @if(Auth::guard('member')->check())
                            @php $notifCountMobile = \App\Models\NotificationRecipient::where('member_id', Auth::guard('member')->id())->whereNull('read_at')->count(); @endphp
                            @if($notifCountMobile > 0)
                                <span class="badge bg-danger ms-2">{{ $notifCountMobile }}</span>
                            @endif
                        @endif
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link" href="{{ route('password.view') }}">
                        <i class="fas fa-user-edit me-2"></i>Update Profile
                        @if($incompleteProfile)
                            <span class="badge bg-danger ms-2" title="Complete your profile">!</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                    <form id="logout-form" action="{{ route('member.logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </div>

<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">Are you sure you want to log out?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger"
                        onclick="document.getElementById('logout-form').submit();">Logout</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
