<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ticket::with('replies.admin');

        // Filter by phone for checking user's own tickets
        if ($request->has('phone')) {
            $query->where('phone', $request->phone);
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = [
            'Layanan Publik',
            'Infrastruktur',
            'Keamanan',
            'Lingkungan',
            'Kesehatan',
            'Pendidikan',
            'Transportasi',
            'Lainnya'
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'category' => 'required|string|max:100',
            'message' => 'required|string|min:10',
            'evidence_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120' // 5MB max
        ]);

        try {
            // Handle file upload
            $evidenceFile = null;
            if ($request->hasFile('evidence_file')) {
                $file = $request->file('evidence_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('evidence', $filename, 'public');
                $evidenceFile = $path;
            }

            // Generate unique ticket number
            $ticketNumber = Ticket::generateTicketNumber();

            // Create new ticket
            $ticket = Ticket::create([
                'ticket_number' => $ticketNumber,
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'category' => $request->category,
                'message' => $request->message,
                'evidence_file' => $evidenceFile,
                'status' => 'baru'
            ]);

            // Send WhatsApp notification to admin about new ticket
            $whatsappSent = WhatsappService::notifyAdminNewTicket($ticket);
            
            if (!$whatsappSent) {
                Log::warning('Failed to send WhatsApp notification for ticket: ' . $ticketNumber);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengaduan Anda berhasil dikirim. Nomor tiket: ' . $ticketNumber,
                'data' => [
                    'ticket' => $ticket,
                    'whatsapp_sent' => $whatsappSent
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating ticket: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengirim pengaduan. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        $ticket->load(['replies.admin']);
        
        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket)
    {
        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'sometimes|in:baru,diproses,selesai'
        ]);

        $ticket->update($request->only(['status']));

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil diupdate',
            'data' => $ticket
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        // Delete evidence file if exists
        if ($ticket->evidence_file) {
            Storage::disk('public')->delete($ticket->evidence_file);
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dihapus'
        ]);
    }

    /**
     * Check ticket status by ticket number and phone
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string',
            'phone' => 'required|string'
        ]);

        $ticket = Ticket::with(['replies.admin'])
            ->where('ticket_number', $request->ticket_number)
            ->where('phone', $request->phone)
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan atau nomor telepon tidak sesuai'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }
}
