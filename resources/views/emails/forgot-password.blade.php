@extends('emails.layout')

@section('content')
  <h2 style="margin:0 0 12px;color:#222;">Password Reset Request</h2>
  <p style="margin:0 0 16px;color:#555;line-height:1.6;">Hi {{ $name }}, we received a request to reset your password.</p>
  <p style="margin:0 0 24px;">
    <a href="{{ $resetUrl }}" style="display:inline-block;background:#7EA35D;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px;">Reset Password</a>
  </p>
  <p style="margin:0;color:#777;font-size:13px;">If you did not request this, you can safely ignore this email.</p>
@endsection

