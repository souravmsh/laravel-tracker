<div class="sidebar">
    <div class="sidebar-header mb-5">
        <h2 class="h5 fw-800 text-white mb-0" style="letter-spacing: -0.5px;">
            <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Analytics
        </h2>
    </div>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.dashboard') ? 'active' : '' }}" href="{{ route('tracker.dashboard') }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.visitors') ? 'active' : '' }}" href="{{ route('tracker.visitors') }}">
                <i class="bi bi-people"></i> Visitors
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.referrals') ? 'active' : '' }}" href="{{ route('tracker.referrals') }}">
                <i class="bi bi-link-45deg"></i> Referrals
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.settings') ? 'active' : '' }}" href="{{ route('tracker.settings') }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </li>
    </ul>
</div>