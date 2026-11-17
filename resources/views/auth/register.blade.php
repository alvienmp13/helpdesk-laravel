@extends('layouts.auth')
@section('title', 'Daftar Akun')
@section('content')
<span class="auth-form-icon"><x-portal.icon name="user-key" :size="25" /></span>
<p class="auth-eyebrow">MULAI DARI SINI</p>
<h2>Buat akun HelpDesk</h2>
<p class="auth-description">Daftar untuk mengakses Portal Pengguna dan layanan bantuan IT.</p>
<x-auth.feedback />
<form method="POST" action="{{ route('register.store') }}" class="auth-form" data-auth-form>
    @csrf
    <x-auth.input name="name" label="Nama lengkap" autocomplete="name" :maxlength="100" :autofocus="true" />
    <x-auth.input name="email" label="Alamat email" type="email" autocomplete="username" :maxlength="254" />
    <x-auth.input name="password" label="Password" type="password" autocomplete="new-password" hint="Minimal 12 karakter, dengan huruf besar, huruf kecil, dan angka. Maksimal 72 byte." />
    <x-auth.input name="password_confirmation" label="Konfirmasi password" type="password" autocomplete="new-password" />
    <p class="auth-account-note"><x-portal.icon name="info" :size="16" /><span>Akun baru mendapatkan akses sebagai pengguna.</span></p>
    <button type="submit" class="auth-submit">Buat Akun<x-portal.icon name="arrow-right" :size="18" /></button>
</form>
<p class="auth-switch">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
@endsection
