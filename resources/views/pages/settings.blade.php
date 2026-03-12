@extends("tracker::app")

@section('tracker-content')
<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <h1 class="display-6 fw-800 text-dark mb-1" style="letter-spacing: -1.5px;">Settings</h1>
            <p class="text-secondary fw-500 mb-0">Manage tracker configuration — changes apply immediately via cache.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert border-0 mb-4 d-flex align-items-center gap-2 px-4 py-3"
             style="background: rgba(16,185,129,0.10); color: #065f46; border-radius: 14px;">
            <i class="bi bi-check-circle-fill text-success fs-5"></i>
            <span class="fw-600">{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('tracker.settings.save') }}" method="POST" id="settingsForm">
        @csrf

        <div class="row g-4">

            {{-- ============================================================
                 GENERAL
            =============================================================== --}}
            <div class="col-12">
                <div class="card px-4 py-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="rounded-3 d-flex align-items-center justify-content-center"
                              style="width:38px;height:38px;background:rgba(99,102,241,.12)">
                            <i class="bi bi-toggles2 text-primary fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 mb-0">General</h5>
                            <small class="text-secondary">Core tracking behaviour</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        @php $g = $settings['general'] ?? [] @endphp

                        {{-- Boolean toggles --}}
                        @foreach(['enabled','debug','queue_enabled','log_to_database'] as $bkey)
                            @if(isset($g[$bkey]))
                            @php $s = $g[$bkey] @endphp
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                                     style="background:rgba(99,102,241,.04);border:1px solid rgba(99,102,241,.1)">
                                    <div>
                                        <div class="fw-600 text-dark">{{ $s['label'] }}</div>
                                        <small class="text-secondary">{{ $s['description'] }}</small>
                                    </div>
                                    <div class="form-check form-switch ms-3 mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               name="{{ $bkey }}" id="toggle_{{ $bkey }}"
                                               style="width:2.8em;height:1.5em;"
                                               {{ filter_var($s['value'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        {{-- Integer fields --}}
                        @foreach(['rate_limit','session_lifetime','max_input_length'] as $ikey)
                            @if(isset($g[$ikey]))
                            @php $s = $g[$ikey] @endphp
                            <div class="col-md-4">
                                <label class="form-label fw-600 small text-dark">{{ $s['label'] }}</label>
                                <input type="number" class="form-control" name="{{ $ikey }}"
                                       value="{{ $s['value'] }}" min="0">
                                @if($s['description'])
                                    <div class="form-text text-secondary">{{ $s['description'] }}</div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 IP GEOLOCATION
            =============================================================== --}}
            <div class="col-12">
                <div class="card px-4 py-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="rounded-3 d-flex align-items-center justify-content-center"
                              style="width:38px;height:38px;background:rgba(99,102,241,.12)">
                            <i class="bi bi-geo-alt text-primary fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 mb-0">IP Geolocation</h5>
                            <small class="text-secondary">Fetch country/city via <a href="https://ipapi.co" target="_blank" class="text-primary">ipapi.co</a></small>
                        </div>
                    </div>

                    @php $ip = $settings['ip_api'] ?? [] @endphp
                    <div class="row g-3">
                        @if(isset($ip['ip_api_enabled']))
                        @php $s = $ip['ip_api_enabled'] @endphp
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                                 style="background:rgba(99,102,241,.04);border:1px solid rgba(99,102,241,.1)">
                                <div>
                                    <div class="fw-600 text-dark">{{ $s['label'] }}</div>
                                    <small class="text-secondary">{{ $s['description'] }}</small>
                                </div>
                                <div class="form-check form-switch ms-3 mb-0">
                                    <input class="form-check-input" type="checkbox"
                                           name="ip_api_enabled" id="toggle_ip_api_enabled"
                                           style="width:2.8em;height:1.5em;"
                                           {{ filter_var($s['value'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(isset($ip['ip_api_token']))
                        @php $s = $ip['ip_api_token'] @endphp
                        <div class="col-md-6">
                            <label class="form-label fw-600 small text-dark">{{ $s['label'] }}</label>
                            <input type="password" class="form-control" name="ip_api_token"
                                   value="{{ $s['value'] }}" autocomplete="off"
                                   placeholder="Leave blank to use free tier">
                            @if($s['description'])
                                <div class="form-text text-secondary">{{ $s['description'] }}</div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 GOOGLE ANALYTICS
            =============================================================== --}}
            <div class="col-12">
                <div class="card px-4 py-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="rounded-3 d-flex align-items-center justify-content-center"
                              style="width:38px;height:38px;background:rgba(234,67,53,.10)">
                            <i class="bi bi-bar-chart-line" style="color:#ea4335;font-size:1.1rem"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 mb-0">Google Analytics 4</h5>
                            <small class="text-secondary">Send events via GA4 Measurement Protocol</small>
                        </div>
                    </div>

                    @php $ga = $settings['google'] ?? [] @endphp
                    <div class="row g-3">
                        @if(isset($ga['ga_enabled']))
                        @php $s = $ga['ga_enabled'] @endphp
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between p-3 rounded-3"
                                 style="background:rgba(234,67,53,.05);border:1px solid rgba(234,67,53,.12)">
                                <div>
                                    <div class="fw-600 text-dark">{{ $s['label'] }}</div>
                                    <small class="text-secondary">{{ $s['description'] }}</small>
                                </div>
                                <div class="form-check form-switch ms-3 mb-0">
                                    <input class="form-check-input" type="checkbox"
                                           name="ga_enabled" id="toggle_ga_enabled"
                                           style="width:2.8em;height:1.5em;"
                                           {{ filter_var($s['value'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        @endif

                        @foreach(['ga_measurement_id','ga_api_secret','ga_event_name'] as $gakey)
                            @if(isset($ga[$gakey]))
                            @php $s = $ga[$gakey] @endphp
                            <div class="col-md-4">
                                <label class="form-label fw-600 small text-dark">{{ $s['label'] }}</label>
                                <input type="{{ $gakey === 'ga_api_secret' ? 'password' : 'text' }}"
                                       class="form-control" name="{{ $gakey }}"
                                       value="{{ $s['value'] }}" autocomplete="off"
                                       placeholder="{{ $gakey === 'ga_measurement_id' ? 'G-XXXXXXXXXX' : '' }}">
                                @if($s['description'])
                                    <div class="form-text text-secondary">{{ $s['description'] }}</div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 ADVANCED FILTERS
            =============================================================== --}}
            <div class="col-12">
                <div class="card px-4 py-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="rounded-3 d-flex align-items-center justify-content-center"
                              style="width:38px;height:38px;background:rgba(100,116,139,.12)">
                            <i class="bi bi-code-slash text-secondary fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-700 mb-0">Advanced Filters</h5>
                            <small class="text-secondary">Manage referral parameters and path filtering (comma-separated or JSON array)</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            @if(isset($g['referral_code_params']))
                            @php $s = $g['referral_code_params'] @endphp
                            <label class="form-label fw-600 small text-dark">{{ $s['label'] }}</label>
                            <textarea class="form-control" name="referral_code_params" rows="3">{{ is_array($s['value']) ? json_encode($s['value']) : $s['value'] }}</textarea>
                            <div class="form-text text-secondary">{{ $s['description'] }}</div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if(isset($g['ignore_paths']))
                            @php $s = $g['ignore_paths'] @endphp
                            <label class="form-label fw-600 small text-dark">{{ $s['label'] }}</label>
                            <textarea class="form-control" name="ignore_paths" rows="3">{{ is_array($s['value']) ? json_encode($s['value']) : $s['value'] }}</textarea>
                            <div class="form-text text-secondary">{{ $s['description'] }}</div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if(isset($g['allowed_paths']))
                            @php $s = $g['allowed_paths'] @endphp
                            <label class="form-label fw-600 small text-dark">{{ $s['label'] }}</label>
                            <textarea class="form-control" name="allowed_paths" rows="3">{{ is_array($s['value']) ? json_encode($s['value']) : $s['value'] }}</textarea>
                            <div class="form-text text-secondary">{{ $s['description'] }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}

        {{-- Save bar --}}
        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-5 py-2 fw-600 d-flex align-items-center gap-2">
                <i class="bi bi-floppy2"></i> Save Settings
            </button>
        </div>

    </form>

</div>
@endsection
