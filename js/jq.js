$('document').ready(function(){

$('[data-toggle="popover"]').popover({
        html: true,
        trigger: 'manual',
        container: $(this).attr('id'),
        placement: 'right',
        content: function () {
            $return = '<div class="hover-hovercard"></div>';
        }
    }).on("mouseenter", function () {
        var _this = this;
        $(this).popover("show");
        $(this).siblings(".popover").on("mouseleave", function () {
            $(_this).popover('hide');
        });
    }).on("mouseleave", function () {
        var _this = this;
        setTimeout(function () {
            if (!$(".popover:hover").length) {
                $(_this).popover("hide")
            }
        }, 100);
    });

    $(document).ready( function() {
  $(".poster").hover( function() {
    var poster = $(this).find("img");
    var posterw = $(poster).innerWidth();
    var posterh = $(poster).innerHeight();
    var posterpos = $(poster).offset();
    
    var scroll = $(window).scrollTop();
    var anchob = $(window).innerWidth();
    var altob = $(window).innerHeight();
    var tooltip = $(this).parent().find(".overview_hidden");
    
    var toolw = $(tooltip).innerWidth();
    var toolh = $(tooltip).innerHeight();
    var toolpos = $(tooltip).offset();
    
    /*
    ((toolpos.left+toolw)<=anchob) &&
    */
    /* reset positions */
    $(tooltip).css({
      "display":"block",
       "top":"auto",
       "bottom":"auto",
       "left":"auto",
       "right":"auto",
       "margin-top":"0"
    });
    
    $(tooltip).removeClass("a_left");
    $(tooltip).removeClass("a_right");
    $(tooltip).removeClass("a_middle");
    $(tooltip).removeClass("a_top");
    $(tooltip).removeClass("a_bottom");
    if( ((posterpos.top+posterh+toolh)-scroll)<=altob ) { /* hacia abajo  con flecha arriba */
      $(tooltip).css({"top":(posterh-35)+"px"});
      $(tooltip).addClass("a_top");
    } else if( (posterpos.top-toolh)>=scroll ) { /* hacia arriba con flecha abajo */
      $(tooltip).css({"top":"-"+(toolh-35)+"px"});
      $(tooltip).addClass("a_bottom");
    } else { /* en medio */
      $(tooltip).css({"top":((posterh/2)-(toolh/2))+"px"});
      $(tooltip).addClass("a_middle");
    }
    
    if( (posterpos.left+posterw+toolw)<=anchob ) {
      $(tooltip).css({"left":(posterw+10)+"px"});
      $(tooltip).addClass("a_left");
    } else {
      $(tooltip).css({"right":(posterw+35)+"px"});
      $(tooltip).addClass("a_right");
    }

  }, function() {
    var tooltip = $(this).parent().find(".overview_hidden");
    $(tooltip).fadeOut(300);
  }); 

});





/*
$('a[href*="#"]:not([href="#"])').click(function() {
  if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
    var target = $(this.hash);
    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
    if (target.length) {
      $('html, body').animate({
        scrollTop: target.offset().top
      }, 1000);
      return false;
    }
  }
});*/

})