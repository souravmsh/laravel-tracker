<div class="sidebar">
    <h2 class="h4 mb-4 text-white">Tracker Analytics</h2>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link {{ url()->current() == route('tracker.dashboard') ? 'active' : '' }}" href="{{ route('tracker.dashboard') }}">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ url()->current() == route('tracker.visitors') ? 'active' : '' }}" href="{{ route('tracker.visitors') }}">Visitors</a></li>
        <li class="nav-item"><a class="nav-link {{ url()->current() == route('tracker.referrals') ? 'active' : '' }}" href="{{ route('tracker.referrals') }}">Referrals</a></li>
    </ul>
</div>