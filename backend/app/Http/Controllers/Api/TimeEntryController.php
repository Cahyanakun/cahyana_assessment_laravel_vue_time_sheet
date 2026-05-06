<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeEntryRequest;
use App\Http\Resources\TimeEntryResource;
use App\Models\TimeEntry;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Http\Requests\IndexTimeEntryRequest;
use App\Http\Requests\UpdateTimeEntryRequest;

class TimeEntryController extends Controller
{
    use ApiResponse;

    /**
     * Get initial data for UI dropdowns.
     *
     * @return JsonResponse
     */
    public function getInitData(Request $request): JsonResponse
    {
        $companyId = $request->query('company_id');

        return $this->success([
            'companies' => Company::select('id', 'name')->get(),
            'employees' => $companyId ? Employee::whereHas('companies', function ($q) use ($companyId) {
                $q->where('companies.id', $companyId);
            })->select('employees.id', 'employees.name')->get() : [],
            'projects' => $companyId ? Project::where('company_id', $companyId)->select('id', 'name')->get() : [],
            'tasks' => $companyId ? Task::where('company_id', $companyId)->select('id', 'name')->get() : [],
        ], 'Initial data retrieved successfully');
    }

    /**
     * Display a listing of time entries.
     *
     * @param IndexTimeEntryRequest $request
     * @return JsonResponse
     */
    public function index(IndexTimeEntryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $perPage = $validated['per_page'] ?? 10;
        $sortBy = $validated['sort_by'] ?? null;
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $companyId = $validated['company_id'] ?? null;

        $search = $validated['search'] ?? null;

        $entries = TimeEntry::with(['company', 'employee', 'project', 'task'])
            ->when($companyId && $companyId !== 'all', function ($query) use ($companyId) {
                return $query->where('company_id', $companyId);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->whereHas('employee', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%");
                    })->orWhereHas('company', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($sortBy, function ($query) use ($sortBy, $sortOrder) {
                return $query->orderBy($sortBy, $sortOrder);
            }, function ($query) {
                return $query->latest();
            })
            ->paginate($perPage);

        return $this->success([
            'items' => TimeEntryResource::collection($entries),
            'meta' => [
                'current_page' => $entries->currentPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
                'last_page' => $entries->lastPage(),
            ],
        ], 'Time entries retrieved successfully');
    }

    /**
     * Store multiple time entries in a single request.
     *
     * @param StoreTimeEntryRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function store(StoreTimeEntryRequest $request): JsonResponse
    {
        $entries = DB::transaction(function () use ($request) {
            $createdEntries = [];

            foreach ($request->validated('entries') as $entryData) {
                $createdEntries[] = TimeEntry::create($entryData);
            }

            return $createdEntries;
        });

        return $this->success(
            TimeEntryResource::collection($entries),
            'Time entries stored successfully',
            201
        );
    }

    /**
     * Update a specific time entry.
     *
     * @param UpdateTimeEntryRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws \Throwable
     */
    public function update(UpdateTimeEntryRequest $request, int $id): JsonResponse
    {
        $timeEntry = TimeEntry::findOrFail($id);

        $updatedEntry = DB::transaction(function () use ($request, $timeEntry) {
            $timeEntry->update($request->validated());
            return $timeEntry->load(['company', 'employee', 'project', 'task']);
        });

        return $this->success(
            new TimeEntryResource($updatedEntry),
            'Time entry updated successfully'
        );
    }
}
