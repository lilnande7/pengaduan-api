<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    /**
     * Test WhatsApp connectivity
     */
    public function testConnection()
    {
        $message = "🧪 *TEST KONEKSI WHATSAPP*\n\n" .
                   "Sistem Pengaduan Online berhasil terhubung dengan WhatsApp!\n\n" .
                   "⏰ Waktu: " . now()->format('d/m/Y H:i:s') . "\n" .
                   "🔧 Status: Aktif dan siap menerima notifikasi";

        $sent = WhatsappService::send($message);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp test message sent',
            'data' => [
                'sent' => $sent,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Send daily summary manually
     */
    public function sendDailySummary()
    {
        $sent = WhatsappService::sendDailySummaryToAdmin();

        return response()->json([
            'success' => true,
            'message' => 'Daily summary sent to admin',
            'data' => [
                'sent' => $sent,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Send custom message to admin
     */
    public function sendCustomMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $message = "📢 *PESAN CUSTOM*\n\n" . $request->message . "\n\n⏰ " . now()->format('d/m/Y H:i:s');
        
        $sent = WhatsappService::send($message);

        return response()->json([
            'success' => true,
            'message' => 'Custom message sent to admin',
            'data' => [
                'sent' => $sent,
                'message' => $request->message,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Resend notification for specific ticket
     */
    public function resendTicketNotification(Request $request, $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        $sent = WhatsappService::notifyAdminNewTicket($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Ticket notification resent to admin',
            'data' => [
                'sent' => $sent,
                'ticket_number' => $ticket->ticket_number,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get WhatsApp notification settings
     */
    public function getSettings()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'fonnte_configured' => !empty(env('FONNTE_TOKEN')),
                'admin_phone_configured' => !empty(env('ADMIN_PHONE')),
                'admin_phone' => env('ADMIN_PHONE') ? substr(env('ADMIN_PHONE'), 0, 4) . '****' : null,
                'notifications_enabled' => !empty(env('FONNTE_TOKEN')) && !empty(env('ADMIN_PHONE'))
            ]
        ]);
    }

    /**
     * Test user notification
     */
    public function testUserNotification(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string|max:1000'
        ]);

        $message = $request->message ?? "🧪 *TEST NOTIFIKASI USER*\n\nSistem Pengaduan Online berhasil mengirim notifikasi ke nomor Anda!\n\n⏰ " . now()->format('d/m/Y H:i:s');
        
        $sent = WhatsappService::sendToUser($request->phone, $message);

        return response()->json([
            'success' => true,
            'message' => 'Test message sent to user',
            'data' => [
                'sent' => $sent,
                'phone' => $request->phone,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get notification logs (from Laravel logs)
     */
    public function getNotificationLogs()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (!file_exists($logFile)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No log file found'
                ]);
            }

            // Read last 50 lines of log file
            $logs = [];
            $file = new \SplFileObject($logFile);
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key() + 1;
            $startLine = max(0, $totalLines - 100);
            
            $file->seek($startLine);
            while (!$file->eof()) {
                $line = $file->current();
                if (strpos($line, 'WhatsApp') !== false) {
                    $logs[] = trim($line);
                }
                $file->next();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'logs' => array_slice($logs, -20), // Last 20 WhatsApp related logs
                    'total_lines_checked' => min(100, $totalLines),
                    'whatsapp_logs_found' => count($logs)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error reading logs: ' . $e->getMessage()
            ], 500);
        }
    }
}
