
@if($data['type'] == 'request')
    Dear,
    <p>A requester has been requested a service.<br/>
    The request is <a href="{{route('myservices.view',[$data['id_transaction']])}}">{{$data['ticket']}}</a> - {{$data['description']}}.</p><br>
    Assign PIC <a href="{{route('myservices.show',[$data['id_transaction']])}}">Here</a>
@endif
@if($data['type'] == 'request_user')
    Dear,
    <p>A requester has been requested a service.<br/>
    The request is <a href="{{route('myservices.view',[$data['id_transaction']])}}">{{$data['ticket']}}</a> - {{$data['description']}}.</p>
@endif
@if($data['type'] == 'reject')
    Dear {{$data['person_name_buyer']}},<br/>
    Thank you, Your Request has been reviewed.<br/>
    Your ticket number, {{$data['ticket']}}, is rejected.<br/>
    Reason : {{$data['comment']}}.
@endif
@if($data['type'] == 'return')
    Dear {{$data['person_name_buyer']}},<br/>
    Thank you, Your Request has been reviewed.<br/>
    Your ticket number, {{$data['ticket']}}, is returned and need revision.<br/>
    Reason : {{$data['comment']}}.
@endif
@if($data['type'] == 'confirm_to_nextflow')
    Dear {{$data['person_name_buyer']}},<br/>
    Your Request with Ticket No: {{$data['ticket']}} has been processed to the Next Workflow / Step.<br/>
    Current Workflow: {{$data['workflowname']}}.<br/>
    Current PIC: {{$data['person_name_pic']}}.<br/>
@endif
@if($data['type'] == 'confirm_to_nextflow_pic')
    Dear {{$data['person_name_pic']}},
    <p>you got a new task to review a request.<br/>
    The request is {{$data['ticket']}} - {{$data['description']}}.</p>
@endif
@if($data['type'] == 'assign_pic')
    Dear {{$data['person_name_buyer']}},
    <p>Thank you, Your Request has been reviewed.<br/>
        Your ticket number, {{$data['ticket']}} has been accepted and assign to {{$data['confirmby']}}.</p>
@endif
@if($data['type'] == 'completed')
    Dear {{$data['person_name_buyer']}},<br/>
    Your Request with Ticket No: {{$data['ticket']}} has been attended. Need your action to close this ticket.<br/>
@endif

