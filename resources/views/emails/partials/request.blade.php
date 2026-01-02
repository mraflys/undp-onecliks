<p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">Dear,</p>
<p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
    A requester has been requested a service.<br />
    The request is <a href="{{ route('myservices.view', [$data['id_transaction']]) }}"
        style="color: #667eea; text-decoration: none; font-weight: 600;">{{ $data['ticket'] }}</a>
    - {{ $data['description'] }}.
</p>

<div
    style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 15px; margin: 20px 0; border-radius: 4px;">
    <table width="100%" cellpadding="5" cellspacing="0">
        <tr>
            <td style="color: #666666; font-size: 14px; padding: 5px 0;"><strong>Service Name:</strong></td>
            <td style="color: #333333; font-size: 14px; padding: 5px 0;">{{ $data['service_name'] }}</td>
        </tr>
        <tr>
            <td style="color: #666666; font-size: 14px; padding: 5px 0;"><strong>Agency Requester:</strong></td>
            <td style="color: #333333; font-size: 14px; padding: 5px 0;">{{ $data['agency_name_buyer'] }}</td>
        </tr>
        <tr>
            <td style="color: #666666; font-size: 14px; padding: 5px 0;"><strong>User Name Requester:</strong></td>
            <td style="color: #333333; font-size: 14px; padding: 5px 0;">{{ $data['user_name_buyer'] }}</td>
        </tr>
    </table>
</div>

<p style="margin: 25px 0;">
    <a href="{{ route('myservices.show', [$data['id_transaction']]) }}"
        style="display: inline-block; background-color: #667eea; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px;">Assign
        PIC</a>
</p>
