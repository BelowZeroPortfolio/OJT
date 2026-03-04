<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RfidRegistrationController extends Controller
{
    /**
     * Display the RFID registration page.
     */
    public function index()
    {
        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $totalStudents = $students->count();
        $registeredCount = $students->where('rfid_number', '!=', null)->count();

        return view('admin.rfid-registration', compact('students', 'totalStudents', 'registeredCount'));
    }

    /**
     * Register an RFID card to a student.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:users,id',
                'rfid_number' => 'required|string|max:255'
            ]);

            $student = User::findOrFail($request->student_id);
            $rfidNumber = trim($request->rfid_number);

            // Check if RFID is already registered to another user
            $existingUser = User::where('rfid_number', $rfidNumber)
                ->where('id', '!=', $student->id)
                ->first();

            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => "RFID card is already registered to {$existingUser->name}"
                ], 400);
            }

            // Check if student already has an RFID card
            if ($student->rfid_number && $student->rfid_number !== $rfidNumber) {
                return response()->json([
                    'success' => false,
                    'message' => "Student already has RFID card: {$student->rfid_number}. Please unregister first."
                ], 400);
            }

            DB::beginTransaction();

            // Register the RFID card
            $student->update([
                'rfid_number' => $rfidNumber
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "RFID card successfully registered to {$student->name}",
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'rfid_number' => $student->rfid_number,
                    'course' => $student->course
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('RFID Registration Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'System error. Please try again.'
            ], 500);
        }
    }

    /**
     * Unregister an RFID card from a student.
     */
    public function unregister(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:users,id'
            ]);

            $student = User::findOrFail($request->student_id);

            if (!$student->rfid_number) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student does not have an RFID card registered.'
                ], 400);
            }

            DB::beginTransaction();

            $oldRfid = $student->rfid_number;
            $student->update([
                'rfid_number' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "RFID card {$oldRfid} unregistered from {$student->name}",
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'rfid_number' => null,
                    'course' => $student->course
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('RFID Unregistration Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'System error. Please try again.'
            ], 500);
        }
    }
}