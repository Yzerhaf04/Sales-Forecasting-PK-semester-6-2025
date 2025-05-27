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
                        'line' => 'Line',
                        'bar' => 'Bar',
                    ];

                    $periodIcons = [
                        'daily' => 'fa-calendar-day',
                        'weekly' => 'fa-calendar-week',
                        'monthly' => 'fa-calendar-alt',
                    ];
                    $chartIcons = [
                        'line' => 'fa-chart-line',
                        'bar' => 'fa-chart-bar',
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
                            {{-- Zoom Button --}}
                            <div class="">
                                <div class="button-container">
                                    <div class="btn-group mb-2 " role="group" aria-label="Zoom controls">
                                        <button id="zoomInBtn" class="btn btn-sm btn-outline-secondary" title="Zoom In"
                                            style="height: 38px; font-size: 0.8rem; min-width: 45px;">
                                            <i class="fas fa-search-plus"></i>
                                        </button>
                                        <button id="zoomOutBtn" class="btn btn-sm btn-outline-secondary" title="Zoom Out"
                                            style="height: 38px; font-size: 0.8rem; min-width: 45px;">
                                            <i class="fas fa-search-minus"></i>
                                        </button>
                                        <button id="resetZoomBtn" class="btn btn-sm btn-outline-secondary"
                                            title="Reset Zoom" style="height: 38px; font-size: 0.8rem; min-width: 45px;">
                                            <i class="fas fa-compress-arrows-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body" wire:ignore>
                        <div style="position: relative; width: 100%; height: 500px;">
                            <canvas id="forecastChart"></canvas>
                        </div>
                    </div>

                    <div class="card-footer">
                        <small class="text-muted">
                            Showing data for Department {{ $selectedDept }} │
                            Last updated:
                            {{ $lastUpdated ?? 'N/A' }}
                        </small>
                    </div>
                </div>
            </div>

            {{-- Analisis Sentimen --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sentimen Analisis</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Kolom Kiri: Card Jumlah Sentimen (Ditumpuk Vertikal) --}}
                        <div class="col-lg-5 col-md-12 mb-4 mb-lg-0">
                            {{-- Card Sentimen Positif --}}
                            <div class="card sentiment-card positive-card shadow-sm mb-3">
                                <div class="card-body text-center d-flex flex-column justify-content-center py-5">
                                    <div class="text-uppercase text-muted mb-1" style="font-size: 1.0rem;">Sentimen
                                        Positif</div>
                                    <div class="h4 font-weight-bold text-success mb-0">
                                        {{ number_format($jumlahPositif ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Card Sentimen Negatif --}}
                            <div class="card sentiment-card negative-card shadow-sm mb-3">
                                <div class="card-body text-center d-flex flex-column justify-content-center py-5">
                                    <div class="text-uppercase text-muted mb-1" style="font-size: 1.0rem;">Sentimen
                                        Negatif</div>
                                    <div class="h4 font-weight-bold text-danger mb-0">
                                        {{ number_format($jumlahNegatif ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Card Sentimen Netral --}}
                            <div class="card sentiment-card neutral-card shadow-sm">
                                <div class="card-body text-center d-flex flex-column justify-content-center py-5">
                                    <div class="text-uppercase text-muted mb-1" style="font-size: 1.0rem;">Sentimen Netral
                                    </div>
                                    <div class="h4 font-weight-bold text-warning mb-0">
                                        {{ number_format($jumlahNetral ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Chart Donat Kata Populer --}}
                        <div class="col-lg-7 col-md-12">
                            {{-- Container untuk chart donat, hilangkan border-top jika tidak diperlukan --}}
                            {{-- <div class="h-100"> --}}
                            <h6 class="m-0 font-weight-bold text-primary mb-5 text-center text-lg-left"
                                style="font-size: 16px;">Kata Populer
                            </h6>
                            <div style="height: 350px; width:100%; max-width: 400px; margin: 0 auto; position: relative;">
                                {{-- Ukuran max-width disesuaikan agar proporsional di kolom yang lebih besar --}}
                                <canvas id="sentimentDonutChart"></canvas>
                            </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <small class="text-muted">
                        Showing data for Sentiment Analysis │
                        Last updated:
                        {{ $sentimentLastUpdateDisplay ?? 'N/A' }}
                    </small>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                let forecastChartInstance = null;
                let sentimentDonutChartInstance = null;

                // --- Configuration for Zoom Plugin (Chart.js v3+) ---
                const zoomPluginOptions = {
                    pan: {
                        enabled: true,
                        mode: 'xy', // Pan both x and y axes
                        threshold: 5, // Pixels to drag before panning starts
                        modifierKey: null, // No modifier key needed for panning
                    },
                    zoom: {
                        wheel: {
                            enabled: false, // Enable zooming with mouse wheel
                            modifierKey: null, // No modifier key needed for wheel zoom
                        },
                        pinch: {
                            enabled: true // Enable zooming with pinch gesture on touch devices
                        },
                        drag: {
                            enabled: false // Disable drag-to-zoom (box zoom) by default, can be enabled if needed
                        },
                        mode: 'xy', // Zoom both x and y axes
                    }
                };

                // Function to create or update the main forecast chart
                function createOrUpdateForecastChart(chartType) {
                    const ctx = document.getElementById('forecastChart');
                    if (!ctx) {
                        console.error("Canvas element with ID 'forecastChart' not found.");
                        return;
                    }

                    const labels = @json($months ?? []);
                    const actualSalesData = @json($actualSales ?? []);
                    const forecastSalesData = @json($forecastSales ?? []);
                    const currentDept = @json($selectedDept ?? 1);
                    const currentStore = @json($selectedStore ?? 1);
                    const currentPeriodLabel = @json($periodDisplayLabels[$currentFilterPeriod] ?? ucfirst($currentFilterPeriod ?? 'monthly'));
                    const currentChartTypeLabel = @json($chartTypeDisplayLabels[$currentFilterChartType] ?? ucfirst($currentFilterChartType ?? 'line'));

                    if (forecastChartInstance) {
                        forecastChartInstance.destroy();
                    }

                    let baseDatasets = [{
                            label: 'Actual Sales - Dept ' + currentDept,
                            data: actualSalesData,
                            borderColor: 'rgba(78, 115, 223, 1)',
                            backgroundColor: 'rgba(78, 115, 223, 0.2)', // Default, will be adjusted by chart type
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false, // Default, will be adjusted
                            order: 2
                        },
                        {
                            label: 'Forecast Sales - Dept ' + currentDept,
                            data: forecastSalesData,
                            borderColor: 'rgba(231, 74, 59, 1)',
                            backgroundColor: 'rgba(231, 74, 59, 0.2)', // Default
                            borderWidth: 2,
                            tension: 0.3,
                            fill: false, // Default
                            borderDash: [5, 5],
                            order: 1
                        }
                    ];

                    let finalDatasets = JSON.parse(JSON.stringify(baseDatasets));
                    let chartSpecificOptions = {};
                    let defaultXScale = {
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 15,
                            color: '#666'
                        },
                        grid: {
                            display: true,
                            color: "rgba(0, 0, 0, 0.05)",
                            drawBorder: false
                        }
                    };
                    let defaultYScale = {
                        beginAtZero: false,
                        ticks: {
                            maxTicksLimit: 10,
                            padding: 10,
                            color: '#666',
                            callback: function(value) {
                                return '$' + parseFloat(value).toLocaleString('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        },
                        grid: {
                            display: true,
                            color: "rgba(0, 0, 0, 0.05)",
                            drawBorder: false,
                            borderDash: [2]
                        }
                    };

                    switch (chartType) {
                        case 'bar':
                            finalDatasets.forEach(ds => {
                                ds.backgroundColor = ds.borderColor.replace('1)', '0.7)'); // Solid bar color
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
                        case 'area': // Example, not in current dropdown but handled
                            finalDatasets[0].fill = 'origin';
                            finalDatasets[0].backgroundColor = 'rgba(78, 115, 223, 0.2)';
                            finalDatasets[1].fill = 'origin'; // Forecast can also be area
                            finalDatasets[1].backgroundColor = 'rgba(231, 74, 59, 0.2)';
                            finalDatasets[1].borderDash = []; // Solid line for area edge
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
                            // Cases for 'doughnut', 'polarArea', 'radar' for the main chart if needed, using actualSalesData
                            // These are less common for time-series sales comparison but the structure is here.
                        case 'doughnut': // Doughnut for main sales data (if selected via URL)
                        case 'polarArea': // PolarArea for main sales data (if selected via URL)
                            finalDatasets = [{
                                label: 'Actual Sales Distribution - Dept ' + currentDept,
                                data: actualSalesData.filter(val => val !== null && val >
                                    0), // Ensure positive values for these chart types
                                backgroundColor: [
                                    'rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(54, 185, 204, 0.8)',
                                    'rgba(246, 194, 62, 0.8)', 'rgba(231, 74, 59, 0.8)', 'rgba(133, 135, 150, 0.8)',
                                    'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)',
                                    'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)'
                                ].slice(0, actualSalesData.filter(val => val !== null && val > 0)
                                    .length), // Use only as many colors as data points
                                borderColor: '#fff',
                                borderWidth: 1,
                                hoverOffset: 8
                            }];
                            // Labels for these chart types should ideally correspond to the data points (e.g., months if data is monthly)
                            // For simplicity, we're using the main `labels` array.
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
                                        color: '#666',
                                        callback: function(value) {
                                            return '$' + parseFloat(value).toLocaleString('en-US');
                                        }
                                    }
                                }
                            } : {}; // Doughnut doesn't use scales in the same way
                            break;
                        case 'radar': // Radar for main sales data (if selected via URL)
                            finalDatasets.forEach(ds => {
                                ds.fill = true;
                                ds.backgroundColor = ds.borderColor.replace('1)', '0.2)');
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
                                        },
                                        color: '#666'
                                    },
                                    ticks: {
                                        z: 1,
                                        backdropColor: 'transparent',
                                        color: '#666',
                                        callback: function(value) {
                                            return '$' + parseFloat(value).toLocaleString('en-US');
                                        }
                                    }
                                }
                            };
                            break;
                        case 'line':
                        default:
                            chartType = 'line'; // Ensure chartType is explicitly line
                            finalDatasets[0].fill = true; // Subtle fill for actual sales line
                            finalDatasets[0].backgroundColor = 'rgba(78, 115, 223, 0.05)';
                            finalDatasets[1].fill = false; // No fill for forecast
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
                                false,
                        },
                        plugins: {
                            tooltip: {
                                backgroundColor: "rgba(0,0,0,0.8)",
                                titleColor: '#fff',
                                bodyColor: "#fff",
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                borderColor: '#444',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') {
                                            label = context.label || ''; // Use item label for these types
                                        }
                                        if (label) {
                                            label += ': ';
                                        }
                                        const value = (chartType === 'pie' || chartType === 'doughnut' || chartType ===
                                                'polarArea') ?
                                            context.raw : (context.parsed ? context.parsed.y : null);
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
                                    padding: 20,
                                    color: '#333'
                                }
                            },
                            zoom: zoomPluginOptions // Apply zoom configuration
                        },
                        scales: chartSpecificOptions.scales || {
                            x: defaultXScale,
                            y: defaultYScale
                        }
                    };

                    forecastChartInstance = new Chart(ctx, {
                        type: chartType,
                        data: {
                            labels: labels, // X-axis labels (e.g., months)
                            datasets: finalDatasets
                        },
                        options: chartOptions
                    });

                    const mainTitleElement = document.getElementById('chartMainTitle');
                    if (mainTitleElement) {
                        mainTitleElement.textContent =
                            `Sales Performance (${currentChartTypeLabel}) - Store ${currentStore} / Dept ${currentDept}`;
                    }
                }

                function formatNumberKMB(num) {
                    if (num < 1000) {
                        return num.toString();
                    } else if (num < 1000000) {
                        // Bagi dengan 1000, tampilkan 1 desimal jika bukan angka bulat
                        const thousands = num / 1000;
                        return (thousands % 1 === 0 ? thousands.toFixed(0) : thousands.toFixed(1).replace('.0', '')) + 'K';
                    } else if (num < 1000000000) {
                        // Bagi dengan 1,000,000
                        const millions = num / 1000000;
                        return (millions % 1 === 0 ? millions.toFixed(0) : millions.toFixed(1).replace('.0', '')) + 'M';
                    } else {
                        // Bagi dengan 1,000,000,000
                        const billions = num / 1000000000;
                        return (billions % 1 === 0 ? billions.toFixed(0) : billions.toFixed(1).replace('.0', '')) + 'B';
                    }
                }

                function renderSentimentDonutChart() {
                    const canvasElement = document.getElementById('sentimentDonutChart');
                    if (!canvasElement) {
                        console.error("Elemen Canvas dengan ID 'sentimentDonutChart' tidak ditemukan.");
                        return;
                    }
                    const ctx = canvasElement.getContext('2d');

                    // Data dari controller Anda (pastikan variabel ini ada di Blade)
                    const sentimentLabels = @json($sentimentDonutLabels ?? []);
                    const sentimentDataValues = @json($sentimentDonutDataValues ?? []);
                    const totalCommentsRaw = @json($totalSentimenComments ?? 0);
                    const formattedTotalComments = formatNumberKMB(totalCommentsRaw);

                    const centerTextPlugin = {
                        id: 'centerText',
                        afterDraw: (chart) => {
                            if (chart.config.type !== 'doughnut' || !chart.config.options.plugins.centerText || !chart
                                .config.options.plugins.centerText.display) {
                                return;
                            }
                            const {
                                ctx
                            } = chart;
                            const {
                                text,
                                color,
                                font,
                                fontStyle,
                                secondText,
                                secondFont,
                                secondColor
                            } = chart.config.options.plugins.centerText;

                            const chartArea = chart.chartArea;
                            if (!chartArea) return;

                            const centerX = (chartArea.left + chartArea.right) / 2;
                            const centerY = (chartArea.top + chartArea.bottom) / 2;

                            ctx.save();
                            ctx.font = fontStyle + ' ' + font; // Menggabungkan style dan font
                            ctx.fillStyle = color;
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            // Teks utama (jumlah yang sudah diformat)
                            ctx.fillText(text, centerX, centerY - 7); // Sedikit ke atas

                            // Teks kedua ("Komentar")
                            if (secondText) {
                                ctx.font = secondFont; // Font untuk teks kedua
                                ctx.fillStyle = secondColor; // Warna untuk teks kedua
                                ctx.fillText(secondText, centerX, centerY + 10); // Sedikit ke bawah
                            }
                            ctx.restore();
                        }
                    };

                    // Hancurkan instance chart sebelumnya jika ada
                    if (sentimentDonutChartInstance) {
                        sentimentDonutChartInstance.destroy();
                        sentimentDonutChartInstance = null; // Set ke null setelah dihancurkan
                    }

                    // Bersihkan canvas sebelum menggambar pesan "tidak ada data" atau chart baru
                    ctx.clearRect(0, 0, canvasElement.width, canvasElement.height);

                    if (sentimentLabels.length === 0 || sentimentDataValues.length === 0) {
                        // Tampilkan pesan jika tidak ada data
                        canvasElement.style.display = 'block'; // Pastikan canvas terlihat untuk pesan
                        ctx.textAlign = 'center'; // Pusatkan teks secara horizontal
                        ctx.textBaseline = 'middle'; // Pusatkan teks secara vertikal
                        ctx.font = '14px "Inter", sans-serif'; // Atur font (sesuaikan jika perlu)
                        ctx.fillStyle = '#888'; // Atur warna teks
                        ctx.fillText('Tidak ada data kata populer untuk ditampilkan.', canvasElement.width / 2, canvasElement
                            .height / 2);
                        console.warn("Tidak ada data untuk chart donat sentimen.");
                        return;
                    } else {
                        canvasElement.style.display = 'block'; // Pastikan canvas terlihat untuk chart
                    }

                    sentimentDonutChartInstance = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: sentimentLabels,
                            datasets: [{
                                label: 'Frekuensi Kata Populer',
                                data: sentimentDataValues,
                                backgroundColor: [
                                    'rgba(28, 200, 138, 0.8)', 'rgba(78, 115, 223, 0.8)',
                                    'rgba(246, 194, 62, 0.8)', 'rgba(231, 74, 59, 0.8)',
                                    'rgba(54, 185, 204, 0.8)', 'rgba(133, 135, 150, 0.8)',
                                    'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)',
                                    'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)'
                                ].slice(0, sentimentDataValues.length),
                                borderColor: '#fff',
                                borderWidth: 3,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '65%',
                            layout: {
                                padding: {
                                    top: 5,
                                    bottom: 20,
                                    left: 5,
                                    right: 5
                                }
                            },
                            plugins: {
                                centerText: {
                                    display: true,
                                    text: formattedTotalComments, // Gunakan total komentar yang sudah diformat K/M/B
                                    color: '#333',
                                    font: "20px 'Inter', sans-serif",
                                    fontStyle: 'bold',
                                    secondText: "Komentar", // Teks kedua
                                    secondFont: "11px 'Inter', sans-serif",
                                    secondColor: "#6c757d"
                                },
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 11,
                                            family: "'Inter', sans-serif"
                                        },
                                        boxWidth: 15,
                                        padding: 20,
                                        color: '#333'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed !== null) {
                                                label += context.parsed.toLocaleString('id-ID');
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                        },
                        plugins: [centerTextPlugin]
                    });
                }


                document.addEventListener('DOMContentLoaded', function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const initialChartType = urlParams.get('chartType') || 'line';
                    createOrUpdateForecastChart(initialChartType);
                    renderSentimentDonutChart(); // Render the sentiment donut chart

                    // Zoom button event listeners for forecastChart
                    const zoomInButton = document.getElementById('zoomInBtn');
                    const zoomOutButton = document.getElementById('zoomOutBtn');
                    const resetZoomButton = document.getElementById('resetZoomBtn');

                    if (zoomInButton && forecastChartInstance) {
                        zoomInButton.addEventListener('click', () => {
                            forecastChartInstance.zoom(1.1); // Zoom in by 10%
                        });
                    }
                    if (zoomOutButton && forecastChartInstance) {
                        zoomOutButton.addEventListener('click', () => {
                            forecastChartInstance.zoom(0.9); // Zoom out by 10%
                        });
                    }
                    if (resetZoomButton && forecastChartInstance) {
                        resetZoomButton.addEventListener('click', () => {
                            forecastChartInstance.resetZoom();
                        });
                    }
                });
            </script>
        @endpush
        @push('styles')
            <style>
                .sentiment-card .card-body {
                    padding: 1.5rem 1rem;
                    /* Sesuaikan padding internal kartu */
                }

                .sentiment-card .display-4 {
                    font-size: 2.75rem;
                    /* Sesuaikan ukuran font angka utama */
                    line-height: 1.2;
                }

                /* Anda bisa menambahkan styling lebih lanjut di sini jika diperlukan */
                /* .positive-card { border-left: .25rem solid #1cc88a !important; }
                                                                                                                                                                                            .negative-card { border-left: .25rem solid #e74a3b !important; }
                                                                                                                                                                                            .neutral-card { border-left: .25rem solid #f6c23e !important; } */
                /* Jika ingin menghilangkan border-left dan mengandalkan warna teks saja: */
                .positive-card,
                .negative-card,
                .neutral-card {
                    border-left: none !important;
                }
            </style>
        @endpush
    @endsection
