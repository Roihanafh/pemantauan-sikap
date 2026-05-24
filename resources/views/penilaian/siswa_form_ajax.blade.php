<form id="formSiswa" action="{{ route('penilaian.siswa.store') }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">Tambah Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
    </div>

    <div class="modal-body">
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Siswa</label>
            <input type="text" id="nama" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label">Gender</label>
            <select id="gender" name="gender" class="form-select" required>
                <option value="">-- Pilih Gender --</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="kelas_id" class="form-label">Kelas</label>
            <select id="kelas_id" name="kelas_id" class="form-select">
                <option value="">-- Pilih kelas dari database --</option>
                @foreach ($kelas as $item)
                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="kelas_manual" class="form-label">Nama Kelas Baru</label>
            <input type="text" id="kelas_manual" name="kelas_manual" class="form-control" placeholder="Isi jika kelas belum ada di daftar">
        </div>

        <div id="formSiswaError" class="alert alert-danger d-none"></div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
$('#kelas_id').on('change', function () {
    if ($(this).val()) {
        $('#kelas_manual').val('');
    }
});

$('#kelas_manual').on('input', function () {
    if ($(this).val().trim()) {
        $('#kelas_id').val('');
    }
});

$('#formSiswa').on('submit', function (event) {
    event.preventDefault();

    const form = $(this);
    const submitButton = form.find('button[type="submit"]');
    const errorBox = $('#formSiswaError');
    errorBox.addClass('d-none').text('');
    submitButton.prop('disabled', true);

    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        data: form.serialize(),
        success: function (response) {
            if (!response.success) {
                errorBox.removeClass('d-none').text(response.message || 'Gagal menambahkan siswa.');
                return;
            }

            const kelas = response.data.kelas;
            const selectKelas = $('#selectKelas');

            if (!selectKelas.find('option[value="' + kelas.id + '"]').length) {
                selectKelas.append(new Option(kelas.nama, kelas.id));
            }

            selectKelas.val(kelas.id);
            bootstrap.Modal.getInstance(document.getElementById('myModal')).hide();
            $('#penilaianTable').DataTable().ajax.reload(null, false);
        },
        error: function (xhr) {
            errorBox.removeClass('d-none').text(xhr.responseJSON?.message || 'Terjadi kesalahan saat menambahkan siswa.');
        },
        complete: function () {
            submitButton.prop('disabled', false);
        }
    });
});
</script>
