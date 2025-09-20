(function($) {
  'use strict';

  var selector = $('#pwork');

  // Toastr
  toastr.options.closeButton = true;
  toastr.options.positionClass = 'toast-bottom-right';
  toastr.options.progressBar = true;
  toastr.options.newestOnTop = false;
  toastr.options.showEasing = 'swing';
  toastr.options.hideEasing = 'linear';
  toastr.options.closeEasing = 'linear';

  // Email field validation
  var validateEmail = (email) => {
    return email.match(
      /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    );
  };

  // Pagination
  function setPagination(target) {
    var items = target.find('>*');
    items.show();
    var num = items.length;
    var perPage = parseInt(target.data('perpage'));
    if (num > perPage) {
      items.slice(perPage).hide();
      var paginationDiv = '<div id="' + target.attr('id') + '-pagination' + '" class="pwork-pagination"></div>';
      target.parent().parent().after(paginationDiv);
      selector.find('#' + target.attr('id') + '-pagination').pagination({
          items: num,
          itemsOnPage: perPage,
          prevText: '<i class="tf-icon bx bx-chevron-left"></i>',
          nextText: '<i class="tf-icon bx bx-chevron-right"></i>',
          displayedPages: 4,
          onPageClick: function (pageNumber, event) {
              if (typeof event !== "undefined") {
                  event.preventDefault();
              }
              var showFrom = perPage * (pageNumber - 1);
              var showTo = showFrom + perPage;
              items.hide().slice(showFrom, showTo).show();
          }
      });
      selector.find('#' + target.attr('id') + '-pagination').pagination('selectPage', 1);
    }
  }

  selector.find('.paginated').each(function() {
      setPagination($(this));
  });

  // Upload Avatar
  selector.find('#pwork-upload-avatar').on('change', function (e) {
    if ($(this).val() == '') {
        return;
    }
    var parent = $(this).parent().parent();
    var img = parent.find('img');
    var reader = new FileReader();
    reader.onload = function (event) {
        var image = new Image();
        image.src = event.target.result;
        image.classList.add("avatar");
        image.onload = function() {
            if (this.width > 512 || this.height > 512) {
                toastr.error(pworkParams.error4, pworkParams.error);
                return;
            }
            if (this.width != this.height) {
                toastr.error(pworkParams.error5, pworkParams.error);
                return;
            }
            img.after(image);
            img.remove();
        };
    };
    reader.readAsDataURL(e.target.files[0]);
  });

  // Save User Settings
  selector.on('click','.pwork-save-settings',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var nickname = selector.find('#pwork-profile-nickname-input').val();
    var firstname = selector.find('#pwork-profile-firstname-input').val();
    var lastname = selector.find('#pwork-profile-lastname-input').val();
    var email = selector.find('#pwork-profile-email-input').val();
    var location = selector.find('#pwork-profile-location-input').val();
    var date = selector.find('#pwork-profile-date-input').val();
    var tel = selector.find('#pwork-profile-tel-input').val();
    var job = selector.find('#pwork-profile-job-input').val();
    var phoneCheck = 'no';
    if (selector.find('#pwork-profile-tel-input-check').is(':checked')) {
      phoneCheck = 'yes';
    }
    var emailCheck = 'no';
    if (selector.find('#pwork-profile-email-input-check').is(':checked')) {
      emailCheck = 'yes';
    }
    var contactCheck = 'no';
    if (selector.find('#pwork-profile-contact-check').is(':checked')) {
      contactCheck = 'yes';
    }
    var customNot = 'no';
    if (selector.find('#pwork-custom-notifications').is(':checked')) {
      customNot = 'yes';
    }
    var dbNot = 'no';
    if (selector.find('#pwork-db-notifications').is(':checked')) {
      dbNot = 'yes';
    }
    var newcomment = 'no';
    if (selector.find('#pwork-newcomment-notifications').is(':checked')) {
      newcomment = 'yes';
    }
    var newreply = 'no';
    if (selector.find('#pwork-newreply-notifications').is(':checked')) {
      newreply = 'yes';
    }
    var projectsNot = 'no';
    if (selector.find('#pwork-projects-notifications').is(':checked')) {
      projectsNot = 'yes';
    }
    var projectactivitiesNot = 'no';
    if (selector.find('#pwork-projectactivities-notifications').is(':checked')) {
      projectactivitiesNot = 'yes';
    }
    var eventsNot = 'no';
    if (selector.find('#pwork-events-notifications').is(':checked')) {
      eventsNot = 'yes';
    }
    var messagesNot = 'no';
    if (selector.find('#pwork-messages-notifications').is(':checked')) {
      messagesNot = 'yes';
    }

    var icons = {};
    var keys = [];
    var values = [];
    selector.find('#pwork-profile-icons-input > .input-group').each(function(index, value) {
      keys.push($(this).find('select').find(':selected').val());
      values.push($(this).find('input').val());
    });
    for (let i = 0; i < keys.length; i++) {
      icons[keys[i]] = values[i];
    }
    var iconsArray = JSON.stringify(icons);

    if (nickname == '') {
        toastr.error(pworkParams.nicknamereq, pworkParams.error);
        return;
    } else if (firstname == '') {
        toastr.error(pworkParams.firstnamereq, pworkParams.error);
        return;
    } else if (lastname == '') {
        toastr.error(pworkParams.lastnamereq, pworkParams.error);
        return;
    } else if (email == '') {
        toastr.error(pworkParams.emailreq, pworkParams.error);
        return;
    } else if (!validateEmail(email)) {
        toastr.error(pworkParams.emailnotvalid, pworkParams.error);
        return;
    }

    var displayname = selector.find('#pwork-profile-displayname-input').find(':selected').val();
    var bio = selector.find('#pwork-profile-bio-input-wrap .note-editable').html();
    var avatar = '';
    
    if (!selector.find('#pwork-avatar-data > img').hasClass('avatar-160')) {
        avatar = selector.find('#pwork-avatar-data > img').attr('src');
    }

    var form_data = new FormData();
    form_data.append('action', 'pworkSaveSettings');
    form_data.append('nickname', nickname);
    form_data.append('firstname', firstname);
    form_data.append('lastname', lastname);
    form_data.append('displayname', displayname);
    form_data.append('bio', bio);
    form_data.append('email', email);
    form_data.append('location', location);
    form_data.append('tel', tel);
    form_data.append('job', job);
    form_data.append('date', date);
    form_data.append('avatar', avatar);
    form_data.append('icons', iconsArray);
    form_data.append('phonecheck', phoneCheck);
    form_data.append('emailcheck', emailCheck);
    form_data.append('contactcheck', contactCheck);
    form_data.append('customnot', customNot);
    form_data.append('dbnot', dbNot);
    form_data.append('newcomment', newcomment);
    form_data.append('newreply', newreply);
    form_data.append('projectsnot', projectsNot);
    form_data.append('projectactivitiesnot', projectactivitiesNot);
    form_data.append('eventsnot', eventsNot);
    form_data.append('messagesnot', messagesNot);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
            if (response == 'done') {
                toastr.success(pworkParams.settingsaved, pworkParams.success);
            } else {
                toastr.error(response, pworkParams.error);
            }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }).done(function() {
        btn.prop('disabled', false);
    });
  });

  // Load More Users
  selector.on('click','#pwork-load-more-users',function(){
    var btn = $(this);
    var grid = selector.find('#pwork-users-grid');
    btn.prop('disabled', true);
    btn.html(pworkParams.loading);
    var search = selector.find('#pwork-search-input').val();
    var page = parseInt(btn.attr('data-page'));

    var form_data = new FormData();
    form_data.append('action', 'pworkLoadMoreUsers');
    form_data.append('search', search);
    form_data.append('page', page);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          btn.parent().remove();
          grid.append(response);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
            btn.html(pworkParams.loadmore);
        }
    });
  });

  // Search User
  selector.on('click','#pwork-search-user',function(){
    var btn = $(this);
    var loadBtn = selector.find('#pwork-load-more-users');
    var searchInput = selector.find('#pwork-search-input');
    var grid = selector.find('#pwork-users-grid');
    btn.prop('disabled', true);
    loadBtn.prop('disabled', true);
    var search = searchInput.val();

    var form_data = new FormData();
    form_data.append('action', 'pworkSearchUsers');
    form_data.append('search', search);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          grid.html(response);
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
            loadBtn.prop('disabled', false);
        }
    });
  });

  // Clear User Search
  selector.on('click','#pwork-search-input-clear',function(){
    $(this).addClass('d-none');
    selector.find('#pwork-search-input').val('');
    selector.find('#pwork-search-input').prop('disabled', false);
    selector.find('#pwork-search-user').prop('disabled', false);
    var grid = selector.find('#pwork-users-grid');
    var form_data = new FormData();
    form_data.append('action', 'pworkSearchUsers');
    form_data.append('search', '');
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          grid.html(response);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
        }
    }); 
  });

  // Search clear input
  selector.on('input paste keyup','#pwork-search-input',function(){
    if ($(this).val() == '') {
      selector.find('#pwork-search-input-clear').addClass('d-none');
    } else {
      selector.find('#pwork-search-input-clear').removeClass('d-none');
    }
  });

  // Add to contacts
  selector.on('click','.add-to-contacts',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var id = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkAddContact');
    form_data.append('id', id);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          btn.html('<span class="tf-icons bx bxs-user-minus"></span>');
          btn.removeClass('add-to-contacts');
          btn.removeClass('btn-secondary');
          btn.addClass('remove-from-contacts');
          btn.addClass('btn-danger');
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }); 
  });

  // Remove from contacts
  selector.on('click','.remove-from-contacts',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var id = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkRemoveContact');
    form_data.append('id', id);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          btn.html('<span class="tf-icons bx bxs-user-plus"></span>');
          btn.removeClass('btn-danger');
          btn.addClass('btn-secondary');
          btn.addClass('add-to-contacts');
          btn.removeClass('remove-from-contacts');
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }); 
  });

  // Remove from table contacts
  selector.on('click','.remove-from-table-contacts',function(){
    var btn = $(this);
    var parent = $(this).parent().parent().parent();
    btn.prop('disabled', true);
    var id = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkRemoveContact');
    form_data.append('id', id);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          parent.remove();
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }); 
  });

  // Search Contacts
  selector.on('input paste keyup','#pwork-search-contact-input input',function(){
    if ($(this).val() != '') {
      var searchTerm = $(this).val().toLowerCase();
      selector.find('#pwork-contacts-table tr').each(function () {
        if ($(this).filter('[data-name *= ' + searchTerm + ']').length > 0 || searchTerm.length < 1) {
            $(this).show();
        } else {
            $(this).hide();
        }
      });
    } else {
      selector.find('#pwork-contacts-table tr').show();
    }
  });

  // Copy to clipboard button
  selector.on('click','.pwork-copy-url',function(){
      var btn = $(this);
      var copyText = btn.attr('data-url');
      navigator.clipboard.writeText(copyText);
      btn.removeClass('btn-secondary');
      btn.addClass('btn-success');
      btn.html('<span class="tf-icons bx bx-check"></span>');
      setTimeout(function(){
        btn.html('<span class="tf-icons bx bxs-copy"></span>');
        btn.removeClass('btn-success');
        btn.addClass('btn-secondary');
      }, 2000); 
  });

  // Search clear input
  selector.on('input paste keyup','#pwork-file-search-input',function(){
    if ($(this).val() == '') {
      selector.find('#pwork-file-search-input-clear').addClass('d-none');
    } else {
      selector.find('#pwork-file-search-input-clear').removeClass('d-none');
    }
  });

  // File Search
  selector.on('click','#pwork-file-search',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var table = selector.find('#pwork-files-tbody');
    var folder = selector.find('#pwork-file-search-folder').find(':selected').val();
    var form_data = new FormData();
    form_data.append('action', 'pworkSearchFiles');
    form_data.append('search', selector.find('#pwork-file-search-input').val());
    form_data.append('folder', folder);
    if (btn.hasClass('my-files')) {
      form_data.append('author', 'my');
    } else {
      form_data.append('author', 'all');
    }
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          table.html(response);
          selector.find('#pwork-files-tbody-pagination').remove();
          setPagination(table);
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }); 
  });

  // Delete File
  selector.on('click','.pwork-delete-file',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var answer = window.confirm(pworkParams.answer);
    if (answer) {
      var id = btn.attr('data-id');
      var form_data = new FormData();
      form_data.append('action', 'pworkDeleteFile');
      form_data.append('id', id);
      form_data.append('nonce', pworkParams.nonce);
      $.ajax({
          url: pworkParams.ajaxurl,
          type: 'POST',
          contentType: false,
          processData: false,
          cache: false,
          data: form_data,
          success: function (response) {
            if (response == 'done') {
              selector.find('#pwork-file-search').trigger('click');
            } else {
              toastr.error(response, pworkParams.error);
              btn.prop('disabled', false);
            }
          },
          error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
          }
      }); 
    }
  });

  // Clear File Search
  selector.on('click','#pwork-file-search-input-clear',function(){
    $(this).addClass('d-none');
    selector.find('#pwork-file-search-input').val('');
    var table = selector.find('#pwork-files-tbody');
    var folder = selector.find('#pwork-file-search-folder').find(':selected').val();
    var form_data = new FormData();
    form_data.append('action', 'pworkSearchFiles');
    form_data.append('search', '');
    form_data.append('folder', folder);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
            table.html(response);
            selector.find('#pwork-files-tbody-pagination').remove();
            setPagination(table);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
        }
    }); 
  });

  /* Upload File */
  selector.find('#pwork-file-upload-btn').on('click', function (e) {
    var btn = $(this);
    btn.prop('disabled', true);
    var file = document.getElementById("pwork-file-upload");
    if (file.files.length == 0) {
      toastr.error(pworkParams.selectfile, pworkParams.error);
      btn.prop('disabled', false);
      return;
    }
    var file_data = document.getElementById('pwork-file-upload').files[0];
    var tags = selector.find('#pwork-selected-tags').val();
    tags = JSON.stringify(tags);
    var members = selector.find('#pwork-upload-members').val();
    members = JSON.stringify(members);
    var form_data = new FormData();
    form_data.append('file', file_data);
    form_data.append('tag', tags);
    form_data.append('members', members);
    form_data.append('action', 'pworkUploadFile');
    form_data.append('nonce', pworkParams.nonce);

    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          selector.find('#pwork-file-search').trigger('click');
          selector.find('#pwork-cancel-upload-btn').trigger('click');
          selector.find('#pwork-file-upload').val('');
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
       }
    }).done(function( response ) {
        if ( false === response.success ) {
            toastr.error(response.data, pworkParams.error);
        } else {
            toastr.success(pworkParams.uploaded, pworkParams.success);
        }
        btn.prop('disabled', false);
    });
  });

  /* Edit File */
  selector.on('click','#pwork-file-edit',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var id = $(this).attr('data-id');
    var name = selector.find('#pwork-file-name-edit').val();
    var tags = selector.find('#pwork-selected-tags-edit').val();
    tags = JSON.stringify(tags);
    var members = selector.find('#pwork-upload-members-edit').val();
    members = JSON.stringify(members);
    var form_data = new FormData();
    form_data.append('fileid', id);
    form_data.append('name', name);
    form_data.append('tag', tags);
    form_data.append('members', members);
    form_data.append('action', 'pworkEditFile');
    form_data.append('nonce', pworkParams.nonce);

    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            toastr.success(pworkParams.fileEdited, pworkParams.success);
          } else {
            toastr.error(response, pworkParams.error);
          }
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
       }
    }).done(function() {
        btn.prop('disabled', false);
    });
  });

  // Add Event
  selector.on('click','#pwork-event-submit',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var id = btn.attr('data-id');
    var title = selector.find('#pwork-event-title').val();
    var start = selector.find('#pwork-event-start').val();
    var end = selector.find('#pwork-event-end').val();
    var color = selector.find('#pwork-event-color').val();
    var url = selector.find('#pwork-event-url').val();
    var allday = selector.find('input[name=allDayEvent]:checked').val();
    if (title == '' || start == '' || end == '' || color == '') {
      toastr.error(pworkParams.fillAll, pworkParams.error);
      btn.prop('disabled', false);
      return;
    }
    var form_data = new FormData();
    form_data.append('action', 'pworkAddEvent');
    form_data.append('id', id);
    form_data.append('title', title);
    form_data.append('start', start);
    form_data.append('end', end);
    form_data.append('color', color);
    form_data.append('url', url);
    form_data.append('allday', allday);
    form_data.append('nonce', pworkParams.nonce);

    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            if (id == '') {
              selector.find('#pwork-event-title').val('');
              selector.find('#pwork-event-start').val('');
              selector.find('#pwork-event-end').val('');
              selector.find('#pwork-event-color').val(pworkParams.eventColor);
              selector.find('#pwork-event-url').val('');
              toastr.success(pworkParams.eventAdded, pworkParams.success);
            } else {
              toastr.success(pworkParams.eventEdited, pworkParams.success);
            }
          } else {
              toastr.error(response, pworkParams.error);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }).done(function() {
      btn.prop('disabled', false);
    }); 
  });

  // Delete Event
  selector.on('click','.pwork-delete-event',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var answer = window.confirm(pworkParams.answer2);
    if (answer) {
      var id = btn.attr('data-id');
      var parent = btn.parent().parent().parent();
      var form_data = new FormData();
      form_data.append('action', 'pworkDeleteEvent');
      form_data.append('id', id);
      form_data.append('nonce', pworkParams.nonce);
      $.ajax({
          url: pworkParams.ajaxurl,
          type: 'POST',
          contentType: false,
          processData: false,
          cache: false,
          data: form_data,
          success: function (response) {
            if (response == 'done') {
              parent.remove();
              selector.find('#pwork-events-tbody-pagination').remove();
              setPagination(selector.find('#pwork-events-tbody'));
            } else {
              toastr.error(response, pworkParams.error);
              btn.prop('disabled', false);
            }
          },
          error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
          }
      }); 
    }
  });

  // Add Announcement
  selector.on('click','#pwork-ann-submit',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var id = btn.attr('data-id');
    var title = selector.find('#pwork-ann-title').val();
    var excerpt = selector.find('#pwork-ann-excerpt').val();
    var content = selector.find('#pwork-ann-content-wrap .note-editable').html();
    var card = selector.find('#pwork-ann-card').find(':selected').val();
    var file = document.getElementById('pwork-ann-featured');
    var file_data = file.files[0];
    var tags = selector.find('#pwork-ann-tags').val();
    tags = JSON.stringify(tags);

    if (title == '') {
      toastr.error(pworkParams.fillAll, pworkParams.error);
      btn.prop('disabled', false);
      return;
    }
    var form_data = new FormData();
    form_data.append('action', 'pworkAddAnn');
    form_data.append('id', id);
    form_data.append('title', title);
    form_data.append('excerpt', excerpt);
    form_data.append('content', content);
    form_data.append('card', card);
    form_data.append('tag', tags);
    form_data.append('file', file_data);
    form_data.append('nonce', pworkParams.nonce);

    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            if (btn.hasClass('refresh-page')) {
              window.location.reload();
              return;
            }
            if (id == '') {
              selector.find('#pwork-ann-title').val('');
              selector.find('#pwork-ann-excerpt').val('');
              selector.find('#pwork-ann-content-wrap .note-editable').html('');
              selector.find('#ppwork-ann-img').val('');
              selector.find('#pwork-ann-card').val('default');
              toastr.success(pworkParams.annsAdded, pworkParams.success);
            } else {
              toastr.success(pworkParams.annsEdited, pworkParams.success);
            }
          } else {
              toastr.error(response, pworkParams.error);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }).done(function() {
      btn.prop('disabled', false);
    }); 
  });

  // Delete Announcement
  selector.on('click','.pwork-delete-ann',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var answer = window.confirm(pworkParams.answer3);
    if (answer) {
      var id = btn.attr('data-id');
      var parent = btn.parent().parent().parent();
      var form_data = new FormData();
      form_data.append('action', 'pworkDeleteAnn');
      form_data.append('id', id);
      form_data.append('nonce', pworkParams.nonce);
      $.ajax({
          url: pworkParams.ajaxurl,
          type: 'POST',
          contentType: false,
          processData: false,
          cache: false,
          data: form_data,
          success: function (response) {
            if (response == 'done') {
              parent.remove();
              selector.find('#pwork-anns-tbody-pagination').remove();
              setPagination(selector.find('#pwork-anns-tbody'));
            } else {
              toastr.error(response, pworkParams.error);
              btn.prop('disabled', false);
            }
          },
          error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
          }
      }); 
    }
  });

  // Add Project Comment
  selector.on('click','.add-ann-comment',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var comment = btn.prev('.note-editor').find('.note-editable').html();
    if (comment == '' || comment == '<p></p>' || comment == '<p><br></p>') {
      toastr.error(pworkParams.error1, pworkParams.error);
      return;
    }
    var postid = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkAnnAddComment');
    form_data.append('comment', comment);
    form_data.append('postid', postid);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            window.location.reload();
          } else {
            toastr.error(response, pworkParams.error);
          }
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    });
  });

  // Show Replies
  selector.on('click','.pwork-show-replies',function(e){
    e.preventDefault();
    $(this).find('a').html(pworkParams.hideReplies + ' <i class="bx bx-chevron-up ms-1"></i>');
    $(this).removeClass('pwork-show-replies');
    $(this).addClass('pwork-hide-replies');
    $(this).next().removeClass('d-none');
    $(this).next().addClass('d-block');
  });

  // Hide Replies
  selector.on('click','.pwork-hide-replies',function(e){
    e.preventDefault();
    $(this).find('a').html(pworkParams.showReplies + '<i class="bx bx-chevron-down ms-1"></i>');
    $(this).addClass('pwork-show-replies');
    $(this).removeClass('pwork-hide-replies');
    $(this).next().addClass('d-none');
    $(this).next().removeClass('d-block');
  });

  // Set notifications
  function setNotifications() {
    var form_data = new FormData();
    form_data.append('action', 'pworkGetNotifications');
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          response = $.parseJSON(response);
          selector.find('#menu-item-announcements > a > div').find('.badge').remove();
          selector.find('#menu-item-events > a > div').find('.badge').remove();
          selector.find('#menu-item-forum > a > div').find('.badge').remove();
          selector.find('#menu-item-messages > a > div').find('.badge').remove();
          selector.find('#menu-item-projects > a > div').find('.badge').remove();
          if (response.newAnn != '' && response.newAnn !== '0') {
            selector.find('#menu-item-announcements > a > div').append('<span class="badge rounded-pill bg-danger">' + response.newAnn + '</span>');
          }
          if (response.newEvent != '' && response.newEvent !== '0') {
            selector.find('#menu-item-events > a > div').append('<span class="badge rounded-pill bg-danger">' + response.newEvent + '</span>');
          }
          if (response.newTopic != '' && response.newTopic !== '0') {
            selector.find('#menu-item-forum > a > div').append('<span class="badge rounded-pill bg-danger">' + response.newTopic + '</span>');
          }
          if (response.newMessage != '' && response.newMessage !== '0') {
            selector.find('#menu-item-messages > a > div').append('<span class="badge rounded-pill bg-danger">' + response.newMessage + '</span>');
          }
          if (response.newProject != '' && response.newProject !== '0') {
            selector.find('#menu-item-projects > a > div').append('<span class="badge rounded-pill bg-danger">' + response.newProject + '</span>');
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
              console.log(jqXHR.responseText);
            }else{
              console.log(pworkParams.wrong);
            }
        }
    });
  }

  // Check new notifications every minute
  if (pworkParams.liveNotifications == 'enable') {
    setInterval(function() {
      setNotifications();
    }, 60 * 1000);
  }

  // Search Topics
  selector.on('click','#pwork-topic-search',function(){
    var btn = $(this);
    var searchInput = selector.find('#pwork-topic-search-input');
    var table = selector.find('#pwork-topics-tbody');
    var tag = selector.find('#pwork-topic-search-tag').find(':selected').val();
    btn.prop('disabled', true);
    var search = searchInput.val();
    var form_data = new FormData();
    form_data.append('action', 'pworkSearchTopic');
    form_data.append('search', search);
    form_data.append('tag', tag);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          table.html(response);
          selector.find('#pwork-topics-tbody-pagination').remove();
          setPagination(table);
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    });
  });

  // Clear Topic Search
  selector.on('click','#pwork-topic-search-input-clear',function(){
    $(this).addClass('d-none');
    var userID = selector.find('#pwork-topic-search').attr('data-id');
    selector.find('#pwork-topic-search-input').val('');
    selector.find('#pwork-topic-search-input').prop('disabled', false);
    selector.find('#pwork-topic-search').prop('disabled', false);
    var table = selector.find('#pwork-topics-tbody');
    var tag = selector.find('#pwork-topic-search-tag').find(':selected').val();
    var form_data = new FormData();
    form_data.append('action', 'pworkSearchTopic');
    form_data.append('search', '');
    form_data.append('tag', tag);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          table.html(response);
          selector.find('#pwork-topics-tbody-pagination').remove();
          setPagination(table);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
        }
    }); 
  });

  // Search clear input
  selector.on('input paste keyup','#pwork-topic-search-input',function(){
    if ($(this).val() == '') {
      selector.find('#pwork-topic-search-input-clear').addClass('d-none');
    } else {
      selector.find('#pwork-topic-search-input-clear').removeClass('d-none');
    }
  });

  // Add Topic
  selector.on('click','#pwork-topic-submit',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var id = btn.attr('data-id');
    var title = selector.find('#pwork-topic-title').val();
    var status = selector.find('#pwork-comments-status').find(':selected').val();
    var content = selector.find('#pwork-topic-content-wrap .note-editable').html();
    var tags = selector.find('#pwork-topic-tag').val();
    tags = JSON.stringify(tags);

    if (title == '') {
      toastr.error(pworkParams.fillAll, pworkParams.error);
      btn.prop('disabled', false);
      return;
    }
    var form_data = new FormData();
    form_data.append('action', 'pworkAddTopic');
    form_data.append('id', id);
    form_data.append('title', title);
    form_data.append('content', content);
    form_data.append('status', status);
    form_data.append('tag', tags);
    form_data.append('nonce', pworkParams.nonce);

    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            if (id == '') {
              selector.find('#pwork-topic-title').val('');
              selector.find('#pwork-topic-content-wrap .note-editable').html('');
              selector.find('#pwork-topic-search-tag').val('');
              selector.find('#pwork-cancel-topic-btn').trigger('click');
              selector.find('#pwork-topic-search').trigger('click');
              toastr.success(pworkParams.topicAdded, pworkParams.success);
            } else {
              toastr.success(pworkParams.topicEdited, pworkParams.success);
            }
          } else {
              toastr.error(response, pworkParams.error);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }).done(function() {
      btn.prop('disabled', false);
    }); 
  });

  // Add Comment
  selector.on('click','#pwork-add-comment-btn',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var comment = selector.find('#pwork-add-comment-modal .note-editable').html();
    if (comment == '' || comment == '<p></p>' || comment == '<p><br></p>') {
      toastr.error(pworkParams.error1, pworkParams.error);
      return;
    }
    var topicid = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkAddComment');
    form_data.append('comment', comment);
    form_data.append('topicid', topicid);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
              btn.prop('disabled', false);
              window.location.reload();
          } else {
              toastr.error(response, pworkParams.error);
              btn.prop('disabled', false);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    });
  });

  var commentID = '';
  selector.on('click','.pwork-add-reply',function(){
    commentID = $(this).attr('data-id');
  });

  // Add Reply
  selector.on('click','#pwork-add-reply-btn',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var comment = selector.find('#pwork-add-reply-modal .note-editable').html();
    if (comment == '' || comment == '<p></p>' || comment == '<p><br></p>') {
      toastr.error(pworkParams.error1, pworkParams.error);
      return;
    }
    var topicid = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkAddReply');
    form_data.append('comment', comment);
    form_data.append('topicid', topicid);
    form_data.append('commentid', commentID);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            btn.prop('disabled', false);
              window.location.reload();
          } else {
              toastr.error(response, pworkParams.error);
              btn.prop('disabled', false);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    });
  });

  // Delete Comment
  selector.on('click','.pwork-delete-comment',function(e){
    e.preventDefault();
    var btn = $(this);
    var parent = btn.parents('.pwork-chat-row');
    btn.prop('disabled', true);
    var commentid = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkDeleteComment');
    form_data.append('commentid', commentid);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
              if (parent.next().next().hasClass('pwork-chat-reply-wrap')) {
                parent.next().next().remove();
              }
              if (parent.next().hasClass('pwork-replies-toggle')) {
                parent.next().remove();
              }
              parent.remove();
          } else {
              toastr.error(response, pworkParams.error);
              btn.prop('disabled', false);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    });
  });

  // Delete Topic
  selector.on('click','.pwork-delete-topic',function(e){
    e.preventDefault();
    var answer = window.confirm(pworkParams.answer4);
    if (answer) {
      var btn = $(this);
      btn.prop('disabled', true);
      var topicid = btn.attr('data-id');
      var form_data = new FormData();
      form_data.append('action', 'pworkDeleteTopic');
      form_data.append('topicid', topicid);
      form_data.append('nonce', pworkParams.nonce);
      $.ajax({
          url: pworkParams.ajaxurl,
          type: 'POST',
          contentType: false,
          processData: false,
          cache: false,
          data: form_data,
          success: function (response) {
            if (response == 'done') {
              selector.find('#pwork-chat-main').remove();
              selector.find('.content-wrapper .pwork-divider').remove();
              selector.find('.content-wrapper .pwork-chat-wrap').remove();
              selector.find('.content-wrapper .container-lg').html('<div class="alert alert-danger">' + pworkParams.topicDeleted + '</div>');
            } else {
              toastr.error(response, pworkParams.error);
              btn.prop('disabled', false);
            }
          },
          error: function(jqXHR,error, errorThrown) {
              if(jqXHR.status&&jqXHR.status==400){
                  toastr.error(jqXHR.responseText, pworkParams.error);
              }else{
                  toastr.error(pworkParams.wrong, pworkParams.error);
              }
              btn.prop('disabled', false);
          }
      });
    }
  });

  // Send Message
  selector.on('click','.pwork-send-message',function(e){
    var btn = $(this);
    var content = btn.parent().find('.note-editable').html();
    if (content == '' || content == '<p></p>' || content == '<p><br></p>') {
      toastr.error(pworkParams.error1, pworkParams.error);
    } else {
      var parent = btn.parent().parent().find('.pwork-single-message-content');
      var loadold = parent.find('.pwork-load-more-msgs');
      btn.prop('disabled', true);
      var contact = btn.attr('data-contact');
      var sender = btn.attr('data-sender');
      var form_data = new FormData();
      form_data.append('action', 'pworkSendMessage');
      form_data.append('contact', contact);
      form_data.append('sender', sender);
      form_data.append('content', content);
      form_data.append('nonce', pworkParams.nonce);
      $.ajax({
          url: pworkParams.ajaxurl,
          type: 'POST',
          contentType: false,
          processData: false,
          cache: false,
          data: form_data,
          success: function (response) {
            if (response == 'error') {
              toastr.error(response, pworkParams.error);
            } else {
              btn.parent().find('.note-editable').html('');
              loadold.remove();
              parent.append(response);
              parent.scrollTop(parent.prop("scrollHeight"));
            }
            btn.prop('disabled', false);
          },
          error: function(jqXHR,error, errorThrown) {
              if(jqXHR.status&&jqXHR.status==400){
                  toastr.error(jqXHR.responseText, pworkParams.error);
              }else{
                  toastr.error(pworkParams.wrong, pworkParams.error);
              }
              btn.prop('disabled', false);
          }
      });
    }
  });

  var firstMessageTab = selector.find('.pwork-single-message-wrap > .pwork-single-message-content');
  firstMessageTab.scrollTop(firstMessageTab.prop("scrollHeight"));

  // Messages Tabs
  selector.on('click','.pwork-messages-sidebar-chat',function(e){
    var target = $($(this).attr('data-target'));
    selector.find('.pwork-messages-sidebar-chat.active').removeClass('active');
    $(this).addClass('active');
    selector.find('.pwork-single-message-wrap').addClass('d-none');
    selector.find('.pwork-single-message-wrap').removeClass('d-flex');
    target.removeClass('d-none');
    target.addClass('d-flex');
    target.find('.pwork-single-message-content').scrollTop(target.find('.pwork-single-message-content').prop("scrollHeight"));
  });

  selector.on('click','.pwork-messages-sidebar-chat:not(.checked)',function(e){
    $(this).addClass('checked');
    var contact = $(this).attr('data-contact');
    var form_data = new FormData();
    form_data.append('action', 'pworkMarkAsRead');
    form_data.append('contact', contact);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response != 'done') {
            console.log(response);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
              console.log(jqXHR.responseText);
            }else{
              console.log(pworkParams.wrong);
            }
        }
    });
  });

  var searchParams = new URLSearchParams(window.location.search);

  if (searchParams.has('userID')) {
    selector.find('.pwork-messages-sidebar-chat[data-contact="' + searchParams.get('userID') + '"]').trigger('click');
  } else {
    selector.find('.pwork-messages-sidebar-chat.active').trigger('click');
  }

  // Search Contacts
  selector.on('input paste keyup','#pwork-messages-search-input',function(){
    if ($(this).val() != '') {
      var searchTerm = $(this).val().toLowerCase();
      selector.find('.pwork-messages-sidebar-chat').each(function () {
        if ($(this).filter('[data-name *= ' + searchTerm + ']').length > 0 || searchTerm.length < 1) {
            $(this).show();
        } else {
            $(this).hide();
        }
      });
    } else {
      selector.find('.pwork-messages-sidebar-chat').show();
    }
  });

  // Load More Messages
  selector.on('click','.load-more-msgs',function(){
    var btn = $(this);
    var parent = btn.parent().parent();
    btn.prop('disabled', true);
    var offset = btn.attr('data-offset');
    var userid = btn.attr('data-userid');
    var form_data = new FormData();
    form_data.append('action', 'pworkLoadOldMsg');
    form_data.append('offset', offset);
    form_data.append('userid', userid);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'error') {
            toastr.error(pworkParams.wrong, pworkParams.error);
            btn.prop('disabled', false);
          } else {
            btn.parent().remove();
            parent.prepend(response);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    });
  });

  // Add Project
  selector.on('click','#pwork-project-submit',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var id = btn.attr('data-id');
    var title = selector.find('#pwork-project-title').val();
    if (title == '') {
      toastr.error(pworkParams.fillAll, pworkParams.error);
      btn.prop('disabled', false);
      return;
    }

    var content = selector.find('#pwork-project-content-wrap .note-editable').html();
    var tags = selector.find('#pwork-project-tags').val();
    tags = JSON.stringify(tags);
    var due = selector.find('#pwork-project-due').val();
    var excerpt = selector.find('#pwork-project-excerpt').val();
    var who = selector.find('#pwork-project-who').val();
    var members = selector.find('#pwork-project-members').val();
    members = JSON.stringify(members);
    var checklist = selector.find('#pwork-project-checklist').val();
    var file = document.getElementById('pwork-project-featured');
    var file_data = file.files[0];
    if (selector.find('#pwork-project-checklist').is(':checked')) {
      checklist = 'enable';
    } else {
      checklist = 'disable';
    }
    var values = [];
    selector.find('#pwork-project-tasks-input > .input-group').each(function(index, value) {
      values.push([$(this).find('input').val(), $(this).find('select').find(':selected').val()]);
    });
    var tasks = JSON.stringify(values);

    var form_data = new FormData();
    form_data.append('action', 'pworkAddProject');
    form_data.append('id', id);
    form_data.append('title', title);
    form_data.append('content', content);
    form_data.append('excerpt', excerpt);
    form_data.append('tag', tags);
    form_data.append('due', due);
    form_data.append('members', members);
    form_data.append('who', who);
    form_data.append('checklist', checklist);
    form_data.append('tasks', tasks);
    form_data.append('file', file_data);
    form_data.append('nonce', pworkParams.nonce);

    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            if (id == '') {
              selector.find('#pwork-project-title').val('');
              selector.find('#pwork-project-content-wrap .note-editable').html('');
              toastr.success(pworkParams.projectAdded, pworkParams.success);
            } else {
              toastr.success(pworkParams.projectEdited, pworkParams.success);
            }
          } else {
              toastr.error(response, pworkParams.error);
          }
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    }).done(function() {
      btn.prop('disabled', false);
    }); 
  });

  selector.on('change','#pwork-project-members',function(){
    $(this).find('option[data-default="yes"]').prop('selected',true);
  });

  // Add Project Comment
  selector.on('click','.add-project-comment',function(){
    var btn = $(this);
    btn.prop('disabled', true);
    var comment = btn.prev('.note-editor').find('.note-editable').html();
    if (comment == '' || comment == '<p></p>' || comment == '<p><br></p>') {
      toastr.error(pworkParams.error1, pworkParams.error);
      return;
    }
    var postid = btn.attr('data-id');
    var form_data = new FormData();
    form_data.append('action', 'pworkProjectAddComment');
    form_data.append('comment', comment);
    form_data.append('postid', postid);
    form_data.append('nonce', pworkParams.nonce);
    $.ajax({
        url: pworkParams.ajaxurl,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            window.location.reload();
          } else {
            toastr.error(response, pworkParams.error);
          }
          btn.prop('disabled', false);
        },
        error: function(jqXHR,error, errorThrown) {
            if(jqXHR.status&&jqXHR.status==400){
                toastr.error(jqXHR.responseText, pworkParams.error);
            }else{
                toastr.error(pworkParams.wrong, pworkParams.error);
            }
            btn.prop('disabled', false);
        }
    });
  });

  // Project Checklist
  selector.on('click','input[name="pwork-project-checkbox"]:not(:disabled)',function(){
    var input = $(this);
    var postid = input.attr('data-id');
    var item = input.attr('data-item');
    var form_data = new FormData();
    if (input.is(':checked')) {
      if (window.confirm(pworkParams.answer5)) {
        form_data.append('action', 'pworkUpdateChecklist');
        form_data.append('postid', postid);
        form_data.append('item', item);
        form_data.append('status', 'completed');
        form_data.append('nonce', pworkParams.nonce);
        $.ajax({
            url: pworkParams.ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            data: form_data,
            success: function (response) {
              if (response == 'error') {
                toastr.error(pworkParams.wrong, pworkParams.error);
                input.prop('checked', false);
              }
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    toastr.error(jqXHR.responseText, pworkParams.error);
                }else{
                    toastr.error(pworkParams.wrong, pworkParams.error);
                }
                input.prop('checked', false);
            }
        });
      } else {
        input.prop('checked', false);
      }
    } else {
      if (window.confirm(pworkParams.answer6)) {
        form_data.append('action', 'pworkUpdateChecklist');
        form_data.append('postid', postid);
        form_data.append('item', item);
        form_data.append('status', 'inprogress');
        form_data.append('nonce', pworkParams.nonce);
        $.ajax({
            url: pworkParams.ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            cache: false,
            data: form_data,
            success: function (response) {
              if (response == 'error') {
                toastr.error(pworkParams.wrong, pworkParams.error);
                input.prop('checked', true);
              }
            },
            error: function(jqXHR,error, errorThrown) {
                if(jqXHR.status&&jqXHR.status==400){
                    toastr.error(jqXHR.responseText, pworkParams.error);
                }else{
                    toastr.error(pworkParams.wrong, pworkParams.error);
                }
                input.prop('checked', true);
            }
        });
      } else {
        input.prop('checked', true);
      }
    }
  });

  // Join Project
  selector.on('click','.join-project',function(){
    var answer = window.confirm(pworkParams.answer7);
    if (answer) {
      var btn = $(this);
      btn.prop("disabled", true);
      var postid = $(this).attr('data-id');
      var form_data = new FormData();
      form_data.append("action", "pworkJoinProject");
      form_data.append("id", postid);
      form_data.append("nonce", pworkParams.nonce);
      $.ajax({
        url: pworkParams.ajaxurl,
        type: "POST",
        contentType: false,
        processData: false,
        cache: false,
        data: form_data,
        success: function (response) {
          if (response == 'done') {
            window.location.reload();
          } else {
            toastr.error(pworkParams.wrong, pworkParams.error);
            btn.prop("disabled", false);
          }
        },
        error: function (jqXHR, error, errorThrown) {
          if (jqXHR.status && jqXHR.status == 400) {
            toastr.error(jqXHR.responseText, pworkParams.error);
          } else {
            toastr.error(pworkParams.wrong, pworkParams.error);
          }
          btn.prop("disabled", true);
        },
      });
    }
  });

  // Project due date countdown
  if (selector.find('#pwork-project-countdown').length > 0 ) {
    updateDueCountdown();
  }

  function updateDueCountdown() {
    var countdownInterval = setInterval(updateDueCountdown, 1000);
    var targetDate = new Date(selector.find('#pwork-project-countdown').attr('data-due'));
    var targetTimestamp = targetDate.getTime();
    var now = new Date().getTime();
    var timeRemaining = targetTimestamp - now;
  
    var days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
    if (days < 10) {
      days = '0' + days;
    }

    var hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    if (hours < 10) {
      hours = '0' + hours;
    }

    var minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
    if (minutes < 10) {
      minutes = '0' + minutes;
    }

    var seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
    if (seconds < 10) {
      seconds = '0' + seconds;
    }
  
    if (timeRemaining <= 0) {
      clearInterval(countdownInterval);
      selector.find('#pwork-project-countdown').html('<div class="alert alert-warning m-0">' + selector.find('#pwork-due-countdown').attr('data-finished') + '</div>');
    } else {
      document.getElementById("due-days").textContent = days;
      document.getElementById("due-hours").textContent = hours;
      document.getElementById("due-minutes").textContent = minutes;
      document.getElementById("due-seconds").textContent = seconds;
    }
  }

  // Summernote Emoji Plugin
  (function(factory) {
    if (typeof define === 'function' && define.amd) {
      define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
      module.exports = factory(require('jquery'));
    } else {
      factory(window.jQuery);
    }
  }(function($) {
    $.extend($.summernote.plugins, {
      'emoji': function(context) {
        var self = this;
        var ui = $.summernote.ui;
  
        context.memo('button.emoji', function() {
          // create button
          var button = ui.buttonGroup([
            ui.button({
              className: 'dropdown-toggle',
              contents: '<span class="emoji">' + '😀' + '</span>',
              data: {
                toggle: 'dropdown'
              }
            }), ui.dropdown({
              className: 'dropdown-emoji',
              items: ['🙂','😀','🤣','😎','😔','😪','😱','😡','✋','👍','👎','👏','🙏','💯'],
              click: function(e) {
                e.preventDefault();
                console.log(e.target);
                context.invoke('editor.insertText', e.target.dataset.value);
            }
            })
          ]);
          var $emoji = button.render();
          return $emoji;
        });
      },
    });
  }));
  
  // Document Ready
  $(document).ready(function () {
    setNotifications();
    // Summernote Init
    var summernoteForum = ['#pwork-comment-content','#pwork-reply-content','#pwork-topic-content'];

    summernoteForum.forEach(function (element) {
      selector.find(element).summernote({
        height: 120,
        styleTags: ['p','h3','h4','h5','h6'],
        toolbar: [
        ['style', ['style']],
        ['font', ['bold','italic', 'underline', 'strikethrough', 'clear']],
        ['para', ['ul', 'ol']],
        ['insert', ['emoji','picture','link','hr']],
        ]
      });
    });

    selector.find('#pwork-project-content').summernote({
      height: 120,
      styleTags: ['p','h3','h4','h5','h6'],
      toolbar: [
      ['style', ['style']],
      ['font', ['bold','italic', 'underline', 'strikethrough', 'clear']],
      ['para', ['ul', 'ol']],
      ['insert', ['emoji','picture','link','hr']],
      ]
    });

    selector.find('.pwork-single-message-textarea').summernote({
      height: 100,
      minHeight:100,
      maxHeight:100,
      toolbar: [
      ['font', ['bold','italic', 'underline', 'strikethrough', 'clear']],
      ['para', ['ul', 'ol']],
      ['insert', ['emoji','picture','link']],
      ]
    });

    selector.find('#pwork-profile-bio-input').summernote({
      height: 120,
      styleTags: ['p','h3','h4','h5','h6'],
      toolbar: [
      ['style', ['style']],
      ['font', ['bold','italic', 'underline', 'strikethrough', 'clear']],
      ['para', ['ul', 'ol']],
      ['insert', ['emoji','link','hr']],
      ]
    });

    selector.find('#pwork-ann-content').summernote({
      height: 120,
      styleTags: ['p','h1','h2','h3','h4','h5','h6'],
      toolbar: [
      ['style', ['style']],
      ['font', ['bold','italic', 'underline', 'strikethrough', 'clear']],
      ['para', ['ul', 'ol']],
      ['insert', ['emoji','picture','link','hr']],
      ]
    });

    selector.find('#pwork-project-comment').summernote({
      height: 120,
      toolbar: [
      ['font', ['bold','italic', 'underline', 'strikethrough', 'clear']],
      ['para', ['ul', 'ol']],
      ['insert', ['emoji','picture','link']],
      ]
    });

    selector.find('#pwork-ann-comment').summernote({
      height: 120,
      toolbar: [
      ['font', ['bold','italic', 'underline', 'strikethrough', 'clear']],
      ['para', ['ul', 'ol']],
      ['insert', ['emoji','picture','link']],
      ]
    });

    // Init repeatable fields
    selector.find("#pwork-profile-icons-input").repeatable({
      template: '#pwork-user-icons-template',
      addTrigger: '.pwork-add-user-icon',
      deleteTrigger: '.pwork-delete-user-icon'
    });

    selector.find("#pwork-project-tasks-input").repeatable({
      template: '#pwork-tasks-template',
      addTrigger: '.pwork-add-task',
      deleteTrigger: '.pwork-delete-task'
    });

  });
  
})(jQuery);
