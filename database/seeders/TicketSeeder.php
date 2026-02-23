<?php

namespace Database\Seeders;

use App\Models\Ticket;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample tickets for testing
        $tickets = [
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'name' => 'John Doe',
                'phone' => '08123456789',
                'email' => 'john@example.com',
                'category' => 'Layanan Publik',
                'message' => 'Pengaduan tentang pelayanan yang kurang memuaskan di kantor kelurahan.',
                'status' => 'baru',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'name' => 'Jane Smith',
                'phone' => '08234567890',
                'email' => 'jane@example.com',
                'category' => 'Infrastruktur',
                'message' => 'Jalan rusak di depan rumah saya sudah lama tidak diperbaiki.',
                'status' => 'diproses',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(1),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'name' => 'Bob Wilson',
                'phone' => '08345678901',
                'email' => 'bob@example.com',
                'category' => 'Keamanan',
                'message' => 'Lampu jalan mati di area perumahan, sangat gelap dan tidak aman.',
                'status' => 'selesai',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(2),
            ],
            [
                'ticket_number' => Ticket::generateTicketNumber(),
                'name' => 'Alice Brown',
                'phone' => '08456789012',
                'email' => 'alice@example.com',
                'category' => 'Lingkungan',
                'message' => 'Banyak sampah berserakan di taman kota.',
                'status' => 'baru',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ];

        foreach ($tickets as $ticket) {
            Ticket::create($ticket);
        }
    }
}