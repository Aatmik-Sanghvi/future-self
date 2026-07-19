<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Store or skip feedback for the authenticated user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'is_skipped' => 'nullable|boolean',
            'helpful_rating' => 'nullable|integer|min:1|max:5',
            'is_personal' => 'nullable|string|max:50',
            'understood_level' => 'nullable|string|max:50',
            'would_use_again' => 'nullable|string|max:50',
            'use_frequency' => 'nullable|string|max:50',
            'most_valuable' => 'nullable|string|max:2000',
            'confused_or_disappointed' => 'nullable|string|max:2000',
            'feature_to_come_back' => 'nullable|string|max:2000',
            'monthly_price_willingness' => 'nullable|string|max:50',
            'subscription_convincer' => 'nullable|string|max:2000',
            'nps_score' => 'nullable|integer|min:0|max:10',
            'one_thing_to_change' => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();

        $existing = Feedback::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        $data = [
            'is_skipped' => $validated['is_skipped'] ?? false,
            'helpful_rating' => $validated['helpful_rating'] ?? null,
            'is_personal' => $validated['is_personal'] ?? null,
            'understood_level' => $validated['understood_level'] ?? null,
            'would_use_again' => $validated['would_use_again'] ?? null,
            'use_frequency' => $validated['use_frequency'] ?? null,
            'most_valuable' => $validated['most_valuable'] ?? null,
            'confused_or_disappointed' => $validated['confused_or_disappointed'] ?? null,
            'feature_to_come_back' => $validated['feature_to_come_back'] ?? null,
            'monthly_price_willingness' => $validated['monthly_price_willingness'] ?? null,
            'subscription_convincer' => $validated['subscription_convincer'] ?? null,
            'nps_score' => $validated['nps_score'] ?? null,
            'one_thing_to_change' => $validated['one_thing_to_change'] ?? null,
        ];

        if ($existing) {
            $existing->update($data);
            $feedback = $existing;
        } else {
            $feedback = Feedback::create(array_merge(['user_id' => $user->id], $data));
        }

        $message = !empty($validated['is_skipped'])
            ? 'Feedback skipped successfully.'
            : 'Thank you for your feedback!';

        return ResponseHelper::send(200, $message, [
            'feedback' => $feedback,
            'hasSubmitted' => !$feedback->is_skipped,
            'hasSkipped' => (bool) $feedback->is_skipped,
        ]);
    }

    /**
     * Check feedback status for the authenticated user for today.
     */
    public function status(Request $request)
    {
        $user = auth()->user();

        $feedback = Feedback::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        return ResponseHelper::send(200, 'Feedback status fetched successfully.', [
            'hasSubmitted' => $feedback ? !$feedback->is_skipped : false,
            'hasSkipped' => $feedback ? (bool) $feedback->is_skipped : false,
            'feedback' => $feedback,
        ]);
    }
}
