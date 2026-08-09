<?php

namespace App\Http\Controllers;

use App\Models\BusinessPulseAssessment;
use App\Models\Client;
use App\Services\BusinessPulseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessPulseController extends Controller
{
    /**
     * Show the Business Pulse™ assessment workspace.
     */
    public function show(
        Client $client,
        BusinessPulseService $businessPulseService
    ): View {
        $assessment = $client->businessPulseAssessment;

        if (! $assessment) {
            $assessment = $client
                ->businessPulseAssessment()
                ->create([
                    'operations_score' => 0,
                    'continuity_score' => 0,
                    'documentation_score' => 0,
                    'compliance_score' => 0,
                    'technology_score' => 0,
                    'readiness_score' => 0,
                    'overall_score' => 0,
                    'status' => 'draft',
                ]);
        }

        $pulse = $businessPulseService->calculate($client);

        return view('business-pulse.workspace', [
            'client' => $client,
            'assessment' => $assessment,
            'pulse' => $pulse,
        ]);
    }

    /**
     * Update the Business Pulse™ assessment.
     */
    public function update(
        Request $request,
        Client $client,
        BusinessPulseService $businessPulseService
    ): JsonResponse|RedirectResponse {
        $validated = $request->validate([
            'operations_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'continuity_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'documentation_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'compliance_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'technology_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'readiness_score' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'nullable',
                'in:draft,completed',
            ],
        ]);

        $assessment = $client->businessPulseAssessment;

        if (! $assessment) {
            $assessment = new BusinessPulseAssessment();
            $assessment->client_id = $client->id;
        }

        $assessment->fill([
            'operations_score' =>
                $validated['operations_score'],

            'continuity_score' =>
                $validated['continuity_score'],

            'documentation_score' =>
                $validated['documentation_score'],

            'compliance_score' =>
                $validated['compliance_score'],

            'technology_score' =>
                $validated['technology_score'],

            'readiness_score' =>
                $validated['readiness_score'],

            'notes' =>
                $validated['notes'] ?? null,

            'status' =>
                $validated['status'] ?? 'completed',

            'assessed_at' => now(),
        ]);

        $assessment->overall_score =
            $assessment->calculateOverallScore();

        $assessment->save();

        /*
        |--------------------------------------------------------------------------
        | Refresh Client Relationship
        |--------------------------------------------------------------------------
        |
        | BusinessPulseService reads the assessment relationship.
        | Clear the cached relationship so the service picks up the
        | newly saved values immediately.
        |
        */

        $client->unsetRelation('businessPulseAssessment');

        $pulse = $businessPulseService->calculate($client);

        /*
        |--------------------------------------------------------------------------
        | AJAX Response
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            return response()->json([
                'message' =>
                    'Business Pulse™ assessment updated successfully.',

                'assessment' => [
                    'operations_score' =>
                        $assessment->operations_score,

                    'continuity_score' =>
                        $assessment->continuity_score,

                    'documentation_score' =>
                        $assessment->documentation_score,

                    'compliance_score' =>
                        $assessment->compliance_score,

                    'technology_score' =>
                        $assessment->technology_score,

                    'readiness_score' =>
                        $assessment->readiness_score,

                    'overall_score' =>
                        $assessment->overall_score,

                    'status' =>
                        $assessment->status,

                    'assessed_at' =>
                        optional(
                            $assessment->assessed_at
                        )->toDateTimeString(),
                ],

                'pulse' => [
                    'score' =>
                        $pulse['score'],

                    'rating' =>
                        $pulse['rating'],

                    'priority_count' =>
                        $pulse['priority_count'],

                    'summary' =>
                        $pulse['summary'],

                    'components' =>
                        $pulse['components'],

                    'business_domains' =>
                        $pulse['business_domains'],

                    'priorities' =>
                        $pulse['priorities'],
                ],
            ]);
        }

        return redirect()
            ->route(
                'business-pulse.workspace',
                $client
            )
            ->with(
                'success',
                'Business Pulse™ assessment updated successfully.'
            );
    }
}