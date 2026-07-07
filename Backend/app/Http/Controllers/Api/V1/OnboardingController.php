<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Jobs\GenerateOnboardingSummary;
use App\Http\Controllers\Controller;
use App\Models\Goals;
use App\Models\Fear;
use App\Models\Struggles;
use App\Models\DesiredTraits;
use App\Models\RoleModel;
use App\Models\CommunicationTone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    protected $goals;
    protected $fears;
    protected $struggles;
    protected $desiredTraits;
    protected $roleModels;
    protected $communicationTone;
    protected $user;

    public function __construct()
    {
        $this->goals = new Goals();
        $this->fears = new Fear();
        $this->struggles = new Struggles();
        $this->desiredTraits = new DesiredTraits();
        $this->roleModels = new RoleModel();
        $this->communicationTone = new CommunicationTone();
        $this->user = new User();
    }

    public function goals(Request $request){
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'timeframe' => 'required|string',
            'priority' => 'required|string',
        ]);

        $goal = $this->goals->store($request->except('_token'));
        if($goal){
            return ResponseHelper::send(200, 'Your goals had been saved successfully.', $goal);
        }

        return ResponseHelper::send(412, 'Failed to save your goals. Please try again later.');
    }

    public function removeDetail(Request $request){
        $removeDetail = '';
        switch($request->type) {
            case 'goals':
                $removeDetail = $this->goals->removeDetail($request->id);
                break;
            case 'fears':
                $removeDetail = $this->fears->removeDetail($request->id);
                break;
            case 'struggles':
                $removeDetail = $this->struggles->removeDetail($request->id);
                break;
            case 'desired-traits':
                $removeDetail = $this->desiredTraits->removeDetail($request->id);
                break;
            case 'role-models':
                $removeDetail = $this->roleModels->removeDetail($request->id);
                break;
            case 'tone':
                $removeDetail = $this->communicationTone->removeDetail($request->id);
                break;
        }
        if($removeDetail){
            return ResponseHelper::send(200, ucfirst($request->type).' had been removed successfully.');
        }

        return ResponseHelper::send(412, 'Failed to remove your '.ucfirst($request->type).'. Please try again later.');
    }

    public function fears(Request $request){
        $request->validate([
            'fear' => 'required|string',
            'category' => 'required|string',
            'priority' => 'required|string',
        ]);

        $fear = $this->fears->store($request->except('_token'));
        if($fear){
            return ResponseHelper::send(200, 'Your fears had been saved successfully.', $fear);
        }

        return ResponseHelper::send(412, 'Failed to save your fears. Please try again later.');
    }

    public function struggles(Request $request){
        $request->validate([
            'struggle' => 'required|string',
            'category' => 'required|string',
            'severity' => 'required|string',
        ]);

        $struggles = $this->struggles->store($request->except('_token'));
        if($struggles){
            return ResponseHelper::send(200, 'Your struggles had been saved successfully.', $struggles);
        }

        return ResponseHelper::send(412, 'Failed to save your struggles. Please try again later.');
    }

    public function desiredTraits(Request $request){
        $request->validate([
            'traits' => 'required|array',
            'traits.*' => 'required|string|max:100'
        ]);

        $traits = $this->desiredTraits->store($request->except('_token'));
        if($traits){
            return ResponseHelper::send(200, 'Your desired traits had been saved successfully.', $traits);
        }

        return ResponseHelper::send(412, 'Failed to save your desired traits. Please try again later.');
    }

    public function roleModels(Request $request){
        $request->validate([
            'names' => 'required|array',
            'names.*' => 'required|string|max:100',
        ]);

        $roleModels = $this->roleModels->store($request->except('_token'));
        if($roleModels){
            return ResponseHelper::send(200, 'Your role models had been saved successfully.', $roleModels);
        }

        return ResponseHelper::send(412, 'Failed to save your role models. Please try again later.');
    }

    public function saveTone(Request $request){
        $request->validate([
            'tone' => 'required|string|in:supportive,strict,motivational,friendly',
        ]);

        $tone = $this->communicationTone->store($request->except('_token'));
        if($tone){
            return ResponseHelper::send(200, 'Your communication tone has been saved successfully.', $tone);
        }

        return ResponseHelper::send(412, 'Failed to save your communication tone. Please try again later.');
    }

    public function updateOnboarded(Request $request){
        
        GenerateOnboardingSummary::dispatch(auth()->id());
        $updateStatus = $this->user->where('id',auth()->id())->update([
            'is_onboarded' => 1
        ]);
        if($updateStatus){
            return ResponseHelper::send(200, 'You had been onboarded successfully.');
        }

        return ResponseHelper::send(412, 'Failed to onboard you. Please try again later.');
    }
    
    public function getDetail(Request $request){
        $request->validate([
            'type' => 'required',
            'goal_id' => 'required_if:type,fears,struggles,desired-traits,role-models,tone'
        ]);
    
        $data = null;
        switch($request->type) {
            case 'goals':
                $data = $this->goals->getGoals();
                break;
            case 'fears':
                $data = $this->fears->getFears($request->goal_id);
                break;
            case 'struggles':
                $data = $this->struggles->getStruggles($request->goal_id);
                break;
            case 'desired-traits':
                $data = $this->desiredTraits->getDesiredTraits($request->goal_id);
                break;
            case 'role-models':
                $data = $this->roleModels->getRoleModels($request->goal_id);
                break;
            case 'tone':
                $data = $this->communicationTone->getTone($request->goal_id);
                break;
        }
        
        if($data && (is_countable($data) ? count($data) > 0 : true)){
            return ResponseHelper::send(200, 'Your details had been retrieved successfully.', $data);
        }
        
        return ResponseHelper::send(412, 'Failed to retrieve your details. Please try again later.');
    }
}
