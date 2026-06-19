<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    @include("layouts.vertical.menu-app-brand")

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->url() === route('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->url() === route('admin.members.index') ? 'active' : '' }}">
            <a href="{{ route('admin.members.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="Members">Members</div>
            </a>
        </li>

        <li class="menu-item {{ request()->url() === route('events') ? 'active' : '' }}">
            <a href="{{ route('events') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-event"></i>
                <div data-i18n="Events">Events</div>
            </a>
        </li>

        <li class="menu-item {{ request()->url() === route('get-bookers') ? 'active' : '' }}">
            <a href="{{ route('get-bookers') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                <div data-i18n="Bookings">Bookings</div>
            </a>
        </li>

        <li class="menu-item {{ request()->url() === route('admin.bulk-booking.index') ? 'active' : '' }}">
            <a href="{{ route('admin.bulk-booking.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-layer"></i>
                <div data-i18n="Bulk Bookings">Bulk Bookings</div>
            </a>
        </li>

        <li class="menu-item {{ request()->url() === route('admin.notifications.index') ? 'active' : '' }}">
            <a href="{{ route('admin.notifications.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div data-i18n="Notifications">Notifications</div>
            </a>
        </li>

        <li class="menu-item {{ request()->url() === route('admin.reports.index') ? 'active' : '' }}">
            <a href="{{ route('admin.reports.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-detail"></i>
                <div data-i18n="Reports">Reports</div>
            </a>
        </li>

        <li class="menu-item {{ request()->url() === route('admin.settings.users') ? 'active' : '' }}">
            <a href="{{ route('admin.settings.users') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Settings">Settings</div>
            </a>
        </li>
    </ul>
</aside>
