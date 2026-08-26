<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * Send a JSON response
     *
     * @param  int  $status
     * @param  string  $message
     * @param  mixed  $responseData
     * @param  array|null  $extraData
     * @return \Illuminate\Http\JsonResponse
     */
    public static function send($status, $message = '', $responseData = null, $extraData = null)
    {
        $data = [
            'status' => $status,
            'message' => $message,
            'data' => ! empty($responseData) ? $responseData : new \stdClass,
        ];

        // Merge extra data into response if provided
        if (! empty($extraData) && is_array($extraData)) {
            $data = array_merge($data, $extraData);
        }

        // Determine the appropriate header status
        $validStatus = [200, 201, 204, 400, 401, 403, 404, 409, 412, 422, 429, 500];
        $headerStatus = in_array($status, $validStatus) ? $status : (is_int($status) && $status >= 100 && $status < 600 ? $status : 412);

        // Return the JSON response through the middleware pipeline
        return response()->json($data, $headerStatus);
    }
}
