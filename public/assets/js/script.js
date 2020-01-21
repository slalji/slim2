
$(document).ready(function(){
    var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'long'};
    var dateObj = new Date(); //Get the current date
   var newdate = dateObj.toLocaleDateString("en-CA");
    $('#created_date').val(newdate);


});