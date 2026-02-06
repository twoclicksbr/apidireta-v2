<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Controller
{
    /**
     * Resolve o Model dinamicamente baseado no nome do módulo
     */
    protected function resolveModel(string $module): string
    {
        // Converte "tenants" para "Tenant", "users" para "User", etc.
        $modelName = Str::studly(Str::singular($module));
        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            abort(404, "Model {$modelName} não encontrado");
        }

        return $modelClass;
    }

    /**
     * Resolve a Request dinamicamente baseada no nome do módulo
     */
    protected function resolveAndValidateRequest(Request $request, string $module): array
    {
        $modelName = Str::studly(Str::singular($module));
        $requestClass = "App\\Http\\Requests\\{$modelName}\\{$modelName}Request";

        if (class_exists($requestClass)) {
            // Cria uma instância da Request específica e valida
            $formRequest = app($requestClass);
            $formRequest->setContainer(app());
            $formRequest->setRedirector(app('redirect'));

            // Resolve as regras de validação
            $validator = validator($request->all(), $formRequest->rules());

            if ($validator->fails()) {
                abort(response()->json([
                    'message' => 'Erro de validação',
                    'errors' => $validator->errors(),
                ], 422, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            return $validator->validated();
        }

        // Se não houver Request específica, retorna todos os dados
        return $request->all();
    }

    /**
     * Monta a resposta JSON padrão com campos comuns
     */
    protected function buildResponse(array $data, int $statusCode = 200): JsonResponse
    {
        $response = array_merge([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => request()->url(),
        ], $data);

        return response()->json($response, $statusCode, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Resposta de sucesso genérica
     */
    protected function successResponse($data, int $statusCode = 200): JsonResponse
    {
        if (is_array($data) && isset($data['data'])) {
            return $this->buildResponse($data, $statusCode);
        }

        return $this->buildResponse(['data' => $data], $statusCode);
    }

    /**
     * Resposta de criação (201)
     */
    protected function createdResponse($data): JsonResponse
    {
        return $this->successResponse($data, 201);
    }

    /**
     * Resposta de mensagem
     */
    protected function messageResponse(string $message, int $statusCode = 200): JsonResponse
    {
        return $this->buildResponse(['message' => $message], $statusCode);
    }

    /**
     * Listagem com paginação (index)
     */
    public function index(Request $request, string $module): JsonResponse
    {
        $modelClass = $this->resolveModel($module);
        $query = $modelClass::query();

        // Paginação
        $perPage = $request->get('per_page', 15);
        $items = $query->paginate($perPage);

        $response = array_merge([
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => $request->url(),
        ], $items->toArray());

        return response()->json($response, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Criar novo recurso (store)
     */
    public function store(Request $request, string $module): JsonResponse
    {
        $modelClass = $this->resolveModel($module);
        $validatedData = $this->resolveAndValidateRequest($request, $module);
        $item = $modelClass::create($validatedData);

        return $this->createdResponse($item);
    }

    /**
     * Exibir recurso específico (show)
     */
    public function show(Request $request, string $module, int $id): JsonResponse
    {
        $modelClass = $this->resolveModel($module);
        $item = $modelClass::findOrFail($id);

        return $this->successResponse($item);
    }

    /**
     * Atualizar recurso (update)
     */
    public function update(Request $request, string $module, int $id): JsonResponse
    {
        $modelClass = $this->resolveModel($module);
        $item = $modelClass::findOrFail($id);
        $validatedData = $this->resolveAndValidateRequest($request, $module);
        $item->update($validatedData);

        return $this->successResponse($item);
    }

    /**
     * Deletar recurso - soft delete (destroy)
     */
    public function destroy(Request $request, string $module, int $id): JsonResponse
    {
        $modelClass = $this->resolveModel($module);
        $item = $modelClass::findOrFail($id);
        $item->delete();

        return $this->messageResponse('Recurso excluído com sucesso.');
    }

    /**
     * Restaurar recurso soft deleted (restore)
     */
    public function restore(Request $request, string $module, int $id): JsonResponse
    {
        $modelClass = $this->resolveModel($module);
        $item = $modelClass::withTrashed()->findOrFail($id);
        $item->restore();

        return $this->successResponse($item);
    }
}
