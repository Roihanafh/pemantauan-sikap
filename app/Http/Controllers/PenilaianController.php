<?php

namespace App\Http\Controllers;

use App\Models\Penilaian;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PenilaianController extends Controller
{
    /**
     * Get teacher's school from authenticated user
     * 
     * @return int|null
     */
    private function getTeacherSekolahId()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return null;
            }

            $guru = $user->guru;
            return $guru ? $guru->sekolah_id : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Verify teacher has access to a class
     * 
     * @param int $kelasId
     * @return bool
     */
    private function canAccessKelas($kelasId): bool
    {
        $sekolahId = $this->getTeacherSekolahId();
        if (!$sekolahId) {
            return false;
        }

        $kelas = Kelas::find($kelasId);
        return $kelas && $kelas->sekolah_id === $sekolahId;
    }

    /**
     * Dapatkan data siswa dengan penilaian untuk DataTables (AJAX)
     * Hanya menampilkan siswa dari sekolah guru
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getDataTables(Request $request): JsonResponse
    {
        if (!$request->filled('kelas_id')) {
            return response()->json([
                'draw' => (int)($request->draw ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'pertemuan' => 'required|integer|min:1|max:16',
        ]);

        // Verify teacher can access this class
        if (!$this->canAccessKelas($request->kelas_id)) {
            return response()->json([
                'draw' => $request->draw ?? 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Anda tidak memiliki akses ke kelas ini',
            ], 403);
        }

        try {
            $sekolahId = $this->getTeacherSekolahId();
            $data = collect(Penilaian::getPenilaianDataTables(
                $request->kelas_id,
                $request->pertemuan
            ));
            $recordsTotal = $data->count();
            $search = trim((string) data_get($request->input('search'), 'value', ''));

            if ($search !== '') {
                $data = $data->filter(function ($item) use ($search) {
                    return stripos($item['nama'], $search) !== false;
                })->values();
            }

            $recordsFiltered = $data->count();
            $start = max((int) $request->input('start', 0), 0);
            $length = (int) $request->input('length', 10);

            if ($length > 0) {
                $data = $data->slice($start, $length)->values();
            }

            // Add action buttons to each row
            $dataWithActions = $data->map(function ($item, $index) use ($start) {
                $item['DT_RowIndex'] = $start + $index + 1;
                
                if ($item['penilaian_id']) {
                    $item['aksi'] = '<button class="btn btn-sm btn-danger" onclick="deletePenilaian(' . $item['penilaian_id'] . ')">
                        <i class="fa fa-trash"></i> Hapus
                    </button>';
                } else {
                    $item['aksi'] = '<span class="text-muted">Isi nilai</span>';
                }
                
                return $item;
            })->toArray();

            return response()->json([
                'draw' => (int)($request->draw ?? 0),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $dataWithActions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => $request->draw ?? 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Gagal mengambil data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan form tambah penilaian untuk modal AJAX.
     */
    public function createAjax(Request $request)
    {
        $sekolahId = $this->getTeacherSekolahId();
        abort_if(!$sekolahId, 403);

        $siswas = Siswa::with('kelas')
            ->whereHas('kelas', function ($query) use ($sekolahId) {
                $query->where('sekolah_id', $sekolahId);
            })
            ->orderBy('nama')
            ->get();

        $selectedSiswaId = $request->integer('siswa_id') ?: null;

        return view('penilaian.form_ajax', [
            'mode' => 'create',
            'penilaian' => null,
            'siswas' => $siswas,
            'selectedSiswaId' => $selectedSiswaId,
        ]);
    }

    /**
     * Tampilkan form edit penilaian untuk modal AJAX.
     */
    public function editAjax($id)
    {
        $penilaian = Penilaian::with('siswa.kelas')->findOrFail($id);

        if ($penilaian->siswa->kelas->sekolah_id !== $this->getTeacherSekolahId()) {
            abort(403);
        }

        return view('penilaian.form_ajax', [
            'mode' => 'edit',
            'penilaian' => $penilaian,
            'siswas' => collect([$penilaian->siswa]),
            'selectedSiswaId' => $penilaian->siswa_id,
        ]);
    }

    /**
     * Tampilkan form tambah siswa untuk modal AJAX.
     */
    public function createSiswaAjax()
    {
        $sekolahId = $this->getTeacherSekolahId();
        abort_if(!$sekolahId, 403);

        $kelas = Kelas::where('sekolah_id', $sekolahId)
            ->orderBy('nama')
            ->get();

        return view('penilaian.siswa_form_ajax', [
            'kelas' => $kelas,
        ]);
    }

    /**
     * Simpan siswa baru dan hubungkan ke kelas dari sekolah guru.
     */
    public function storeSiswa(Request $request): JsonResponse
    {
        $sekolahId = $this->getTeacherSekolahId();

        if (!$sekolahId) {
            return response()->json([
                'success' => false,
                'message' => 'Guru belum terhubung ke sekolah.',
            ], 403);
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'kelas_manual' => 'nullable|string|max:255|required_without:kelas_id',
        ]);

        if (empty($validated['kelas_id']) && trim((string) $validated['kelas_manual']) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Pilih kelas dari database atau isi nama kelas baru.',
            ], 422);
        }

        try {
            if (!empty($validated['kelas_id'])) {
                if (!$this->canAccessKelas($validated['kelas_id'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kelas tidak ditemukan di sekolah Anda.',
                    ], 403);
                }

                $kelas = Kelas::findOrFail($validated['kelas_id']);
            } else {
                $kelas = Kelas::firstOrCreate([
                    'nama' => trim($validated['kelas_manual']),
                    'sekolah_id' => $sekolahId,
                ]);
            }

            $siswa = Siswa::create([
                'nama' => $validated['nama'],
                'gender' => $validated['gender'],
                'kelas_id' => $kelas->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil ditambahkan',
                'data' => [
                    'siswa' => $siswa,
                    'kelas' => $kelas,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan siswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan atau update penilaian siswa
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validasi = $request->validate(
            Penilaian::validasiData($request->all())
        );

        try {
            // Verify student belongs to teacher's school
            $siswa = Siswa::with('kelas')->findOrFail($request->siswa_id);
            if ($siswa->kelas->sekolah_id !== $this->getTeacherSekolahId()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan di sekolah Anda',
                ], 403);
            }

            $penilaian = Penilaian::simpanPenilaian(
                $request->siswa_id,
                $request->pertemuan,
                $validasi
            );

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan',
                'data' => $penilaian,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update penilaian siswa
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $penilaian = Penilaian::with('siswa.kelas')->findOrFail($id);

        // Verify teacher can update this assessment
        if ($penilaian->siswa->kelas->sekolah_id !== $this->getTeacherSekolahId()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah penilaian ini',
            ], 403);
        }

        $validasi = $request->validate([
            'respect' => 'nullable|integer|min:1|max:4',
            'participation' => 'nullable|integer|min:1|max:4',
            'self_direction' => 'nullable|integer|min:1|max:4',
            'caring' => 'nullable|integer|min:1|max:4',
            'transfer' => 'nullable|integer|min:1|max:4',
        ]);

        try {
            $penilaian->update($validasi);

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil diupdate',
                'data' => $penilaian,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan multiple penilaian sekaligus (batch)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function storeBatch(Request $request): JsonResponse
    {
        $request->validate([
            'penilaian' => 'required|array|min:1',
            'penilaian.*.siswa_id' => 'required|integer|exists:siswa,id',
            'penilaian.*.pertemuan' => 'required|integer|min:1|max:16',
            'penilaian.*.respect' => 'required|integer|min:1|max:4',
            'penilaian.*.participation' => 'required|integer|min:1|max:4',
            'penilaian.*.self_direction' => 'required|integer|min:1|max:4',
            'penilaian.*.caring' => 'required|integer|min:1|max:4',
            'penilaian.*.transfer' => 'required|integer|min:1|max:4',
        ]);

        try {
            $sekolahId = $this->getTeacherSekolahId();
            $savedData = [];
            
            foreach ($request->penilaian as $data) {
                // Verify student belongs to teacher's school
                $siswa = Siswa::with('kelas')->find($data['siswa_id']);
                if (!$siswa || $siswa->kelas->sekolah_id !== $sekolahId) {
                    continue;
                }

                $penilaian = Penilaian::simpanPenilaian(
                    $data['siswa_id'],
                    $data['pertemuan'],
                    $data
                );
                $savedData[] = $penilaian;
            }

            return response()->json([
                'success' => true,
                'message' => count($savedData) . ' penilaian berhasil disimpan',
                'data' => $savedData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan penilaian batch: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dapatkan detail penilaian siswa
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $penilaian = Penilaian::with('siswa.kelas')->findOrFail($id);

            // Verify teacher can view this assessment
            if ($penilaian->siswa->kelas->sekolah_id !== $this->getTeacherSekolahId()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke penilaian ini',
                ], 403);
            }

            $rataRataNilai = $penilaian->hitungRataNilai();

            return response()->json([
                'success' => true,
                'data' => array_merge($penilaian->toArray(), $rataRataNilai),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data penilaian tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Hapus penilaian siswa
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $penilaian = Penilaian::with('siswa.kelas')->findOrFail($id);

            // Verify teacher can delete this assessment
            if ($penilaian->siswa->kelas->sekolah_id !== $this->getTeacherSekolahId()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus penilaian ini',
                ], 403);
            }

            $penilaian->delete();

            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export penilaian ke format Excel-compatible
     * Hanya export untuk kelas di sekolah guru
     * 
     * @param Request $request
     */
    public function export(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'pertemuan' => 'required|integer|min:1|max:16',
            'search' => 'nullable|string|max:255',
        ]);

        // Verify teacher can access this class
        if (!$this->canAccessKelas($request->kelas_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke kelas ini',
            ], 403);
        }

        try {
            $kelas = Kelas::findOrFail($request->kelas_id);
            $data = collect(Penilaian::getPenilaianDataTables(
                $request->kelas_id,
                $request->pertemuan
            ));
            $search = trim((string) $request->input('search', ''));

            if ($search !== '') {
                $data = $data->filter(function ($item) use ($search) {
                    return stripos($item['nama'], $search) !== false;
                })->values();
            }

            $rows = $data->map(function ($item, $index) {
                return [
                    'No' => $index + 1,
                    'Nama Siswa' => $item['nama'],
                    'Gender' => $item['gender'] === 'L' ? 'Laki-laki' : 'Perempuan',
                    'Respect' => $item['respect'] ?: '',
                    'Participation' => $item['participation'] ?: '',
                    'Self Direction' => $item['self_direction'] ?: '',
                    'Caring' => $item['caring'] ?: '',
                    'Transfer' => $item['transfer'] ?: '',
                ];
            });

            $html = view('penilaian.export_excel', [
                'kelas' => $kelas,
                'pertemuan' => $request->pertemuan,
                'rows' => $rows,
            ])->render();
            $filename = 'penilaian_kelas_' . str_replace(' ', '_', strtolower($kelas->nama)) . '_pertemuan_' . $request->pertemuan . '.xls';

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal export data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
