<div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filters</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="{{ url()->current() }}">
            <div class="mb-3">
                <label for="ip_address" class="form-label">IP Address</label>
                <input type="text" name="ip_address" class="form-control" id="ip_address" placeholder="Enter IP address" value="{{ request('ip_address') }}"/>
            </div>
            <div class="mb-3">
                <label for="referral_code" class="form-label">Referral Code</label>
                <input type="text" name="referral_code" class="form-control" id="referral_code" placeholder="Enter referral code" value="{{ request('referral_code') }}"/>
            </div>
            <div class="mb-3">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" id="date_from" value="{{ request('date_from') }}" />
            </div>
            <div class="mb-3">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" id="date_to" value="{{ request('date_to') }}"/>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="{{ url()->current() }}" class="btn btn-danger">Reset</a>
            </div>
        </form>
    </div>
</div>