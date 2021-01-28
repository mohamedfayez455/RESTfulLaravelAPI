@component('mail::message')
    # hi {{$user->name}}

    Tank you for created an account .please verify your account using this link :

    @component('mail::button', ['url' => route('verify' , $user->verification_token) ])
        Button Text
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
