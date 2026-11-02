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

    /**
     * Helper to verify that a given goal_id belongs to the authenticated user.
     */
    protected function verifyGoalOwnership(?int $goalId): ?Goals
    {
        if (!$goalId) {
            return null;
        }

        return Goals::where('id', $goalId)
            ->where('user_id', auth()->id())
            ->first();
    }

    public function goals(Request $request){
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'timeframe' => 'required|string|max:50',
            'priority' => 'required|string|max:50',
        ]);

        $goal = $this->goals->store($validated);
        if($goal){
            return ResponseHelper::send(200, 'Your goals had been saved successfully.', $goal);
        }

        return ResponseHelper::send(412, 'Failed to save your goals. Please try again later.');
    }

    public function removeDetail(Request $request){
        $request->validate([
            'type' => 'required|string|in:goals,fears,struggles,desired-traits,role-models,tone',
            'id' => 'required|integer',
        ]);

        $userId = auth()->id();
        $removed = false;

        switch($request->type) {
            case 'goals':
                $removed = Goals::where('id', $request->id)
                    ->where('user_id', $userId)
                    ->delete();
                break;
            case 'fears':
                $removed = Fear::where('id', $request->id)
                    ->whereHas('goal', fn($q) => $q->where('user_id', $userId))
                    ->delete();
                break;
            case 'struggles':
                $removed = Struggles::where('id', $request->id)
                    ->whereHas('goal', fn($q) => $q->where('user_id', $userId))
                    ->delete();
                break;
            case 'desired-traits':
                $removed = DesiredTraits::where('id', $request->id)
                    ->whereHas('goal', fn($q) => $q->where('user_id', $userId))
                    ->delete();
                break;
            case 'role-models':
                $removed = RoleModel::where('id', $request->id)
                    ->whereHas('goal', fn($q) => $q->where('user_id', $userId))
                    ->delete();
                break;
            case 'tone':
                $removed = CommunicationTone::where('id', $request->id)
                    ->whereHas('goal', fn($q) => $q->where('user_id', $userId))
                    ->delete();
                break;
        }

        if ($removed) {
            return ResponseHelper::send(200, ucfirst($request->type).' had been removed successfully.');
        }

        return ResponseHelper::send(404, 'Record not found or unauthorized.');
    }

    public function fears(Request $request){
        $validated = $request->validate([
            'goal_id' => 'required|integer',
            'fear' => 'required|string',
            'category' => 'required|string|max:100',
            'priority' => 'required|string|max:50',
        ]);

        if (!$this->verifyGoalOwnership($validated['goal_id'])) {
            return ResponseHelper::send(403, 'Unauthorized access to the specified goal.');
        }

        $fear = $this->fears->store($validated);
        if($fear){
            return ResponseHelper::send(200, 'Your fears had been saved successfully.', $fear);
        }

        return ResponseHelper::send(412, 'Failed to save your fears. Please try again later.');
    }

    public function struggles(Request $request){
        $validated = $request->validate([
            'goal_id' => 'required|integer',
            'struggle' => 'required|string',
            'category' => 'required|string|max:100',
            'severity' => 'required|string|max:50',
        ]);

        if (!$this->verifyGoalOwnership($validated['goal_id'])) {
            return ResponseHelper::send(403, 'Unauthorized access to the specified goal.');
        }

        $struggles = $this->struggles->store($validated);
        if($struggles){
            return ResponseHelper::send(200, 'Your struggles had been saved successfully.', $struggles);
        }

        return ResponseHelper::send(412, 'Failed to save your struggles. Please try again later.');
    }

    public function desiredTraits(Request $request){
        $validated = $request->validate([
            'goal_id' => 'required|integer',
            'traits' => 'required|array',
            'traits.*' => 'required|string|max:100'
        ]);

        if (!$this->verifyGoalOwnership($validated['goal_id'])) {
            return ResponseHelper::send(403, 'Unauthorized access to the specified goal.');
        }

        $traits = $this->desiredTraits->store($validated);
        if($traits){
            return ResponseHelper::send(200, 'Your desired traits had been saved successfully.', $traits);
        }

        return ResponseHelper::send(412, 'Failed to save your desired traits. Please try again later.');
    }

    public function roleModels(Request $request){
        $validated = $request->validate([
            'goal_id' => 'required|integer',
            'names' => 'required|array',
            'names.*' => 'required|string|max:100',
        ]);

        if (!$this->verifyGoalOwnership($validated['goal_id'])) {
            return ResponseHelper::send(403, 'Unauthorized access to the specified goal.');
        }

        $roleModels = $this->roleModels->store($validated);
        if($roleModels){
            return ResponseHelper::send(200, 'Your role models had been saved successfully.', $roleModels);
        }

        return ResponseHelper::send(412, 'Failed to save your role models. Please try again later.');
    }

    public function saveTone(Request $request){
        $validated = $request->validate([
            'goal_id' => 'required|integer',
            'tone' => 'required|string|in:supportive,strict,motivational,friendly',
        ]);

        if (!$this->verifyGoalOwnership($validated['goal_id'])) {
            return ResponseHelper::send(403, 'Unauthorized access to the specified goal.');
        }

        $tone = $this->communicationTone->store($validated);
        if($tone){
            return ResponseHelper::send(200, 'Your communication tone has been saved successfully.', $tone);
        }

        return ResponseHelper::send(412, 'Failed to save your communication tone. Please try again later.');
    }

    public function updateOnboarded(Request $request){
        GenerateOnboardingSummary::dispatch(auth()->id());
        $updateStatus = $this->user->where('id', auth()->id())->update([
            'is_onboarded' => 1
        ]);
        if($updateStatus){
            return ResponseHelper::send(200, 'You had been onboarded successfully.');
        }

        return ResponseHelper::send(412, 'Failed to onboard you. Please try again later.');
    }
    
    public function getDetail(Request $request){
        $request->validate([
            'type' => 'required|string|in:goals,fears,struggles,desired-traits,role-models,tone',
            'goal_id' => 'required_if:type,fears,struggles,desired-traits,role-models,tone'
        ]);

        if ($request->goal_id && !$this->verifyGoalOwnership((int) $request->goal_id)) {
            return ResponseHelper::send(403, 'Unauthorized access to the specified goal.');
        }
    
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
