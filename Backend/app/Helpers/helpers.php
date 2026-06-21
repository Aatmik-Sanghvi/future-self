<?php

use App\Helpers\LogHelper;
use App\Mail\User_Password_Send_OTP_Mail;
use App\Models\AdminNotification;
use App\Models\Device;
use App\Models\Notification;
use App\Models\OrderTransaction;
use App\Models\PushLog;
use App\Models\User;
use App\Models\UserEmailLog;
use Carbon\Carbon;
use Google\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Stripe\Refund;

if (! function_exists('get_constants')) {
    function get_constants($name)
    {
        return config('constants.'.$name);
    }
}

if (! function_exists('generateUsername')) {
    function generateUsername($name)
    {
        // Convert the name to a URL-friendly version
        $baseUsername = Str::slug($name);

        // Step 1: Check if username exists
        $username = $baseUsername;
        $counter = 1;

        // Step 2: Append a number to make it unique if needed
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        return $username;
    }
}

if (! function_exists('get_header_auth_token')) {
    function get_header_auth_token()
    {
        $full_token = request()->header('VAuthorization');

        return (substr($full_token, 0, 7) === 'Bearer ') ? substr($full_token, 7) : null;
    }
}

if (! function_exists('checkFileExist')) {
    function checkFileExist($path = '', $no_image = 'no_image')
    {
        if (! empty($path) && file_exists(public_path($path))) {
            $url = url($path);  // This will return the full URL
        } else {
            if ($no_image == 'no_user_image') {
                $url = url('default/no_user_image.png');
            } else {
                $url = url('default/'.$no_image.'.png');
            }
        }

        return $url;
    }
}

if (! function_exists('upload_file')) {
    function upload_file($file_name = '', $path = '')
    {
        $file = '';
        $request = \request();

        // Check if file exists and path is provided
        if ($request->hasFile($file_name) && $path) {
            $path = config('constants.upload_paths.'.$path, 'default/path'); // Fallback in case config is not set
            $file = $request->file($file_name)->store($path, config('constants.upload_type', 'public'));
        } else {
            echo 'Provide Valid Const from web controller';
            exit();
        }

        return $file;
    }
}
if (! function_exists('un_link_file')) {
    function un_link_file($image_name = '')
    {
        $result = true; // Default to success
        if (! empty($image_name)) {
            try {
                // Get base URL and default images
                $default_url = URL::to('/');
                $default_images = config('constants.default');
                $file_name = str_replace($default_url.'/', '', $image_name);
                $default_image_list = is_array($default_images) ? str_replace($default_url, '', array_values($default_images)) : [];
                if (! in_array($file_name, $default_image_list)) {
                    Storage::disk(get_constants('upload_type'))->delete($file_name);
                }
            } catch (Exception $exception) {
                // Log the exception for debugging
                Log::error('File Deletion Error: '.$exception->getMessage());

                // Set result to false on failure
                $result = false;
            }
        } else {
            // If image name is empty, log and return a failure message
            Log::warning('Empty file name provided to un_link_file function');
            $result = false;
        }

        return $result;
    }
}

if (! function_exists('get_dashboard_route_name')) {
    function get_dashboard_route_name()
    {
        $name = 'front.dashboard';
        $user_data = Auth::user();
        if ($user_data) {
            if (in_array($user_data->type, ['admin'])) {
                $name = 'admin.dashboard';
            }
            if(in_array($user_data->type, ['user'])){
                $name = 'web.home';
            }
        }

        return $name;
    }
}

if (! function_exists('genUniqueStr')) {
    function genUniqueStr($table, $field = null, $length = 10, $prefix = null, $isAlphaNum = false)
    {
        // Define character sets
        $numeric = range(0, 9);
        $alpha = array_merge(range('a', 'z'), range('A', 'Z'));

        // Create character pool
        $characters = $numeric;
        if ($isAlphaNum) {
            $characters = array_merge($characters, $alpha);
        }

        $maxLen = max($length - strlen($prefix), 0);
        $attempts = 0;
        $token = '';

        do {
            $token = $prefix;

            // Generate token
            for ($i = 0; $i < $maxLen; $i++) {
                $token .= $characters[array_rand($characters)];
            }

            $attempts++;
            // Add a limit to prevent infinite loop (e.g., 10 attempts)
            if ($attempts > 10) {
                throw new Exception('Unable to generate a unique string after multiple attempts.');
            }

        } while (isTokenExist($token, $table, $field));

        return $token;
    }
}

if (! function_exists('isTokenExist')) {
    function isTokenExist($token, $table, $field)
    {
        return ! empty($token) && DB::table($table)->where($field, $token)->exists();
    }
}

if (! function_exists('flash_session')) {
    function flash_session($type = '', $value = '')
    {
        session()->flash('message', ['type' => $type, 'text' => $value]);
    }
}

if (! function_exists('get_error_html')) {
    function get_error_html($error)
    {
        $content = '';
        if ($error->any() !== null && $error->any()) {
            foreach ($error->all() as $value) {
                flash_session('error', $value);
            }
        }

        return $content;
    }
}

if (! function_exists('is_active_module')) {
    function is_active_module($routes)
    {
        return in_array(Route::currentRouteName(), $routes) ? 'mm-active' : '';
    }
}

if (! function_exists('echo_extra_for_site_setting')) {
    function echo_extra_for_site_setting($extra = '')
    {
        $attributes = '';

        // Decode the extra attributes if JSON is valid
        $extraData = json_decode($extra, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($extraData)) {
            foreach ($extraData as $key => $value) {
                // Escape attribute name and value to prevent XSS
                $attributes .= htmlspecialchars($key).'="'.htmlspecialchars($value).'" ';
            }
        }

        return $attributes;
    }
}

if (! function_exists('get_fancy_box_html')) {
    function get_fancy_box_html($imagePath, $class = 'avatar-sm')
    {
        if (! $imagePath) {
            return '';
        }

        return '<a data-fancybox="" href="'.url($imagePath).'">
                <img class="img-thumbnail '.$class.'" src="'.url($imagePath).'" alt="Profile Image">
            </a>';
    }
}

if (! function_exists('get_generate_switch')) {
    function get_generate_switch($status, $id, $route)
    {
        $checked = $status == 'active' ? 'checked' : ($status == 1 ? 'checked' : ($status == 'marketplace' ? 'checked' : ''));

        return '<div class="form-check form-switch form-switch-md mb-3" dir="ltr">
                    <input class="form-check-input status-switch" type="checkbox" id="SwitchCheckSizemd_'.$id.'" data-id="'.$id.'" data-url="'.$route.'" '.$checked.'>
                </div>';
    }
}

if (! function_exists('generate_actions_buttons')) {
    function generate_actions_buttons($params = [], $extra = [], $target = false)
    {
        $operation = '';
        $targetAttr = $target ? 'target="_blank"' : '';
        $route = $params['route'] ?? null;

        // Standard action buttons (view, edit, delete)
        $actions = [
            'edit' => [
                'icon' => 'mdi mdi-pencil',
                'class' => 'btn btn-sm btn-outline-success waves-effect waves-light btnEdit',
                'title' => 'Edit',
                'type'  => 'link',
            ],
            'view' => [
                'icon' => 'mdi mdi-eye',
                'class' => 'btn btn-sm btn-outline-primary waves-effect waves-light btnView',
                'title' => 'View',
                'type'  => 'link',
            ],
            'delete' => [
                'icon' => 'mdi mdi-trash-can',
                'class' => 'btn btn-sm btn-outline-danger waves-effect waves-light btnDelete',
                'title' => 'Delete',
                'type'  => 'link',
                // 'data-confirm' => 'Are you sure you want to delete this item?',
            ],
           
        ];
        if(str_contains($route,'admin.orders')){
            $actions['change_status'] = [
                'icon' => 'mdi mdi-sync',
                'class' => 'btn btn-sm btn-outline-info waves-effect waves-light btnChangeStatus',
                'title' => 'Change Status',
                'type'  => 'modal',
             ];
        }

        if(isset($route) && $route == 'admin.orders.being_inspect'){
            $actions['damage'] = [
                'icon' => 'mdi mdi-alert',
                'class' => 'btn btn-sm btn-outline-warning waves-effect waves-light btnDamage',
                'title' => 'Damage',
                'type'   => 'modal',
            ];
            $actions['complete'] = [
                'icon' => 'mdi mdi-check-decagram',
                'class' => 'btn btn-sm btn-outline-success waves-effect waves-light btnComplete',
                'title' => 'Complete',
                'type' => 'link',
                // 'data-confirm' => 'Are you sure you want to complete this item?',
            ];
        }

        if(isset($route) && $route == 'admin.orders.confirmed' || $route == 'admin.orders.delivered'){
            $actions['download_label'] = [
                'icon' => 'mdi mdi-file-document-outline',
                'class' => 'btn btn-sm btn-outline-secondary waves-effect waves-light btnDownloadLabel',
                'title' => 'Download label',
                'type'   => 'link',
                'download' => true,
                // 'data-confirm' => 'Are you sure you want to dispatch this item?',
            ];
        }

        $operation = '';

        foreach ($actions as $action => $details) {
            // Skip if no URL/data-id is provided for this action
            if (empty($params['url'][$action] ?? '') && $details['type'] === 'link') {
                continue;
            }

            $title      = $details['title'];
            $icon       = $details['icon'];
            $class      = $details['class'];
            $extraAttrs = '';

            if ($details['type'] === 'modal') {
                // ----- Modal Trigger Button -----
                if ($action === 'change_status') {
                    // For change_status, use JavaScript handler instead of Bootstrap modal
                    $extraAttrs = '
                        href="javascript:void(0);"
                        data-id="' . ($params['id'] ?? '') . '"
                        data-route="' . ($route ?? '') . '"
                        data-url="' . htmlspecialchars($params['url'][$action] ?? '#') . '"
                    ';
                } else {
                    // For other modals (like damage), use Bootstrap modal
                    $extraAttrs = '
                        data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop"
                        data-id="' . ($params['id'] ?? '') . '"
                        data-route="' . ($route ?? '') . '"
                    ';
                }
            } else {
                // ----- Normal Link Buttons -----
                $href = $params['url'][$action] ?? '#';
                $extraAttrs .= ' href="' . htmlspecialchars($href) . '"';
    
                // dd($details);
                // Add target="_blank" ONLY for download links
                if (!empty($details['download'])) {
                    $extraAttrs .= 'target="_blank"';
                } else {
                    $extraAttrs .= $targetAttr;
                }

                if (!empty($details['data-confirm'])) {
                    $extraAttrs .= ' onclick="return confirm(\'' . addslashes($details['data-confirm']) . '\')"';
                }
            }

            $operation .= sprintf(
                '<a title="%s" %s class="font-size-14 me-2 %s" data-action="%s">
                    <i class="%s"></i>
                </a>',
                htmlspecialchars($title),
                $extraAttrs,
                htmlspecialchars($class),
                $action,          // useful for JS to know which button was clicked
                htmlspecialchars($icon)
            );
        }

        // Log::info($operation);
        return $operation;
    }
}

if (! function_exists('get_badge_html')) {
    function get_badge_html($status)
    {
        if ($status == 'active' || $status == 1) {
            $badge = '<span class="badge bg-success">'.($status == 'active' ? ucfirst($status) : 'Yes').'</span>';
        } elseif ($status == 'inactive' || $status == 0) {
            $badge = '<span class="badge bg-danger">'.($status == 'inactive' ? ucfirst($status) : 'No').'</span>';
        } else {
            $badge = '<span class="badge bg-primary">'.ucfirst($status).'</span>';
        }

        return $badge;
    }
}

if (! function_exists('register_user_email')) {
    function register_user_email($email,$subjectTomail='Signup Process')
    {
        // Check if environment is local
        if (app()->environment('local')) {
            // Static OTP for local environment (development)
            $otp = 1234;
        } else {
            // Random OTP for production/live environment
            $otp = rand(1111, 9999);
        }

        // Update or create user email log entry
        $user = UserEmailLog::updateOrCreate(
            ['email' => $email],
            ['email' => $email]
        );

        if ($user) {
            // Update OTP and timestamp
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => now()->addMinutes(10),
            ]);

            $name = !is_null($user->name) ? $user->name : 'User';
            // Send OTP email
            Mail::to($user->email)->send(new User_Password_Send_OTP_Mail($name, $otp, $subjectTomail));

            return true;
        }

        return false;
    }
}

if(! function_exists('already_registered_email')){
    function already_registered_email($email){
        return User::where('email',$email)->exists();
    }
}

if (! function_exists('format_price')) {
    function format_price($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}

if (! function_exists('upload_base_64_img')) {
    function upload_base_64_img($base64 = '', $path = '')
    {
        $file = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
            $data = substr($base64, strpos($base64, ',') + 1);
            $up_file = rtrim($path, '/').'/'.md5(uniqid()).'.png';
            $img = Storage::disk('local')->put($up_file, base64_decode($data));
            if ($img) {
                $file = $up_file;
            }
        }

        return $file;
    }
}

if (! function_exists('checkoutURL')) {
    function checkoutURL($order_id,$payment_method_id){
        $payload = [
            'order_id' => $order_id,
            'payment_method_id' => $payment_method_id,
            'token' => Str::uuid()->toString(),
        ];

        Log::info($payload);

        $encodedPayload = base64_encode(json_encode($payload));

        $checkoutUrl = URL::temporarySignedRoute(
            'payment-page',
            now()->addMinutes(5),
            ['data' => $encodedPayload]
        );

        // store this token in cache as "unused"
        Cache::put('checkout_token_' . $payload['token'], true, now()->addMinutes(5));

        return $checkoutUrl;
    }
}

if (! function_exists('getAccessToken')) {
    function getAccessToken($serviceAccountPath)
    {
        $client = new Client();
        $client->setAuthConfig($serviceAccountPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->useApplicationDefaultCredentials();
        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }
}


if (!function_exists('checkNotificationStatus')) {
    function checkNotificationStatus($id)
    {
        $sender_user = User::where('id', $id)->first();
        if ($sender_user->notification == 'yes') {
            return true;
        } else {
            return false;
        }
    }
}

if (! function_exists('send_push')) {
    function send_push($user_id = 0, $data = [], $notification_entry = false)
    {
        if(checkNotificationStatus($user_id)){
            LogHelper::write('shippo','info','-------------------------------------------------------------Coming into Push Sending Main Function-------------------------------------------------------------');
            $main_name = defined('site_name') ? site_name : env('APP_NAME');
            $push_data = [
                'user_id' => (string)$user_id,
                'from_user_id' => isset($data['from_user_id']) ? (string)$data['from_user_id'] : null,
                'sound' => 'default',
                'push_type' => isset($data['push_type']) ? (string)$data['push_type'] : '0',
                'push_title' => $data['push_title'] ?? $main_name,
                'push_message' => $data['push_message'] ?? "",
                'extra' => isset($data['extra']) ? json_encode($data['extra'], true) : null
            ];
            LogHelper::write('shippo','info','Push Data Prepared', $push_data);
            $fcm_config_file = config('constants.firebase_credentials');
            if ($fcm_config_file && file_exists($fcm_config_file)){
                $credentials = json_decode(file_get_contents($fcm_config_file), true);
                if ($push_data['user_id'] !== $push_data['from_user_id']) {
                    $get_user_tokens = Device::get_user_tokens($user_id);
                    if (count($get_user_tokens)) {
                        foreach ($get_user_tokens as $value) {
                            $push_status = "Sent";
                            $value->update(['badge' => $value->badge + 1]);
                            try {
                                $device_token = $value['device_token'];
                                if ($device_token) {
                                    $url = 'https://fcm.googleapis.com/v1/projects/'.$credentials['project_id'].'/messages:send';
                                    $token = getAccessToken($credentials);
                                    $headers = [
                                        "Authorization: Bearer $token",
                                        'Content-Type: application/json'
                                    ];
                                    $payload = [
                                        'token' => $device_token,
                                        'notification' => [
                                            'title' => $push_data['push_title'],
                                            'body' => $push_data['push_message'],
                                        ],
                                        'data' => $push_data,
                                    ];
                                    $ch = curl_init();
                                    curl_setopt_array($ch, array(
                                        CURLOPT_URL => $url,
                                        CURLOPT_POST => true,
                                        CURLOPT_HTTPHEADER => $headers,
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_SSL_VERIFYPEER => false,
                                        CURLOPT_POSTFIELDS => json_encode(['message' => $payload]),
                                    ));
                                    $result = curl_exec($ch);
                                    if (curl_errno($ch)) {
                                        $push_status = 'Curl Error:' . curl_error($ch);
                                    }
                                    curl_close($ch);
                                    if (config('constants.push_log')) {
                                        PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $push_status, json_encode($push_data), $result);
                                    }
                                } else {
                                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Token Is empty");
                                }
                            } catch (Exception $e) {
                                if (config('constants.push_log')) {
                                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $e->getMessage());
                                }
                            }
                        }
                    } else {
                        if (config('constants.push_log')) {
                            PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Users Is not A Login With app");
                        }
                    }
                    if ($notification_entry) {
                        // AdminNotification::create([
                        //     'push_type' => $push_data['push_type'],
                        //     'title' => $push_data['push_title'],
                        //     'message' => $push_data['push_message'],
                        // ]);
                        Notification::create([
                            'push_type' => $push_data['push_type'],
                            'user_id' => $push_data['user_id'],
                            'from_user_id' => $push_data['from_user_id'],
                            'title' => $push_data['push_title'],
                            'message' => $push_data['push_message'],
                            'extra' => (isset($data['extra']) && !empty($data['extra'])) ? json_encode($data['extra']) : json_encode([]),
                        ]);
                    }
                } else {
                    if (config('constants.push_log')) {
                        PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "User Cant Sent Push To Own Profile.");
                    }
                }
            }else{
                if (config('constants.push_log')) {
                    PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "FCM config file not found");
                }
            }
        }
    }

    function getAdminId(){
        $id = User::where('type','admin')->first();
        return $id->id;
    }
}

function route_is($pattern) {
    return request()->is($pattern);
}

function refundOrder($order, $paymentIntentId, $amount)
{
    if (!$paymentIntentId) {
        LogHelper::write('transaction', 'warning', 'No payment intent provided for refund');
        return;
    }

    try {
        // Create Stripe refund
        $payment = Refund::create([
            'payment_intent' => $paymentIntentId,
            // 'amount' => $amount * 100, // only if partial refund
        ]);

        $paymentMethodId = $order->orderTransaction->payment_method_id ?? null;

        // Create internal transaction record
        app(OrderTransaction::class)->createTransaction(
            $order->id,
            $order->user_id,
            $paymentMethodId,
            $amount,
            $payment->status,
            'refund'
        );

        // Send push
        send_push($order->user_id, [
            'push_type' => 14,
            'from_user_id' => getAdminId(),
            'push_message' => "Your payment refund for order #{$order->id}.",
            'push_title' => "Payment refund initiated 💸",
            'extra' => ['order_id' => $order->id]
        ], true);

        LogHelper::write('transaction', 'info', 'Refund processed successfully', [
            'order_id' => $order->id,
            'payment_intent' => $paymentIntentId
        ]);

    } catch (\Exception $e) {
        LogHelper::write('transaction', 'error', 'Refund failed: ' . $e->getMessage(), [
            'order_id' => $order->id
        ]);
    }
}



