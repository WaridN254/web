<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'HALIS' }}</title>
    <!--[if mso]>
    <style>table,td{font-family:system-ui,-apple-system,sans-serif!important}</style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:system-ui,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7">
        <tr>
            <td align="center" style="padding:40px 16px">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%">
                    {{-- Logo --}}
                    <tr>
                        <td align="center" style="padding-bottom:32px">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background-color:#6366f1;border-radius:8px;padding:8px 14px;text-align:center">
                                        <span style="color:#ffffff;font-size:18px;font-weight:800;letter-spacing:-0.5px;text-decoration:none">HALIS</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Main Content Card --}}
                    <tr>
                        <td>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden">
                                {{-- Content --}}
                                <tr>
                                    <td style="padding:40px 32px">
                                        @yield('content')
                                    </td>
                                </tr>

                                {{-- Footer --}}
                                <tr>
                                    <td style="padding:24px 32px;background-color:#f9fafb;border-top:1px solid #e5e7eb">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:13px;color:#9ca3af;line-height:1.6;text-align:center">
                                                    <p style="margin:0 0 8px">
                                                        <strong style="color:#6b7280">{{ config('email.from.name', 'HALIS') }}</strong> — Modern POS & Inventory Management
                                                    </p>
                                                    <p style="margin:0 0 8px">
                                                        This is a transactional email regarding your account.
                                                    </p>
                                                    <p style="margin:0">
                                                        &copy; {{ date('Y') }} {{ config('email.from.name', 'HALIS') }}. All rights reserved.
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
