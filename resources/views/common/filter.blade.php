<div class="offcanvas offcanvas-start border-0 shadow-lg" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel" style="border-radius: 0 24px 24px 0;">
    <div class="offcanvas-header bg-dark text-white p-4">
        <h5 class="offcanvas-title fw-700" id="filterOffcanvasLabel">
            <i class="bi bi-funnel me-2"></i>Apply Filters
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form method="GET" action="{{ url()->current() }}">
            <div class="mb-4">
                <label for="ip_address" class="form-label fw-600 text-secondary small">IP ADDRESS</label>
                <input type="text" name="ip_address" class="form-control bg-light border-0 py-2 px-3" id="ip_address" placeholder="e.g. 192.168.1.1" value="{{ request('ip_address') }}" style="border-radius: 12px;"/>
            </div>
            <div class="mb-4">
                <label for="referral_code" class="form-label fw-600 text-secondary small">REFERRAL CODE</label>
                <input type="text" name="referral_code" class="form-control bg-light border-0 py-2 px-3" id="referral_code" placeholder="Enter code" value="{{ request('referral_code') }}" style="border-radius: 12px;"/>
            </div>
            <div class="mb-4">
                <label for="date_from" class="form-label fw-600 text-secondary small">DATE FROM</label>
                <input type="date" name="date_from" class="form-control bg-light border-0 py-2 px-3" id="date_from" value="{{ request('date_from') }}" style="border-radius: 12px;"/>
            </div>
            <div class="mb-4">
                <label for="date_to" class="form-label fw-600 text-secondary small">DATE TO</label>
                <input type="date" name="date_to" class="form-control bg-light border-0 py-2 px-3" id="date_to" value="{{ request('date_to') }}" style="border-radius: 12px;"/>
            </div>

            <div class="d-grid gap-3 pt-3">
                <button type="submit" class="btn btn-primary py-2 shadow-sm" style="border-radius: 12px;">Update Results</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-danger py-2" style="border-radius: 12px;">Reset All</a>
            </div>
        </form>
    </div>
</div>