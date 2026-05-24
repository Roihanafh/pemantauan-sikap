<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sekolah;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\User;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create schools
        $sekolah1 = Sekolah::create(['nama' => 'SD Negeri 1']);
        $sekolah2 = Sekolah::create(['nama' => 'SD Negeri 2']);

        // Create classes for sekolah 1
        Kelas::create(['nama' => '1A', 'sekolah_id' => $sekolah1->id]);
        Kelas::create(['nama' => '1B', 'sekolah_id' => $sekolah1->id]);
        Kelas::create(['nama' => '2A', 'sekolah_id' => $sekolah1->id]);

        // Create classes for sekolah 2
        Kelas::create(['nama' => '1A', 'sekolah_id' => $sekolah2->id]);
        Kelas::create(['nama' => '1B', 'sekolah_id' => $sekolah2->id]);

        // Get or create users for teachers
        $user1 = User::firstOrCreate(
            ['email' => 'guru1@example.com'],
            ['name' => 'Guru Satu', 'password' => bcrypt('password')]
        );

        $user2 = User::firstOrCreate(
            ['email' => 'guru2@example.com'],
            ['name' => 'Guru Dua', 'password' => bcrypt('password')]
        );

        // Create or update gurus with school assignments
        $guru1 = Guru::firstOrCreate(
            ['user_id' => $user1->id],
            ['sekolah_id' => $sekolah1->id]
        );

        $guru2 = Guru::firstOrCreate(
            ['user_id' => $user2->id],
            ['sekolah_id' => $sekolah2->id]
        );

        // Ensure gurus have sekolah_id set
        if (!$guru1->sekolah_id) {
            $guru1->update(['sekolah_id' => $sekolah1->id]);
        }
        if (!$guru2->sekolah_id) {
            $guru2->update(['sekolah_id' => $sekolah2->id]);
        }

        $this->command->info('✅ Sekolah, Kelas, dan Guru berhasil di-seed!');
        $this->command->info('Guru 1: ' . $guru1->user->email . ' → ' . $guru1->sekolah->nama);
        $this->command->info('Guru 2: ' . $guru2->user->email . ' → ' . $guru2->sekolah->nama);
    }
}
