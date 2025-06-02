@extends('tracker::app')

@section('tracker-content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="display-5 mb-4 fw-bold text-dark">Page Visitors</h1>

        <div class="btn-group">
            @if(request()->has(['referral_code', 'date_from', 'date_to']))
            <div class="btn btn-info" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="fw-400">
                    {{ request('referral_code') ? 'Referral: ' . request('referral_code') : 'All Referrals' }}
                </span>
                <span class="mx-2">|</span>
                <span class="fw-400">
                    Date:
                    {{ request('date_from') ?: 'All Time' }}
                    &ndash;
                    {{ request('date_to') ?: 'Present' }}
                </span>
            </div>
            @endif
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-filter"></i> Filters
            </button>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h4 mb-3">List</h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th class="bg-primary text-white">IP Address<br/>Country</th>
                            <th class="bg-primary text-white">Visit Page &<br/>Referral URL</th>
                            <th class="bg-primary text-white">Visits</th>
                            <th class="bg-primary text-white">Referral Code &<br/>User</th>
                            <th class="bg-primary text-white">Source &<br/>Medium</th>
                            <th class="bg-primary text-white">Campaign</th>
                            <th class="bg-primary text-white">User Agent</th>
                            <th class="bg-primary text-white">First Visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($visitors as $visitor)
                            <tr>
                                <td>
                                    <span class="d-block">{!! ($visitor->country_geo ? ("<a href='https://www.google.com/maps?q={$visitor->country_geo}' target='_blank'>{$visitor->ip_address}</a>") : $visitor->ip_address) !!}</span>
                                    <i class="badge text-success p-0" title="{{ $visitor->country_code }}">{!! $visitor->country_flag !!} {{ $visitor->country_name }}</i>
                                </td>
                                <td>
                                    <a href="{{ url($visitor->visit_url ?? '/') }}" target="_blank" class="text-black d-block">
                                        {{ $visitor->visit_url }}
                                    </a>
                                    <a href="{{ $visitor->referral_url }}" target="_blank" class="text-decoration-none">
                                        {{ $visitor->referral_url }}
                                    </a>
                                </td>
                                <td>{{ $visitor->visits ?? 0 }}</td>
                                <td>
                                    {{ $visitor->referral_code }}
                                    <i class="d-block">{{ $visitor->user?->name ?? 'N/A' }}</i>
                                </td>
                                <td>
                                    {{ $visitor->utm_source }}
                                    <i class="d-block">{{ $visitor->utm_medium }}</i>
                                </td>
                                <td>{{ $visitor->utm_campaign }}</td>
                                <td>{{ Str::limit($visitor->user_agent, 40) }}</td>
                                <td>{{ $visitor->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">No visitors found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                @if (!empty($visitors->count()))
                    {!! $visitors->appends(request()->all())->links('pagination::bootstrap-5') !!}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
