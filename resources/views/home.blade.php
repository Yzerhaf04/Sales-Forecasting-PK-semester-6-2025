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
                                {{ \Carbon\Carbon::parse($lastUpdated)->format('M d, Y') }}</div>
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
                    // Variabel $selectedDept dan $selectedStore diasumsikan datang dari controller dan merupakan nilai default/awal
                    // Jika tidak, gunakan request() untuk semuanya.
                    $currentFilterDept = request('department', $selectedDept ?? 1);
                    $currentFilterStore = request('store', $selectedStore ?? 1);
                    $currentFilterPeriod = request('period', 'monthly');
                    $currentFilterChartType = request('chartType', 'line');

                    $periodDisplayLabels = [
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                    ];
                    $chartTypeDisplayLabels = [
                        'line' => 'Garis (Line)',
                        'bar' => 'Batang (Bar)',
                        'area' => 'Area',
                    ];

                    // Icon untuk periode
                    $periodIcons = [
                        'daily' => 'fa-calendar-day',
                        'weekly' => 'fa-calendar-week',
                        'monthly' => 'fa-calendar-alt',
                    ];
                @endphp

                <div class="card-header py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center">
                        <h6 class="m-0 font-weight-bold text-primary mb-3 mb-md-0">
                            Sales Forecast - Store {{ $currentFilterStore }} / Dept {{ $currentFilterDept }}
                        </h6>
                        <div class="d-flex flex-wrap justify-content-start justify-content-md-end">
                            <div class="dropdown mr-2 mb-2">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                    style="width: 150px; height: 40px; font-size: 14px;" type="button" id="storeDropdown"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-store mr-1"></i> Store: {{ $currentFilterStore }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="storeDropdown"
                                    style="font-size: 14px;">
                                    @for ($i = 1; $i <= 5; $i++)
                                        {{-- Asumsi ada 5 store --}}
                                        <a class="dropdown-item {{ $i == $currentFilterStore ? 'active' : '' }}"
                                            href="?department={{ $currentFilterDept }}&store={{ $i }}&period={{ $currentFilterPeriod }}&chartType={{ $currentFilterChartType }}">
                                            <i class="fas fa-store mr-2"></i> Store {{ $i }}
                                        </a>
                                    @endfor
                                </div>
                            </div>

                            <div class="dropdown mr-2 mb-2">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                    style="width: 170px; height: 40px; font-size: 14px;" type="button"
                                    id="departmentDropdown" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fas fa-building mr-1"></i> Department: {{ $currentFilterDept }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm"
                                    aria-labelledby="departmentDropdown" style="font-size: 14px;">
                                    @for ($i = 1; $i <= 10; $i++)
                                        {{-- Asumsi ada 10 department --}}
                                        <a class="dropdown-item {{ $i == $currentFilterDept ? 'active' : '' }}"
                                            href="?department={{ $i }}&store={{ $currentFilterStore }}&period={{ $currentFilterPeriod }}&chartType={{ $currentFilterChartType }}">
                                            <i class="fas fa-building mr-2"></i> Department
                                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @endfor
                                </div>
                            </div>

                            <div class="dropdown mr-2 mb-2">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                    style="width: 150px; height: 40px; font-size: 14px;" type="button" id="periodDropdown"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas {{ $periodIcons[$currentFilterPeriod] ?? 'fa-calendar-alt' }} mr-1"></i>
                                    Periode:
                                    {{ $periodDisplayLabels[$currentFilterPeriod] ?? ucfirst($currentFilterPeriod) }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm" aria-labelledby="periodDropdown"
                                    style="font-size: 14px; width: 150px;">
                                    @foreach ($periodDisplayLabels as $periodValue => $periodLabel)
                                        <a class="dropdown-item {{ $periodValue == $currentFilterPeriod ? 'active' : '' }}"
                                            href="?department={{ $currentFilterDept }}&store={{ $currentFilterStore }}&period={{ $periodValue }}&chartType={{ $currentFilterChartType }}">
                                            <i class="fas {{ $periodIcons[$periodValue] ?? 'fa-calendar-alt' }} mr-2"></i>
                                            {{ $periodLabel }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="dropdown mb-2">
                                <button class="btn btn-sm btn-outline-success dropdown-toggle"
                                    style="width: 170px; height: 40px; font-size: 14px;" type="button"
                                    id="chartTypeDropdown" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fas fa-chart-bar mr-1"></i> Jenis:
                                    {{ $chartTypeDisplayLabels[$currentFilterChartType] ?? ucfirst($currentFilterChartType) }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm"
                                    aria-labelledby="chartTypeDropdown" id="chartTypeDropdownMenu"
                                    style="font-size: 14px; width: 170px;">
                                    @foreach ($chartTypeDisplayLabels as $typeValue => $typeLabel)
                                        <a class="dropdown-item chart-type-selector {{ $typeValue == $currentFilterChartType ? 'active' : '' }}"
                                            href="?department={{ $currentFilterDept }}&store={{ $currentFilterStore }}&period={{ $currentFilterPeriod }}&chartType={{ $typeValue }}">
                                            {{-- Pertimbangkan menambahkan ikon spesifik per jenis chart jika diinginkan --}}
                                            {{ $typeLabel }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body" wire:ignore>
                    {{-- Kontainer chart dibuat responsif --}}
                    <div style="width: 100%; height: 400px; position: relative;">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <small class="text-muted">
                        Menampilkan data untuk Store {{ $currentFilterStore }} / Department {{ $currentFilterDept }} |
                        Periode: {{ $periodDisplayLabels[$currentFilterPeriod] ?? ucfirst($currentFilterPeriod) }} | Jenis
                        Grafik: {{ $chartTypeDisplayLabels[$currentFilterChartType] ?? ucfirst($currentFilterChartType) }}
                        <br>
                        @if (isset($lastUpdated) && $lastUpdated)
                            Data terakhir diperbarui:
                            {{ \Carbon\Carbon::parse($lastUpdated)->translatedFormat('d F Y H:i') }}
                            {{-- Menggunakan translatedFormat untuk bahasa Indonesia jika Carbon locale diatur --}}
                        @else
                            Data terakhir diperbarui: N/A
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Content Column -->
        <div class="col-lg-12 mb-2">
            <!-- Sentiment Analisys -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sentimen Analisis</h6>
                    <div class="d-flex">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let forecastChartInstance = null; // Variabel untuk menyimpan instance chart

            // Fungsi untuk membuat atau memperbarui chart
            function createOrUpdateForecastChart(chartType) {
                const ctx = document.getElementById('forecastChart').getContext('2d');
                const labels = @json($months);
                const actualSalesData = @json($actualSales);
                const forecastSalesData = @json($forecastSales);
                const selectedDept = @json($selectedDept); // Pastikan $selectedDept adalah string atau angka
                const currentPeriod = @json(request('period', 'monthly'));

                // Hancurkan instance chart lama jika ada
                if (forecastChartInstance) {
                    forecastChartInstance.destroy();
                }

                let datasetsConfig = [{
                        label: 'Actual Sales - Dept ' + selectedDept,
                        data: actualSalesData,
                        borderColor: '#4e73df',
                        backgroundColor: (chartType === 'bar') ? '#4e73df' : ((chartType === 'area' || chartType ===
                            'line') ? 'rgba(78, 115, 223, 0.1)' : '#4e73df'),
                        borderWidth: 2,
                        tension: (chartType === 'line' || chartType === 'area') ? 0.3 : 0, // Tension untuk line/area
                        fill: (chartType === 'area' || (chartType === 'line' &&
                            true)), // Fill untuk area (atau line pertama jika diinginkan)
                        order: 1 // Untuk mixed chart, bar biasanya di belakang line
                    },
                    {
                        label: 'Forecast Sales - Dept ' + selectedDept,
                        data: forecastSalesData,
                        borderColor: '#e74a3b',
                        backgroundColor: (chartType === 'bar') ? '#e74a3b' : ((chartType === 'area' || chartType ===
                            'line') ? 'rgba(231, 74, 59, 0.1)' : '#e74a3b'),
                        borderWidth: 2,
                        borderDash: (chartType === 'line' || chartType === 'area') ? [5, 5] : [], // Dash untuk line/area
                        tension: (chartType === 'line' || chartType === 'area') ? 0.3 : 0,
                        fill: (chartType === 'area'), // Hanya fill jika tipe area untuk dataset kedua
                        order: (chartType === 'bar') ? 1 : 2 // Forecast di atas bar actual
                    }
                ];

                // Penyesuaian dataset khusus untuk tipe chart tertentu
                if (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') {
                    // Untuk tipe ini, biasanya kita tampilkan satu set data. Misal, Actual Sales.
                    // Label dari $months akan menjadi label segmen.
                    datasetsConfig = [{
                        label: 'Actual Sales - Dept ' + selectedDept, // Label ini akan muncul di tooltip/legend
                        data: actualSalesData,
                        backgroundColor: [ // Sediakan warna yang cukup untuk segmen
                            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796',
                            '#e051a7', '#f08f2f', '#9c55d9', '#4bc0c0', '#ffcd56', '#ff9f40'
                            // Tambahkan lebih banyak warna jika $months Anda > 12
                        ],
                        hoverOffset: 4
                    }];
                } else if (chartType === 'radar') {
                    // Untuk radar, pastikan data tidak ada nilai null/undefined di tengah karena akan memutus garis
                    // Opsi fill bisa menarik untuk radar.
                    datasetsConfig[0].fill = true; // Misal, fill dataset pertama
                    datasetsConfig[0].backgroundColor = 'rgba(78, 115, 223, 0.2)';
                    datasetsConfig[1].fill = true;
                    datasetsConfig[1].backgroundColor = 'rgba(231, 74, 59, 0.2)';
                } else if (chartType === 'bar') {
                    // Untuk bar, `fill` di dataset tidak berlaku seperti di line/area.
                    // Warna batang ditentukan oleh backgroundColor di level dataset.
                    datasetsConfig[0].backgroundColor = '#4e73df';
                    datasetsConfig[1].backgroundColor = '#e74a3b';
                    // Jika ingin bar berkelompok, tidak ada setting khusus, itu defaultnya.
                    // Jika ingin bar bertumpuk (stacked), tambahkan di options.scales:
                    // x: { stacked: true }, y: { stacked: true }
                }


                const chartData = {
                    labels: labels,
                    datasets: datasetsConfig
                };

                // Opsi default, bisa disesuaikan per chart type
                let chartOptions = {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: {
                        mode: 'index', // Baik untuk line/bar, mungkin perlu 'point' atau 'nearest' untuk pie/radar
                        intersect: false,
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (chartType === 'pie' || chartType === 'doughnut' || chartType === 'polarArea') {
                                        label = context.label || ''; // Label segmen
                                    }
                                    if (label) {
                                        label += ': ';
                                    }
                                    // Untuk pie/doughnut/polarArea, nilai ada di context.raw atau context.parsed
                                    // Untuk chart lain, context.parsed.y
                                    const value = (chartType === 'pie' || chartType === 'doughnut' || chartType ===
                                            'polarArea') ?
                                        context.raw :
                                        context.parsed.y;

                                    if (value !== null && typeof value !== 'undefined') {
                                        label += '$' + parseFloat(value).toLocaleString('id-ID', {
                                            minimumFractionDigits: 0,
                                            maximumFractionDigits: 0
                                        });
                                    }
                                    return label;
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Sales Performance Dept ' + selectedDept + ' - ' + (chartType.charAt(0).toUpperCase() +
                                chartType.slice(1))
                        }
                    },
                    scales: {} // Default kosong, akan diisi di bawah
                };

                // Penyesuaian skala khusus untuk tipe chart
                if (chartType === 'line' || chartType === 'bar' || chartType === 'area') {
                    chartOptions.scales = {
                        y: {
                            beginAtZero: chartType === 'bar', // Mulai dari nol untuk bar lebih baik
                            ticks: {
                                callback: function(value) {
                                    return '$' + parseFloat(value).toLocaleString('id-ID', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            },
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    };
                    if (chartType === 'bar') {
                        // Jika ingin bar berdampingan, ini sudah default.
                        // Jika ingin stacked bar:
                        // chartOptions.scales.x.stacked = true;
                        // chartOptions.scales.y.stacked = true;
                    }
                } else if (chartType === 'radar' || chartType === 'polarArea') {
                    chartOptions.scales = {
                        r: { // Sumbu radial untuk radar dan polar area
                            angleLines: {
                                display: true
                            },
                            suggestedMin: 0,
                            // suggestedMax: ... , // bisa diatur jika perlu
                            pointLabels: {
                                font: {
                                    size: 10
                                }
                            },
                            ticks: {
                                backdropColor: 'transparent',
                                // Format tick jika perlu (misalnya, mata uang)
                                callback: function(value) {
                                    return '$' + parseFloat(value).toLocaleString('id-ID', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            }
                        }
                    };
                    // Untuk polarArea, interaksi mode 'nearest' mungkin lebih baik
                    if (chartType === 'polarArea') {
                        chartOptions.interaction.mode = 'nearest';
                        chartOptions.interaction.intersect = true;
                    }
                }
                // Untuk Pie & Doughnut, scales tidak diperlukan/digunakan.

                // Buat instance chart baru
                forecastChartInstance = new Chart(ctx, {
                    type: chartType,
                    data: chartData,
                    options: chartOptions
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Ambil chartType dari URL saat halaman dimuat
                const urlParams = new URLSearchParams(window.location.search);
                const currentChartType = urlParams.get('chartType') || 'line'; // Default ke 'line'

                // Inisialisasi chart dengan tipe yang sesuai
                createOrUpdateForecastChart(currentChartType);

                // Update teks tombol dropdown jenis grafik (opsional, jika tidak menggunakan reload halaman)
                const chartTypeDropdownButton = document.getElementById('chartTypeDropdown');
                const activeChartTypeLabel = document.querySelector(
                    `#chartTypeDropdownMenu a[href*="chartType=${currentChartType}"]`)?.textContent?.trim();
                if (chartTypeDropdownButton && activeChartTypeLabel) {
                    chartTypeDropdownButton.innerHTML =
                        `<i class="fas fa-chart-bar mr-2"></i> Jenis: ${activeChartTypeLabel}`;
                }

                // Jika Anda ingin mengubah chart TANPA reload halaman penuh saat item dropdown diklik:
                // (Namun, link di HTML sudah dibuat untuk reload halaman dengan parameter baru,
                // jadi kode di bawah ini adalah alternatif jika Anda mengubah link menjadi # atau javascript:void(0))
                /*
                document.querySelectorAll('#chartTypeDropdownMenu .chart-type-selector').forEach(item => {
                    item.addEventListener('click', function(event) {
                        event.preventDefault(); // Hentikan navigasi link standar
                        const newUrl = new URL(this.href);
                        const newChartType = newUrl.searchParams.get('chartType');

                        // Update URL di browser tanpa reload (opsional)
                        history.pushState({chartType: newChartType}, '', this.href);

                        // Perbarui chart
                        createOrUpdateForecastChart(newChartType);

                        // Update teks tombol dropdown
                        if (chartTypeDropdownButton) {
                            chartTypeDropdownButton.innerHTML = `<i class="fas fa-chart-bar mr-2"></i> Jenis: ${this.textContent.trim()}`;
                        }
                    });
                });
                */
            });
        </script>
    @endpush
@endsection
