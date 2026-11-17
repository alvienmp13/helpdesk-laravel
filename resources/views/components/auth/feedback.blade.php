@if(session('status'))<div class="auth-status" role="status"><x-portal.icon name="check-circle" :size="18" /><span>{{ session('status') }}</span></div>@endif
@if($errors->any())
    <div class="auth-errors" role="alert" tabindex="-1" data-auth-errors><strong>Periksa kembali isian Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
