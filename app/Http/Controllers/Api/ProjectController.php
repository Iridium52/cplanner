<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        $abilities = $token->abilities;

        // Determine accessible projects from token abilities
        $projectIds = collect($abilities)
            ->filter(fn($a) => preg_match('/^project:(\d+):/', $a, $m) || $a === '*:tasks:read')
            ->map(fn($a) => preg_match('/^project:(\d+):/', $a, $m) ? (int) $m[1] : null)
            ->filter()
            ->unique();

        $projects = Project::with('type')
            ->when($projectIds->isNotEmpty(), fn($q) => $q->whereIn('id', $projectIds))
            ->get(['id', 'name', 'key', 'description', 'color', 'status', 'type_id']);

        return response()->json(['data' => $projects]);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorizeTokenAbility($request, $project, 'tasks:read');

        return response()->json([
            'data' => $project->load('type', 'statuses', 'categories'),
        ]);
    }

    private function authorizeTokenAbility(Request $request, Project $project, string $action): void
    {
        $token = $request->user()->currentAccessToken();
        $abilities = $token->abilities;

        $allowed = collect($abilities)->contains(fn($a) =>
            $a === '*' ||
            $a === "*:{$action}" ||
            $a === "project:{$project->id}:{$action}" ||
            $a === "project:{$project->id}:*"
        );

        abort_unless($allowed, 403, "Token does not have ability: {$action} on project {$project->id}");
    }
}
