<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Mood;
use Illuminate\Http\Request;

class MoodController extends Controller
{
    protected $mood;

    public function __construct() {
        $this->mood = new Mood();
    }

    public function mood(Request $request){
        $request->validate([
            'mood_type' => 'in:exhausted,sad,neutral,happy,great'
        ]);

        $mood = $this->mood->store($request->all());

        return ResponseHelper::send(200, 'Saved your today\'s mood successfully', $mood);        
    }
}
