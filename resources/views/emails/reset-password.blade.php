@extends('emails.layout')

@section('title', ($siteName ?? 'EtosCoach').' — إعادة تعيين كلمة المرور')

@section('content')
    <h1 style="margin-top:0; font-size:20px;">إعادة تعيين كلمة المرور</h1>

    <p style="margin:0 0 12px;">مرحباً،</p>

    <p style="margin:0 0 12px;">
        تلقّينا طلباً لإعادة تعيين كلمة المرور لحسابك في {{ $siteName }}.
    </p>

    <p style="margin:0 0 16px;">
        <a href="{{ $url }}" target="_blank" style="display:inline-block; background:#0ea5e9; color:#ffffff; padding:10px 16px; border-radius:6px; text-decoration:none;">
            تعيين كلمة المرور
        </a>
    </p>

    <p style="margin:0 0 12px; font-size:14px; color:#475569;">
        رابط إعادة التعيين صالح لمدة {{ $expireMinutes }} دقيقة.
    </p>

    <p style="margin:0; font-size:13px; color:#64748b; word-break:break-all;">
        إذا لم يعمل الزر، انسخ الرابط التالي إلى المتصفح:<br>
        <a href="{{ $url }}" style="color:#0ea5e9;">{{ $url }}</a>
    </p>
@endsection
