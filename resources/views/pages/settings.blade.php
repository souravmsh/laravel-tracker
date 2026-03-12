@extends(config('tracker.layout', 'tracker::app'))

@section('tracker-content')
<div class="container-fluid">

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h1 class="h6 fw-700 text-main mb-0 mono" style="color: var(--accent-cyan)">SYSTEM_SETTINGS</h1>
            <p class="text-muted small mb-0 mono" style="font-size: 0.65rem">CORE_CONFIGURATION_PANEL</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert border-0 mb-4 d-flex align-items-center gap-2 px-3 py-2 small"
        style="background: rgba(16,185,129,0.10); color: #065f46; border-radius: 8px;">
        <i class="bi bi-check-circle-fill text-success"></i>
        <span class="fw-600">{{ session('success') }}</span>
    </div>
    @endif

    <form action="{{ route('tracker.settings.save') }}" method="POST" id="settingsForm">
        @csrf

        <div class="row g-3">

            {{-- GENERAL --}}
            <div class="col-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:32px;height:32px;">
                            <i class="bi bi-toggles2 text-primary"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 h6 mb-0">General</h5>
                            <small class="text-secondary" style="font-size: 0.75rem;">Core tracking behaviour</small>
                        </div>
                    </div>

                    <div class="row g-2">
                        @php $g = $settings['general'] ?? [] @endphp

                        @foreach(['enabled','debug','queue_enabled','log_to_database'] as $bkey)
                        @if(isset($g[$bkey]))
                        @php $s = $g[$bkey] @endphp
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light bg-opacity-50">
                                <div>
                                    <div class="fw-600 text-dark small">{{ $s['label'] }}</div>
                                    <small class="text-secondary" style="font-size: 0.7rem;">{{ str($s['description'])->limit(50) }}</small>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                        name="{{ $bkey }}" id="toggle_{{ $bkey }}"
                                        style="width:2.2em;height:1.1em;"
                                        {{ filter_var($s['value'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach

                        @foreach(['rate_limit','session_lifetime','max_input_length', 'cache_ttl'] as $ikey)
                        @if(isset($g[$ikey]))
                        @php $s = $g[$ikey] @endphp
                        <div class="col-md-3">
                            <label class="form-label fw-600 small text-muted mb-1 mono" style="font-size: 0.65rem">{{ strtoupper(str_replace(' ', '_', $s['label'])) }}</label>
                            <input type="number" class="form-control form-control-sm mono bg-dark text-main border-secondary" name="{{ $ikey }}"
                                value="{{ $s['value'] }}" min="0" style="font-size: 0.75rem;">
                        </div>
                        @endif
                        @endforeach

                        @if(isset($g['title']))
                        @php $s = $g['title'] @endphp
                        <div class="col-md-12">
                            <label class="form-label fw-600 small text-muted mb-1 mono" style="font-size: 0.65rem">{{ strtoupper(str_replace(' ', '_', $s['label'])) }}</label>
                            <input type="text" class="form-control form-control-sm mono bg-dark text-main border-secondary" name="title"
                                value="{{ $s['value'] }}" style="font-size: 0.75rem;">
                            <div class="form-text text-muted mono" style="font-size: 0.6rem;">{{ $s['description'] }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- IP GEOLOCATION --}}
            <div class="col-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="rounded bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:32px;height:32px;">
                            <i class="bi bi-geo-alt text-primary"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 h6 mb-0">IP Geolocation</h5>
                            <small class="text-secondary" style="font-size: 0.75rem;">Powered by <a href="https://ipapi.co" target="_blank" class="text-primary text-decoration-none">ipapi.co</a></small>
                        </div>
                    </div>

                    @php $ip = $settings['ip_api'] ?? [] @endphp
                    <div class="row g-2">
                        @if(isset($ip['ip_api_enabled']))
                        @php $s = $ip['ip_api_enabled'] @endphp
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded border bg-light bg-opacity-50">
                                <div>
                                    <div class="fw-600 text-dark small">{{ $s['label'] }}</div>
                                    <small class="text-secondary" style="font-size: 0.7rem;">{{ str($s['description'])->limit(50) }}</small>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                        name="ip_api_enabled" id="toggle_ip_api_enabled"
                                        style="width:2.2em;height:1.1em;"
                                        {{ filter_var($s['value'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($ip['ip_api_token']))
                        @php $s = $ip['ip_api_token'] @endphp
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-dark mb-1">{{ $s['label'] }}</label>
                            <input type="password" class="form-control form-control-sm" name="ip_api_token"
                                value="{{ $s['value'] }}" autocomplete="off">
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- GOOGLE ANALYTICS --}}
            <div class="col-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="rounded bg-danger bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:32px;height:32px;">
                            <i class="bi bi-bar-chart-line text-danger"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 h6 mb-0">Google Analytics 4</h5>
                            <small class="text-secondary" style="font-size: 0.75rem;">Measurement Protocol</small>
                        </div>
                    </div>

                    @php $ga = $settings['google'] ?? [] @endphp
                    <div class="row g-2">
                        @if(isset($ga['ga_enabled']))
                        @php $s = $ga['ga_enabled'] @endphp
                        <div class="col-12 mb-1">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded border"
                                style="background: rgba(234,67,53,0.03); border-color: rgba(234,67,53,0.1) !important;">
                                <div>
                                    <div class="fw-600 text-dark small">{{ $s['label'] }}</div>
                                    <small class="text-secondary" style="font-size: 0.7rem;">{{ str($s['description'])->limit(60) }}</small>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                        name="ga_enabled" id="toggle_ga_enabled"
                                        style="width:2.2em;height:1.1em;"
                                        {{ filter_var($s['value'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        @endif

                        @foreach(['ga_measurement_id','ga_api_secret','ga_event_name'] as $gakey)
                        @if(isset($ga[$gakey]))
                        @php $s = $ga[$gakey] @endphp
                        <div class="col-md-4">
                            <label class="form-label fw-600 small text-dark mb-1">{{ $s['label'] }}</label>
                            <input type="{{ $gakey === 'ga_api_secret' ? 'password' : 'text' }}"
                                class="form-control form-control-sm" name="{{ $gakey }}"
                                value="{{ $s['value'] }}" autocomplete="off"
                                placeholder="{{ $gakey === 'ga_measurement_id' ? 'G-XXXXXXXXXX' : '' }}">
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ADVANCED FILTERS --}}
            <div class="col-12">
                <div class="card p-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="rounded bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center"
                            style="width:32px;height:32px;">
                            <i class="bi bi-code-slash text-secondary"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 h6 mb-0">Advanced & Layout</h5>
                            <small class="text-secondary" style="font-size: 0.75rem;">Path filtering and view options</small>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            @if(isset($g['referral_code_params']))
                            @php $s = $g['referral_code_params'] @endphp
                            <label class="form-label fw-600 small text-dark mb-1">{{ $s['label'] }}</label>
                            <textarea class="form-control form-control-sm" name="referral_code_params" rows="2" style="font-size: 0.75rem;">{{ is_array($s['value']) ? json_encode($s['value']) : $s['value'] }}</textarea>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if(isset($g['ignore_paths']))
                            @php $s = $g['ignore_paths'] @endphp
                            <label class="form-label fw-600 small text-dark mb-1">{{ $s['label'] }}</label>
                            <textarea class="form-control form-control-sm" name="ignore_paths" rows="2" style="font-size: 0.75rem;">{{ is_array($s['value']) ? json_encode($s['value']) : $s['value'] }}</textarea>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if(isset($g['allowed_paths']))
                            @php $s = $g['allowed_paths'] @endphp
                            <label class="form-label fw-600 small text-dark mb-1">{{ $s['label'] }}</label>
                            <textarea class="form-control form-control-sm" name="allowed_paths" rows="2" style="font-size: 0.75rem;">{{ is_array($s['value']) ? json_encode($s['value']) : $s['value'] }}</textarea>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(isset($g['layout']))
                            @php $s = $g['layout'] @endphp
                            <label class="form-label fw-600 small text-muted mb-1 mono" style="font-size: 0.65rem">MASTER_LAYOUT</label>
                            <input type="text" class="form-control form-control-sm mono bg-dark text-main border-secondary" name="layout" value="{{ $s['value'] }}" style="font-size: 0.75rem;">
                            <div class="form-text text-muted mono" style="font-size: 0.6rem;">{{ $s['description'] }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(isset($g['route_prefix']))
                            @php $s = $g['route_prefix'] @endphp
                            <label class="form-label fw-600 small text-muted mb-1 mono" style="font-size: 0.65rem">ROUTE_PREFIX</label>
                            <input type="text" class="form-control form-control-sm mono bg-dark text-main border-secondary" name="route_prefix" value="{{ $s['value'] }}" style="font-size: 0.75rem;">
                            <div class="form-text text-muted mono" style="font-size: 0.6rem;">{{ $s['description'] }}</div>
                            @endif
                        </div>
                        <div class="col-md-12">
                            @if(isset($g['route_middleware']))
                            @php $s = $g['route_middleware'] @endphp
                            <label class="form-label fw-600 small text-muted mb-1 mono" style="font-size: 0.65rem">ROUTE_MIDDLEWARE</label>
                            <textarea class="form-control form-control-sm mono bg-dark text-main border-secondary" name="route_middleware" rows="2" style="font-size: 0.75rem;">{{ is_array($s['value']) ? json_encode($s['value']) : $s['value'] }}</textarea>
                            <div class="form-text text-muted mono" style="font-size: 0.6rem;">{{ $s['description'] }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Save bar --}}
        <div class="mt-3 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4 py-2 fw-600 d-flex align-items-center gap-2">
                <i class="bi bi-floppy2"></i> Save Changes
            </button>
        </div>

    </form>

</div>
@endsection
