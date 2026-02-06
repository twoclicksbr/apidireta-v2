<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tenant\TenantRequest;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tenant::query();

        // Paginação
        $perPage = $request->get('per_page', 15);
        $tenants = $query->paginate($perPage);

        $response = array_merge([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => $request->url(),
        ], $tenants->toArray());

        return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantRequest $request): JsonResponse
    {
        $tenant = Tenant::create($request->validated());

        $response = [
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => $request->url(),
            'data' => $tenant,
        ];

        return response()->json($response, 201, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Tenant $tenant): JsonResponse
    {
        $response = [
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => $request->url(),
            'data' => $tenant,
        ];

        return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantRequest $request, Tenant $tenant): JsonResponse
    {
        $tenant->update($request->validated());

        $response = [
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => $request->url(),
            'data' => $tenant,
        ];

        return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy(Request $request, Tenant $tenant): JsonResponse
    {
        $tenant->delete();

        $response = [
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => $request->url(),
            'message' => 'Tenant excluído com sucesso.',
        ];

        return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Restore the specified soft deleted resource.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $tenant->restore();

        $response = [
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => $request->url(),
            'data' => $tenant,
        ];

        return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
