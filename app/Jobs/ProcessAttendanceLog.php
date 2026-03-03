<?php

namespace App\Jobs;

use App\Services\AttendanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAttendanceLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [1, 5, 15];

    /**
     * The attendance data to process.
     *
     * @var array
     */
    protected array $data;

    /**
     * Create a new job instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->onQueue('high');
    }

    /**
     * Execute the job.
     *
     * @param AttendanceService $attendanceService
     * @return void
     */
    public function handle(AttendanceService $attendanceService): void
    {
        try {
            // Create the attendance record
            $attendanceRecord = $attendanceService->createAttendanceRecord($this->data);

            Log::info('Attendance record created successfully', [
                'attendance_id' => $attendanceRecord->id,
                'user_id' => $this->data['user_id'],
                'location_id' => $this->data['location_id'],
                'scan_type' => $this->data['scan_type'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process attendance log', [
                'error' => $e->getMessage(),
                'data' => $this->data,
                'attempt' => $this->attempts(),
            ]);

            // Re-throw the exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessAttendanceLog job failed permanently', [
            'error' => $exception->getMessage(),
            'data' => $this->data,
        ]);

        // TODO: Notify administrators about the failed job
    }
}
