<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Jobs\GenerateReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Generate a new report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:daily,weekly,monthly',
            'format' => 'required|in:csv,pdf',
            'date' => 'required|date',
            'filters' => 'nullable|array',
            'filters.student_id' => 'nullable|exists:users,id',
            'filters.student_name' => 'nullable|string|max:255',
            'filters.course' => 'nullable|string|max:100',
            'filters.location_id' => 'nullable|exists:locations,id',
            'filters.location_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create report record
        $report = Report::create([
            'generated_by' => $request->user()->id,
            'report_type' => $request->report_type,
            'format' => $request->format,
            'filters' => $request->filters ?? [],
            'status' => 'pending',
        ]);

        // Dispatch job to generate report
        GenerateReport::dispatch($report->id, $request->date);

        return response()->json([
            'success' => true,
            'message' => 'Report generation started',
            'report_id' => $report->id,
            'status' => $report->status,
        ], 202);
    }

    /**
     * Download a completed report.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function download($id)
    {
        $report = Report::findOrFail($id);

        // Check if report is completed
        if ($report->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Report is not ready yet',
                'status' => $report->status,
            ], 400);
        }

        // Check if file exists
        if (!$report->file_path || !Storage::exists($report->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Report file not found',
            ], 404);
        }

        // Return file download
        return Storage::download($report->file_path, basename($report->file_path));
    }

    /**
     * Get report status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($id)
    {
        $report = Report::findOrFail($id);

        return response()->json([
            'success' => true,
            'report_id' => $report->id,
            'status' => $report->status,
            'report_type' => $report->report_type,
            'format' => $report->format,
            'completed_at' => $report->completed_at,
            'can_download' => $report->status === 'completed' && $report->file_path,
        ]);
    }
}
