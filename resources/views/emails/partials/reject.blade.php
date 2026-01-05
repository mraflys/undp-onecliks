<p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">Dear {{ $data['person_name_buyer'] }},
</p>
<p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 15px 0;">Thank you, Your Request has been
    reviewed.</p>
<div
    style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
    <p style="color: #856404; font-size: 14px; margin: 0;">
        Your ticket number <strong>{{ $data['ticket'] }}</strong> is rejected.<br />
        <strong>Reason:</strong> {{ $data['comment'] }}
    </p>
</div>
