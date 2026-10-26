<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BmiStoreRequest;
use App\Http\Resources\BmiRecordResource;
use App\Models\BmiRecord;
use App\Services\Audit\AuditLogService;
use App\Services\BMI\BmiRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BmiRecordController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return BmiRecordResource::collection(
            $request->user()->bmiRecords()->latest('measured_at')->paginate(20)
        );
    }

    public function store(
        BmiStoreRequest $request,
        BmiRecordService $service,
        AuditLogService $audit,
    ): JsonResponse {
        $record = $service->record($request->user(), $request->validated());
        $audit->record($request->user(), 'bmi.api_calculated', $record, request: $request);

        return (new BmiRecordResource($record))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $bmiRecord): BmiRecordResource
    {
        $record = $this->ownedRecord($request, $bmiRecord);
        $this->authorize('view', $record);

        return new BmiRecordResource($record);
    }

    public function destroy(Request $request, int $bmiRecord, AuditLogService $audit): JsonResponse
    {
        $record = $this->ownedRecord($request, $bmiRecord);
        $this->authorize('delete', $record);
        $audit->record($request->user(), 'bmi.api_deleted', $record, request: $request);
        $record->delete();

        return response()->json(status: 204);
    }

    private function ownedRecord(Request $request, int $id): BmiRecord
    {
        return $request->user()->bmiRecords()->findOrFail($id);
    }
}
