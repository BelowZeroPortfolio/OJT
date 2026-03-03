<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateReport implements ShouldQueue
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
     * The report ID to generate.
     *
     * @var int
     */
    protected int $reportId;

    /**
     * The date for the report.
     *
     * @var string
     */
    protected string $date;

    /**
     * Create a new job instance.
     *
     * @param int $reportId
     * @param string $date
     */
    public function __construct(int $reportId, string $date)
    {
        $this->reportId = $reportId;
        $this->date = $date;
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     *
     * @param ReportService $reportService
     * @return void
     */
    public function handle(ReportService $reportService): void
    {
        try {
            // Find the report record
            $report = Report::findOrFail($this->reportId);

            Log::info('Starting report generation', [
                'report_id' => $this->reportId,
                'report_type' => $report->report_type,
                'format' => $report->format,
            ]);

            // Generate report data based on type
            $data = match ($report->report_type) {
                'daily' => $reportService->generateDailyReport($this->date, $report->filters ?? []),
                'weekly' => $reportService->generateWeeklyReport($this->date, $report->filters ?? []),
                'monthly' => $reportService->generateMonthlyReport($this->date, $report->filters ?? []),
                default => throw new \InvalidArgumentException('Invalid report type'),
            };

            // Generate filename
            $filename = sprintf(
                '%s_report_%s_%s',
                $report->report_type,
                Carbon::parse($this->date)->format('Y-m-d'),
                uniqid()
            );

            // Export to requested format
            $filePath = match ($report->format) {
                'csv' => $reportService->exportToCsv($data, $filename),
                'pdf' => $reportService->exportToPdf(
                    $data,
                    $filename,
                    ucfirst($report->report_type) . ' Attendance Report',
                    $this->getDateRangeString($report->report_type, $this->date)
                ),
                default => throw new \InvalidArgumentException('Invalid report format'),
            };

            // Update report record
            $report->update([
                'file_path' => $filePath,
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);

            Log::info('Report generated successfully', [
                'report_id' => $this->reportId,
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'report_id' => $this->reportId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Update report status to failed if this is the last attempt
            if ($this->attempts() >= $this->tries) {
                Report::where('id', $this->reportId)->update([
                    'status' => 'failed',
                ]);
            }

            // Re-throw the exception to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Get a human-readable date range string for the report.
     *
     * @param string $reportType
     * @param string $date
     * @return string
     */
    protected function getDateRangeString(string $reportType, string $date): string
    {
        return match ($reportType) {
            'daily' => Carbon::parse($date)->format('F d, Y'),
            'weekly' => sprintf(
                '%s - %s',
                Carbon::parse($date)->startOfWeek()->format('F d, Y'),
                Carbon::parse($date)->endOfWeek()->format('F d, Y')
            ),
            'monthly' => Carbon::parse($date)->format('F Y'),
            default => $date,
        };
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateReport job failed permanently', [
            'report_id' => $this->reportId,
            'error' => $exception->getMessage(),
        ]);

        // Update report status
        Report::where('id', $this->reportId)->update([
            'status' => 'failed',
        ]);

        // TODO: Notify administrators about the failed job
    }
}
