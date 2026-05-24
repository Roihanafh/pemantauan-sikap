@php
    $isEdit = $mode === 'edit';
    $actionUrl = $isEdit ? url('/penilaian/' . $penilaian->id) : url('/penilaian');
@endphp

<form id="formPenilaian" action="{{ $actionUrl }}" method="POST">
    @csrf
    <div class="modal-header">
        <h5 class="modal-title">{{ $isEdit ? 'Edit Penilaian' : 'Tambah Penilaian' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
    </div>

    <div class="modal-body">
        <div class="mb-3">
            <label for="siswa_id" class="form-label">Siswa</label>
            <select id="siswa_id" name="siswa_id" class="form-select" {{ $isEdit ? 'disabled' : '' }} required>
                <option value="">-- Pilih Siswa --</option>
                @foreach ($siswas as $siswa)
                    <option value="{{ $siswa->id }}" @selected((int) $selectedSiswaId === $siswa->id)>
                        {{ $siswa->nama }}{{ $siswa->kelas ? ' - ' . $siswa->kelas->nama : '' }}
                    </option>
                @endforeach
            </select>
            @if ($isEdit)
                <input type="hidden" name="siswa_id" value="{{ $penilaian->siswa_id }}">
            @endif
        </div>

        <div class="mb-3">
            <label for="pertemuan" class="form-label">Pertemuan</label>
            <select id="pertemuan" name="pertemuan" class="form-select" {{ $isEdit ? 'disabled' : '' }} required>
                @for ($i = 1; $i <= 16; $i++)
                    <option value="{{ $i }}" @selected((int) old('pertemuan', $penilaian->pertemuan ?? request('pertemuan', 1)) === $i)>
                        Pertemuan {{ $i }}
                    </option>
                @endfor
            </select>
            @if ($isEdit)
                <input type="hidden" name="pertemuan" value="{{ $penilaian->pertemuan }}">
            @endif
        </div>

        <div class="row g-3">
            @foreach ([
                'respect' => 'Respect',
                'participation' => 'Participation',
                'self_direction' => 'Self Direction',
                'caring' => 'Caring',
                'transfer' => 'Transfer',
            ] as $field => $label)
                <div class="col-md-6">
                    <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                    <select id="{{ $field }}" name="{{ $field }}" class="form-select" required>
                        <option value="">-- Pilih Nilai --</option>
                        @for ($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" @selected((int) old($field, $penilaian->{$field} ?? 0) === $i)>
                                {{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>
            @endforeach
        </div>

        <div id="formPenilaianError" class="alert alert-danger mt-3 d-none"></div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
</form>

<script>
$('#formPenilaian').on('submit', function (event) {
    event.preventDefault();

    const form = $(this);
    const errorBox = $('#formPenilaianError');
    errorBox.addClass('d-none').text('');

    $.ajax({
        url: form.attr('action'),
        type: '{{ $isEdit ? 'PUT' : 'POST' }}',
        data: form.serialize(),
        success: function (response) {
            if (response.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('myModal'));
                modal.hide();
                $('#penilaianTable').DataTable().ajax.reload(null, false);
                return;
            }

            errorBox.removeClass('d-none').text(response.message || 'Gagal menyimpan penilaian.');
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan penilaian.';
            errorBox.removeClass('d-none').text(message);
        }
    });
});
</script>
