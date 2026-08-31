Hi {{ $user->name }},

Your daily mission is still pending. A few minutes of focused effort keeps your progress alive.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
PENDING MISSION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

{{ $mission->title }}

@if($mission->estimated_minutes)
Estimated Time: {{ $mission->estimated_minutes }} minutes
@endif

{{ $mission->description }}

@if($streak > 0)
Your current streak: {{ $streak }} days
@endif

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Complete and reflect here:
{{ $missionUrl }}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FutureSelf · India
© {{ date('Y') }} FutureSelf. All rights reserved.

Manage your email preferences: {{ rtrim(env('FRONTEND_URL', 'https://futureself.in'), '/') }}/missions?settings=1
