Hi {{ $user->name }},

Your daily mission is ready. A few minutes of focused effort keeps your momentum alive.

----------------------------------------
TODAY'S MISSION
----------------------------------------

{{ $mission->title }}

@if($mission->estimated_minutes)
Estimated Time: {{ $mission->estimated_minutes }} minutes
@endif

{{ $mission->description }}

@if($streak > 0)
Current streak: {{ $streak }} days
@endif

----------------------------------------

Open your mission here:
{{ $missionUrl }}

----------------------------------------

FutureSelf · India
© {{ date('Y') }} FutureSelf. All rights reserved.

Manage your email preferences: {{ $preferencesUrl }}
