(function($) {
    'use strict';
    var selector = $('#pwork');
    var buttons = ['#pwork-ann-submit','.pwork-delete-ann','#pwork-topic-submit','.pwork-delete-topic','.tasks-list input','.add-project-comment','#pwork-project-submit','.pwork-delete-project','#pwork-event-submit','.pwork-delete-event','.pwork-send-message','#pwork-file-upload-btn','.pwork-delete-file','.remove-from-table-contacts','.pwork-save-settings','.add-ann-comment','#pwork-add-comment-btn','.pwork-delete-comment','#pwork-add-reply-btn','.join-project','#pwork-file-edit'];
    var buttons2 = ['.remove-from-contacts','.add-to-contacts'];
    buttons.forEach(function (element) {
        selector.find(element).click(function(){
            toastr.warning(pworkParams.demoContent, pworkParams.demoTitle);
            $(this).prop('disabled', true);
            return false;
        });
    });
    buttons2.forEach(function (element) {
        selector.find(element).click(function(){
            toastr.warning(pworkParams.demoContentAlt, pworkParams.demoTitle);
            $(this).prop('disabled', true);
            return false;
        });
    });
    selector.find('.pwork-file-field .btn').click(function(){
        selector.find('#pwork-file-upload').prop('disabled', true);
        return false;
    });
    selector.find('#pwork-upload-avatar-label').click(function(){
        selector.find('#pwork-upload-avatar-label').addClass('pe-none');
        toastr.warning(pworkParams.demoContent, pworkParams.demoTitle);
        return false;
    });
})(jQuery);