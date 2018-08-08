jQuery(document).ready(function() {
  $('#potpesa-contribution-form').submit(function(e) {
    e.preventDefault();
 
    var form = $(this);
    
    $.post(form.attr('action'), form.serialize(), function(data) {
      if ( data['errorCode'] ) {
          var response = "MPesa Error "+data['errorCode']+": "+data['errorMessage']+".";
          var alertcl = "alert-danger";
      } else {
          var response = "Request <b>"+data['MerchantRequestID']+"</b> Sent.";
          var alertcl = "alert-success";
      }
      
      $('#potpesa-contribution-form').html( '<div class="alert '+alertcl+' text-center" id="ipn-response" role="alert">'+response+'</div>' );
    }, 'json');
  });
});