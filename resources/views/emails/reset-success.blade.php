@extends('emails.layout')

@section('content')
  <h2 style="margin:0 0 12px;color:#222;">Password Updated</h2>
  <p style="margin:0 0 16px;color:#555;line-height:1.6;">Hi {{ $name }}, your password has been reset successfully.</p>
  <p style="margin:0 0 24px;">
    <a href="{{ $loginUrl }}" style="display:inline-block;background:#7EA35D;color:#fff;text-decoration:none;padding:12px 18px;border-radius:6px;">Login Now</a>
  </p>
  <p style="margin:0;color:#777;font-size:13px;">If this was not you, contact support immediately.</p>
@endsection

