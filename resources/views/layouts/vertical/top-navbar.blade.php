<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <h5 class="mb-0 text-iia-blue fw-bold">IIA Malawi Event Management</h5>
            </div>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">

            {{-- NOTIFICATION BELL --}}
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);"
                   data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <i class="bx bx-bell bx-sm"></i>
                    @if(isset($notificationCount) && $notificationCount > 0)
                        <span class="badge bg-danger rounded-pill badge-notifications"
                              style="position:absolute;top:2px;right:2px;font-size:9px;padding:2px 5px;">
                            {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu dropdown-menu-end py-0" style="width:320px;">
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-2">
                            <h6 class="text-body mb-0 me-auto">Notifications</h6>
                            @if(isset($notificationCount) && $notificationCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $notificationCount }}</span>
                            @endif
                        </div>
                    </li>
                    <li class="dropdown-notifications-list scrollable-container" style="max-height:300px;">
                        <ul class="list-group list-group-flush">
                            @if(isset($recentBookings) && $recentBookings->count())
                                @foreach($recentBookings as $bk)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item py-2">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-2">
                                            <span class="avatar-initial rounded-circle bg-label-warning p-2">
                                                <i class="bx bx-calendar-check text-white"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <small class="fw-semibold d-block">{{ $bk->name }}</small>
                                            <small class="text-muted">New booking — {{ $bk->booking_status }}</small>
                                            <small class="text-muted d-block">{{ $bk->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            @else
                                <li class="list-group-item text-center py-3">
                                    <small class="text-muted">No new notifications</small>
                                </li>
                            @endif
                        </ul>
                    </li>
                    @if(isset($recentBookings) && $recentBookings->count())
                    <li class="dropdown-menu-footer border-top">
                        <a href="{{ route('get-bookers') }}" class="dropdown-item d-flex justify-content-center py-2">
                            <small>View all bookings</small>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            {{-- /NOTIFICATION BELL --}}

            {{-- USER DROPDOWN --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('default-user.png') }}" alt class="w-px-40 h-auto rounded-circle">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('default-user.png') }}" alt class="w-px-40 h-auto rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ Auth::user()->name ?? 'Admin' }}</span>
                                    <small class="text-muted">{{ Auth::user()->roles->first()->name ?? 'Admin' }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="dropdown-item" href="{{ route('change.password') }}">
                                <i class="bx bx-key me-2"></i>
                                <span class="align-middle">Change password</span>
                            </a>
                            <a class="dropdown-item" id="main-logout-link" href="#"
                               onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="bx bx-power-off me-2"></i>
                                <span class="align-middle">Log Out</span>
                            </a>
                        </form>
                    </li>
                </ul>
            </li>
            {{-- /USER --}}

        </ul>
    </div>

    <div class="navbar-search-wrapper search-input-wrapper d-none">
        <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..." aria-label="Search...">
        <i class="bx bx-x bx-sm search-toggler cursor-pointer"></i>
    </div>
</nav>
