<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reply extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ticket_id',
        'admin_id', 
        'message'
    ];
    
    /**
     * Get the ticket that owns the reply.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
    
    /**
     * Get the admin that made the reply.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
