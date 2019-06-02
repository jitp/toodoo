@component('mail::message')
# Welcome to TOODOO!

Hello {{ $participant->name }}!

We are glad you have decided to join us! Have the best on Toodoo collaborating!

You can access your Todo List clicking the bellow button.

@component('mail::button', ['url' => '/todo-list/' . $participant->participant->hash])
Collaborate!
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
