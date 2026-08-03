@extends(config('tracker.layout', 'tracker::app'))

@section('tracker-content')
    <div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h1 class="h6 fw-700 text-main mb-0 mono" style="color: var(--accent-cyan)">REFERRAL_SYSTEM</h1>
            <p class="text-muted small mb-0 mono" style="font-size: 0.65rem">CAMPAIGN_TRACKING_CODES</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if (request()->has(['referral_code', 'date_from', 'date_to']))
                <div class="badge border text-muted fw-500 px-2 py-1 small d-none d-md-block mono" style="border-color: var(--border-primary); border-radius: 2px; font-size: 0.65rem">
                    {{ request('referral_code') ?: 'ALL' }} // {{ request('date_from') ?: 'START' }}
                </div>
            @endif
            <button class="btn btn-primary d-flex align-items-center gap-1 btn-sm mono" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" style="font-size: 0.65rem">
                <i class="bi bi-sliders"></i> <span>FILTERS</span>
            </button>
        </div>
    </div>

        <div class="card border-0 shadow-sm overflow-hidden mb-4">
            <div class="card-header border-bottom p-2 d-flex justify-content-between align-items-center">
                <h2 class="chart-title mb-0 mono">ACTIVE_CODES</h2>
                <button class="btn btn-primary btn-sm px-2 mono" data-bs-toggle="modal" data-bs-target="#trackerRef" data-item='' style="font-size: 0.65rem">
                    <i class="bi bi-plus-lg me-1"></i> NEW_CODE
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Campaign</th>
                                <th class="text-center">Visits</th>
                                <th class="d-none d-md-table-cell">Timing</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($referrals as $referral)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-700 text-dark small">{{ $referral->code }}</span>
                                            <span class="badge {{ $referral->status ? 'text-success' : 'text-secondary' }} p-0 text-uppercase fw-800" style="font-size: 0.6rem;">
                                                {{ $referral->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-600 text-dark small">{{ str($referral->title)->limit(20) }}</span>
                                            <small class="text-secondary d-none d-sm-block" style="font-size: 0.7rem;">{{ str($referral->description)->limit(30) }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('tracker.visitors') }}?referral_code={{ $referral->code }}" class="text-primary fw-700 small text-decoration-none">
                                            {{ $referral->logs_count }} <i class="bi bi-arrow-right-short"></i>
                                        </a>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <div class="d-flex flex-column">
                                            <small class="text-dark fw-500" style="font-size: 0.75rem;">Created {{ $referral->created_at->format('M d, Y') }}</small>
                                            @if($referral->expires_at)
                                                <small class="text-danger fw-600" style="font-size: 0.7rem;">Exp. {{ $referral->expires_at->format('M d, Y') }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-light btn-sm rounded-circle p-1" style="width: 28px; height: 28px;" data-bs-toggle="modal" data-bs-target="#trackerRef" data-item='{{ json_encode($referral) }}'>
                                            <i class="bi bi-pencil-square" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <div class="p-3">
                                            <i class="bi bi-link-45deg fs-2 text-secondary opacity-25 d-block mb-2"></i>
                                            <p class="text-secondary small mb-0">No codes found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 p-3">
                    @if (!empty($referrals->count()))
                        {!! $referrals->appends(request()->all())->links('pagination::bootstrap-5') !!}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="trackerRef" tabindex="-1" aria-labelledby="trackerRefLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
                <form method="POST" class="referralForm" id="referralForm" action="{{ route('tracker.referrals.store') }}">
                    @csrf
                    <input type="hidden" name="id" id="referralId">
                    <div class="modal-header bg-dark text-white border-0 p-4">
                        <h1 class="modal-title h5 fw-700 mb-0" id="trackerRefLabel">Create Referral</h1>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-sm-12">
                                <label for="code" class="form-label fw-600 text-secondary small">REFERRAL CODE</label>
                                <input type="text" class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" id="code" name="code" placeholder="e.g. SUMMER25">
                            </div>
                            <div class="col-sm-12">
                                <label for="title" class="form-label fw-600 text-secondary small">TITLE</label>
                                <input type="text" class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" id="title" name="title" placeholder="Campaign Name">
                            </div>
                            <div class="col-sm-12">
                                <label for="description" class="form-label fw-600 text-secondary small">DESCRIPTION</label>
                                <textarea class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" id="description" name="description" rows="3" placeholder="Notes about this campaign..."></textarea>
                            </div>
                            <div class="col-sm-6">
                                <label for="status" class="form-label fw-600 text-secondary small">STATUS</label>
                                <select class="form-select bg-light border-0 py-2 px-3" style="border-radius: 12px;" id="status" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="position" class="form-label fw-600 text-secondary small">POSITION</label>
                                <input type="number" class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" id="position" name="position" min="0" value="0">
                            </div>
                            <div class="col-sm-12">
                                <label for="expires_at" class="form-label fw-600 text-secondary small">EXPIRES AT (OPTIONAL)</label>
                                <input type="datetime-local" class="form-control bg-light border-0 py-2 px-3" style="border-radius: 12px;" id="expires_at" name="expires_at">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light fw-600 px-4" style="border-radius: 12px;" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-600 px-4" style="border-radius: 12px;">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('tracker-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('trackerRef');
        const form = document.getElementById('referralForm');
        const modalTitle = document.getElementById('trackerRefLabel');
        const referralId = document.getElementById('referralId');

        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            let item = null;
            if (button && button.getAttribute('data-item')) {
                try {
                    item = JSON.parse(button.getAttribute('data-item'));
                } catch (e) {
                    console.error('Error parsing data-item:', e);
                }
            }

            if (item && item.id) {
                modalTitle.textContent = 'Edit Referral';
                referralId.value = item.id || '';
                document.getElementById('code').value = item.code || '';
                document.getElementById('title').value = item.title || '';
                document.getElementById('description').value = item.description || '';
                document.getElementById('status').value = item.status ? '1' : '0';
                document.getElementById('position').value = item.position || '0';
                document.getElementById('expires_at').value = item.expires_at ? 
                    new Date(item.expires_at).toISOString().slice(0, 16) : '';
            } else {
                // Create mode
                modalTitle.textContent = 'Create Referral';
                form.reset();
                referralId.value = '';
                document.getElementById('status').value = '1';
                document.getElementById('position').value = '0';
            }
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            
            const formData = new FormData(form);
            const method   = form.method || 'POST';
            const url      = form.action;

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    alert(`Referral ${referralId ? 'updated' : 'created'} successfully!`);
                    window.location.reload();
                } else {
                    alert(`Error: ${data.message || 'Something went wrong'}`);
                }
            } catch (error) {
                alert(`Error: ${error.message}`);
            }
        });
    });
</script>
@endpush
