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
                            <textarea class="form-control" name="review_text" id="review_text" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Analisis Sentimen</button>
                    </form>

                    <div class="mt-4 d-none" id="hasilBox">
                        <h5>Prediksi Sentimen: <span id="prediksiLabel" class="font-weight-bold text-primary"></span></h5>
                        <button class="btn btn-success" id="btnYes">Yes, Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let predictedLabel = '';

        document.getElementById('sentimenForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const review = document.getElementById('review_text').value;

            // Kirim ke backend untuk prediksi
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
            predictedLabel = data.label_sentimen;

            document.getElementById('prediksiLabel').innerText = predictedLabel;
            document.getElementById('hasilBox').classList.remove('d-none');
        });

        document.getElementById('btnYes').addEventListener('click', async function() {
            const review = document.getElementById('review_text').value;

            // Kirim ke backend untuk simpan ke DB
            await fetch("{{ route('sentimen.save') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    review_text: review,
                    label_sentimen: predictedLabel
                })
            });

            Swal.fire({
                title: 'Komentar berhasil disimpan!',
                icon: 'success',
                confirmButtonText: 'Oke'
            }).then(() => {
                location.reload();
            });
        });
    </script>
@endsection
