@extends('tracker::app')

@section('tracker-content')
<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <h1 class="display-6 fw-800 text-dark mb-1" style="letter-spacing: -1.5px;">Page Visitors</h1>
            <p class="text-secondary fw-500 mb-0">Detailed log of all visitor interactions</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(request()->has(['referral_code', 'date_from', 'date_to']))
                <div class="badge bg-white border text-dark px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 500;">
                    <i class="bi bi-info-circle text-primary"></i>
                    {{ request('referral_code') ?: 'All Referrals' }} | {{ request('date_from') ?: 'All Time' }}
                </div>
            @endif
            <button class="btn btn-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-sliders"></i> <span>Filters</span>
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><i class="bi bi-geo-alt me-2"></i>Visitor / Location</th>
                            <th><i class="bi bi-link-45deg me-2"></i>Destination</th>
                            <th><i class="bi bi-reception-4 me-2"></i>Visits</th>
                            <th><i class="bi bi-ticket-perforated me-2"></i>Source Info</th>
                            <th><i class="bi bi-device-ssd me-2"></i>Device</th>
                            <th><i class="bi bi-calendar3 me-2"></i>First Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitors as $visitor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light p-2 rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                            {!! $visitor->country_flag ?: '<i class="bi bi-globe text-secondary"></i>' !!}
                                        </div>
                                        <div>
                                            <span class="d-block fw-700 text-dark">
                                                {!! ($visitor->country_geo ? ("<a href='https://www.google.com/maps?q={$visitor->country_geo}' target='_blank' class='text-decoration-none'>{$visitor->ip_address}</a>") : $visitor->ip_address) !!}
                                            </span>
                                            <small class="text-secondary fw-500">{{ $visitor->country_name ?: 'Unknown Location' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small class="d-block text-primary fw-600 mb-1">{{ Str::limit($visitor->visit_url, 30) }}</small>
                                    @if($visitor->referral_url)
                                        <small class="text-secondary d-block" style="font-size: 0.75rem;">From: {{ Str::limit($visitor->referral_url, 30) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-700">
                                        {{ $visitor->visits ?? 1 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-600 text-dark small">{{ $visitor->referral_code ?: 'Direct' }}</span>
                                        <small class="text-secondary">{{ $visitor->utm_source ?: 'No Source' }} / {{ $visitor->utm_medium ?: 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-secondary small fw-500" title="{{ $visitor->user_agent }}">
                                        @if(Str::contains($visitor->user_agent, 'Mobile')) <i class="bi bi-smartphone"></i> Mobile 
                                        @else <i class="bi bi-display"></i> Desktop @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark fw-500 small">{{ $visitor->created_at->diffForHumans() }}</span>
                                    <small class="d-block text-secondary" style="font-size: 0.7rem;">{{ $visitor->created_at->format('M d, H:i') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="p-4">
                                        <i class="bi bi-mailbox fs-1 text-secondary opacity-25 d-block mb-3"></i>
                                        <p class="text-secondary fw-500">No visitors tracked yet matching your criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-5 d-flex justify-content-center">
        @if ($visitors instanceof \Illuminate\Pagination\LengthAwarePaginator)
            {!! $visitors->appends(request()->all())->links('pagination::bootstrap-5') !!}
        @endif
    </div>
</div>
@endsection

