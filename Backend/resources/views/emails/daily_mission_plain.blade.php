Good morning, {{ $user->name }}!

Your future self has prepared today's mission to keep you on track.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TODAY'S MISSION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

{{ $mission->title }}

@if($mission->category)
Category: {{ $mission->category }}
@endif
@if($mission->estimated_minutes)
Estimated Time: {{ $mission->estimated_minutes }} minutes
@endif
@if($mission->difficulty)
Difficulty: {{ ucfirst($mission->difficulty) }}
@endif

{{ $mission->description }}

@if($mission->future_self_note)
Note from Future You:
"{{ $mission->future_self_note }}"
@endif

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Complete your mission here:
{{ $missionUrl }}

If this task remains pending, a reminder will be sent at {{ $reminderTime }}.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

FutureSelf · India
© {{ date('Y') }} FutureSelf. All rights reserved.

Manage your email preferences: {{ rtrim(env('FRONTEND_URL', 'https://futureself.in'), '/') }}/missions?settings=1

