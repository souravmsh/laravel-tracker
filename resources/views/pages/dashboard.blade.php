@extends("tracker::app")

@section('tracker-content')
<div class="container-fluid"> 

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="display-5 mb-4 fw-bold text-dark">Dashboard</h1>

        <div class="btn-group">
            @if(request()->has(['referral_code', 'date_from', 'date_to']))
            <div class="btn btn-info" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="fw-400">
                    {{ request('referral_code') ? 'Referral: ' . request('referral_code') : 'All Referrals' }}
                </span>
                <span class="mx-2">|</span>
                <span class="fw-400">
                    Date: 
                    {{ request('date_from') ?: 'All Time' }}
                    &ndash;
                    {{ request('date_to') ?: 'Present' }}
                </span>
            </div>
            @endif
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                <i class="bi bi-filter"></i> Filters
            </button>
        </div>
    </div>
    
    <!-- Key Metrics Section -->
    <section id="dashboard" class="mb-5">
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="counter-box">
                    <h3>{{ $totalVisitors ?? 0 }}</h3>
                    <p>Total Visitors</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="counter-box">
                    <h3>{{ $uniqueVisitors ?? 0 }}</h3>
                    <p>Unique Visitors</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="counter-box">
                    <h3>{{ $totalRerferral ?? 0 }}</h3>
                    <p>Total Referrals</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="counter-box">
                    <h3>{{ $totalUniqueSource ?? 0 }}</h3>
                    <p>Total Unique Sources</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="chart-title">Performance (Last 30 Days)</h2>
                        <canvas id="last30DaysChart" height="100"></canvas>
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
                    <canvas id="mediumTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="chart-title">Visitor Distribution by Source</h2>
                    <canvas id="sourcePieChart" height="100"></canvas>
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
                        <canvas id="uniqueVisitorChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h2 class="chart-title">Campaign Performance</h2>
                        <canvas id="campaignBarChart" height="100"></canvas>
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
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Unique Visitors',
                        data: @json($uniqueVisitorChart['unique_visitors']),
                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Count' }
                    },
                    x: {
                        title: { display: true, text: 'Referral Codes' }
                    }
                },
                plugins: {
                    legend: { display: true, position: 'top' }
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
                        label: 'Total Page Hits',
                        data: @json($last30DaysChart['total_count']),
                        backgroundColor: 'rgba(0, 123, 255, 0.2)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1,
                        fill: true
                    },
                    {
                        label: 'Total Unique Hits',
                        data: @json($last30DaysChart['unique_count']),
                        backgroundColor: 'rgba(40, 167, 69, 0.2)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Date' }},
                    y: { title: { display: true, text: 'Count' }, beginAtZero: true }
                },
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: true, text: 'Total Page Hits (Last 30 Days)' }
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
                datasets: [
                    {
                        label: 'Visitors by Source',
                        data: @json($sourceChart['counts']),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: true, text: 'Visitor Distribution by UTM Source' }
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
                    backgroundColor: `rgba(${(index * 50) % 255}, ${(100 + index * 50) % 255}, ${(200 - index * 30) % 255}, 0.2)`,
                    borderColor: `rgba(${(index * 50) % 255}, ${(100 + index * 50) % 255}, ${(200 - index * 30) % 255}, 1)`,
                    borderWidth: 1
                }))
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top' },
                    title: { display: true, text: 'Visitor Distribution by UTM Medium' }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
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
                    label: 'All Campaign',
                    data: @json($campaignChart['visits']),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Campaign' }},
                    y: { title: { display: true, text: 'Total Visits' }, beginAtZero: true }
                },
                plugins: {
                    legend: { display: true, position: 'top' },
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