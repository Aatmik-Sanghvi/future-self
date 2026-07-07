<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\FutureSelf;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIController extends Controller
{
    /**
     * Send a message to the FutureSelf agent.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|string',
        ]);

        $user = auth()->user();
        $agent = new FutureSelf($user);

        // Continue existing conversation or start a new one
        if ($request->conversation_id) {
            $agent->continue($request->conversation_id, $user);
        } else {
            $agent->forUser($user);
        }

        $response = $agent->prompt($request->message);

        return ResponseHelper::send(200, 'Message sent successfully.', [
            'reply' => $response->text,
            'conversation_id' => $response->conversationId,
        ]);
    }

    /**
     * List the user's past conversations.
     */
    public function conversations(Request $request)
    {
        $table = config('ai.conversations.tables.conversations', 'agent_conversations');

        $conversations = DB::table($table)
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'created_at', 'updated_at']);

        return ResponseHelper::send(200, 'Conversations fetched successfully.', $conversations);
    }

    /**
     * Get messages for a specific conversation.
     */
    public function messages(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $table = config('ai.conversations.tables.messages', 'agent_conversation_messages');

        $messages = DB::table($table)
            ->where('conversation_id', $request->conversation_id)
            ->where('user_id', auth()->id())
            ->orderBy('created_at')
            ->get(['id', 'role', 'content', 'created_at']);

        return ResponseHelper::send(200, 'Messages fetched successfully.', $messages);
    }
}
