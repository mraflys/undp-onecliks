<table>
    <thead>
        <tr>
            <th style="height: 200px; width: 140px;"></th>
            <th colspan="15" style="height: 200px; text-align: center;margin: auto;width: 50%;">My Report - Coa</th>
        </tr>
        <tr>
            <th>Date Period</th>
            <th>:</th>
            @if(count($coa)!=0)
                <th>{{$coa[0]->dateperiod}}</th>
            @endif
        </tr>
        <tr>
            <th style="width: 140px">Ticket</th>
            <th>Service Name</th>
            <th>Description</th>
            <th>Unit Name / Service Unit</th>
            <th>Requester Unit</th>
            <th>PCBU</th>
            <th>Project</th>
            <th>Activities</th>
            <th>Contract Number</th>
            <th>Expenditure Type</th>
            <th>Funding Source</th>
            <th>ACC</th>
            <th>OPU</th>
            <th>FUND</th>
            <th>Department</th>
            <th>Agent</th>
            <th>Donor</th>
            <th>(%)</th>
            <th>Value</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($coa as $c)
        <tr>
            <td style="width: 140px">{{ $c->transaction_code }}</td>
            <td>{{ $c->service_name }}</td>
            <td>{{ $c->description }}</td>
            <td>{{ $c->unit_name }}</td>
            <td>{{ $c->requester_unit}}</td>
            <td>{{ $c->pcbu }}</td>
            <td>{{ $c->project }}</td>
            <td>{{ $c->activities }}</td>
            <td>{{ $c->contract_number }}</td>
            <td>{{ $c->exp_type }}</td>
            <td>{{ $c->funding_source }}</td>
            <td>{{ $c->acc }}</td>
            <td>{{ $c->opu }}</td>
            <td>{{ $c->fund }}</td>
            <td>{{ $c->dept }}</td>
            <td>{{ $c->imp_agent }}</td>
            <td>{{ $c->donor }}</td>
            <td>{{ $c->percentage }}</td>
            <td>{{ $c->service_price }}</td>
            <td>{{ $c->status_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>