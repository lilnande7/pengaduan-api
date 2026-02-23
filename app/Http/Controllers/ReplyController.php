<?php

namespace App\Http\Controllers;

use App\Models\Reply;
use App\Models\Ticket;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Reply::with(['ticket', 'admin']);

        if ($request->has('ticket_id')) {
            $query->where('ticket_id', $request->ticket_id);
        }

        $replies = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $replies
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'message' => 'required|string|min:5'
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);
        
        try {
            // Create reply
            $reply = Reply::create([
                'ticket_id' => $request->ticket_id,
                'admin_id' => Auth::id(),
                'message' => $request->message
            ]);

            // Update ticket status to 'diproses' if it's 'baru'
            if ($ticket->status === 'baru') {
                $ticket->update(['status' => 'diproses']);
            }

            // Load relations
            $reply->load(['admin', 'ticket']);

            // Send WhatsApp notification to user
            $waMessage = "🔄 *BALASAN PENGADUAN*\n\n";
            $waMessage .= "📋 *No. Tiket:* {$ticket->ticket_number}\n";
            $waMessage .= "👤 *Kepada:* {$ticket->name}\n";
            $waMessage .= "👨‍💼 *Dari:* {$reply->admin->name} (Admin)\n";
            $waMessage .= "💬 *Balasan:* {$request->message}\n";
            $waMessage .= "📊 *Status:* " . ucfirst($ticket->status) . "\n";
            $waMessage .= "\n⏰ *Waktu:* " . now()->format('d-m-Y H:i:s');

            // Try to send WhatsApp to user's phone
            $whatsappSent = WhatsappService::sendToUser($ticket->phone, $waMessage);
            
            if (!$whatsappSent) {
                Log::warning('Failed to send WhatsApp reply notification for ticket: ' . $ticket->ticket_number);
            }

            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dikirim',
                'data' => [
                    'reply' => $reply,
                    'ticket_status' => $ticket->status,
                    'whatsapp_sent' => $whatsappSent
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating reply: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim balasan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reply $reply)
    {
        $reply->load(['ticket', 'admin']);
        
        return response()->json([
            'success' => true,
            'data' => $reply
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reply $reply)
    {
        $request->validate([
            'message' => 'required|string|min:5'
        ]);

        // Only admin who created the reply can update it
        if (Auth::id() !== $reply->admin_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengedit balasan ini'
            ], 403);
        }

        $reply->update([
            'message' => $request->message
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil diupdate',
            'data' => $reply
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reply $reply)
    {
        // Only admin who created the reply can delete it
        if (Auth::id() !== $reply->admin_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus balasan ini'
            ], 403);
        }

        $reply->delete();

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dihapus'
        ]);
    }
}
