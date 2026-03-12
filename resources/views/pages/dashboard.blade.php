@extends("tracker::app")

@section('tracker-content')
<div class="container-fluid"> 

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <h1 class="display-6 fw-800 text-dark mb-1" style="letter-spacing: -1.5px;">Dashboard</h1>
            <p class="text-secondary fw-500 mb-0">Overview of your traffic and referrals</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if(request()->has(['referral_code', 'date_from', 'date_to']))
                <div class="badge bg-white border text-dark px-3 py-2 d-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 500;">
                    <i class="bi bi-info-circle text-primary"></i>
                    {{ request('referral_code') ?: 'All Referrals' }} | {{ request('date_from') ?: 'All Time' }} - {{ request('date_to') ?: 'Present' }}
                </div>
            @endif
            <button class="btn btn-primary d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-sliders"></i> <span>Filters</span>
            </button>
        </div>
    </div>
    
    <!-- Key Metrics Section -->
    <section id="dashboard" class="mb-5">
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="counter-box shadow-sm">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3>{{ $totalVisitors ?? 0 }}</h3>
                            <p>Total Visitors</p>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded-3">
                            <i class="bi bi-eye text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="counter-box shadow-sm">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3>{{ $uniqueVisitors ?? 0 }}</h3>
                            <p>Unique Visitors</p>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded-3">
                            <i class="bi bi-person-check text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="counter-box shadow-sm">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3>{{ $totalRerferral ?? 0 }}</h3>
                            <p>Active Referrals</p>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded-3">
                            <i class="bi bi-link-45deg text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="counter-box shadow-sm">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3>{{ $totalUniqueSource ?? 0 }}</h3>
                            <p>Unique Sources</p>
                        </div>
                        <div class="bg-info bg-opacity-10 p-2 rounded-3">
                            <i class="bi bi-share text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="chart-title">Performance (Last 30 Days)</h2>
                        <div style="height: 300px; position: relative;">
                            <canvas id="last30DaysChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="visitors" class="row mb-5">
        <div class="col-lg-8">    
            <div class="card">
                <div class="card-body">
                    <h2 class="chart-title">Latest Visitors</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Country</th>
                                    <th>Referral Code</th>
                                    <th>Source & Medium</th>
                                    <th>Visits</th>
                                    <th>First Visit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($visitors as $visitor)
                                    <tr>
                                        <td>
                                            {!! $visitor->country_flag !!} {{ $visitor->country_name }}
                                        </td>
                                        <td>{{ $visitor->referral_code }}</td>
                                        <td>{{ $visitor->utm_source }} - {{ $visitor->utm_medium }}</td>
                                        <td>{{ $visitor->visits ?? 0 }}</td>
                                        <td>{{ $visitor->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No visitors found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">    
            <div class="card">
                <div class="card-body">
                    <h2 class="chart-title">Most Visited Page</h2>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Pages</th>
                                    <th>Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($mostVisitedPages as $page)
                                    <tr>
                                        <td>
                                            <a href="{{ url($page->visit_url ?? '/') }}" target="_blank" class="text-black d-block">
                                                {{ $page->visit_url }}
                                            </a>
                                        </td>
                                        <td>{{ $page->visit_count ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No ip address found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="visitors" class="row mb-5">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="chart-title">Visitor Trends by Medium</h2>
                    <div style="height: 300px; position: relative;">
                        <canvas id="mediumTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="chart-title">Visitor Distribution by Source</h2>
                    <div style="height: 300px; position: relative;">
                        <canvas id="sourcePieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Detailed Analytics Section -->
    <section id="analytics" class="mb-5">
        <h2 class="h4 mb-4">Detailed Analytics</h2>
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="chart-title">Performance by Referral Codes</h2>
                        <div style="height: 300px; position: relative;">
                            <canvas id="uniqueVisitorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="chart-title">Campaign Performance</h2>
                        <div style="height: 300px; position: relative;">
                            <canvas id="campaignBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </section>
</div>
@endsection

@push('tracker-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartConfigs = [];

    // Common Chart Defaults
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.tooltip.backgroundColor = '#1e1b4b';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;

    // Unique Visitor Chart (Bar)
    chartConfigs.push({
        ctx: 'uniqueVisitorChart',
        config: {
            type: 'bar',
            data: {
                labels: @json($uniqueVisitorChart['labels']),
                datasets: [
                    {
                        label: 'Total Visits',
                        data: @json($uniqueVisitorChart['visits']),
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                    },
                    {
                        label: 'Unique Visitors',
                        data: @json($uniqueVisitorChart['unique_visitors']),
                        backgroundColor: '#f43f5e',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 6 } }
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
                        label: 'Page Hits',
                        data: @json($last30DaysChart['total_count']),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.05)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0,
                    },
                    {
                        label: 'Unique Hits',
                        data: @json($last30DaysChart['unique_count']),
                        borderColor: '#10b981',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 3,
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
                    y: { border: { dash: [4, 4] }, grid: { color: '#f1f5f9' } }
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
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
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
                    borderColor: ['#6366f1', '#10b981', '#f59e0b'][index % 3],
                    backgroundColor: ['rgba(99, 102, 241, 0.1)', 'rgba(16, 185, 129, 0.1)'][index % 2],
                    borderWidth: 2,
                    pointRadius: 3
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    r: { ticks: { display: false }, grid: { color: '#f1f5f9' } }
                }
            }
        }
    });

    // Campaign Performance Chart
    chartConfigs.push({
        ctx: 'campaignBarChart',
        config: {
            type: 'bar',
            data: {
                labels: @json($campaignChart['labels']),
                datasets: [{
                    label: 'Visits',
                    data: @json($campaignChart['visits']),
                    backgroundColor: '#6366f1',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
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