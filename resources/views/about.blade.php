@extends('layouts.admin')

@section('main-content')
    <!-- Page Heading -->
    <h1 class="h3 mb-4 text-gray-800">{{ __('About') }}</h1>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow mb-4 text-center">
                <div class="card-profile-image mt-4">
                    <img src="{{ asset('img/favicon.png') }}" class="rounded-circle" style="width: 100px;" alt="project-logo">
                </div>

                <div class="card-body">
                    <h5 class="font-weight-bold">Sales Forecasting</h5>
                    <p class="text-muted">Proyek prediksi penjualan untuk Walmart berbasis Laravel dan Machine Learning.</p>

                    <hr>

                    <h5 class="font-weight-bold">Our Members</h5>

                    <div class="row justify-content-center mt-3">
                        <!-- Member 1 -->
                        <div class="col-md-6 mb-4">
                            <div class="card shadow border-bottom-primary position-relative overflow-hidden">
                                <!-- Gradasi atas -->
                                <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 50%;
                                    background: linear-gradient(to bottom, #0043da, transparent);
                                    z-index: 0;
                                "></div>

                                <div class="card-body text-center position-relative" style="z-index: 1;">
                                    <div style="width: 150px; height: 150px; margin: 0 auto; overflow: hidden; border-radius: 50%;">
                                        <img src="{{ asset('img/member1.jpg') }}"
                                             style="width: 100%; height: 100%; object-fit: cover;" alt="Member 1">
                                    </div>
                                    <h6 class="mt-3 font-weight-bold text-primary">M Rizki Awaluddin M</h6>
                                </div>
                            </div>
                        </div>

                        <!-- Member 2 -->
                        <div class="col-md-6 mb-3">
                            <div class="card shadow border-bottom-primary position-relative overflow-hidden">
                                <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 50%;
                                    background: linear-gradient(to bottom, #0043da, transparent);
                                    z-index: 0;
                                "></div>

                                <div class="card-body text-center position-relative" style="z-index: 1;">
                                    <div style="width: 150px; height: 150px; margin: 0 auto; overflow: hidden; border-radius: 50%;">
                                        <img src="{{ asset('img/member1.jpg') }}"
                                             style="width: 100%; height: 100%; object-fit: cover;" alt="Member 2">
                                    </div>
                                    <h6 class="mt-3 font-weight-bold text-primary">Dzaky Naufal A</h6>
                                </div>
                            </div>
                        </div>

                        <!-- Member 3 -->
                        <div class="col-md-6 mb-3">
                            <div class="card shadow border-bottom-primary position-relative overflow-hidden">
                                <div style="
                                    position: absolute;
                                    top: 0;
                                    left: 0;
                                    width: 100%;
                                    height: 50%;
                                    background: linear-gradient(to bottom, #0043da, transparent);
                                    z-index: 0;
                                "></div>

                                <div class="card-body text-center position-relative" style="z-index: 1;">
                                    <div style="width: 150px; height: 150px; margin: 0 auto; overflow: hidden; border-radius: 50%;">
                                        <img src="{{ asset('img/member1.jpg') }}"
                                             style="width: 100%; height: 100%; object-fit: cover;" alt="Member 3">
                                    </div>
                                    <h6 class="mt-3 font-weight-bold text-primary">Teuku Rafly F</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
