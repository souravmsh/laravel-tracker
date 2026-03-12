@extends(config('tracker.layout', 'tracker::app'))

@section('tracker-content')
    <div class="container-fluid">
        {{-- HEADER --}}
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
            <div>
                <h1 class="h5 fw-700 text-main mb-0 mono" style="color: var(--accent-cyan)">REFERRAL_SYSTEM</h1>
                <p class="text-muted small mb-0 mono" style="font-size: 0.65rem">CAMPAIGN_TRACKING_AND_ATTRIBUTION</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-primary d-flex align-items-center gap-2 btn-sm px-3 py-2 fw-600 mono" type="button"
                    data-bs-toggle="modal" data-bs-target="#trackerRef" data-item='' style="font-size: 0.7rem; border-radius: 4px;">
                    <i class="bi bi-plus-lg"></i> <span>NEW_REFERRAL</span>
                </button>
                <button class="btn btn-secondary d-flex align-items-center gap-2 btn-sm px-3 py-2 fw-600 mono" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" style="font-size: 0.7rem; border-radius: 4px;">
                    <i class="bi bi-sliders"></i> <span>FILTERS</span>
                </button>
            </div>
        </div>

        {{-- STATS ROW --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card bg-panel border-0 p-3 h-100 shadow-sm" style="border: 1px solid var(--border-primary) !important;">
                    <small class="text-muted mono mb-1 d-block" style="font-size: 0.6rem;">TOTAL_CODES</small>
                    <div class="h5 mb-0 fw-700 text-main">{{ $referrals->total() }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-panel border-0 p-3 h-100 shadow-sm" style="border: 1px solid var(--border-primary) !important;">
                    <small class="text-muted mono mb-1 d-block" style="font-size: 0.6rem;">ACTIVE_STATUS</small>
                    <div class="h5 mb-0 fw-700 text-success">{{ $referrals->where('status', 1)->count() }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-panel border-0 p-3 h-100 shadow-sm" style="border: 1px solid var(--border-primary) !important;">
                    <small class="text-muted mono mb-1 d-block" style="font-size: 0.6rem;">TOTAL_VISITS</small>
                    <div class="h5 mb-0 fw-700 text-info">{{ number_format($referrals->sum('logs_count')) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card bg-panel border-0 p-3 h-100 shadow-sm" style="border: 1px solid var(--border-primary) !important;">
                    <small class="text-muted mono mb-1 d-block" style="font-size: 0.6rem;">AVG_CONVERSION</small>
                    <div class="h5 mb-0 fw-700 text-accent-cyan">
                        {{ $referrals->total() > 0 ? round($referrals->sum('logs_count') / $referrals->total(), 1) : 0 }}
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="card border-0 shadow-lg overflow-hidden mb-4 bg-panel"
            style="border: 1px solid var(--border-primary) !important; border-radius: 8px;">
            <div class="card-header bg-dark border-bottom p-3 d-flex justify-content-between align-items-center"
                style="border-color: var(--border-primary) !important;">
                <h2 class="h6 mb-0 fw-700 text-info mono"><i class="bi bi-table me-2"></i>CAMPAIGN_REGISTRY</h2>
                <div class="text-muted small mono" style="font-size: 0.6rem;">{{ $referrals->count() }} ENTRIES_VIEWED</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-dark bg-opacity-50">
                            <tr>
                                <th class="ps-3 py-3 mono small text-muted">IDENTIFIER</th>
                                <th class="py-3 mono small text-muted">CAMPAIGN_INFO</th>
                                <th class="text-center py-3 mono small text-muted">ANALYTICS</th>
                                <th class="d-none d-md-table-cell py-3 mono small text-muted">TIMELINES</th>
                                <th class="text-end pe-3 py-3 mono small text-muted">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse ($referrals as $referral)
                                <tr>
                                    <td class="ps-3 py-3 align-middle">
                                        <div class="d-flex flex-column">
                                            <span class="fw-700 text-main" style="letter-spacing: 0.5px;">{{ $referral->code }}</span>
                                            <div class="mt-1">
                                                @if($referral->status)
                                                    <span class="badge border border-success text-success fw-600 bg-success bg-opacity-10" style="font-size: 0.55rem; padding: 2px 6px;">LIVE</span>
                                                @else
                                                    <span class="badge border border-secondary text-muted fw-600 bg-secondary bg-opacity-10" style="font-size: 0.55rem; padding: 2px 6px;">PAUSED</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 align-middle">
                                        <div class="d-flex flex-column">
                                            <span class="fw-600 small">{{ str($referral->title)->limit(30) }}</span>
                                            <small class="text-muted d-none d-sm-block mt-1" style="font-size: 0.7rem;">
                                                {{ $referral->description ?: 'No description provided.' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center py-3 align-middle">
                                        <a href="{{ route('tracker.visitors') }}?referral_code={{ $referral->code }}"
                                            class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded bg-info bg-opacity-10 text-info text-decoration-none border border-info border-opacity-20 hover-scale">
                                            <span class="fw-700">{{ number_format($referral->logs_count) }}</span>
                                            <i class="bi bi-bar-chart-fill" style="font-size: 0.7rem;"></i>
                                        </a>
                                    </td>
                                    <td class="d-none d-md-table-cell py-3 align-middle">
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-calendar-plus text-muted" style="font-size: 0.7rem;"></i>
                                                <small class="text-muted" style="font-size: 0.7rem;">{{ $referral->created_at->format('M d, Y') }}</small>
                                            </div>
                                            @if($referral->expires_at)
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-hourglass-split text-danger" style="font-size: 0.7rem;"></i>
                                                    <small class="text-danger fw-600" style="font-size: 0.7rem;">{{ $referral->expires_at->format('M d, Y') }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end pe-3 py-3 align-middle">
                                        <button class="btn btn-outline-info btn-sm rounded-circle p-0 d-inline-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;"
                                            data-bs-toggle="modal" data-bs-target="#trackerRef"
                                            data-item='{{ json_encode($referral) }}'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="p-3">
                                            <i class="bi bi-database-dash fs-1 text-muted opacity-25 d-block mb-3"></i>
                                            <h3 class="h6 text-muted fw-600">No Referral Records</h3>
                                            <p class="text-muted small mb-0">Start by creating a new campaign code above.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- PAGINATION --}}
                <div class="p-3 border-top" style="border-color: var(--border-primary) !important;">
                    @if (!empty($referrals->count()))
                        {!! $referrals->appends(request()->all())->links('pagination::bootstrap-5') !!}
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="trackerRef" tabindex="-1" aria-labelledby="trackerRefLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg bg-panel" style="border-radius: 12px; overflow: hidden; border: 1px solid var(--border-primary) !important;">
                <form method="POST" class="referralForm" id="referralForm" action="{{ route('tracker.referrals.store') }}">
                    @csrf
                    <input type="hidden" name="id" id="referralId">
                    <div class="modal-header bg-dark text-main border-bottom p-3" style="border-color: var(--border-primary) !important;">
                        <h1 class="modal-title h6 fw-700 mb-0 mono" id="trackerRefLabel" style="color: var(--accent-cyan); letter-spacing: 1px;">CREATE_REFERRAL</h1>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 bg-panel">
                        <div class="row g-4">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="code" class="form-label fw-600 small mono mb-2" style="font-size: 0.65rem; opacity: 0.8;">IDENTIFIER_CODE</label>
                                    <input type="text" class="form-control bg-dark text-main border-secondary py-2 px-3 mono shadow-none" 
                                        style="border-color: var(--border-primary) !important; border-radius: 6px; font-size: 0.8rem;" 
                                        id="code" name="code" placeholder="e.g. SUMMER2025" required>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="title" class="form-label fw-600 small mono mb-2" style="font-size: 0.65rem; opacity: 0.8;">CAMPAIGN_TITLE</label>
                                    <input type="text" class="form-control bg-dark text-main border-secondary py-2 px-3 mono shadow-none" 
                                        style="border-color: var(--border-primary) !important; border-radius: 6px; font-size: 0.8rem;" 
                                        id="title" name="title" placeholder="Marketing Campaign X" required>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="description" class="form-label fw-600 small mono mb-2" style="font-size: 0.65rem; opacity: 0.8;">CAMPAIGN_DETAILS</label>
                                    <textarea class="form-control bg-dark text-main border-secondary py-2 px-3 mono shadow-none" 
                                        style="border-color: var(--border-primary) !important; border-radius: 6px; font-size: 0.8rem;" 
                                        id="description" name="description" rows="3" placeholder="Enter notes or campaign description..."></textarea>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="status" class="form-label fw-600 small mono mb-2" style="font-size: 0.65rem; opacity: 0.8;">OPERATIONAL_STATUS</label>
                                    <select class="form-select bg-dark text-main border-secondary py-2 px-3 mono shadow-none" 
                                        style="border-color: var(--border-primary) !important; border-radius: 6px; font-size: 0.8rem;" 
                                        id="status" name="status">
                                        <option value="1">ACTIVE</option>
                                        <option value="0">PAUSED</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="position" class="form-label fw-600 small mono mb-2" style="font-size: 0.65rem; opacity: 0.8;">DISPLAY_PRIORITY</label>
                                    <input type="number" class="form-control bg-dark text-main border-secondary py-2 px-3 mono shadow-none" 
                                        style="border-color: var(--border-primary) !important; border-radius: 6px; font-size: 0.8rem;" 
                                        id="position" name="position" min="0" value="0">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="expires_at" class="form-label fw-600 small mono mb-2" style="font-size: 0.65rem; opacity: 0.8;">TERMINATION_TIMESTAMP</label>
                                    <input type="datetime-local" class="form-control bg-dark text-main border-secondary py-2 px-3 mono shadow-none" 
                                        style="border-color: var(--border-primary) !important; border-radius: 6px; font-size: 0.8rem;" 
                                        id="expires_at" name="expires_at">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-dark p-3 d-flex gap-2" style="border-color: var(--border-primary) !important;">
                        <button type="button" class="btn btn-outline-secondary fw-700 px-4 py-2 mono shadow-none" 
                            style="border-radius: 4px; font-size: 0.7rem; border-color: var(--border-primary) !important; color: var(--text-muted);" 
                            data-bs-dismiss="modal">TERMINATE_OP</button>
                        <button type="submit" class="btn btn-primary fw-700 px-4 py-2 mono shadow-none" 
                            style="border-radius: 4px; font-size: 0.7rem; background-color: var(--accent-cyan) !important; border: none; color: #000;">
                            SAVE_ENTITY
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('tracker-scripts')
<style>
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: scale(1.05); }
    .table-hover tbody tr:hover { background-color: rgba(255,255,255,0.02) !important; }
</style>
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
                modalTitle.textContent = 'EDIT_REFERRAL';
                referralId.value = item.id || '';
                document.getElementById('code').value = item.code || '';
                document.getElementById('title').value = item.title || '';
                document.getElementById('description').value = item.description || '';
                document.getElementById('status').value = item.status ? '1' : '0';
                document.getElementById('position').value = item.position || '0';
                document.getElementById('expires_at').value = item.expires_at ? 
                    new Date(item.expires_at).toISOString().slice(0, 16) : '';
            } else {
                modalTitle.textContent = 'CREATE_REFERRAL';
                form.reset();
                referralId.value = '';
                document.getElementById('status').value = '1';
                document.getElementById('position').value = '0';
            }
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            
            const formData = new FormData(form);
            const url      = form.action;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
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
