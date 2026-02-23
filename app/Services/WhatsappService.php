<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Service for sending messages via Fonnte API
 */
class WhatsappService
{
    /**
     * Send WhatsApp message to admin
     *
     * @param string $message
     * @return bool
     */
    public static function send($message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN')
            ])->post('https://api.fonnte.com/send', [
                'target' => env('ADMIN_PHONE'),
                'message' => $message
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'message' => $message,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp message', [
                    'message' => $message,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending WhatsApp message', [
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Send WhatsApp message to specific user
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public static function sendToUser($phoneNumber, $message)
    {
        try {
            // Format phone number (remove leading 0 and add 62 for Indonesia)
            $formattedPhone = self::formatPhoneNumber($phoneNumber);
            
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN')
            ])->post('https://api.fonnte.com/send', [
                'target' => $formattedPhone,
                'message' => $message
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp message sent to user successfully', [
                    'phone' => $formattedPhone,
                    'message' => $message,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp message to user', [
                    'phone' => $formattedPhone,
                    'message' => $message,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while sending WhatsApp message to user', [
                'phone' => $phoneNumber,
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Notify admin about new ticket submission
     *
     * @param \App\Models\Ticket $ticket
     * @return bool
     */
    public static function notifyAdminNewTicket($ticket)
    {
        $message = "🆕 *TIKET BARU MASUK*\n\n" .
                   "📝 Nomor: {$ticket->ticket_number}\n" .
                   "👤 Nama: {$ticket->name}\n" .
                   "📞 Telepon: {$ticket->phone}\n" .
                   "📧 Email: {$ticket->email}\n" .
                   "📂 Kategori: {$ticket->category}\n" .
                   "💬 Pesan: {$ticket->message}\n" .
                   "🕒 Waktu: " . $ticket->created_at->format('d/m/Y H:i') . "\n\n" .
                   "Silakan login ke sistem admin untuk memberikan tanggapan.";
        
        return self::send($message);
    }
    
    /**
     * Notify admin about ticket status change
     *
     * @param \App\Models\Ticket $ticket
     * @param string $oldStatus
     * @param string $newStatus
     * @return bool
     */
    public static function notifyAdminStatusChange($ticket, $oldStatus, $newStatus)
    {
        $statusEmoji = [
            'baru' => '🆕',
            'diproses' => '⚙️',
            'selesai' => '✅'
        ];
        
        $message = "🔄 *STATUS TIKET DIUPDATE*\n\n" .
                   "📝 Nomor: {$ticket->ticket_number}\n" .
                   "👤 Nama: {$ticket->name}\n" .
                   "📊 Status Lama: {$statusEmoji[$oldStatus]} " . ucfirst($oldStatus) . "\n" .
                   "📊 Status Baru: {$statusEmoji[$newStatus]} " . ucfirst($newStatus) . "\n" .
                   "🕒 Diupdate: " . now()->format('d/m/Y H:i') . "\n\n" .
                   "Pengaduan sedang dalam penanganan.";
        
        return self::send($message);
    }
    
    /**
     * Notify admin about new reply from user  
     *
     * @param \App\Models\Reply $reply
     * @return bool
     */
    public static function notifyAdminNewReply($reply)
    {
        $ticket = $reply->ticket;
        
        $message = "💬 *BALASAN BARU DARI USER*\n\n" .
                   "📝 Tiket: {$ticket->ticket_number}\n" .
                   "👤 Dari: {$ticket->name}\n" .
                   "📞 Telepon: {$ticket->phone}\n" .
                   "💬 Balasan: {$reply->message}\n" .
                   "🕒 Waktu: " . $reply->created_at->format('d/m/Y H:i') . "\n\n" .
                   "Silakan berikan tanggapan melalui sistem admin.";
        
        return self::send($message);
    }
    
    /**
     * Send daily summary to admin
     *
     * @return bool
     */
    public static function sendDailySummaryToAdmin()
    {
        $today = now()->startOfDay();
        $todayTickets = \App\Models\Ticket::whereDate('created_at', $today)->count();
        $pendingTickets = \App\Models\Ticket::where('status', 'baru')->count();
        $processedTickets = \App\Models\Ticket::where('status', 'diproses')->count();
        $completedTickets = \App\Models\Ticket::where('status', 'selesai')->count();
        
        $message = "📊 *LAPORAN HARIAN PENGADUAN*\n\n" .
                   "📅 Tanggal: " . $today->format('d/m/Y') . "\n\n" .
                   "📈 *STATISTIK:*\n" .
                   "🆕 Tiket Hari Ini: {$todayTickets}\n" .
                   "⏳ Menunggu: {$pendingTickets}\n" .
                   "⚙️ Diproses: {$processedTickets}\n" .
                   "✅ Selesai: {$completedTickets}\n\n" .
                   "Total Aktif: " . ($pendingTickets + $processedTickets) . " tiket\n\n" .
                   "Terima kasih atas dedikasi Anda! 🙏";
        
        return self::send($message);
    }
    
    /**
     * Format phone number for WhatsApp API
     *
     * @param string $phoneNumber
     * @return string
     */
    private static function formatPhoneNumber($phoneNumber)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // If doesn't start with 62, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}