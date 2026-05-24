<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penilaian Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <button onclick="createSiswa()" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Tambah Siswa
                        </button>
                        <button id="exportBtn" class="btn btn-success">
                            <i class="fa fa-file-excel"></i> Export Excel
                        </button>
                        <button id="reloadBtn" class="btn btn-info ms-auto">
                            <i class="fa fa-refresh"></i> Muat Ulang
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Modal -->
                    <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content"></div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="selectKelas" class="form-label">Filter Kelas:</label>
                            <select id="selectKelas" class="form-control">
                                <option value="">-- Pilih Kelas --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="selectPertemuan" class="form-label">Filter Pertemuan:</label>
                            <select id="selectPertemuan" class="form-control">
                                @for ($i = 1; $i <= 16; $i++)
                                    <option value="{{ $i }}">Pertemuan {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <table id="penilaianTable" class="display table table-striped table-hover" style="width: 100%">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 5%">No</th>
                                <th>Nama Siswa</th>
                                <th style="width: 8%">Gender</th>
                                <th style="width: 8%">Respect</th>
                                <th style="width: 8%">Participation</th>
                                <th style="width: 8%">Self Direction</th>
                                <th style="width: 8%">Caring</th>
                                <th style="width: 8%">Transfer</th>
                                <th style="width: 12%">Aksi</th>
                            </tr>
                        </thead>
                        <tfoot></tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>



    @push('scripts')
    <script>
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function scoreSelect(field, value, row) {
            const labels = {
                1: '1 - Kurang',
                2: '2 - Cukup',
                3: '3 - Baik',
                4: '4 - Sangat Baik'
            };
            const displayValue = value ? value : '-';
            let html = '<div class="dropdown score-dropdown">';
            html += '<button class="btn btn-sm btn-outline-secondary dropdown-toggle score-dropdown-toggle" type="button" data-field="' + field + '" data-siswa-id="' + row.id + '" data-penilaian-id="' + (row.penilaian_id || '') + '" data-value="' + (value || '') + '">' + displayValue + '</button>';
            html += '<ul class="dropdown-menu">';

            Object.keys(labels).forEach(function (score) {
                html += '<li><button class="dropdown-item score-option" type="button" data-value="' + score + '">' + labels[score] + '</button></li>';
            });

            html += '</ul></div>';
            return html;
        }

        var tablePenilaian = $('#penilaianTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('penilaian/datatable') }}",
                type: "POST",
                data: function (d) {
                    d.kelas_id = $('#selectKelas').val();
                    d.pertemuan = $('#selectPertemuan').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', className: "text-center", orderable: false, searchable: false, width: "5%" },
                { data: 'nama', name: 'nama' },
                { data: 'gender', name: 'gender', render: function(data) {
                    return data === 'L' ? '<span class="badge bg-primary">Laki-laki</span>' : '<span class="badge bg-danger">Perempuan</span>';
                }},
                { data: 'respect', name: 'respect', orderable: false, searchable: false, render: function(data, type, row) {
                    return type === 'display' ? scoreSelect('respect', data, row) : data;
                }},
                { data: 'participation', name: 'participation', orderable: false, searchable: false, render: function(data, type, row) {
                    return type === 'display' ? scoreSelect('participation', data, row) : data;
                }},
                { data: 'self_direction', name: 'self_direction', orderable: false, searchable: false, render: function(data, type, row) {
                    return type === 'display' ? scoreSelect('self_direction', data, row) : data;
                }},
                { data: 'caring', name: 'caring', orderable: false, searchable: false, render: function(data, type, row) {
                    return type === 'display' ? scoreSelect('caring', data, row) : data;
                }},
                { data: 'transfer', name: 'transfer', orderable: false, searchable: false, render: function(data, type, row) {
                    return type === 'display' ? scoreSelect('transfer', data, row) : data;
                }},
                { data: 'aksi', className: "text-center", orderable: false, searchable: false, width: "12%" }
            ]
        });

        $.get("{{ route('api.kelas.index') }}", function (response) {
            if (!response.success) {
                return;
            }

            response.data.forEach(function (kelas) {
                $('#selectKelas').append(new Option(kelas.nama, kelas.id));
            });
        });

        // Filter listeners
        $('#selectKelas').off('change').on('change', function () {
            if ($(this).val()) {
                tablePenilaian.ajax.reload();
            }
        });

        $('#selectPertemuan').off('change').on('change', function () {
            if ($('#selectKelas').val()) {
                tablePenilaian.ajax.reload();
            }
        });

        // Reload button
        $('#reloadBtn').off('click').on('click', function() {
            tablePenilaian.ajax.reload();
        });

        $('#penilaianTable').on('click', '.score-dropdown-toggle', function (event) {
            event.preventDefault();
            event.stopPropagation();

            bootstrap.Dropdown.getOrCreateInstance(this, {
                boundary: 'viewport'
            }).toggle();
        });

        $(document).on('click', function () {
            $('.score-dropdown-toggle').each(function () {
                const dropdown = bootstrap.Dropdown.getInstance(this);

                if (dropdown) {
                    dropdown.hide();
                }
            });
        });

        $('#penilaianTable').on('click', '.score-option', function () {
            const option = $(this);
            const row = option.closest('tr');
            const button = option.closest('.score-dropdown').find('.score-dropdown-toggle');
            const penilaianId = button.data('penilaian-id');
            const field = button.data('field');
            const value = option.data('value').toString();

            if (!value) {
                return;
            }

            const dropdown = bootstrap.Dropdown.getInstance(button[0]);
            if (dropdown) {
                dropdown.hide();
            }

            button.prop('disabled', true).text(value).attr('data-value', value).data('value', value);

            if (penilaianId) {
                $.ajax({
                    type: 'PUT',
                    url: '{{ url('/penilaian') }}/' + penilaianId,
                    data: { [field]: value },
                    success: function (response) {
                        if (!response.success) {
                            alert(response.message || 'Gagal menyimpan penilaian.');
                        }
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan penilaian.');
                        tablePenilaian.ajax.reload(null, false);
                    },
                    complete: function () {
                        button.prop('disabled', false);
                    }
                });
                return;
            }

            const payload = {
                siswa_id: button.data('siswa-id'),
                pertemuan: $('#selectPertemuan').val()
            };
            let complete = true;

            row.find('.score-dropdown-toggle').each(function () {
                const current = $(this);
                const currentValue = current.data('value') ? current.data('value').toString() : '';

                if (!currentValue) {
                    complete = false;
                }

                payload[current.data('field')] = currentValue;
            });

            if (!complete) {
                button.prop('disabled', false);
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ url('/penilaian') }}',
                data: payload,
                success: function (response) {
                    if (response.success) {
                        tablePenilaian.ajax.reload(null, false);
                        return;
                    }

                    alert(response.message || 'Gagal menyimpan penilaian.');
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan penilaian.');
                    tablePenilaian.ajax.reload(null, false);
                },
                complete: function () {
                    button.prop('disabled', false);
                }
            });
        });

        // Modal close event - reload table
        $('#myModal').on('hidden.bs.modal', function () {
            tablePenilaian.ajax.reload(null, false);
        });

        $('#exportBtn').off('click').on('click', function () {
            if (!$('#selectKelas').val()) {
                alert('Pilih kelas terlebih dahulu.');
                return;
            }

            const button = $(this);
            button.prop('disabled', true);

            fetch('{{ route('penilaian.export') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/vnd.ms-excel'
                },
                body: JSON.stringify({
                    kelas_id: $('#selectKelas').val(),
                    pertemuan: $('#selectPertemuan').val(),
                    search: tablePenilaian.search()
                })
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Export gagal.');
                }

                const disposition = response.headers.get('Content-Disposition') || '';
                const match = disposition.match(/filename="?([^"]+)"?/);

                return response.blob().then(function (blob) {
                    return {
                        blob: blob,
                        filename: match ? match[1] : 'penilaian.xls'
                    };
                });
            })
            .then(function (file) {
                const url = window.URL.createObjectURL(file.blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = file.filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);
            })
            .catch(function (error) {
                alert(error.message || 'Export gagal.');
            })
            .finally(function () {
                button.prop('disabled', false);
            });
        });
    });

    function modalAction(url = '') {
        $('#myModal .modal-content').load(url, function () {
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('myModal'));
            modal.show();
        });
    }

    function createSiswa() {
        modalAction('{{ route('penilaian.siswa.create_ajax') }}');
    }

    function deletePenilaian(penilaianId) {
        if (confirm('Apakah Anda yakin ingin menghapus penilaian ini?')) {
            $.ajax({
                type: 'DELETE',
                url: '{{ url('/penilaian') }}/' + penilaianId,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        alert('Penilaian berhasil dihapus');
                        $('#penilaianTable').DataTable().ajax.reload();
                    } else {
                        alert('Gagal menghapus: ' + data.message);
                    }
                },
                error: function(err) {
                    alert('Terjadi kesalahan: ' + err.responseJSON.message);
                }
            });
        }
    }
    </script>
    @endpush
</x-app-layout>
