<?php

namespace App\Http\Controllers;

use App\Http\Requests\BmiStoreRequest;
use App\Models\BmiRecord;
use App\Services\Audit\AuditLogService;
use App\Services\BMI\BmiRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BmiController extends Controller
{
    public function index(Request $request): View
    {
        $records = $request->user()->bmiRecords()
            ->latest('measured_at')
            ->paginate(10);

        $chartRecords = $request->user()->bmiRecords()
            ->oldest('measured_at')
            ->limit(30)
            ->get(['bmi', 'measured_at']);

        $resultRecord = $request->filled('result')
            ? $request->user()->bmiRecords()->find($request->integer('result'))
            : $records->first();

        return view('bmi.index', [
            'profile' => $request->user()->healthProfile()->first(),
            'records' => $records,
            'chartRecords' => $chartRecords,
            'latestRecord' => $resultRecord,
        ]);
    }

    public function store(
        BmiStoreRequest $request,
        BmiRecordService $service,
        AuditLogService $audit,
    ): RedirectResponse {
        $record = $service->record($request->user(), $request->validated());
        $audit->record($request->user(), 'bmi.calculated', $record, request: $request);

        return redirect()->route('bmi.index', ['result' => $record->id])
            ->with('status', 'bmi-calculated');
    }

    public function destroy(Request $request, int $bmiRecord, AuditLogService $audit): RedirectResponse
    {
        $record = $this->ownedRecord($request, $bmiRecord);
        $this->authorize('delete', $record);
        $audit->record($request->user(), 'bmi.deleted', $record, request: $request);
        $record->delete();

        return redirect()->route('bmi.index')->with('status', 'bmi-deleted');
    }

    private function ownedRecord(Request $request, int $id): BmiRecord
    {
        return $request->user()->bmiRecords()->findOrFail($id);
    }
}
