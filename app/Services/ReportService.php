<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Generate a daily attendance report.
     *
     * @param string $date
     * @param array $filters
     * @return Collection
     */
    public function generateDailyReport(string $date, array $filters = []): Collection
    {
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = Carbon::parse($date)->endOfDay();

        return $this->fetchAttendanceData($startDate, $endDate, $filters);
    }

    /**
     * Generate a weekly attendance report.
     *
     * @param string $startDate
     * @param array $filters
     * @return Collection
     */
    public function generateWeeklyReport(string $startDate, array $filters = []): Collection
    {
        $start = Carbon::parse($startDate)->startOfWeek();
        $end = Carbon::parse($startDate)->endOfWeek();

        return $this->fetchAttendanceData($start, $end, $filters);
    }

    /**
     * Generate a monthly attendance report.
     *
     * @param string $yearMonth (format: YYYY-MM)
     * @param array $filters
     * @return Collection
     */
    public function generateMonthlyReport(string $yearMonth, array $filters = []): Collection
    {
        $start = Carbon::parse($yearMonth . '-01')->startOfMonth();
        $end = Carbon::parse($yearMonth . '-01')->endOfMonth();

        return $this->fetchAttendanceData($start, $end, $filters);
    }

    /**
     * Fetch attendance data with filters applied.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    protected function fetchAttendanceData(Carbon $startDate, Carbon $endDate, array $filters): Collection
    {
        $query = AttendanceRecord::with(['user', 'location'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('scan_type', 'time_in');

        $query = $this->applyFilters($query, $filters);

        $records = $query->get();

        return $this->calculateStatistics($records);
    }

    /**
     * Apply filters to the attendance query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilters($query, array $filters)
    {
        if (!empty($filters['student_id'])) {
            $query->where('user_id', $filters['student_id']);
        }

        if (!empty($filters['student_name'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['student_name'] . '%');
            });
        }

        if (!empty($filters['course'])) {
            $query->whereHas('user', function ($q) use ($filters) {
                $q->where('course', $filters['course']);
            });
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['location_name'])) {
            $query->whereHas('location', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['location_name'] . '%');
            });
        }

        return $query;
    }

    /**
     * Calculate attendance statistics for the report.
     *
     * @param Collection $records
     * @return Collection
     */
    protected function calculateStatistics(Collection $records): Collection
    {
        return $records->groupBy('user_id')->map(function ($userRecords) {
            $user = $userRecords->first()->user;
            $location = $userRecords->first()->location;

            $totalHours = $userRecords->sum(function ($record) {
                return $record->total_hours ?? 0;
            });

            $totalDays = $userRecords->count();
            
            // Calculate late entries (assuming 8:00 AM is the standard time)
            $lateEntries = $userRecords->filter(function ($record) {
                $timeIn = Carbon::parse($record->time_in);
                $standardTime = Carbon::parse($record->date)->setTime(8, 0, 0);
                return $timeIn->greaterThan($standardTime);
            })->count();

            // Calculate attendance percentage (assuming 8 hours per day is expected)
            $expectedHours = $totalDays * 8;
            $attendancePercentage = $expectedHours > 0 
                ? round(($totalHours / $expectedHours) * 100, 2) 
                : 0;

            return [
                'student_id' => $user->student_id,
                'student_name' => $user->name,
                'course' => $user->course,
                'location' => $location->name ?? 'N/A',
                'total_hours' => round($totalHours, 2),
                'total_days' => $totalDays,
                'attendance_percentage' => $attendancePercentage,
                'late_entries' => $lateEntries,
            ];
        })->values();
    }

    /**
     * Export report data to CSV format.
     *
     * @param Collection $data
     * @param string $filename
     * @return string File path
     */
    public function exportToCsv(Collection $data, string $filename): string
    {
        $csvContent = $this->generateCsvContent($data);
        
        $filePath = 'reports/' . $filename . '.csv';
        Storage::put($filePath, $csvContent);

        return $filePath;
    }

    /**
     * Generate CSV content from report data.
     *
     * @param Collection $data
     * @return string
     */
    protected function generateCsvContent(Collection $data): string
    {
        if ($data->isEmpty()) {
            return "No data available\n";
        }

        // CSV header
        $headers = array_keys($data->first());
        $csv = implode(',', $headers) . "\n";

        // CSV rows
        foreach ($data as $row) {
            $csv .= implode(',', array_map(function ($value) {
                // Escape values that contain commas or quotes
                if (is_string($value) && (strpos($value, ',') !== false || strpos($value, '"') !== false)) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }
                return $value ?? '';
            }, $row)) . "\n";
        }

        return $csv;
    }

    /**
     * Export report data to PDF format.
     *
     * @param Collection $data
     * @param string $filename
     * @param string $reportType
     * @param string $dateRange
     * @return string File path
     */
    public function exportToPdf(Collection $data, string $filename, string $reportType = 'Report', string $dateRange = ''): string
    {
        $htmlContent = $this->generatePdfHtml($data, $reportType, $dateRange);
        
        $filePath = 'reports/' . $filename . '.pdf';
        
        // Using DomPDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlContent);
        Storage::put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Generate HTML content for PDF.
     *
     * @param Collection $data
     * @param string $reportType
     * @param string $dateRange
     * @return string
     */
    protected function generatePdfHtml(Collection $data, string $reportType, string $dateRange): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($reportType) . '</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; color: #333; }
        .date-range { text-align: center; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #4a5568; color: white; padding: 10px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f7fafc; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <h1>' . htmlspecialchars($reportType) . '</h1>
    <div class="date-range">' . htmlspecialchars($dateRange) . '</div>
    <table>
        <thead>
            <tr>';

        if ($data->isNotEmpty()) {
            foreach (array_keys($data->first()) as $header) {
                $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
            }
        }

        $html .= '</tr>
        </thead>
        <tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody>
    </table>
    <div class="footer">
        Generated on ' . Carbon::now()->format('Y-m-d H:i:s') . '
    </div>
</body>
</html>';

        return $html;
    }
}
