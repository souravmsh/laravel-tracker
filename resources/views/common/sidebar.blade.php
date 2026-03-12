<div class="sidebar">
    <div class="sidebar-header">
        <h2 class="mono">
            <i class="bi bi-graph-up"></i> <span>{{ config('tracker.title') }}</span>
        </h2>
    </div>
    
    <ul class="nav flex-column mt-4">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.dashboard') ? 'active' : '' }}" href="{{ route('tracker.dashboard') }}">
                <i class="bi bi-cpu"></i> <span>DASHBOARD</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.visitors') ? 'active' : '' }}" href="{{ route('tracker.visitors') }}">
                <i class="bi bi-terminal"></i> <span>VISITORS</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.referrals') ? 'active' : '' }}" href="{{ route('tracker.referrals') }}">
                <i class="bi bi-link-45deg"></i> <span>REFERRALS</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('tracker.settings') ? 'active' : '' }}" href="{{ route('tracker.settings') }}">
                <i class="bi bi-gear"></i> <span>SETTINGS</span>
            </a>
        </li>
    </ul>
</div>