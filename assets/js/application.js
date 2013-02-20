!function ($) {

    $('textarea').focus().tabby();

    $(".alert").alert();

    $("UL li h4").click(function(){
        $(this).parent().next("ul").toggle();
    });

}(window.jQuery)