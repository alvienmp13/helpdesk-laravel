@extends('layouts.auth')
@section('title', 'Akses Tidak Diizinkan')
@section('content')
<span class="auth-form-icon"><x-portal.icon name="lock" :size="26" /></span>
<p class="auth-eyebrow">403 · AKSES DIBATASI</p><h2>Akses tidak diizinkan</h2><p class="auth-description">Halaman ini tidak tersedia untuk role akun Anda. Silakan kembali ke ruang kerja yang sesuai.</p>
@if(auth()->check() && \App\Enums\Role::tryFrom(auth()->user()->role ?? ''))<a class="auth-submit" href="{{ route(auth()->user()->dashboardRoute()) }}">Kembali ke dashboard<x-portal.icon name="arrow-right" :size="18" /></a>@endif
@auth<form method="POST" action="{{ route('logout') }}" class="auth-switch">@csrf<button type="submit">Keluar dari akun</button></form>@else<p class="auth-switch"><a href="{{ route('login') }}">Masuk ke HelpDesk</a></p>@endauth
@endsection
