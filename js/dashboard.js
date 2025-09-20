(function ($) {
    "use strict";
    var selector = $("#pwork");
    var minicalendar = '';
    var defaultWidgets = '';

    // Masonry
    var dashboardGrid = selector.find('#pworkDashboard').masonry({percentPosition: true,columnWidth: '.grid-sizer'});

    // Widgets Setup
    if (localStorage.getItem('pwork-dashboard') !== null) {
        defaultWidgets = localStorage.getItem('pwork-dashboard');
        selector.find('.pwork-widget-settings-list .form-check-input').each(function(index, value) {
            var key = $(this).attr('data-key');
            var widget = $('#' + $(this).attr('data-widget'));
            if (defaultWidgets.includes(key)) {
                $(this).prop('checked', true);
            } else {
                $(this).prop('checked', false);
                widget.addClass('d-none');
            }
        });
        dashboardGrid.masonry();
    } else {
        selector.find('.pwork-widget-settings-list .form-check-input').prop('checked', true);
        defaultWidgets = [];
        selector.find('.pwork-widget-settings-list .form-check-input').each(function(index, value) {
            var key = $(this).attr('data-key');
            if ($(this).is(':checked')) {
                defaultWidgets.push(key);
            }
        });
        localStorage.setItem("pwork-dashboard", defaultWidgets);
    }

    // Show/Hide Widgets
    selector.on('change','.pwork-widget-settings-list .form-check-input',function(){
        var defaultWidgets = [];
        var widget = $('#' + $(this).attr('data-widget'));
        if ($(this).is(':checked')) {
            widget.removeClass('d-none');
            minicalendar.updateSize();
        } else {
            widget.addClass('d-none');
        }
        setTimeout(function(){ 
            dashboardGrid.masonry();
            selector.find('.pwork-widget-settings-list .form-check-input').each(function(index, value) {
                var key = $(this).attr('data-key');
                if ($(this).is(':checked')) {
                    defaultWidgets.push(key);
                }
            });
            localStorage.setItem("pwork-dashboard", defaultWidgets);
        }, 100);
    });

    // Reset Dashboard
    selector.on('click','#pwork-reset-dashboard',function(){
        localStorage.removeItem('pwork-dashboard');
        localStorage.removeItem('pwork-dashboard-order');
        window.location.reload();
    });

    // Calendar Widget
    document.addEventListener("DOMContentLoaded", function () {
        var minicalendarEl = document.getElementById("mini-calendar");
        if (typeof minicalendarEl != "undefined" && minicalendarEl != null) {
            minicalendar = new FullCalendar.Calendar(minicalendarEl, {
            headerToolbar: false,
            stickyHeaderDates: false,
            initialView: "dayGridMonth",
            timeZone: "UTC",
            locale: pworkParams.calendarLocale,
            themeSystem: "bootstrap5",
            eventColor: pworkParams.eventColor,
            navLinks: true,
            editable: false,
            dayMaxEvents: true,
            events: pworkCalendarEvents,
            });
            minicalendar.render();
        }
    });

    // Document Ready
    $(document).ready(function () {
        selector.find('#pwork-dashboard-page').imagesLoaded( function() {
            dashboardGrid.masonry();
            var sortable = new Sortable(pworkDashboard, {
                group: 'pwork-dashboard-order',
                handle: '.bx-move',
                animation: 150,
                ghostClass: 'pwork-ghost',
                onChange: function (evt) {
                    dashboardGrid.masonry('destroy');
                    dashboardGrid = selector.find('#pworkDashboard').masonry({percentPosition: true,columnWidth: '.grid-sizer'});
                },
                store: {
                    get: function (sortable) {
                        var order = localStorage.getItem(sortable.options.group.name);
                        setTimeout(function(){ 
                            dashboardGrid.masonry('destroy');
                            dashboardGrid = selector.find('#pworkDashboard').masonry({percentPosition: true,columnWidth: '.grid-sizer'});
                            setTimeout(function(){ 
                                selector.find('#pwork-page-loader').remove();
                            }, 100);
                        }, 100);
                        return order ? order.split('|') : [];
                    },
                    set: function (sortable) {
                        var order = sortable.toArray();
                        localStorage.setItem(sortable.options.group.name, order.join('|'));
                    }
                }
            });
        });
    });
})(jQuery);