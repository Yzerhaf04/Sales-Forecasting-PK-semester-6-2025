@extends('layouts.admin')

@section('main-content')
    <div class="row">
        <div class="col-lg-12 mb-2">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Sentimen Analisis Input Komentar</h6>
                </div>
                <div class="card-body">
                    <form id="sentimenForm">
                        @csrf
                        <div class="form-group">
                            <label for="review_text">Tulis Komentar:</label>
                            <textarea class="form-control" name="review_text" id="review_text" rows="4" required minlength="3"></textarea>
                            <div class="invalid-feedback">
                                Komentar minimal 3 karakter.
                            </div>
                        </div>
                        <button type="submit" id="btnAnalisis" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="analisisSpinner" role="status"
                                aria-hidden="true"></span>
                            Analisis Sentimen
                        </button>
                    </form>

                    <div class="mt-4 d-none" id="hasilBox">
                        <h5>Prediksi Sentimen: <span id="prediksiLabel" class="font-weight-bold"></span></h5>
                        <p>Komentar Anda: <em id="originalCommentText"></em></p>
                        <button class="btn btn-success" id="btnYes">
                            <span class="spinner-border spinner-border-sm d-none" id="simpanSpinner" role="status"
                                aria-hidden="true"></span>
                            Simpan Hasil Ini
                        </button>
                        {{-- Tombol "Tidak, Analisis Ulang" dihapus --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            let predictedLabel = '';
            let originalComment = ''; // Untuk menyimpan teks komentar yang digunakan untuk prediksi

            const sentimenForm = document.getElementById('sentimenForm');
            const reviewTextarea = document.getElementById('review_text');
            const btnAnalisis = document.getElementById('btnAnalisis');
            const analisisSpinner = document.getElementById('analisisSpinner'); // Spinner untuk tombol analisis
            const hasilBox = document.getElementById('hasilBox');
            const prediksiLabelSpan = document.getElementById('prediksiLabel');
            const originalCommentSpan = document.getElementById('originalCommentText');
            const btnYes = document.getElementById('btnYes');
            const simpanSpinner = document.getElementById('simpanSpinner'); // Spinner untuk tombol simpan

            sentimenForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (!sentimenForm.checkValidity()) {
                    sentimenForm.classList.add('was-validated');
                    return;
                }
                sentimenForm.classList.remove('was-validated');

                const review = reviewTextarea.value;
                originalComment = review; // Simpan komentar untuk disimpan nanti

                // Tampilkan spinner dan nonaktifkan tombol analisis
                analisisSpinner.classList.remove('d-none');
                btnAnalisis.disabled = true;
                hasilBox.classList.add('d-none'); // Sembunyikan hasil sebelumnya

                try {
                    const response = await fetch("{{ route('sentimen.predict') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            review_text: review
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        predictedLabel = data.label_sentimen
                    .toLowerCase(); // Pastikan sudah lowercase dari controller
                        // const displaySentiment = predictedLabel.charAt(0).toUpperCase() + predictedLabel.slice(1); // Baris ini diubah

                        prediksiLabelSpan.innerText = predictedLabel; // Langsung gunakan label lowercase
                        originalCommentSpan.innerText = data.original_comment || review;

                        prediksiLabelSpan.classList.remove('text-success', 'text-danger', 'text-warning',
                            'text-primary');
                        if (predictedLabel === 'positif') {
                            prediksiLabelSpan.classList.add('text-success');
                        } else if (predictedLabel === 'negatif') {
                            prediksiLabelSpan.classList.add('text-danger');
                        } else if (predictedLabel === 'netral') {
                            prediksiLabelSpan.classList.add('text-warning');
                        } else {
                            prediksiLabelSpan.classList.add('text-primary'); // Default untuk 'Tidak diketahui'
                        }

                        hasilBox.classList.remove('d-none');
                    } else {
                        let errorMessage = data.error || 'Gagal mendapatkan prediksi sentimen.';
                        if (data.details && typeof data.details === 'object') {
                            errorMessage += ` (Detail: ${data.details.message || JSON.stringify(data.details)})`;
                        } else if (data.details) {
                            errorMessage += ` (Detail: ${data.details})`;
                        }
                        Swal.fire({
                            title: 'Error Prediksi!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'Oke'
                        });
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    Swal.fire({
                        title: 'Koneksi Error!',
                        text: 'Tidak dapat terhubung ke server untuk prediksi. Periksa koneksi internet Anda.',
                        icon: 'error',
                        confirmButtonText: 'Oke'
                    });
                } finally {
                    // Sembunyikan spinner dan aktifkan kembali tombol analisis
                    analisisSpinner.classList.add('d-none');
                    btnAnalisis.disabled = false;
                }
            });

            btnYes.addEventListener('click', async function() {
                if (!originalComment || !predictedLabel) {
                    Swal.fire('Data Tidak Lengkap', 'Tidak ada komentar atau prediksi untuk disimpan.', 'warning');
                    return;
                }

                // Tampilkan spinner dan nonaktifkan tombol simpan
                simpanSpinner.classList.remove('d-none');
                btnYes.disabled = true;

                try {
                    const response = await fetch("{{ route('sentimen.save') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            review_text: originalComment,
                            label_sentimen: predictedLabel
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message || 'Komentar berhasil disimpan!',
                            icon: 'success',
                            confirmButtonText: 'Oke'
                        }).then(() => {
                            reviewTextarea.value = '';
                            hasilBox.classList.add('d-none');
                            predictedLabel = '';
                            originalComment = '';
                            sentimenForm.classList.remove('was-validated');
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal Menyimpan!',
                            text: data.error || 'Terjadi kesalahan saat menyimpan data.',
                            icon: 'error',
                            confirmButtonText: 'Oke'
                        });
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    Swal.fire({
                        title: 'Koneksi Error!',
                        text: 'Tidak dapat terhubung ke server untuk menyimpan. Periksa koneksi internet Anda.',
                        icon: 'error',
                        confirmButtonText: 'Oke'
                    });
                } finally {
                    // Sembunyikan spinner dan aktifkan kembali tombol simpan
                    simpanSpinner.classList.add('d-none');
                    btnYes.disabled = false;
                }
            });

            reviewTextarea.addEventListener('input', function() {
                if (reviewTextarea.value.length < 3) {
                    reviewTextarea.classList.add('is-invalid');
                } else {
                    reviewTextarea.classList.remove('is-invalid');
                    reviewTextarea.classList.add('is-valid');
                }
            });

            reviewTextarea.addEventListener('focus', function() {
                reviewTextarea.classList.remove('is-invalid', 'is-valid');
                sentimenForm.classList.remove('was-validated');
            });
        </script>
    @endpush
@endsection
