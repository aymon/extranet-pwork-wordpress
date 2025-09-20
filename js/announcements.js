(function ($) {
    "use strict";
    var selector = $("#pwork");
    var grid = selector.find('#pwork-anns-wrap');
    var annsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});
    $(document).ready(function () {
      grid.imagesLoaded( function() {
        annsGrid = selector.find("#pwork-anns-wrap").masonry({percentPosition: true,columnWidth: '.grid-sizer'});
        grid.removeClass('pwork-hide-row');
      });
    });

    // Load More Announcements
    selector.on("click", "#pwork-load-more-anns", function () {
      var btn = $(this);
      btn.prop("disabled", true);
      btn.html(pworkParams.loading);
      var offset = parseInt(btn.attr("data-offset"));
      var tag = selector.find('#pwork-ann-search-tag').find(':selected').val();
      var search = selector.find('#pwork-ann-search-input').val();
      var form_data = new FormData();
      form_data.append("action", "pworkLoadMoreAnns");
      form_data.append("offset", offset);
      form_data.append("tag", tag);
      form_data.append("search", search);
      form_data.append("nonce", pworkParams.nonce);
      $.ajax({
        url: pworkParams.ajaxurl,
        type: "POST",
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          btn.parent().remove();
          var content = $(response);
          annsGrid.append(content).masonry("appended", content);
          selector.find('#pwork-anns-wrap').imagesLoaded( function() {
            annsGrid.masonry();
          });
        },
        error: function (jqXHR, error, errorThrown) {
          if (jqXHR.status && jqXHR.status == 400) {
            toastr.error(jqXHR.responseText, pworkParams.error);
          } else {
            toastr.error(pworkParams.wrong, pworkParams.error);
          }
          btn.prop("disabled", false);
          btn.html(pworkParams.loadmore);
        },
      });
    });

    // Search Announcements
    selector.on("click", "#pwork-ann-search", function () {
      var btn = $(this);
      btn.prop("disabled", true);
      grid.addClass('pwork-hide-row');
      var tag = selector.find('#pwork-ann-search-tag').find(':selected').val();
      var search = selector.find('#pwork-ann-search-input').val();
      var form_data = new FormData();
      form_data.append("action", "pworkLoadMoreAnns");
      form_data.append("offset", 0);
      form_data.append('search', search);
      form_data.append('tag', tag);
      form_data.append("nonce", pworkParams.nonce);
      $.ajax({
        url: pworkParams.ajaxurl,
        type: "POST",
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == '') {
            grid.html('<div class="col-12"><div class="alert alert-warning">' + pworkParams.nothing + '</div>');
            grid.imagesLoaded( function() {
              annsGrid.masonry('destroy');
              annsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});
              grid.removeClass('pwork-hide-row');
            });
          } else {
            grid.html('<div class="grid-sizer col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3"></div>' + response);
            grid.imagesLoaded( function() {
              annsGrid.masonry('destroy');
              annsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});
              grid.removeClass('pwork-hide-row');
            });
          }
          btn.prop("disabled", false);
        },
        error: function (jqXHR, error, errorThrown) {
          if (jqXHR.status && jqXHR.status == 400) {
            toastr.error(jqXHR.responseText, pworkParams.error);
          } else {
            toastr.error(pworkParams.wrong, pworkParams.error);
          }
          btn.prop("disabled", false);
          grid.removeClass('pwork-hide-row');
        },
      });
    });

    // Search clear input
    selector.on('input paste keyup','#pwork-ann-search-input',function(){
      if ($(this).val() == '') {
        selector.find('#pwork-ann-search-input-clear').addClass('d-none');
      } else {
        selector.find('#pwork-ann-search-input-clear').removeClass('d-none');
      }
    });

    // Clear Announcement Search
    selector.on('click','#pwork-ann-search-input-clear',function(){
      $(this).addClass('d-none');
      grid.addClass('pwork-hide-row');
      selector.find('#pwork-ann-search-tag').val('');
      selector.find('#pwork-ann-search-input').val('');
      var form_data = new FormData();
        form_data.append("action", "pworkLoadMoreAnns");
        form_data.append("offset", 0);
        form_data.append("nonce", pworkParams.nonce);
        $.ajax({
          url: pworkParams.ajaxurl,
          type: "POST",
          contentType: false,
          processData: false,
          cache: false,
          data: form_data,
          success: function (response) {
            grid.html('<div class="grid-sizer col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3"></div>' + response);
            grid.imagesLoaded( function() {
              annsGrid.masonry('destroy');
              annsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});
              grid.removeClass('pwork-hide-row');
            });
          },
          error: function (jqXHR, error, errorThrown) {
            if (jqXHR.status && jqXHR.status == 400) {
              toastr.error(jqXHR.responseText, pworkParams.error);
            } else {
              toastr.error(pworkParams.wrong, pworkParams.error);
            }
            grid.removeClass('pwork-hide-row');
          },
        });
    });
})(jQuery);