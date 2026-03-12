<div class="offcanvas offcanvas-start border-0 shadow-lg mono" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel" style="background: var(--bg-panel); color: var(--text-main); border-right: 1px solid var(--border-primary) !important;">
    <div class="offcanvas-header p-3" style="border-bottom: 1px solid var(--border-primary)">
        <h5 class="offcanvas-title fw-700 h6 mono" id="filterOffcanvasLabel" style="color: var(--accent-cyan)">
            <i class="bi bi-funnel me-2"></i>APPLY_FILTERS
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <form method="GET" action="{{ url()->current() }}">
            <div class="mb-3">
                <label for="ip_address" class="form-label fw-600 text-muted small mono">IP_ADDRESS</label>
                <input type="text" name="ip_address" class="form-control bg-dark border-secondary text-main py-2 px-3" id="ip_address" placeholder="0.0.0.0" value="{{ request('ip_address') }}" style="border-radius: 2px; font-size: 0.75rem; border: 1px solid var(--border-primary) !important;"/>
            </div>
            <div class="mb-3">
                <label for="referral_code" class="form-label fw-600 text-muted small mono">REFERRAL_CODE</label>
                <input type="text" name="referral_code" class="form-control bg-dark border-secondary text-main py-2 px-3" id="referral_code" placeholder="CODE_X" value="{{ request('referral_code') }}" style="border-radius: 2px; font-size: 0.75rem; border: 1px solid var(--border-primary) !important;"/>
            </div>
            <div class="mb-3">
                <label for="date_from" class="form-label fw-600 text-muted small mono">DATE_FROM</label>
                <input type="date" name="date_from" class="form-control bg-dark border-secondary text-main py-2 px-3" id="date_from" value="{{ request('date_from') }}" style="border-radius: 2px; font-size: 0.75rem; border: 1px solid var(--border-primary) !important;"/>
            </div>
            <div class="mb-3">
                <label for="date_to" class="form-label fw-600 text-muted small mono">DATE_TO</label>
                <input type="date" name="date_to" class="form-control bg-dark border-secondary text-main py-2 px-3" id="date_to" value="{{ request('date_to') }}" style="border-radius: 2px; font-size: 0.75rem; border: 1px solid var(--border-primary) !important;"/>
            </div>

            <div class="d-grid gap-2 pt-3">
                <button type="submit" class="btn btn-primary py-2 mono">UPDATE_RESULTS</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary py-2 mono" style="font-size: 0.75rem; border-radius: 2px;">RESET_ALL</a>
            </div>
        </form>
    </div>
</div>