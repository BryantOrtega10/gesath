@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
{{ $nomEmpresa }}
@endcomponent
@endslot

{{-- Body --}}
# Recupera tu contraseña
<br>
Has solicitado recuperar tu contraseña. Para ello, haz click en el boton "Recuperar contraseña"

@component('mail::button', ['url' => 'http://gesath.web-html.com/vista_rec_pass/'.$token])
Recuperar Contraseña
@endcomponent
<br>
<br>

Gracias<br>

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
@endcomponent
@endslot
@endcomponent