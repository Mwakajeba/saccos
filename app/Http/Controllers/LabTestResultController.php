<?php

namespace App\Http\Controllers;

use App\Models\LabTestResult;
use App\Models\LabTest;
use App\Helpers\HashidsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LabTestResultController extends Controller
{
    /**
     * Download result file
     */
    public function download($encodedId)
    {
        $id = HashidsHelper::decode($encodedId)[0] ?? null;
        if (!$id) {
            abort(404);
        }

        $result = LabTestResult::findOrFail($id);
        
        if (!$result->result_file || !Storage::disk('public')->exists($result->result_file)) {
            abort(404, 'Result file not found.');
        }

        return Storage::disk('public')->download($result->result_file);
    }
}
