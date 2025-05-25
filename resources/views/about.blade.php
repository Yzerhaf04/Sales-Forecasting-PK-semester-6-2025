@extends('layouts.admin')

@section('main-content')
    <h1 class="h3 mb-4 text-gray-800">{{ __('About Project') }}</h1>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8"> {{-- Container utama sedikit lebih lebar --}}
            <div class="card shadow mb-5">
                <div class="card-body p-md-5">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/favicon.png') }}" class="img-fluid mb-3" style="max-height: 80px;"
                            alt="Project Logo">
                        <h4 class="font-weight-bold text-primary">Sales Forecasting Project</h4>
                        <p class="text-muted lead" style="font-size: 1.1rem;">
                            Sebuah aplikasi inovatif untuk memprediksi tren penjualan harian menggunakan kekuatan Laravel
                            dan Machine Learning, membantu pengambilan keputusan bisnis yang lebih cerdas.
                        </p>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <h5 class="font-weight-bold text-gray-800 mb-4">Tim Pengembang</h5>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card member-card h-100 border-left-primary shadow-sm">
                                <div class="card-body text-center">
                                    <img src="{{ asset('img/member1.jpg') }}"
                                        onerror="this.onerror=null;this.src='https://placehold.co/150x150/E0E0E0/909090?text=Member+1';"
                                        class="rounded-circle mb-3"
                                        style="width: 130px; height: 130px; object-fit: cover; border: 0px solid #e3e6f0;"
                                        alt="M Rizki Awaluddin M">
                                    <h6 class="font-weight-bold text-primary mb-1">M Rizki Awaluddin M</h6>
                                    <p class="text-muted small mb-2">
                                        <a href="mailto:muhammad.rizki.awaluddin.mubin.tik22@mhsw.pnj.ac.id"
                                            class="text-muted">muhammad.rizki.awaluddin.mubin.tik22@mhsw.pnj.ac.id</a>
                                    </p>
                                    <div class="social-links">
                                        <a href="https://www.instagram.com/rizz_.ki/" class="btn btn-sm btn-outline-secondary mx-1"><i
                                                class="fab fa-instagram"></i></a>
                                        <a href="https://github.com/Yzerhaf04/Sales-Forecasting-PK-semester-6-2025" class="btn btn-sm btn-outline-secondary mx-1"><i
                                                class="fab fa-github"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card member-card h-100 border-left-success shadow-sm">
                                <div class="card-body text-center">
                                    <img src="{{ asset('img/member2.jpg') }}" {{-- Ganti dengan path gambar yang benar --}}
                                        onerror="this.onerror=null;this.src='https://placehold.co/150x150/E8E8E8/A0A0A0?text=Member+2';"
                                        class="rounded-circle mb-3"
                                        style="width: 130px; height: 130px; object-fit: cover; border: 0px solid #e3e6f0;"
                                        alt="Dzaky Naufal A">
                                    <h6 class="font-weight-bold text-success mb-1">M Dzaky Naufal Asadel</h6>
                                    <p class="text-muted small mb-2">
                                        <a href="mailto:teuku.rafli.fahrezy.tik22@mhsw.pnj.ac.id"
                                            class="text-muted">muhammad.dzaky.naufal.asadel.tik22@mhsw.pnj.ac.id</a>
                                    </p>
                                    <div class="social-links">
                                        <a href="#" class="btn btn-sm btn-outline-secondary mx-1"><i
                                                class="fab fa-instagram"></i></a>
                                        <a href="https://github.com/Yzerhaf04/Sales-Forecasting-PK-semester-6-2025" class="btn btn-sm btn-outline-secondary mx-1"><i
                                                class="fab fa-github"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card member-card h-100 border-left-info shadow-sm">
                                <div class="card-body text-center">
                                    <img src="{{ asset('img/member3.jpg') }}" {{-- Ganti dengan path gambar yang benar --}}
                                        onerror="this.onerror=null;this.src='https://placehold.co/150x150/F0F0F0/B0B0B0?text=Member+3';"
                                        class="rounded-circle mb-3"
                                        style="width: 130px; height: 130px; object-fit: cover; border: 3px solid #e3e6f0;"
                                        alt="Teuku Rafly F">
                                    <h6 class="font-weight-bold text-info mb-1">Teuku Rafly Fahrezy</h6>
                                    <p class="text-muted small mb-2">
                                        <a href="mailto:teuku.rafli.fahrezy.tik22@mhsw.pnj.ac.id" class="text-muted">
                                            teuku.rafli.fahrezy.tik22@mhsw.pnj.ac.id
                                        </a>
                                    </p>
                                    <div class="social-links">
                                        <a href="#" class="btn btn-sm btn-outline-secondary mx-1"><i
                                                class="fab fa-instagram"></i></a>
                                        <a href="https://github.com/Yzerhaf04/Sales-Forecasting-PK-semester-6-2025" class="btn btn-sm btn-outline-secondary mx-1"><i
                                                class="fab fa-github"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-gray-600">
                            Dikembangkan dengan <i class="fas fa-heart text-danger"></i> menggunakan Laravel, Bootstrap, dan
                            Chart.js.
                        </p>
                        <a href="{{ url('/home') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt fa-fw mr-2"></i> Kembali ke Dashboard
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .member-card img {
            transition: transform 0.3s ease-in-out;
        }

        .member-card:hover img {
            transform: scale(1.05);
        }

        .social-links a {
            color: #6c757d;
            transition: color 0.2s ease;
        }

        .social-links a:hover {
            color: #4e73df;
            /* Warna primary SB Admin 2 */
        }

        .card-body p.lead {
            font-size: 1.05rem;
            /* Sedikit lebih kecil dari default lead */
            line-height: 1.6;
        }
    </style>
@endpush
