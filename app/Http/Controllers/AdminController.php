<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_tickets' => Ticket::count(),
            'pending_tickets' => Ticket::where('status', 'baru')->count(),
            'processed_tickets' => Ticket::where('status', 'diproses')->count(), 
            'completed_tickets' => Ticket::where('status', 'selesai')->count(),
            'today_tickets' => Ticket::whereDate('created_at', today())->count(),
            'this_month_tickets' => Ticket::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count()
        ];

        // Get recent tickets
        $recent_tickets = Ticket::with('replies')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get monthly statistics for chart
        $dbDriver = config('database.default');
        
        if ($dbDriver === 'sqlite') {
            // SQLite syntax
            $monthly_stats = Ticket::select(
                    DB::raw('strftime("%m", created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereRaw('strftime("%Y", created_at) = ?', [now()->year])
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        } else {
            // MySQL syntax
            $monthly_stats = Ticket::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'recent_tickets' => $recent_tickets,
                'monthly_chart' => $monthly_stats
            ]
        ]);
    }

    /**
     * Get all tickets with filters
     */
    public function tickets(Request $request)
    {
        $query = Ticket::with(['replies.admin']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Update ticket status
     */
    public function updateTicketStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:baru,diproses,selesai'
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $request->status;

        // Update ticket status
        $ticket->update([
            'status' => $newStatus
        ]);

        // Send notification to admin if status changed
        if ($oldStatus !== $newStatus) {
            WhatsappService::notifyAdminStatusChange($ticket, $oldStatus, $newStatus);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status tiket berhasil diupdate',
            'data' => $ticket
        ]);
    }

    /**
     * Delete ticket
     */
    public function deleteTicket(Ticket $ticket)
    {
        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tiket berhasil dihapus'
        ]);
    }

    /**
     * Export tickets to CSV
     */
    public function exportTickets(Request $request)
    {
        $query = Ticket::with('replies.admin');

        // Apply same filters as tickets list
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date . ' 23:59:59'
            ]);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $csvData = [];
        $csvData[] = [
            'No Tiket', 'Nama', 'Telepon', 'Email', 'Kategori', 
            'Pesan', 'Status', 'Tanggal Dibuat', 'Jumlah Balasan'
        ];

        foreach ($tickets as $ticket) {
            $csvData[] = [
                $ticket->ticket_number,
                $ticket->name,
                $ticket->phone,
                $ticket->email,
                $ticket->category,
                $ticket->message,
                ucfirst($ticket->status),
                $ticket->created_at->format('Y-m-d H:i:s'),
                $ticket->replies->count()
            ];
        }

        $filename = 'laporan_pengaduan_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response()->json([
            'success' => true,
            'message' => 'Data siap untuk diexport',
            'data' => [
                'filename' => $filename,
                'csv_data' => $csvData
            ]
        ]);
    }

    /**
     * Get categories statistics
     */
    public function categoryStats()
    {
        $categories = Ticket::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}