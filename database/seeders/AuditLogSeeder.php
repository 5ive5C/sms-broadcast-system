<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $maybank = Client::where('slug', 'maybank-berhad')->firstOrFail();
        $admin = User::where('email', 'superadmin@example.com')->first();
        $siti = $maybank->users()->where('email', 'siti.rahman@maybank.com')->firstOrFail();
        $hafiz = $maybank->users()->where('email', 'hafiz.aziz@maybank.com')->firstOrFail();

        $rows = [
            ['actor_id' => $admin?->id, 'actor_name' => $admin?->name ?? 'admin.faridah', 'client_id' => $maybank->id, 'action' => 'Top-up approved', 'detail' => 'Maybank · TOP-2026-0418 · +100,000 credits', 'created_at' => '2026-09-13 09:44'],
            ['actor_id' => $hafiz->id, 'actor_name' => $hafiz->name, 'client_id' => $maybank->id, 'action' => 'Campaign launched', 'detail' => 'Raya statement reminder · 23,962 recipients', 'created_at' => '2026-09-13 09:41'],
            ['actor_id' => $siti->id, 'actor_name' => $siti->name, 'client_id' => $maybank->id, 'action' => 'API key generated', 'detail' => 'key_live_8f2a•••• · IP 203.115.4.0/24', 'created_at' => '2026-09-12 16:02'],
            ['actor_id' => $siti->id, 'actor_name' => $siti->name, 'client_id' => $maybank->id, 'action' => 'Role changed', 'detail' => 'lim.wei · Campaign officer to Reports only', 'created_at' => '2026-09-12 15:38'],
            ['actor_id' => null, 'actor_name' => 'system', 'client_id' => $maybank->id, 'action' => 'Credit refunded', 'detail' => '38 messages rejected by iSMS after 3 attempts', 'created_at' => '2026-09-11 22:15'],
        ];

        foreach ($rows as $row) {
            AuditLog::updateOrCreate(
                ['action' => $row['action'], 'detail' => $row['detail'], 'created_at' => $row['created_at']],
                $row,
            );
        }
    }
}
