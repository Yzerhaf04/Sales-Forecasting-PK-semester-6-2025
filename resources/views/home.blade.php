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

        <!-- Users Card -->
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
                {{-- Filter --}}
                @php
                    $currentFilterDept = request('department', $selectedDept ?? 1);
                    $currentFilterStore = request('store', $selectedStore ?? 1);
                    $currentFilterPeriod = request('period', 'monthly'); // This is the raw period value e.g. 'daily', 'weekly'
                    $currentFilterChartType = request('chartType', 'line');

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
                        {{-- Title Chart --}}
                        <h6 class="m-0 font-weight-bold text-primary mb-3 mb-md-0" id="chartMainTitle">
                            Sales Performance - Store {{ $currentFilterStore }} / Dept {{ $currentFilterDept }}
                        </h6>
                        <div class="d-flex flex-wrap justify-content-start justify-content-md-end align-items-center">
                            {{-- Dropdown Store --}}
                            <div class="dropdown mr-1 mb-1">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle text-nowrap"
                                    style="min-width: 160px; height: 38px; font-size: 0.8rem;" type="button"
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
                            {{-- Dropdown Departemen --}}
                            <div class="dropdown mr-1 mb-1">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle text-nowrap"
                                    style="min-width: 165px; height: 38px; font-size: 0.8rem;" type="button"
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
                            {{-- Dropdown Periode --}}
                            <div class="dropdown mr-1 mb-1">
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
                            {{-- Dropdown Chart --}}
                            <div class="dropdown mr-1 mb-1">
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
                                    <div class="btn-group mb-1 " role="group" aria-label="Zoom controls">
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
        <div class="col-lg-12 mb-2">
            <div class="card shadow mb-4">
            <div class="card-header py-6">
                <h6 class="m-0 font-weight-bold text-primary">Sentimen Analisis</h6>
            </div>
            <div class="card-body">
                <div class="row">
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

                    {{-- Chart Donat Kata Populer --}}
                    <div class="col-lg-7 col-md-12">
                        <h6 class="m-0 font-weight-bold text-primary mb-5 text-center text-lg-left"
                            style="font-size: 16px;">Kata Populer
                        </h6>
                        <div style="height: 350px; width:100%; max-width: 900px; margin: 0 auto; position: relative;">
                            <canvas id="sentimentDonutChart"></canvas>
                        </div>
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

            // Konfigurasi Zoom Plugin (Chart.js v3+)
            const zoomPluginOptions = {
                pan: {
                    enabled: true,
                    mode: 'xy',
                    threshold: 5,
                    modifierKey: null,
                },
                zoom: {
                    wheel: {
                        enabled: false, // Di-disable agar tidak konflik dengan scroll halaman
                        modifierKey: null,
                    },
                    pinch: {
                        enabled: true
                    },
                    drag: {
                        enabled: false // Biasanya drag zoom lebih cocok untuk chart tertentu
                    },
                    mode: 'xy',
                }
            };


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
                // Mengambil nilai mentah dari $currentFilterPeriod untuk pengecekan di JavaScript
                const rawCurrentFilterPeriod = @json($currentFilterPeriod ?? 'monthly');


                if (forecastChartInstance) {
                    forecastChartInstance.destroy();
                }

                let baseDatasets = [{
                        label: 'Actual Sales - Dept ' + currentDept,
                        data: actualSalesData,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.2)', // Warna area untuk line chart
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false, // Default untuk line chart, bisa diubah per tipe
                        order: 2
                    },
                    {
                        label: 'Forecast Sales - Dept ' + currentDept,
                        data: forecastSalesData,
                        borderColor: 'rgba(231, 74, 59, 1)',
                        backgroundColor: 'rgba(231, 74, 59, 0.2)', // Warna area untuk line chart
                        borderWidth: 2,
                        tension: 0.3,
                        fill: false, // Default untuk line chart
                        borderDash: [5, 5], // Garis putus-putus untuk forecast
                        order: 1
                    }
                ];

                let finalDatasets = JSON.parse(JSON.stringify(baseDatasets));
                let chartSpecificOptions = {};
                let defaultXScale = {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 15, // Batasi jumlah tick untuk kejelasan
                        color: '#666'
                    },
                    grid: {
                        display: true,
                        color: "rgba(0, 0, 0, 0.05)", // Warna grid lebih halus
                        drawBorder: false
                    }
                };
                let defaultYScale = {
                    beginAtZero: false, // Biarkan Chart.js menentukan skala awal Y
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
                        borderDash: [2] // Garis putus-putus untuk grid Y
                    }
                };

                // Penyesuaian berdasarkan tipe chart
                switch (chartType) {
                    case 'bar':
                        finalDatasets.forEach(ds => {
                            ds.backgroundColor = ds.borderColor.replace('1)', '0.7)'); // Warna bar lebih solid
                            ds.tension = 0; // Bar chart tidak butuh tension
                            ds.borderDash = []; // Tidak ada dash untuk bar
                            ds.fill = true;
                        });
                        chartSpecificOptions.scales = {
                            x: { ...defaultXScale, stacked: false }, // Bar chart bisa di-stack jika mau
                            y: { ...defaultYScale, beginAtZero: true, stacked: false }
                        };
                        break;
                    case 'area': // Contoh jika ingin menambahkan tipe area
                        finalDatasets[0].fill = 'origin'; // Fill actual sales ke origin
                        finalDatasets[0].backgroundColor = 'rgba(78, 115, 223, 0.2)';
                        finalDatasets[1].fill = 'origin'; // Fill forecast sales ke origin
                        finalDatasets[1].backgroundColor = 'rgba(231, 74, 59, 0.2)';
                        finalDatasets[1].borderDash = []; // Area chart biasanya tidak dashed
                        chartSpecificOptions.scales = {
                             x: { ...defaultXScale },
                             y: { ...defaultYScale, beginAtZero: true } // Area chart sering dimulai dari nol
                        };
                        break;
                    case 'doughnut':
                    case 'polarArea':
                        // Data untuk doughnut/polarArea biasanya hanya satu set dan berbeda format
                        finalDatasets = [{
                            label: 'Actual Sales Distribution - Dept ' + currentDept,
                            data: actualSalesData.filter(val => val !== null && val > 0), // Ambil data yang valid
                            backgroundColor: [ // Palet warna
                                'rgba(78, 115, 223, 0.8)', 'rgba(28, 200, 138, 0.8)', 'rgba(54, 185, 204, 0.8)',
                                'rgba(246, 194, 62, 0.8)', 'rgba(231, 74, 59, 0.8)', 'rgba(133, 135, 150, 0.8)',
                                'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)', 'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)', 'rgba(153, 102, 255, 0.8)', 'rgba(255, 159, 64, 0.8)'
                            ].slice(0, actualSalesData.filter(val => val !== null && val > 0).length),
                            borderColor: '#fff',
                            borderWidth: 1,
                            hoverOffset: 8
                        }];
                        chartSpecificOptions.scales = (chartType === 'polarArea') ? {
                            r: {
                                angleLines: { display: true },
                                suggestedMin: 0,
                                pointLabels: { font: { size: 10 } },
                                ticks: { z: 1, backdropColor: 'transparent', color: '#666', callback: function(value) { return '$' + parseFloat(value).toLocaleString('en-US'); }}
                            }
                        } : {}; // Doughnut tidak punya scales
                        break;
                    case 'radar':
                         finalDatasets.forEach(ds => {
                            ds.fill = true;
                            ds.backgroundColor = ds.borderColor.replace('1)', '0.2)');
                        });
                        chartSpecificOptions.scales = {
                            r: {
                                angleLines: { display: true },
                                suggestedMin: 0,
                                pointLabels: { font: { size: 10 }, color: '#666' },
                                ticks: { z: 1, backdropColor: 'transparent', color: '#666', callback: function(value) { return '$' + parseFloat(value).toLocaleString('en-US'); }}
                            }
                        };
                        break;
                    case 'line':
                    default:
                        chartType = 'line'; // Pastikan defaultnya line
                        finalDatasets[0].fill = true; // Fill untuk dataset pertama (Actual Sales)
                        finalDatasets[0].backgroundColor = 'rgba(78, 115, 223, 0.05)'; // Warna fill yang sangat transparan
                        finalDatasets[1].fill = false; // Forecast tidak di-fill
                        chartSpecificOptions.scales = {
                            x: { ...defaultXScale },
                            y: { ...defaultYScale }
                        };
                        break;
                }

                const chartOptions = {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: {
                        mode: (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') ? 'nearest' : 'index',
                        intersect: (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') ? true : false,
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: "rgba(0,0,0,0.8)",
                            titleColor: '#fff',
                            bodyColor: "#fff",
                            titleFont: { size: 14, weight: 'bold'},
                            bodyFont: { size: 12 },
                            borderColor: '#444',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: true, // Tampilkan kotak warna di tooltip
                            callbacks: {
                                title: function(tooltipItems) {
                                    // tooltipItems adalah array, kita biasanya fokus pada item pertama
                                    if (tooltipItems.length > 0) {
                                        const originalTitle = tooltipItems[0].label;
                                        // Cek jika periode saat ini adalah 'daily'
                                        if (rawCurrentFilterPeriod === 'daily') {
                                            try {
                                                // Coba parse tanggal. Asumsi originalTitle adalah string tanggal yang bisa diparsing.
                                                const date = new Date(originalTitle);
                                                // Cek apakah hasil parsing adalah tanggal yang valid
                                                if (!isNaN(date.getTime())) {
                                                    // Format ke "DD NamaBulanSingkat" (misal: "21 Mei") menggunakan lokal Indonesia
                                                    return date.toLocaleDateString('id-ID', {
                                                        day: 'numeric',
                                                        month: 'short' // 'short' akan menghasilkan "Mei", "Jun", "Jul", dll.
                                                    });
                                                } else {
                                                    // Jika parsing gagal menghasilkan tanggal valid, kembalikan judul asli
                                                    console.warn("Gagal mem-parsing tanggal untuk judul tooltip harian (invalid date):", originalTitle);
                                                    return originalTitle;
                                                }
                                            } catch (e) {
                                                // Jika terjadi error saat parsing, kembalikan judul asli
                                                console.error("Error saat mem-parsing tanggal untuk judul tooltip harian:", originalTitle, e);
                                                return originalTitle;
                                            }
                                        }
                                        // Untuk periode selain 'daily' atau jika parsing gagal, kembalikan judul asli
                                        return originalTitle;
                                    }
                                    return ''; // Kembalikan string kosong jika tidak ada item tooltip
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') {
                                        label = context.label || ''; // Untuk tipe chart ini, label diambil dari context.label
                                    }

                                    if (label) {
                                        label += ': ';
                                    }
                                    const value = (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') ?
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
                                font: { size: 12 },
                                boxWidth: 20,
                                padding: 20,
                                color: '#333'
                            }
                        },
                        zoom: zoomPluginOptions // Tambahkan konfigurasi zoom di sini
                    },
                    scales: chartSpecificOptions.scales || { // Gunakan scales spesifik tipe chart atau default
                        x: defaultXScale,
                        y: defaultYScale
                    }
                };

                forecastChartInstance = new Chart(ctx, {
                    type: chartType,
                    data: {
                        labels: labels,
                        datasets: finalDatasets
                    },
                    options: chartOptions
                });

                // Update judul utama chart
                const mainTitleElement = document.getElementById('chartMainTitle');
                if (mainTitleElement) {
                    mainTitleElement.textContent = `Sales Performance (${currentChartTypeLabel}) - Store ${currentStore} / Dept ${currentDept}`;
                }
            }

            function formatNumberKMB(num) {
                if (num < 1000) {
                    return num.toString();
                } else if (num < 1000000) {
                    const thousands = num / 1000;
                    return (thousands % 1 === 0 ? thousands.toFixed(0) : thousands.toFixed(1).replace('.0', '')) + 'K';
                } else if (num < 1000000000) {
                    const millions = num / 1000000;
                    return (millions % 1 === 0 ? millions.toFixed(0) : millions.toFixed(1).replace('.0', '')) + 'M';
                } else {
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
                const sentimentLabels = @json($sentimentDonutLabels ?? []);
                const sentimentDataValues = @json($sentimentDonutDataValues ?? []);
                const totalCommentsRaw = @json($totalSentimenComments ?? 0);
                const formattedTotalComments = formatNumberKMB(totalCommentsRaw);

                const centerTextPlugin = {
                    id: 'centerText',
                    afterDraw: (chart) => {
                        if (chart.config.type !== 'doughnut' || !chart.config.options.plugins.centerText || !chart.config.options.plugins.centerText.display) {
                            return;
                        }
                        const { ctx } = chart;
                        const { text, color, font, fontStyle, secondText, secondFont, secondColor } = chart.config.options.plugins.centerText;

                        const chartArea = chart.chartArea;
                        if (!chartArea) return;

                        const centerX = (chartArea.left + chartArea.right) / 2;
                        const centerY = (chartArea.top + chartArea.bottom) / 2;

                        ctx.save();
                        ctx.font = fontStyle + ' ' + font;
                        ctx.fillStyle = color;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        ctx.fillText(text, centerX, centerY - 7); // Sesuaikan posisi Y untuk teks utama

                        if (secondText) {
                            ctx.font = secondFont;
                            ctx.fillStyle = secondColor;
                            ctx.fillText(secondText, centerX, centerY + 10); // Sesuaikan posisi Y untuk teks kedua
                        }
                        ctx.restore();
                    }
                };

                if (sentimentDonutChartInstance) {
                    sentimentDonutChartInstance.destroy();
                    sentimentDonutChartInstance = null; // Pastikan instance lama di-null-kan
                }

                // Bersihkan canvas sebelum menggambar ulang atau menampilkan pesan "tidak ada data"
                ctx.clearRect(0, 0, canvasElement.width, canvasElement.height);

                if (sentimentLabels.length === 0 || sentimentDataValues.length === 0) {
                    canvasElement.style.display = 'block'; // Pastikan canvas terlihat untuk pesan
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '14px "Inter", sans-serif'; // Gunakan font yang konsisten
                    ctx.fillStyle = '#888'; // Warna teks yang lebih lembut
                    ctx.fillText('Tidak ada data kata populer untuk ditampilkan.', canvasElement.width / 2, canvasElement.height / 2);
                    console.warn("Tidak ada data untuk chart donat sentimen.");
                    return; // Hentikan eksekusi jika tidak ada data
                } else {
                    canvasElement.style.display = 'block'; // Pastikan canvas terlihat jika ada data
                }

                sentimentDonutChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: sentimentLabels,
                        datasets: [{
                            label: 'Frekuensi Kata Populer',
                            data: sentimentDataValues,
                            backgroundColor: [ // Palet warna yang lebih beragam dan modern
                                'rgba(28, 200, 138, 0.8)', 'rgba(78, 115, 223, 0.8)',
                                'rgba(246, 194, 62, 0.8)', 'rgba(231, 74, 59, 0.8)',
                                'rgba(54, 185, 204, 0.8)', 'rgba(133, 135, 150, 0.8)',
                                'rgba(255, 99, 132, 0.8)', 'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 206, 86, 0.8)', 'rgba(75, 192, 192, 0.8)'
                                // Tambahkan lebih banyak warna jika perlu
                            ].slice(0, sentimentDataValues.length), // Sesuaikan jumlah warna dengan data
                            borderColor: '#fff', // Border putih untuk memisahkan segmen
                            borderWidth: 3,       // Border lebih tebal
                            hoverOffset: 8        // Efek hover yang lebih jelas
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%', // Buat lubang donat lebih besar
                        layout: {
                            padding: { // Padding agar chart tidak terpotong
                                top: 5, bottom: 20, left: 5, right: 5
                            }
                        },
                        plugins: {
                            centerText: { // Konfigurasi untuk teks di tengah donat
                                display: true,
                                text: formattedTotalComments, // Teks utama (jumlah komentar)
                                color: '#333',             // Warna teks utama
                                font: "20px 'Inter', sans-serif", // Font teks utama
                                fontStyle: 'bold',          // Gaya font teks utama
                                secondText: "Komentar",     // Teks sekunder
                                secondFont: "11px 'Inter', sans-serif", // Font teks sekunder
                                secondColor: "#6c757d"      // Warna teks sekunder
                            },
                            legend: {
                                position: 'bottom', // Posisi legenda di bawah
                                labels: {
                                    font: {
                                        size: 11,
                                        family: "'Inter', sans-serif" // Font yang konsisten
                                    },
                                    boxWidth: 15, // Ukuran kotak warna legenda
                                    padding: 20,    // Jarak antar item legenda
                                    color: '#333'
                                }
                            },
                            tooltip: { // Kustomisasi tooltip untuk chart donat
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            // Format angka dengan pemisah ribuan Indonesia
                                            label += context.parsed.toLocaleString('id-ID');
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                    },
                    plugins: [centerTextPlugin] // Daftarkan plugin kustom
                });
            }


            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const initialChartType = urlParams.get('chartType') || 'line'; // Default ke line jika tidak ada di URL
                createOrUpdateForecastChart(initialChartType);
                renderSentimentDonutChart(); // Panggil fungsi untuk merender chart sentimen

                // Event listener untuk tombol zoom
                const zoomInButton = document.getElementById('zoomInBtn');
                const zoomOutButton = document.getElementById('zoomOutBtn');
                const resetZoomButton = document.getElementById('resetZoomBtn');

                if (zoomInButton && forecastChartInstance) {
                    zoomInButton.addEventListener('click', () => {
                        forecastChartInstance.zoom(1.1); // Zoom in 10%
                    });
                }
                if (zoomOutButton && forecastChartInstance) {
                    zoomOutButton.addEventListener('click', () => {
                        forecastChartInstance.zoom(0.9); // Zoom out 10%
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
                padding: 1.5rem 1rem; /* Padding yang konsisten */
            }

            .sentiment-card .display-4 { /* Jika Anda menggunakan kelas ini untuk angka besar */
                font-size: 2.75rem;
                line-height: 1.2;
            }

            /* Hapus border kiri default dari template jika tidak diinginkan untuk kartu sentimen */
            .positive-card,
            .negative-card,
            .neutral-card {
                border-left: none !important;
            }

            /* Styling untuk tombol filter agar lebih rapi di berbagai ukuran layar */
            .card-header .d-flex.flex-wrap .dropdown,
            .card-header .d-flex.flex-wrap .btn-group {
                margin-bottom: 0.5rem; /* Tambahkan margin bawah untuk group tombol jika wrap */
            }
            @media (min-width: 768px) { /* md breakpoint */
                 .card-header .d-flex.flex-wrap .dropdown,
                 .card-header .d-flex.flex-wrap .btn-group {
                    margin-bottom: 0; /* Hapus margin bawah di layar lebih besar */
                }
            }

        </style>
    @endpush
@endsection
