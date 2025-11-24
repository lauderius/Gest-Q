<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CsvImportSeeder extends Seeder
{
    public function run(): void
    {
        // 1. IMPORTAR SERVIÇOS (services.csv)
        // Alterado para public_path()
        $servicesPath = public_path('data/services.csv');

        if (file_exists($servicesPath)) {
            $this->command->info('A ler serviços de: ' . $servicesPath);

            $handle = fopen($servicesPath, "r");
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // row[0] = Grupo, row[1] = Nome
                if (isset($row[0]) && isset($row[1])) {
                    // Evitar duplicados
                    Queue::firstOrCreate(
                        [
                            'name' => trim($row[1]),
                            'group_name' => trim($row[0])
                        ],
                        [
                            'active' => true,
                            'avg_service_sec' => 180
                        ]
                    );
                }
            }
            fclose($handle);
        } else {
            $this->command->error('ERRO: services.csv não encontrado em ' . $servicesPath);
        }

        // 2. IMPORTAR USUÁRIOS (users.csv)
        // Alterado para public_path()
        $usersPath = public_path('data/users.csv');

        if (file_exists($usersPath)) {
            $this->command->info('A ler usuários de: ' . $usersPath);

            $handle = fopen($usersPath, "r");
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // row[0] = User, row[1] = Pass
                if (isset($row[0]) && isset($row[1])) {
                    $username = trim($row[0]);
                    $password = trim($row[1]);
                    $email = strtolower(str_replace(' ', '', $username)) . '@pitagoras.system';

                    if (!User::where('email', $email)->exists()) {
                        User::create([
                            'name' => $username,
                            'email' => $email,
                            'password' => Hash::make($password),
                        ]);
                    }
                }
            }
            fclose($handle);
        } else {
            $this->command->error('ERRO: users.csv não encontrado em ' . $usersPath);
        }
    }
}
