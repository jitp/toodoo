@component('mail::message')
# Welcome to TOODOO!

Hello {{ $participant->name }}!

{{ $inviting->name }} has invited you to collaborate with him in a todo list. If you agreed to you can click the button

@component('mail::button', ['url' => '/todo-list/' . $participant->participant->hash])
Collaborate!
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
