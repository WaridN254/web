@extends('emails.layouts.main')

@section('content')
    <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#1a1a2e">
        Create your account
    </h1>

    <p style="margin:0 0 16px;font-size:15px;color:#4a4a68;line-height:1.7">
        Welcome to <strong>{{ config('email.from.name', 'HALIS') }}</strong>!
    </p>

    <p style="margin:0 0 24px;font-size:15px;color:#4a4a68;line-height:1.7">
        Your business, <strong>{{ $businessName }}</strong>, has been registered. Click the button below to create your admin account and start setting up your POS.
    </p>

    @include('emails.partials.cta', ['url' => $activationUrl, 'label' => 'Create My Account'])

    @include('emails.partials.fallback-url', ['url' => $activationUrl])

    @include('emails.partials.expiry', ['hours' => $expiresHours])

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 0">
        <tr>
            <td style="font-size:13px;color:#9ca3af;line-height:1.6">
                If you didn't register this business, you can safely ignore this email. No account has been created yet.
            </td>
        </tr>
    </table>
@endsection

@section('plain-text')
Create your account

Welcome to {{ config('email.from.name', 'HALIS') }}!

Your business, {{ $businessName }}, has been registered. Click the link below to create your admin account and start setting up your POS.

Create Your Account: {{ $activationUrl }}

This link expires in {{ $expiresHours }} hours.

If you didn't register this business, you can safely ignore this email. No account has been created yet.

---
{{ config('email.from.name', 'HALIS') }} — Modern POS & Inventory Management
© {{ date('Y') }} {{ config('email.from.name', 'HALIS') }}. All rights reserved.
@endsection
