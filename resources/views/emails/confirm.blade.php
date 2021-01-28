@component('mail::message')
    # hi {{$user->name}}

    you changed your email so you need to verify the new address using this link :

    @component('mail::button', ['url' => route('verify' , $user->verification_token) ])
        Button Text
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
