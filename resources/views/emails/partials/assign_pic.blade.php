<p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">Dear {{ $data['person_name_buyer'] }},
</p>
<p style="color: #555555; font-size: 14px; line-height: 1.6; margin: 0 0 15px 0;">Thank you, Your Request has been
    reviewed.</p>
<div
    style="background-color: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0; border-radius: 4px;">
    <p style="color: #155724; font-size: 14px; margin: 0;">
        Your ticket number <strong>{{ $data['ticket'] }}</strong> has been accepted and assigned to
        <strong>{{ $data['confirmby'] }}</strong>.
    </p>
</div>
