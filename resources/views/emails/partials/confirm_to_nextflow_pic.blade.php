<p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">Dear {{ $data['person_name_pic'] }},
</p>
<div
    style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; border-radius: 4px;">
    <p style="color: #0c5460; font-size: 14px; margin: 0;">
        You got a new task to review a request.<br />
        The request is <strong>{{ $data['ticket'] }}</strong> - {{ $data['description'] }}.
    </p>
</div>
