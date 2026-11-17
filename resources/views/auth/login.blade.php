@extends('layouts.auth')
@section('title', 'Masuk')
@section('content')
<span class="auth-form-icon"><x-portal.icon name="user" :size="25" /></span>
<p class="auth-eyebrow">SELAMAT DATANG KEMBALI</p>
<h2>Masuk ke HelpDesk</h2>
<p class="auth-description">Gunakan akun Anda untuk membuka ruang kerja HelpDesk.</p>
<x-auth.feedback />
<form method="POST" action="{{ route('login.store') }}" class="auth-form" data-auth-form>
    @csrf
    <x-auth.input name="email" label="Alamat email" type="email" autocomplete="username" :maxlength="254" :autofocus="true" />
    <x-auth.input name="password" label="Password" type="password" autocomplete="current-password" />
    <label class="auth-remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Ingat saya di perangkat ini</span></label>
    <button type="submit" class="auth-submit">Masuk<x-portal.icon name="arrow-right" :size="18" /></button>
</form>
<p class="auth-switch">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
@endsection
