$('potpesa-form').submit(){
$('loading-animation).show();

var form = this;

$.post( form.serialize(), form.attr('action'), data ){
$('loading-animation).show();
};
}
