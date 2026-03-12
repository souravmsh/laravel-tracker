@extends(config('tracker.layout', 'tracker::app'))

@section('tracker-content')
<div class="container-fluid">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h1 class="h6 fw-700 text-main mb-0 mono" style="color: var(--accent-cyan)">PAGE_VISITORS</h1>
            <p class="text-muted small mb-0 mono" style="font-size: 0.65rem">DETAILED_INTERACTION_LOG</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(request()->has(['referral_code', 'date_from', 'date_to']))
                <div class="badge border text-muted fw-500 px-2 py-1 small d-none d-md-block mono" style="border-color: var(--border-primary); border-radius: 2px; font-size: 0.65rem">
                    {{ request('referral_code') ?: 'ALL' }} // {{ request('date_from') ?: 'START' }}
                </div>
            @endif
            <button class="btn btn-primary d-flex align-items-center gap-1 btn-sm mono" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" style="font-size: 0.65rem">
                <i class="bi bi-sliders"></i> <span>FILTERS</span>
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Visitor</th>
                            <th class="d-none d-lg-table-cell">Location</th>
                            <th>Destination</th>
                            <th class="text-center">Visits</th>
                            <th class="d-none d-md-table-cell">Source</th>
                            <th class="text-end">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitors as $visitor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-light rounded-circle text-center" style="width: 32px; height: 32px; line-height: 32px; font-size: 0.8rem;">
                                            {!! $visitor->country_flag ?: '<i class="bi bi-globe"></i>' !!}
                                        </div>
                                        <div>
                                            <span class="d-block fw-600 text-dark small">
                                                {!! ($visitor->country_geo ? ("<a href='https://www.google.com/maps?q={$visitor->country_geo}' target='_blank' class='text-decoration-none'>{$visitor->ip_address}</a>") : $visitor->ip_address) !!}
                                            </span>
                                            <small class="text-secondary d-lg-none" style="font-size: 0.7rem;">{{ $visitor->country_name ?: 'Unknown' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small class="text-secondary">{{ $visitor->country_name ?: 'Unknown' }}</small>
                                </td>
                                <td>
                                    <small class="d-block text-primary fw-600" title="{{ $visitor->visit_url }}">{{ str($visitor->visit_url)->limit(25) }}</small>
                                    @if($visitor->referral_url)
                                        <small class="text-secondary d-none d-md-block" style="font-size: 0.7rem;" title="{{ $visitor->referral_url }}">via {{ str($visitor->referral_url)->limit(20) }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill fw-700" style="font-size: 0.75rem;">
                                        {{ $visitor->visits ?? 1 }}
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <div class="d-flex flex-column">
                                        <span class="fw-600 text-dark small">{{ $visitor->referral_code ?: 'Direct' }}</span>
                                        <small class="text-secondary" style="font-size: 0.7rem;">{{ $visitor->utm_source ?: '-' }}</small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="text-dark fw-500 small d-block">{{ $visitor->created_at->diffForHumans(null, true) }}</span>
                                    <small class="text-secondary" style="font-size: 0.65rem;">{{ $visitor->created_at->format('M d, H:i') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="p-3">
                                        <i class="bi bi-mailbox fs-2 text-secondary opacity-25 d-block mb-2"></i>
                                        <p class="text-secondary small mb-0">No visitors found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4 d-flex justify-content-center">
        @if ($visitors instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {!! $visitors->appends(request()->all())->links('pagination::bootstrap-5') !!}
        @endif
    </div>
</div>
@endsection

