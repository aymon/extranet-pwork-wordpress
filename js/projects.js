(function ($) {
    "use strict";
    var selector = $("#pwork");
    var grid = selector.find('#pwork-projects-wrap');
    var projectsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});

    // Load More Projects
    selector.on("click", "#pwork-load-more-projects", function () {
      var btn = $(this);
      btn.prop("disabled", true);
      btn.html(pworkParams.loading);
      var userid = grid.attr('data-userid');
      var offset = parseInt(btn.attr("data-offset"));
      var tag = selector.find('#pwork-project-search-tag').find(':selected').val();
      var search = selector.find('#pwork-project-search-input').val();
      var form_data = new FormData();
      form_data.append("action", "pworkLoadProjects");
      form_data.append("userid", userid);
      form_data.append("offset", offset);
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
          btn.parent().remove();
          var content = $(response);
          projectsGrid.append(content).masonry("appended", content);
          grid.imagesLoaded( function() {
            projectsGrid.masonry();
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

    // Search Projects
    selector.on("click", "#pwork-project-search", function () {
      var btn = $(this);
      btn.prop("disabled", true);
      grid.addClass('pwork-hide-row');
      var userid = grid.attr('data-userid');
      var tag = selector.find('#pwork-project-search-tag').find(':selected').val();
      var search = selector.find('#pwork-project-search-input').val();
      var form_data = new FormData();
      form_data.append("action", "pworkLoadProjects");
      form_data.append("userid", userid);
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
              projectsGrid.masonry('destroy');
              projectsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});
              grid.removeClass('pwork-hide-row');
            });
          } else {
            grid.html('<div class="grid-sizer col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3"></div>' + response);
            grid.imagesLoaded( function() {
              projectsGrid.masonry('destroy');
              projectsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});
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

    // Clear Project Search
    selector.on('click','#pwork-project-search-input-clear',function(){
      $(this).addClass('d-none');
      grid.addClass('pwork-hide-row');
      var userid = grid.attr('data-userid');
      selector.find('#pwork-project-search-tag').val('');
      selector.find('#pwork-project-search-input').val('');
      var form_data = new FormData();
        form_data.append("action", "pworkLoadProjects");
        form_data.append("offset", 0);
        form_data.append("userid", userid);
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
              projectsGrid.masonry('destroy');
              projectsGrid = grid.masonry({percentPosition: true,columnWidth: '.grid-sizer'});
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

  // Search clear input
  selector.on('input paste keyup','#pwork-project-search-input',function(){
    if ($(this).val() == '') {
      selector.find('#pwork-project-search-input-clear').addClass('d-none');
    } else {
      selector.find('#pwork-project-search-input-clear').removeClass('d-none');
    }
  });

  // Document Ready
  $(document).ready(function () {
    grid.imagesLoaded( function() {
      projectsGrid.masonry();
      grid.removeClass('pwork-hide-row');
    });
  });
})(jQuery);