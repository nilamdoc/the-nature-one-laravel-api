@extends('emails.layout')

@section('content')
  <h2 style="margin:0 0 12px;color:#222;">Welcome, {{ $name }}</h2>
  <p style="margin:0 0 16px;color:#555;line-height:1.6;">Thanks for joining NatureOne. Please verify your account to continue.</p>
  <p style="margin:0 0 24px;">
    <a href="{{ $verificationUrl }}" style="display:inline-block;background:#7EA35D;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px;">Verify Account</a>
  </p>
  <p style="margin:0;color:#777;font-size:13px;">This link expires in 60 minutes.</p>
@endsection

