<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\SecurityControl;
use App\Services\SecurityScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityWorkspaceController extends Controller
{
    public function show(
        Client $client,
        SecurityScoreService $securityScoreService
    ): View {
        $client->load([
            'securityControls' => fn ($query) => $query
                ->orderBy('category')
                ->orderBy('control'),
        ]);

        $security = $securityScoreService->calculate($client);

        return view('security.workspace', [
            'client' => $client,
            'security' => $security,
            'controlsByCategory' => $client
                ->securityControls
                ->groupBy('category'),
        ]);
    }

    public function update(
        Request $request,
        Client $client,
        SecurityControl $securityControl,
        SecurityScoreService $securityScoreService
    ): JsonResponse|RedirectResponse {
        abort_unless(
            $securityControl->client_id === $client->id,
            404
        );

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'evidence' => ['nullable', 'string', 'max:255'],
        ]);

        $securityControl->update([
            'enabled' => (bool) $validated['enabled'],
            'notes' => $validated['notes'] ?? $securityControl->notes,
            'evidence' => $validated['evidence']
                ?? $securityControl->evidence,
            'last_reviewed_at' => now(),
        ]);

        $client->unsetRelation('securityControls');
        $security = $securityScoreService->calculate($client);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "{$securityControl->control} updated.",
                'control' => [
                    'id' => $securityControl->id,
                    'enabled' => $securityControl->enabled,
                    'status' => $securityControl->enabled
                        ? 'Complete'
                        : 'Outstanding',
                ],
                'security' => [
                    'score' => $security['score'],
                    'rating' => $security['rating'],
                    'earned' => $security['earned'],
                    'maximum' => $security['maximum'],
                    'completed' => $security['completed'],
                    'outstanding' => $security['outstanding'],
                    'recommendations' => $security['recommendations'],
                ],
            ]);
        }

        return back()->with(
            'success',
            "{$securityControl->control} updated successfully."
        );
    }
}