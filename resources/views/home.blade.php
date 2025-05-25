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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStores }}</div>
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
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Departments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalDepartments }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $lastUpdatedDateOnly ?? 'N/A' }}
                            </div>
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
                @php
                    // Definisikan semua nilai filter saat ini sekali di atas
                    $currentFilterDept = request('department', $selectedDept ?? 1);
                    $currentFilterStore = request('store', $selectedStore ?? 1);
                    $currentFilterPeriod = request('period', 'monthly');
                    $currentFilterChartType = request('chartType', 'line'); // Default ke 'line'

                    $periodDisplayLabels = [
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                    ];
                    $chartTypeDisplayLabels = [
                        'line' => 'Garis (Line)',
                        'bar' => 'Batang (Bar)',
                        'pie' => 'Pai (Pie)',
                        'doughnut' => 'Donat (Doughnut)',
                    ];

                    $periodIcons = [
                        'daily' => 'fa-calendar-day',
                        'weekly' => 'fa-calendar-week',
                        'monthly' => 'fa-calendar-alt',
                    ];
                    $chartIcons = [
                        'line' => 'fa-chart-line',
                        'bar' => 'fa-chart-bar',
                        'pie' => 'fa-chart-pie',
                        'doughnut' => 'fa-dot-circle',
                    ];
                @endphp

                <div class="card-header py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
                        <h6 class="m-0 font-weight-bold text-primary mb-3 mb-md-0" id="chartMainTitle">
                            Sales Performance - Store {{ $currentFilterStore }} / Dept {{ $currentFilterDept }}
                        </h6>
                        <div class="d-flex flex-wrap justify-content-start justify-content-md-end align-items-center">
                            <div class="dropdown mr-1 mb-2">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle text-nowrap"
                                    style="min-width: 130px; height: 38px; font-size: 0.8rem;" type="button"
                                    id="storeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-store mr-1"></i> Store: {{ $currentFilterStore }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="storeDropdown"
                                    style="font-size: 0.8rem;">
                                    @foreach ($distinctStores as $storeId)
                                        <a class="dropdown-item {{ $storeId == $currentFilterStore ? 'active' : '' }}"
                                            href="?department={{ $currentFilterDept }}&store={{ $storeId }}&period={{ $currentFilterPeriod }}&chartType={{ $currentFilterChartType }}">
                                            <i class="fas fa-store fa-fw mr-2"></i> Store
                                            {{ str_pad($storeId, 2, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="dropdown mr-1 mb-2">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle text-nowrap"
                                    style="min-width: 150px; height: 38px; font-size: 0.8rem;" type="button"
                                    id="departmentDropdown" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fas fa-building mr-1"></i> Dept: {{ $currentFilterDept }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm"
                                    aria-labelledby="departmentDropdown" style="font-size: 0.8rem;">
                                    @foreach ($distinctDepartments as $deptId)
                                        <a class="dropdown-item {{ $deptId == $currentFilterDept ? 'active' : '' }}"
                                            href="?department={{ $deptId }}&store={{ $currentFilterStore }}&period={{ $currentFilterPeriod }}&chartType={{ $currentFilterChartType }}">
                                            <i class="fas fa-building fa-fw mr-2"></i> Department
                                            {{ str_pad($deptId, 2, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="dropdown mr-1 mb-2">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle text-nowrap"
                                    style="min-width: 130px; height: 38px; font-size: 0.8rem;" type="button"
                                    id="periodDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas {{ $periodIcons[$currentFilterPeriod] ?? 'fa-calendar-alt' }} mr-1"></i>
                                    {{ $periodDisplayLabels[$currentFilterPeriod] ?? ucfirst($currentFilterPeriod) }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="periodDropdown"
                                    style="font-size: 0.8rem; min-width: 130px;">
                                    @foreach ($periodDisplayLabels as $periodValue => $periodLabel)
                                        <a class="dropdown-item {{ $periodValue == $currentFilterPeriod ? 'active' : '' }}"
                                            href="?department={{ $currentFilterDept }}&store={{ $currentFilterStore }}&period={{ $periodValue }}&chartType={{ $currentFilterChartType }}">
                                            <i
                                                class="fas {{ $periodIcons[$periodValue] ?? 'fa-calendar-alt' }} fa-fw mr-2"></i>
                                            {{ $periodLabel }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="dropdown mr-1 mb-2">
                                <button class="btn btn-sm btn-outline-success dropdown-toggle text-nowrap"
                                    style="min-width: 150px; height: 38px; font-size: 0.8rem;" type="button"
                                    id="chartTypeDropdown" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fas {{ $chartIcons[$currentFilterChartType] ?? 'fa-chart-line' }} mr-1"></i>
                                    {{ $chartTypeDisplayLabels[$currentFilterChartType] ?? ucfirst($currentFilterChartType) }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm"
                                    aria-labelledby="chartTypeDropdown" id="chartTypeDropdownMenu"
                                    style="font-size: 0.8rem; min-width: 150px;">
                                    @foreach ($chartTypeDisplayLabels as $typeValue => $typeLabel)
                                        <a class="dropdown-item chart-type-selector {{ $typeValue == $currentFilterChartType ? 'active' : '' }}"
                                            href="?department={{ $currentFilterDept }}&store={{ $currentFilterStore }}&period={{ $currentFilterPeriod }}&chartType={{ $typeValue }}">
                                            <i
                                                class="fas {{ $chartIcons[$typeValue] ?? 'fa-chart-line' }} fa-fw mr-2"></i>
                                            {{ $typeLabel }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            <div class="mb-2">
                                <button id="resetZoomBtn" class="btn btn-sm btn-outline-info" title="Reset Zoom"
                                    style="height: 38px; font-size: 0.8rem;">
                                    <i class="fas fa-search-minus"></i> Reset Zoom
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body" wire:ignore>
                    <div style="position: relative; width: 100%; height: 480px;">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>

                <div class="card-footer">
                    <small class="text-muted">
                        Showing data for Department {{ $selectedDept }} |
                        Last updated:
                        {{ $lastUpdated ?? 'N/A' }}
                    </small>
                </div>
            </div>

            @push('scripts')
                <script>
                    let forecastChartInstance = null; // Variabel untuk menyimpan instance chart

                    // Fungsi untuk membuat atau memperbarui chart
                    function createOrUpdateForecastChart(chartType) {
                        const ctx = document.getElementById('forecastChart');
                        if (!ctx) {
                            console.error("Canvas element with ID 'forecastChart' not found.");
                            return;
                        }

                        // Data dari controller
                        const labels = @json($months ?? []); // Ini akan menjadi judul tooltip (tanggal)
                        const actualSalesData = @json($actualSales ?? []);
                        const forecastSalesData = @json($forecastSales ?? []);
                        const currentDept = @json($selectedDept ?? 1);
                        const currentStore = @json($selectedStore ?? 1);

                        // Variabel ini dibutuhkan untuk judul dinamis chart, pastikan ada di Blade
                        const currentPeriodLabel = @json($periodDisplayLabels[$currentFilterPeriod] ?? ucfirst($currentFilterPeriod ?? 'monthly'));
                        const currentChartTypeLabel = @json($chartTypeDisplayLabels[$currentFilterChartType] ?? ucfirst($currentFilterChartType ?? 'line'));

                        if (forecastChartInstance) {
                            forecastChartInstance.destroy();
                        }

                        // --- Konfigurasi Dataset Dasar ---
                        let baseDatasets = [{
                                label: 'Actual Sales - Dept ' + currentDept, // Ini akan muncul di body tooltip
                                data: actualSalesData,
                                borderColor: 'rgba(78, 115, 223, 1)',
                                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                order: 2
                            },
                            {
                                label: 'Forecast Sales - Dept ' + currentDept, // Ini akan muncul di body tooltip
                                data: forecastSalesData,
                                borderColor: 'rgba(231, 74, 59, 1)',
                                backgroundColor: 'rgba(231, 74, 59, 0.2)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                borderDash: [5, 5],
                                order: 1
                            }
                        ];

                        let finalDatasets = JSON.parse(JSON.stringify(baseDatasets));
                        let chartSpecificOptions = {};
                        let defaultXScale = {
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 15
                            },
                            grid: {
                                display: true,
                                color: "rgba(234, 236, 244, 0.5)",
                                drawBorder: false,
                                borderDash: [],
                            }
                        };
                        let defaultYScale = {
                            beginAtZero: false,
                            ticks: {
                                maxTicksLimit: 10,
                                padding: 10,
                                callback: function(value) {
                                    return '$' + parseFloat(value).toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            },
                            grid: {
                                display: true,
                                color: "rgb(234, 236, 244)",
                                drawBorder: false,
                                borderDash: [2],
                            }
                        };

                        // --- Penyesuaian berdasarkan Tipe Chart ---
                        switch (chartType) {
                            case 'bar':
                                finalDatasets.forEach(ds => {
                                    ds.backgroundColor = ds.borderColor;
                                    ds.tension = 0;
                                    ds.borderDash = [];
                                    ds.fill = true;
                                });
                                chartSpecificOptions.scales = {
                                    x: {
                                        ...defaultXScale,
                                        stacked: false
                                    },
                                    y: {
                                        ...defaultYScale,
                                        beginAtZero: true,
                                        stacked: false
                                    }
                                };
                                break;
                            case 'area':
                                finalDatasets[0].fill = 'origin';
                                finalDatasets[0].backgroundColor = 'rgba(78, 115, 223, 0.2)';
                                finalDatasets[1].fill = 'origin';
                                finalDatasets[1].backgroundColor = 'rgba(231, 74, 59, 0.2)';
                                finalDatasets[1].borderDash = [];
                                chartSpecificOptions.scales = {
                                    x: {
                                        ...defaultXScale
                                    },
                                    y: {
                                        ...defaultYScale,
                                        beginAtZero: true
                                    }
                                };
                                break;
                            case 'pie':
                            case 'doughnut':
                            case 'polarArea':
                                finalDatasets = [{
                                    label: 'Actual Sales Distribution - Dept ' + currentDept,
                                    data: actualSalesData.filter(val => val !== null && val > 0),
                                    backgroundColor: [
                                        'rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(54, 185, 204, 0.8)',
                                        'rgba(246, 194, 62, 0.8)', 'rgba(231, 74, 59, 0.8)', 'rgba(133, 135, 150, 0.8)',
                                        'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)',
                                        'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)'
                                    ],
                                    borderColor: '#fff',
                                    borderWidth: 1,
                                    hoverOffset: 8
                                }];
                                chartSpecificOptions.scales = (chartType === 'polarArea') ? {
                                    r: {
                                        angleLines: {
                                            display: true
                                        },
                                        suggestedMin: 0,
                                        pointLabels: {
                                            font: {
                                                size: 10
                                            }
                                        },
                                        ticks: {
                                            z: 1,
                                            backdropColor: 'transparent',
                                            callback: function(value) {
                                                return '$' + parseFloat(value).toLocaleString('en-US', {
                                                    minimumFractionDigits: 0,
                                                    maximumFractionDigits: 0
                                                });
                                            }
                                        }
                                    }
                                } : {};
                                break;
                            case 'radar':
                                finalDatasets.forEach(ds => {
                                    ds.fill = true;
                                });
                                chartSpecificOptions.scales = {
                                    r: {
                                        angleLines: {
                                            display: true
                                        },
                                        suggestedMin: 0,
                                        pointLabels: {
                                            font: {
                                                size: 10
                                            }
                                        },
                                        ticks: {
                                            z: 1,
                                            backdropColor: 'transparent',
                                            callback: function(value) {
                                                return '$' + parseFloat(value).toLocaleString('en-US', {
                                                    minimumFractionDigits: 0,
                                                    maximumFractionDigits: 0
                                                });
                                            }
                                        }
                                    }
                                };
                                break;
                            case 'line':
                            default:
                                chartType = 'line';
                                finalDatasets[0].fill = true;
                                finalDatasets[0].backgroundColor = 'rgba(78, 115, 223, 0.05)';
                                finalDatasets[1].fill = false;
                                chartSpecificOptions.scales = {
                                    x: {
                                        ...defaultXScale
                                    },
                                    y: {
                                        ...defaultYScale
                                    }
                                };
                                break;
                        }

                        const chartOptions = {
                            maintainAspectRatio: false,
                            responsive: true,
                            interaction: {
                                mode: (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') ? 'nearest' :
                                    'index',
                                intersect: (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') ? true :
                                    false, // Intersect true untuk pie/doughnut
                            },
                            plugins: {
                                tooltip: {
                                    backgroundColor: "rgba(0,0,0,0.8)", // Latar belakang gelap
                                    titleColor: '#fff', // Judul putih
                                    bodyColor: "#fff", // Isi putih
                                    titleFont: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 12
                                    },
                                    borderColor: '#444', // Border lebih gelap
                                    borderWidth: 1,
                                    padding: 10,
                                    displayColors: true, // MENAMPILKAN KOTAK WARNA
                                    intersect: false, // Tooltip muncul meski tidak tepat di atas titik (umumnya)
                                    mode: 'index', // Tooltip untuk semua dataset pada index yang sama
                                    callbacks: {
                                        // title: function(tooltipItems) { // Default title sudah mengambil label sumbu X (tanggal)
                                        // if (tooltipItems.length > 0) {
                                        // return tooltipItems[0].label;
                                        // }
                                        // return '';
                                        // },
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') {
                                                label = context.label || '';
                                            }
                                            if (label) {
                                                label += ': ';
                                            }
                                            const value = (chartType === 'pie' || chartType === 'doughnut' || chartType ===
                                                    'polarArea') ?
                                                context.raw :
                                                (context.parsed ? context.parsed.y : null);

                                            if (value !== null && typeof value !== 'undefined') {
                                                label += '$' + parseFloat(value).toLocaleString('en-US', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                });
                                            } else {
                                                label += 'N/A';
                                            }
                                            return label;
                                        }
                                    }
                                },
                                legend: {
                                    position: 'top',
                                    labels: {
                                        font: {
                                            size: 12
                                        },
                                        boxWidth: 20,
                                        padding: 20
                                    }
                                },

                                zoom: {
                                    pan: {
                                        enabled: true,
                                        mode: 'xy',
                                        threshold: 5
                                    },
                                    zoom: {
                                        wheel: {
                                            enabled: true
                                        },
                                        pinch: {
                                            enabled: true
                                        },
                                        mode: 'xy'
                                    }
                                }
                            },
                            scales: chartSpecificOptions.scales || {
                                x: defaultXScale,
                                y: defaultYScale
                            }
                        };

                        forecastChartInstance = new Chart(ctx, {
                            type: chartType,
                            data: {
                                labels: labels, // Ini akan digunakan untuk judul tooltip secara default
                                datasets: finalDatasets
                            },
                            options: chartOptions
                        });

                        const mainTitleElement = document.getElementById('chartMainTitle');
                        if (mainTitleElement) {
                            mainTitleElement.textContent = `Sales Performance - Store ${currentStore} / Dept ${currentDept}`;
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const initialChartType = urlParams.get('chartType') || 'line';
                        createOrUpdateForecastChart(initialChartType);

                        const resetZoomButton = document.getElementById('resetZoomBtn');
                        if (resetZoomButton) {
                            resetZoomButton.addEventListener('click', () => {
                                if (forecastChartInstance && typeof forecastChartInstance.resetZoom === 'function') {
                                    forecastChartInstance.resetZoom();
                                } else {
                                    console.error("Chart instance or resetZoom function is not available.");
                                }
                            });
                        } else {
                            console.warn("Reset zoom button (resetZoomBtn) not found.");
                        }
                    });
                </script>
            @endpush
        @endsection
