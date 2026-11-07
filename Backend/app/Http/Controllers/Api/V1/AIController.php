<?php

namespace App\Http\Controllers\Api\V1;

use App\Ai\Agents\FutureSelf;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Models\Conversation;

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
            $convTable = config('ai.conversations.tables.conversations', 'agent_conversations');
            $conversationExists = DB::table($convTable)
                ->where('id', $request->conversation_id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$conversationExists) {
                return ResponseHelper::send(404, 'Conversation not found.');
            }

            $agent->continue($request->conversation_id, $user);
        } else {
            $agent->forUser($user);
        }

        $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');
        $participantType = Conversation::participantType($user);
        $participantId = Conversation::participantKey($user);

        $userDailyMessageCount = DB::table($messagesTable)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        $dailyMessageLimit = config('constants.message_limit') + ($user->bonus_chats ?? 0);

        if ($userDailyMessageCount >= $dailyMessageLimit) {
            return ResponseHelper::send(429, 'You have reached your daily message limit.');
        }

        $response = $agent->prompt($request->message);
        Log::info($response);

        $userDailyMessageCountAfter = DB::table($messagesTable)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        return ResponseHelper::send(200, 'Message sent successfully.', [
            'reply' => $response->text,
            'conversation_id' => $response->conversationId,
            'dailyMessageLimit' => $dailyMessageLimit,
            'userMessageCount' => $userDailyMessageCountAfter,
            'canMessage' => $userDailyMessageCountAfter < $dailyMessageLimit,
        ]);
    }

    /**
     * List the user's past conversations.
     */
    public function conversations(Request $request)
    {
        $user = auth()->user();
        $table = config('ai.conversations.tables.conversations', 'agent_conversations');
        $participantType = Conversation::participantType($user);
        $participantId = Conversation::participantKey($user);

        $conversations = DB::table($table)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'created_at', 'updated_at']);

        $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');

        $userDailyMessageCount = DB::table($messagesTable)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        $dailyMessageLimit = config('constants.message_limit') + ($user->bonus_chats ?? 0);

        return ResponseHelper::send(200, 'Conversations fetched successfully.', [
            'conversations' => $conversations,
            'dailyMessageLimit' => $dailyMessageLimit,
            'userMessageCount' => $userDailyMessageCount,
            'canMessage' => $userDailyMessageCount < $dailyMessageLimit,
        ]);
    }

    /**
     * Get messages for a specific conversation.
     */
    public function messages(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $user = auth()->user();
        $table = config('ai.conversations.tables.messages', 'agent_conversation_messages');
        $participantType = Conversation::participantType($user);
        $participantId = Conversation::participantKey($user);

        $messages = DB::table($table)
            ->where('conversation_id', $request->conversation_id)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->orderBy('created_at')
            ->get(['id', 'role', 'content', 'created_at']);

        $userDailyMessageCount = DB::table($table)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->where('role', 'user')
            ->whereDate('created_at', today())
            ->count();

        $dailyMessageLimit = config('constants.message_limit') + ($user->bonus_chats ?? 0);
        $canMessage = $userDailyMessageCount < $dailyMessageLimit;

        return ResponseHelper::send(200, 'Messages fetched successfully.', [
            'messages' => $messages,
            'dailyMessageLimit' => $dailyMessageLimit,
            'userMessageCount' => $userDailyMessageCount,
            'canMessage' => $canMessage
        ]);
    }

    /**
     * Delete a conversation.
     */
    public function deleteConversation(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $user = auth()->user();
        $table = config('ai.conversations.tables.conversations', 'agent_conversations');
        $participantType = Conversation::participantType($user);
        $participantId = Conversation::participantKey($user);

        $deleted = DB::table($table)
            ->where('id', $request->conversation_id)
            ->where('participant_type', $participantType)
            ->where('participant_id', $participantId)
            ->delete();

        if ($deleted) {
            return ResponseHelper::send(200, 'Conversation deleted successfully.');
        } else {
            return ResponseHelper::send(404, 'Conversation not found.');
        }
    }
}
