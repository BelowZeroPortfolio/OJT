<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyAdminOfFailedJob
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(JobFailed $event): void
    {
        // Log the failed job details
        Log::error('Queue job failed permanently', [
            'connection' => $event->connectionName,
            'queue' => $event->job->getQueue(),
            'job' => $event->job->getName(),
            'exception' => $event->exception->getMessage(),
            'data' => $event->data,
        ]);

        // In a production environment, you would send an email notification here
        // Example:
        // Mail::to(config('mail.admin_email'))->send(new JobFailedNotification($event));
        
        // For now, we'll just log it
        // Administrators can monitor the logs or set up log aggregation services
    }
}
