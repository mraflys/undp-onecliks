<script>

var date_input = document.getElementById('start_date');


var date_input_2 = document.getElementById('end_date');


var today = new Date();
    today = new Date(today.setDate(today.getDate())).toISOString().split('T')[0];
    // document.getElementsByName("start_date")[0].setAttribute('min', today);
    document.getElementsByName("end_date")[0].setAttribute('min', today);

    date_input.onchange = function(){
        var start_date = document.getElementsByName("start_date")[0].value;
        document.getElementsByName("end_date")[0].setAttribute('min', start_date);
        document.getElementsByName("start_date")[0].setAttribute('max', end_date);
        console.log(start_date);
    }
    date_input_2.onchange = function(){

        var start_date_1 = document.getElementsByName("start_date")[0].value;
        var end_date_1 = document.getElementsByName("end_date")[0].value;
        
        document.getElementsByName("start_date")[0].setAttribute('max', end_date_1);
        console.log(end_date);
    }
</script>