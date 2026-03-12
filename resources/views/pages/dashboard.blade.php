@extends(config('tracker.layout', 'tracker::app'))

@section('tracker-content')
<div class="container-fluid px-2"> 

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h1 class="h6 fw-700 text-main mb-0 mono" style="color: var(--accent-cyan)">SYSTEM_OVERVIEW</h1>
            <p class="text-muted small mb-0 mono" style="font-size: 0.65rem">LOGS_AND_TRAFFIC_DATA</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(request()->has(['referral_code', 'date_from', 'date_to']))
                <div class="badge border text-muted fw-500 px-2 py-1 small d-none d-md-block mono" style="border-color: var(--border-primary); border-radius: 2px; font-size: 0.65rem">
                    {{ request('referral_code') ?: 'ALL' }} // {{ request('date_from') ?: 'START' }} - {{ request('date_to') ?: 'NOW' }}
                </div>
            @endif
            <button class="btn btn-primary d-flex align-items-center gap-1 btn-sm mono" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas" style="font-size: 0.65rem">
                <i class="bi bi-sliders"></i> <span>FILTERS</span>
            </button>
        </div>
    </div>
    
    <!-- Key Metrics Section -->
    <section id="dashboard" class="mb-3">
        <div class="row g-2">
            <div class="col-6 col-lg-3">
                <div class="counter-box">
                    <p>TOTAL_VISITORS</p>
                    <h3>{{ $totalVisitors ?? 0 }}</h3>
                    <div class="position-absolute end-0 top-0 p-2 opacity-25">
                        <i class="bi bi-eye fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="counter-box">
                    <p>UNIQUE_VISITORS</p>
                    <h3>{{ $uniqueVisitors ?? 0 }}</h3>
                    <div class="position-absolute end-0 top-0 p-2 opacity-25">
                        <i class="bi bi-person-check fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="counter-box">
                    <p>ACTIVE_REFS</p>
                    <h3>{{ $totalRerferral ?? 0 }}</h3>
                    <div class="position-absolute end-0 top-0 p-2 opacity-25">
                        <i class="bi bi-link-45deg fs-2"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="counter-box">
                    <p>UNIQUE_SOURCES</p>
                    <h3>{{ $totalUniqueSource ?? 0 }}</h3>
                    <div class="position-absolute end-0 top-0 p-2 opacity-25">
                        <i class="bi bi-share fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-2">
            <div class="card-body p-2">
                <h2 class="chart-title mb-2 mono">PERFORMANCE_30D</h2>
                <div style="height: 200px; position: relative;">
                    <canvas id="last30DaysChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    <section class="row mb-3 g-2">
        <div class="col-lg-8">    
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h2 class="chart-title mb-0 mono">LATEST_LOGS</h2>
                    <a href="{{ route('tracker.visitors') }}" class="btn btn-link btn-sm text-decoration-none mono" style="font-size: 0.65rem; color: var(--accent-cyan)">VIEW_ALL</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>COUNTRY</th>
                                    <th class="d-none d-md-table-cell">REF</th>
                                    <th>SOURCE</th>
                                    <th class="text-end">VISITS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($visitors as $visitor)
                                    <tr>
                                        <td>
                                            <span class="d-flex align-items-center gap-1">
                                                {!! $visitor->country_flag !!} <span class="d-none d-sm-inline">{{ $visitor->country_name }}</span>
                                            </span>
                                        </td>
                                        <td class="d-none d-md-table-cell mono" style="font-size: 0.65rem">{{ $visitor->referral_code ?: '-' }}</td>
                                        <td class="mono" style="font-size: 0.7rem">{{ $visitor->utm_source ?: 'DIRECT' }}</td>
                                        <td class="text-end fw-700 mono" style="color: var(--accent-cyan)">{{ $visitor->visits ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted mono">NO_DATA_FOUND</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">    
            <div class="card h-100">
                <div class="card-header py-2">
                    <h2 class="chart-title mb-0 mono">TOP_PAGES</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>PAGE</th>
                                    <th class="text-end">HITS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mostVisitedPages as $page)
                                    <tr>
                                        <td class="text-truncate mono" style="max-width: 140px; font-size: 0.7rem">
                                            <a href="{{ url($page->visit_url ?? '/') }}" target="_blank" class="text-decoration-none" style="color: var(--accent-cyan)">
                                                {{ $page->visit_url }}
                                            </a>
                                        </td>
                                        <td class="text-end fw-700 mono">{{ $page->visit_count ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted mono">NO_DATA</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row mb-3 g-2">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-2">
                    <h2 class="chart-title mb-2 mono">MEDIUM_DISTRIBUTION</h2>
                    <div style="height: 200px; position: relative;">
                        <canvas id="mediumTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body p-2">
                    <h2 class="chart-title mb-2 mono">SOURCE_DISTRIBUTION</h2>
                    <div style="height: 200px; position: relative;">
                        <canvas id="sourcePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="analytics" class="mb-3">
        <div class="row g-2">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body p-2">
                        <h2 class="chart-title mb-2 mono">REFERRAL_PERFORMANCE</h2>
                        <div style="height: 200px; position: relative;">
                            <canvas id="uniqueVisitorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</div>
@endsection

@push('tracker-scripts')
<script src="{{ asset('vendor/tracker/js/chart.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartConfigs = [];

    // Tech Theme Chart Defaults
    Chart.defaults.font.family = "'JetBrains Mono', monospace";
    Chart.defaults.font.size = 10;
    Chart.defaults.color = '#8b949e';
    Chart.defaults.plugins.tooltip.backgroundColor = '#151921';
    Chart.defaults.plugins.tooltip.titleColor = '#00f2ff';
    Chart.defaults.plugins.tooltip.borderColor = '#30363d';
    Chart.defaults.plugins.tooltip.borderWidth = 1;
    Chart.defaults.plugins.tooltip.padding = 8;
    Chart.defaults.plugins.tooltip.cornerRadius = 2;

    const accentCyan = '#00f2ff';
    const accentOrange = '#f0883e';
    const accentPurple = '#8b5cf6';
    const accentGreen = '#10b981';
    const borderPrimary = '#30363d';

    // Unique Visitor Chart (Bar)
    chartConfigs.push({
        ctx: 'uniqueVisitorChart',
        config: {
            type: 'bar',
            data: {
                labels: @json($uniqueVisitorChart['labels']),
                datasets: [
                    {
                        label: 'VISITS',
                        data: @json($uniqueVisitorChart['visits']),
                        backgroundColor: accentCyan,
                        borderRadius: 0,
                        barThickness: 20,
                    },
                    {
                        label: 'UNIQUE',
                        data: @json($uniqueVisitorChart['unique_visitors']),
                        backgroundColor: accentOrange,
                        borderRadius: 0,
                        barThickness: 20,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 6, font: { size: 9 } } }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: borderPrimary } }
                }
            }
        }
    });

    // Last 30 Days Chart (Line)
    chartConfigs.push({
        ctx: 'last30DaysChart',
        config: {
            type: 'line',
            data: {
                labels: @json($last30DaysChart['labels']),
                datasets: [
                    {
                        label: 'PAGE_HITS',
                        data: @json($last30DaysChart['total_count']),
                        borderColor: accentCyan,
                        backgroundColor: 'rgba(0, 242, 255, 0.05)',
                        fill: true,
                        tension: 0,
                        borderWidth: 2,
                        pointRadius: 0,
                    },
                    {
                        label: 'UNIQUE_HITS',
                        data: @json($last30DaysChart['unique_count']),
                        borderColor: accentGreen,
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0,
                        borderWidth: 2,
                        pointRadius: 0,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: borderPrimary } }
                }
            }
        }
    });

    // Source Pie Chart
    chartConfigs.push({
        ctx: 'sourcePieChart',
        config: {
            type: 'doughnut',
            data: {
                labels: @json($sourceChart['labels']),
                datasets: [{
                    data: @json($sourceChart['counts']),
                    backgroundColor: [accentCyan, accentGreen, accentOrange, accentPurple, '#6366f1'],
                    borderWidth: 1,
                    borderColor: borderPrimary,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '80%',
                plugins: {
                    legend: { position: 'right', labels: { usePointStyle: true, padding: 10, font: { size: 9 } } }
                }
            }
        }
    });

    // Medium Trend Radar Chart
    chartConfigs.push({
        ctx: 'mediumTrendChart',
        config: {
            type: 'radar',
            data: {
                labels: @json($mediumTrendChart['labels']),
                datasets: @json($mediumTrendChart['datasets']).map((dataset, index) => ({
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: [accentCyan, accentGreen, accentOrange][index % 3],
                    backgroundColor: ['rgba(0, 242, 255, 0.1)', 'rgba(16, 185, 129, 0.1)'][index % 2],
                    borderWidth: 1,
                    pointRadius: 2
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 9 } } }
                },
                scales: {
                    r: { 
                        ticks: { display: false }, 
                        grid: { color: borderPrimary },
                        angleLines: { color: borderPrimary },
                        pointLabels: { font: { size: 8 } }
                    }
                }
            }
        }
    });

    // Render all charts
    chartConfigs.forEach(({ ctx, config }) => {
        const canvas = document.getElementById(ctx);
        if (canvas) {
            new Chart(canvas.getContext('2d'), config);
        }
    });
});
</script>
@endpush