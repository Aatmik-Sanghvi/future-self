<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ai\Action\AskAIAction;

class AIController extends Controller
{
    public function test(Request $request, AskAIAction $action)
    {
        $response = $action->execute($request->message);

        return response()->json([
            'response' => $response
        ]);
    }
}
