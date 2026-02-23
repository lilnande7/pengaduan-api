<?php

namespace App\Console\Commands;

use App\Services\WhatsappService;
use Illuminate\Console\Command;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily summary of tickets to admin via WhatsApp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending daily summary to admin...');
        
        $sent = WhatsappService::sendDailySummaryToAdmin();
        
        if ($sent) {
            $this->info('✅ Daily summary sent successfully to admin!');
            return 0;
        } else {
            $this->error('❌ Failed to send daily summary to admin');
            return 1;
        }
    }
}
