<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ticket_number',
        'name', 
        'phone',
        'email',
        'category',
        'message',
        'status',
        'evidence_file'
    ];
    
    /**
     * Get the replies for the ticket.
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
    
    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber()
    {
        $prefix = 'TCK';
        $date = date('Ymd');
        $lastTicket = self::where('ticket_number', 'LIKE', $prefix . '-' . $date . '-%')
            ->orderBy('ticket_number', 'desc')
            ->first();
            
        if ($lastTicket) {
            $lastNumber = intval(substr($lastTicket->ticket_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . '-' . $date . '-' . $newNumber;
    }
    
    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
