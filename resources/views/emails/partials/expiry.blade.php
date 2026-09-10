{{-- Expiry Notice Partial --}}
{{-- Usage: @include('emails.partials.expiry', ['hours' => 48]) --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0 0">
    <tr>
        <td style="padding:12px 16px;background-color:#fef3c7;border-radius:8px;border:1px solid #fde68a">
            <p style="margin:0;font-size:13px;color:#92400e;line-height:1.5">
                ⏱ This link expires in <strong>{{ $hours }} hours</strong>. If you did not request this, you can safely ignore this email.
            </p>
        </td>
    </tr>
</table>
