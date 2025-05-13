@extends('layouts.admin')

@section('main-content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('Dashboard') }}</h1>

    @if (session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success border-left-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="row">
        <!-- Total Store Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Store</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">50</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Departments Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Departments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">20</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Best Performance Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Best Performance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">Store 2</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Updated Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Last Updated</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">April, 2025</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">{{ __('Users') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $widget['users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-2">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Forecast - Department {{ $selectedDept }}</h6>
                    <div class="d-flex">
                        <!-- Dropdown Store -->
                        <div class="dropdown ml-2">
                            <button class="btn btn-sm btn-primary dropdown-toggle"
                                    style="width: 130px; height: 40px; font-size: 16px;"
                                    type="button"
                                    id="storeDropdown"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                <i class="fas fa-store mr-2"></i> Store {{ $selectedStore }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right"
                                 aria-labelledby="storeDropdown"
                                 style="font-size: 16px;">
                                @for($s = 1; $s <= 5; $s++)
                                    <a class="dropdown-item" href="?store={{ $s }}&department={{ $selectedDept }}&period={{ $selectedPeriod }}">
                                        <i class="fas fa-store mr-2"></i> Store {{ $s }}
                                    </a>
                                @endfor
                            </div>
                        </div>

                        <!-- Dropdown Department -->
                        <div class="dropdown ml-2">
                            <button class="btn btn-sm btn-primary dropdown-toggle"
                                    style="width: 180px; height: 40px; font-size: 16px;"
                                    type="button"
                                    id="departmentDropdown"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                <i class="fas fa-building mr-2"></i> Department {{ $selectedDept }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right"
                                 aria-labelledby="departmentDropdown"
                                 style="font-size: 16px;">
                                @for($i = 1; $i <= 10; $i++)
                                    <a class="dropdown-item" href="?store={{ $selectedStore }}&department={{ $i }}&period={{ $selectedPeriod }}">
                                        <i class="fas fa-building mr-2"></i> Department {{ str_pad($i, 2, ' ', STR_PAD_LEFT) }}
                                    </a>
                                @endfor
                            </div>
                        </div>

                        <!-- Dropdown Period -->
                        <div class="dropdown ml-2">
                            <button class="btn btn-sm btn-primary dropdown-toggle"
                                    style="width: 130px; height: 40px; font-size: 16px;"
                                    type="button"
                                    id="periodDropdown"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                <i class="fas fa-clock mr-2"></i> {{ ucfirst($selectedPeriod) }}
                            </button>
                            <div class="dropdown-menu dropdown-menu-right"
                                 aria-labelledby="periodDropdown"
                                 style="font-size: 16px;">
                                <a class="dropdown-item" href="?store={{ $selectedStore }}&department={{ $selectedDept }}&period=daily">
                                    <i class="fas fa-calendar-day mr-2"></i> Harian
                                </a>
                                <a class="dropdown-item" href="?store={{ $selectedStore }}&department={{ $selectedDept }}&period=weekly">
                                    <i class="fas fa-calendar-week mr-2"></i> Mingguan
                                </a>
                                <a class="dropdown-item" href="?store={{ $selectedStore }}&department={{ $selectedDept }}&period=monthly">
                                    <i class="fas fa-calendar-alt mr-2"></i> Bulanan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body" wire:ignore style="position: relative; height:400px;">
                    <canvas id="forecastChart"></canvas>
                </div>

                <div class="card-footer">
                    <small class="text-muted">
                        Showing data from {{ $dataStart }} to {{ $dataEnd }} | Department {{ $selectedDept }} | Last updated: {{ now()->format('M d, Y H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('forecastChart').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_merge($labelsActual, $labelsForecast)) !!},
                datasets: [
                    {
                        label: 'Actual Sales - Dept {{ $selectedDept }}',
                        data: {!! json_encode($actualPart) !!},
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2
                    },
                    {
                        label: 'Forecast (30 Hari Terakhir)',
                        data: Array({{ count($actualPart) }}).fill(null).concat({!! json_encode($forecastPart) !!}),
                        borderColor: '#e74a3b',
                        backgroundColor: 'rgba(231, 74, 59, 0.1)',
                        borderDash: [5, 5],
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 2
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                            }
                        }
                    },
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Department {{ $selectedDept }} Sales Performance'
                    },
                    annotation: {
                        annotations: {
                            lineForecastStart: {
                                type: 'line',
                                xMin: '{{ $transitionLabel }}',
                                xMax: '{{ $transitionLabel }}',
                                borderColor: 'purple',
                                borderWidth: 2,
                                borderDash: [5, 5],
                                label: {
                                    enabled: true,
                                    content: 'Start Forecast',
                                    position: 'start',
                                    color: '#6c757d',
                                    backgroundColor: '#fff',
                                    font: { size: 10 }
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: { drawBorder: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
    </script>
    @endpush
@endsection
