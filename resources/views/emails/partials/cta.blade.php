{{-- CTA Button Partial --}}
{{-- Usage: @include('emails.partials.cta', ['url' => '...', 'label' => '...']) --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:24px 0">
    <tr>
        <td align="center">
            <a href="{{ $url }}" style="display:inline-block;background-color:#6366f1;color:#ffffff;padding:14px 32px;border-radius:8px;font-weight:700;font-size:15px;text-decoration:none;text-align:center">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
