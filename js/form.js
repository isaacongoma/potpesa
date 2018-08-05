$('potpesa-form').submit(){
  $('loading-animation').show();

  var form = this;

  $.post( form.serialize(), form.attr('action'), data ){
    if( data['errorMessage'] ){
       var response = "Error "+ data['errorMessage']+": "+ data['errorMessage']+".";
    } else {
       var response = "";
    }
       
    $('loading-animation').hide();
    $('response-data').html('response');
  };
}
