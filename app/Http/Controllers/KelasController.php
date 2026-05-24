<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KelasController extends Controller
{
    /**
     * Dapatkan daftar kelas dari sekolah guru (API)
     * Hanya menampilkan kelas yang ada di sekolah guru
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }

            $guru = $user->guru;
            if (!$guru) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ini tidak memiliki data guru',
                ], 403);
            }

            if (!$guru->sekolah_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru tidak memiliki sekolah yang terdaftar',
                ], 403);
            }

            $kelas = Kelas::where('sekolah_id', $guru->sekolah_id)
                ->select('id', 'nama', 'sekolah_id')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $kelas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kelas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dapatkan detail kelas dengan siswa
     * Hanya untuk kelas di sekolah guru
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi',
                ], 401);
            }

            $guru = $user->guru;
            if (!$guru) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ini tidak memiliki data guru',
                ], 403);
            }

            if (!$guru->sekolah_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru tidak memiliki sekolah yang terdaftar',
                ], 403);
            }

            $kelas = Kelas::where('sekolah_id', $guru->sekolah_id)
                ->with(['siswa' => function ($query) {
                    $query->select('id', 'nama', 'gender', 'kelas_id');
                }])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $kelas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas tidak ditemukan: ' . $e->getMessage(),
            ], 404);
        }
    }
}
