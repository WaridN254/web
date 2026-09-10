{{-- Fallback URL Partial --}}
{{-- Usage: @include('emails.partials.fallback-url', ['url' => '...']) --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:16px 0 0">
    <tr>
        <td style="font-size:13px;color:#9ca3af;line-height:1.6">
            If the button above doesn't work, copy and paste this link into your browser:
            <br>
            <a href="{{ $url }}" style="color:#6366f1;word-break:break-all">{{ $url }}</a>
        </td>
    </tr>
</table>
