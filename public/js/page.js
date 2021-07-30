$(function() {

    /**
     * Make ajax request for forms with ajax_form class
     */
    $(document).on('submit', '.ajax_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: data,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Call successCallback for submitted form
                    form.trigger('successCallback', [data]);
                }
                // Replace form with errors
                else {
                    if (data.html) {
                        $(e.target).parent().html(data.html);
                    }
                }
            }
        });
    });

    $(document).on('click', '.additional_options_menu', function(e) {
        if ($(this).hasClass('menu_open')) {
            $(this).removeClass('menu_open');
            $('.additional_options.additional_options_cloned_menu').remove();
        } else {
            $('.additional_options_menu').removeClass('menu_open');
            $(this).addClass('menu_open');

            //remove any popups that are currently open
            $('.additional_options.additional_options_cloned_menu').remove();

            var additional_options = $(this).find('.additional_options');
            if (additional_options) {
                var additional_options_clone = additional_options.clone();
                additional_options_clone.addClass('additional_options_cloned_menu');
                additional_options_clone.appendTo('body');
                additional_options_clone.find('.popup_link').removeClass('popup_link');
                additional_options_clone.find('.ajax_action').removeClass('ajax_action');

                var position = $(this).offset();

                var ul = additional_options_clone.find('ul');
                var bottom_offset = position.top + ul.height();

                if (bottom_offset > ($(window).scrollTop() + $(window).height()) - 20) {
                    //if close to bottom of window, move the popup to be aligned at the bottom with the ellipses
                    additional_options_clone.offset({
                        top: position.top - additional_options_clone.find('ul').height() + 8,
                        left: position.left - additional_options_clone.find('ul').width() - 4
                    });
                } else {
                    additional_options_clone.offset({
                        top: position.top,
                        left: position.left - additional_options_clone.find('ul').width() - 4
                    });
                }
            }
        }
    });

    $(document).on('click', '.additional_options_menu a, .additional_options_cloned_menu.additional_options a', function(e) {
        if (!$(e.target).parents('.actions').length) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
        }

        if ($(this).is('a') && (!$(this).closest('.additional_options_menu').hasClass('actions') || $(this).hasClass('popup_link'))) {
            //get which li item was selected
            var action_selected = $('.additional_options_cloned_menu.additional_options ul li').index($(this).closest('li'));

            if (action_selected >= 0) {
                //click the same li item but in the hidden additional options list within the table
                var hidden_option_element = $('.additional_options_menu.menu_open ul li:eq(' + action_selected + ') a').get('0');

                hidden_option_element.click();
                $('.additional_options_cloned_menu.additional_options').remove();
            }
        }

        $('.additional_options_menu').removeClass('menu_open');
    });

    $(document).on('mousedown', function(e) {

        /* if the clicked element is not the menu */
        if (!$(e.target).hasClass('additional_options_menu') && !$(e.target).parents('.additional_options_menu').length && !$(e.target).hasClass('additional_options') && !$(e.target).parents('.additional_options').length) {
            $('.additional_options_menu').removeClass('menu_open');
            $('.additional_options_cloned_menu.additional_options').remove();
        }

        if ($('#left_menu .subnav_container:visible').length && !$(e.target).closest('#left_menu').length) {
            left_menu.remove_selected();
        }
    });
});