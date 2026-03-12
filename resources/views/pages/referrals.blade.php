@extends('tracker::app')

@section('tracker-content')
    <div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <h1 class="display-6 fw-800 text-dark mb-1" style="letter-spacing: -1.5px;">Referrals</h1>
            <p class="text-secondary fw-500 mb-0">Manage tracking codes and campaign sources</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if (request()->has(['referral_code', 'date_from', 'date_to']))
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

        <div class="card border-0 shadow-sm overflow-hidden mb-5">
            <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                <h2 class="h5 fw-700 mb-0">Referral Codes</h2>
                <button class="btn btn-primary btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#trackerRef" data-item=''>
                    <i class="bi bi-plus-lg me-1"></i> New Referral
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Code / Status</th>
                                <th>Campaign Details</th>
                                <th>Performance</th>
                                <th>Timing</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($referrals as $referral)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-700 text-dark">{{ $referral->code }}</span>
                                            <span class="badge {{ $referral->status ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 {{ $referral->status ? 'text-success' : 'text-secondary' }} px-2 py-1 rounded-pill mt-1" style="width: fit-content; font-size: 0.65rem;">
                                                {{ $referral->status ? 'ACTIVE' : 'INACTIVE' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-600 text-dark small">{{ $referral->title }}</span>
                                            <small class="text-secondary">{{ Str::limit($referral->description, 40) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('tracker.visitors') }}?referral_code={{ $referral->code }}" class="btn btn-light btn-sm text-primary fw-700 px-3 rounded-pill">
                                                <i class="bi bi-person-up me-1"></i> {{ $referral->logs_count }} visits
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-dark fw-500">Created {{ $referral->created_at->format('M d, Y') }}</small>
                                            @if($referral->expires_at)
                                                <small class="text-danger fw-600">Expires {{ $referral->expires_at->format('M d, Y') }}</small>
                                            @else
                                                <small class="text-secondary">No expiry</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-outline-primary btn-sm border-0 rounded-circle" data-bs-toggle="modal" data-bs-target="#trackerRef" data-item='{{ json_encode($referral) }}'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="p-4">
                                            <i class="bi bi-link-45deg fs-1 text-secondary opacity-25 d-block mb-3"></i>
                                            <p class="text-secondary fw-500">No referral codes found. Create your first one above.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
