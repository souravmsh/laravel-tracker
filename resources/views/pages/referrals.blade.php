@extends('tracker::app')

@section('tracker-content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="display-5 mb-4 fw-bold text-dark">Referrals</h1>

            <div class="btn-group">
                @if (request()->has(['referral_code', 'date_from', 'date_to']))
                    <div class="btn btn-info" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="fw-400">
                            {{ request('referral_code') ? 'Referral: ' . request('referral_code') : 'All Referrals' }}
                        </span>
                        <span class="mx-2">|</span>
                        <span class="fw-400">
                            Date:
                            {{ request('date_from') ?: 'All Time' }}
                            –
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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h4 mb-0">List</h2>
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#trackerRef" data-item=''>New Referral</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th class="bg-primary text-white">Code</th>
                                <th class="bg-primary text-white">Title</th>
                                <th class="bg-primary text-white">Description</th>
                                <th class="bg-primary text-white">Status</th>
                                <th class="bg-primary text-white">Position</th>
                                <th class="bg-primary text-white">Expires Date</th>
                                <th class="bg-primary text-white">Visits</th>
                                <th class="bg-primary text-white">Created By</th>
                                <th class="bg-primary text-white">Created Date</th>
                                <th class="bg-primary text-white">Update By</th>
                                <th class="bg-primary text-white">Update Date</th>
                                <th class="bg-primary text-white">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($referrals as $referral)
                                <tr>
                                    <td>{{ $referral->code }}</td>
                                    <td>{{ $referral->title }}</td>
                                    <td>{{ $referral->description }}</td>
                                    <td>{{ $referral->status ? 'Active' : 'Inactive' }}</td>
                                    <td>{{ $referral->position }}</td>
                                    <td>{{ $referral?->expires_at?->format('Y-m-d H:i:s') }}</td>
                                    <td><a href="{{ route('tracker.visitors') }}?referral_code={{ $referral->code }}" title="Visitors by Referral Code">{{ $referral->logs_count }}</a></td>
                                    <td>{{ $referral?->created_by?->name ?? 'N/A' }}</td>
                                    <td>{{ $referral?->created_at?->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ $referral?->updated_by?->name ?? 'N/A' }}</td>
                                    <td>{{ $referral?->updated_at?->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#trackerRef" data-item='{{ json_encode($referral) }}'>Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No referrals found</td>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" class="referralForm" id="referralForm" action="{{ route('tracker.referrals.store') }}">
                    @csrf
                    <input type="hidden" name="id" id="referralId">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="trackerRefLabel">Create Referral</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12 mb-3">
                                <label for="code" class="form-label">Referral Code</label>
                                <input type="text" class="form-control" id="code" name="code">
                            </div>
                            <div class="col-sm-12 mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title">
                            </div>
                            <div class="col-sm-12 mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="number" class="form-control" id="position" name="position" min="0" value="0">
                            </div>
                            <div class="col-sm-12 mb-3">
                                <label for="expires_at" class="form-label">Expires At</label>
                                <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
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
