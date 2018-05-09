(function( $ ) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length,c.length);
            }
        }
        return "";
    }

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+ d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }

    function webShims() {
        // Make Firefox  work with 'date' input types
        webshims.setOptions('waitReady', false);
        webshims.setOptions('forms-ext', {types: 'date time datetime-local'});
        webshims.polyfill('forms forms-ext');
        webshims.activeLang('en-GB');
    }

    function adminConfig() {
        // This stuff should only be executed on the Meeting Support config page
        if ($('body').hasClass('toplevel_page_meeting-support')) {
            // Configure variables to use in later statements
            var mps_meeting_logo_url = $("input[name='mps_meeting_logo_url']");
            var mps_meeting_timezone = $("select[name='mps_meeting_timezone']");
            var mps_meeting_timezone_short = $("#mps_meeting_timezone_short");
            var mps_meeting_increments = $("input[name='mps_meeting_increments']");
            var mps_meeting_increments_value = $(".mps_meeting_increments_value");
            var create_pcss_area = $('#btn-create-pcss-area');
            var expand_bios = $('#expand-wg-chair-bios');
            var collapse_bios = $('#collapse-wg-chair-bios');
            var flush_wg_chair_bios = $('#btn-flush-cache-wg-chairs-area');
            var create_wg_chair_bios = $('#btn-create-wg-chairs-area');
            // Check to see if the URL specified for the meeting logo is good.
            mps_meeting_logo_url.keyup(function() {
                // Make a GET request
                $.get( $(this).val(), function() {
                    // If successful, give it a green background
                    mps_meeting_logo_url.css('background-color', 'green');
                })
                .fail(function() {
                    // If fail, give it a red background
                    mps_meeting_logo_url.css('background-color', 'red');
                })
            });

            mps_meeting_timezone.select2();

            mps_meeting_timezone.change(function() {
                mps_meeting_timezone_short.val('Save to update...');
            });

            // Catch slider change
            mps_meeting_increments.change(function() {
                mps_meeting_increments_value.html($(this).val());
            });

            // Fire to make above event happen on page load
            mps_meeting_increments.change();

            create_pcss_area.click(function() {
                // Go through the checked boxes and create pages for those which we can
                var pages_to_create = [];
                $('.checkbox-create-pcss-area:checked').each(function() {
                    pages_to_create.push($(this).data('path'));
                });

                var data = {
                    'action': 'ms_create_pcss_area',
                    'pages': pages_to_create
                };

                $.post(ajaxurl, data, function(response) {
                    if (response.success == true) {
                        location.reload();
                    }
                }, "json");

            })

            flush_wg_chair_bios.click(function() {
                var data = {
                    'action': 'ms_create_wg_chair_bios',
                    'flush_only': true
                };

                $.post(ajaxurl, data, function(response) {
                    if (response.success == true) {
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                }, "json");
            });

            create_wg_chair_bios.click(function() {

                var data = {
                    'action': 'ms_create_wg_chair_bios',
                    'flush_only': false
                };

                $.post(ajaxurl, data, function(response) {
                    if (response.success == true) {
                        location.reload();
                    } else {
                        alert(response.data);
                    }
                }, "json");

            });

            expand_bios.click(function() {
                $('#wg-chairs-pages').slideDown();
                expand_bios.hide();
                collapse_bios.show();
            });

            collapse_bios.click(function() {
                $('#wg-chairs-pages').slideUp();
                collapse_bios.hide();
                expand_bios.show();
            });

        }
    }

    function presentationRatingsConfig() {
        if ($('body').hasClass('meeting-support_page_meeting-support-presentation-ratings')) {

            $('#btnRandomRater').click(function() {

                var data = {
                    'action': 'ms_pick_random_rater'
                };

                $.post(ajaxurl, data, function(response) {
                    $('#random-rater-winner').html("<p>Name: " + response.name + "<br>Email: " + response.email + "</p>");
                    tb_show('', '#TB_inline?height=200&amp;width=405&amp;inlineId=random-rater-winner&amp;modal=true",null', false);

                }, "json");

            });


            // Enable dataTables
            $('#presentationRatingsTable').DataTable({
                "bPaginate": false
            });

        }

    }

    function videoConfig() {

        if ($('body').hasClass('meeting-support_page_meeting-support-videos')) {

            // Enable dataTables
            $('#video-admin-table').DataTable({
                "bPaginate": false,
                "order": [[ 0, "desc" ]]
            });

            function drawSessionSlots(slot_id = 0) {
                var data = {
                    'action': 'ms_get_session_slots',
                    'session_id': $('select[name="presentation_session"]').val()
                };

                $.post(ajaxurl, data, function(response) {

                    $('select[name="presentation_slot"]').empty();

                    $('select[name="presentation_slot"]').append('<option value="0">Other</option>');

                    $.each(response, function(index, slot) {
                        if (slot.id == slot_id) {
                            $('select[name="presentation_slot"]').append('<option selected="selected" value="' + slot.id + '">' + slot.title + '</option>');
                        } else {
                            $('select[name="presentation_slot"]').append('<option value="' + slot.id + '">' + slot.title + '</option>');
                        }
                    });

                },'json');

            }

            drawSessionSlots();


            $('.btn-edit-video').click(function() {
                var data = {
                    'action': 'ms_get_video',
                    'video_id': $(this).data('videoId')
                }

                $.post(ajaxurl, data, function(response) {
                    $('input[name="video_id"]').val(response.id);
                    $('input[name="presenter_name"]').val(response.presenter_name);
                    $('input[name="presentation_title"]').val(response.presentation_title);
                    $('input[name="status"]').val(response.status);
                    $('input[name="created"]').val(response.created);
                    $('select[name="room"]').val(response.room);
                    $('input[name="video_url"]').val(response.video_url);
                    $('select[name="presentation_session"]').val(response.session_id);
                    $('input[name="incomplete_video"]').prop('checked', response.is_incomplete);
                    drawSessionSlots(response.presentation_id);
                },'json');

            });


            $('#accept_danger').change(function() {
                if ($(this).is(':checked')) {
                    $('input[name="presenter_name"]').attr('readonly', false);
                    $('input[name="presentation_title"]').attr('readonly', false);
                    $('input[name="created"]').attr('readonly', false);
                } else{
                    $('input[name="presenter_name"]').attr('readonly', true);
                    $('input[name="presentation_title"]').attr('readonly', true);
                    $('input[name="created"]').attr('readonly', true);
                }

            });

            $('#delete-video').click(function(event) {
                if ( ! confirm('Are you sure you want to delete this video') ) {
                    event.preventDefault();
                    return false;
                }

            });


            $('#add-video').click(function() {

                Number.prototype.padLeft = function(base,chr){
                    var  len = (String(base || 10).length - String(this).length)+1;
                    return len > 0? new Array(len).join(chr || '0')+this : this;
                }

                var d = new Date,
                    dformat = [ d.getFullYear().padLeft(),
                                (d.getMonth()+1).padLeft(),
                                d.getDate()].join('-')+
                                ' ' +
                              [ d.getHours().padLeft(),
                                d.getMinutes().padLeft(),
                                d.getSeconds().padLeft()].join(':');

                $('input[name="video_id"]').val('0');
                $('input[name="presenter_name"]').val('');
                $('input[name="presentation_title"]').val('');
                $('input[name="status"]').val('FINISHED');
                $('input[name="created"]').val(dformat);
                $('input[name="incomplete_video"]').prop('checked', false);

            });

        }
    }

    function pcElectionsConfig() {
        if ($('body').hasClass('meeting-support_page_meeting-support-pc-elections')) {

            $('.delete-candidate').click(function() {
                var candidate_id = $(this).data('candidateId');
                var candidate_row = $(this).parent('tr');
                if (confirm("Are you sure you want to delete this candidate?")) {

                    var data = {
                        'action': 'ms_delete_candidate',
                        'candidate_id': candidate_id
                    };

                    $.post(ajaxurl, data, function(response) {
                        if (response.success == 1) {
                            location.reload();
                        }
                    }, 'json');

                }
            })

        }
    }

    function slotConfig() {

        // This stuff should only be executed on the Meeting Support slot config page
        if ($('body').hasClass('meeting-support_page_meeting-support-slots')) {

            // Configure variables to use in later statements
            var btn_add_slot = $('#add_slot');
            var btn_edit_slot = $('.edit_slot');
            var btn_move_up = $('.move_up');
            var btn_move_down = $('.move_down');
            var btn_delete_slot = $('.delete_slot');
            var select_session_id = $('#session_select');
            var select_session_id_modal = $('#session_select_modal');
            var text_slot_title = $('#slot_title');
            var hidden_slot_id = $("input[name='slot_id']");
            var slot_preview = $('#slot_preview');
            var slot_table = $('#slot_management');
            var select_slot_parent_id = $('#slot_parent_id');
            var check_slot_ratable = $('#slot_ratable');
            var current_session;

            // Empty the content
            tmce_setContent('', 'slot_content');

            // If there's a cookie for which slot we're showing, change the select_session_id to that.
            var current_slot = getCookie('mps_slots_session_id');
            if (current_slot) {
                select_session_id.val(current_slot);
            }

            // Draw the view for for the currently selected session
            drawSession();

            // Adding a new slot
            btn_add_slot.click(function() {
                current_session = '';
                hidden_slot_id.val('');
                $('#session_select_modal').val($('#session_select').val());
                $('#slot_title').val('');
                tinymce.remove();
                tmce_setContent('', 'slot_content');
                select_slot_parent_id.val('0');
                $('#slot_ratable').prop('checked', true);
            });

            // Editing an existing slot
            btn_edit_slot.live('click', function() {

                // Open the Thickbox modal
                tb_show("","#TB_inline?width=800&height=570&inlineId=edit_slot_thickbox", null);

                // Load the slot information to populate into the modal
                var slot_id = $(this).parent().data('slotId');
                populateSlotModal(slot_id);

            });

            // Moving a slot down
            btn_move_down.live('click', function() {

                // Don't do anything is this button has a disabled class
                if ($(this).hasClass('disabled')) {
                    return false;
                }

                $('body').addClass("mps_loading");

                // If we're moving this slot down, we want to swap it with the slot below this one
                // Are we dealing with a parent slot or a child slot?
                if ($(this).parent().parent().hasClass('is_parent')) {
                    var slot_below_id = $(this).parent().parent().nextAll('.is_parent').first().children('td').eq(1).data('slotId');
                    var slot_above_id = $(this).parent().data('slotId');
                } else {
                    var slot_below_id = $(this).parent().parent().nextAll('.is_child').first().children('td').eq(1).data('slotId');
                    var slot_above_id = $(this).parent().data('slotId');
                }

                var data = {
                    'action': 'ms_swap_slots',
                    'slot_1': slot_below_id,
                    'slot_2': slot_above_id
                };

                $.post(ajaxurl, data, function(response) {
                    drawSession();
                });

            });

            // Moving a slot up
            btn_move_up.live('click', function() {


                // Don't do anything is this button has a disabled class
                if ($(this).hasClass('disabled')) {
                    return false;
                }

                $('body').addClass("mps_loading");

                // If we're moving this slot up, we want to swap it with the slot above this one
                // Are we dealing with a parent slot or a child slot?
                if ($(this).parent().parent().hasClass('is_parent')) {
                    var slot_above_id = $(this).parent().parent().prevAll('.is_parent').first().children('td').eq(1).data('slotId');
                    var slot_below_id = $(this).parent().data('slotId');
                } else {
                    var slot_above_id = $(this).parent().parent().prevAll('.is_child').first().children('td').eq(1).data('slotId');
                    var slot_below_id = $(this).parent().data('slotId');
                }

                var data = {
                    'action': 'ms_swap_slots',
                    'slot_1': slot_below_id,
                    'slot_2': slot_above_id
                };

                $.post(ajaxurl, data, function(response) {
                    drawSession();
                });

            });


            // Deleting a slot
            btn_delete_slot.live('click', function() {

                if ( ! confirm('Are you sure you want to delete this slot? It will also delete any children of this slot') ) {
                    return false;
                }

                $('body').addClass("mps_loading");

                var data = {
                    'action': 'ms_delete_slot',
                    'slot_id': $(this).parent().data('slotId')
                };

                $.post(ajaxurl, data, function(response) {
                    drawSession();
                });

            });

            // Defocusing a Slot Title, should we pre-populate some content?
            text_slot_title.blur(function() {
                if (tmce_getContent() == '') {
                    tmce_setContent('<b>' + $(this).val() + '</b><br>');
                }
            });

            // Change of session selection
            select_session_id.change(function() {
                setCookie('mps_slots_session_id', $(this).val());
                select_session_id_modal.val($(this).val());
                drawSession();
            });

            // Change of session selection from inside modal
            select_session_id_modal.change(function() {
                select_session_id.val($(this).val());
                drawSession();
            });

        }

        // Helper functions for slotConfig()
        function drawSession() {

            current_session = select_session_id.val();

            $('body').addClass("mps_loading");

            $('#shortcode_hinter').html('[session_slots custom_title="" session="' + current_session + '"]');


            var data = {
                'action': 'ms_do_shortcode',
                'shortcode': '[session_slots session=' + current_session + ']'
            };

            $.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);
                slot_preview.html(response);
                $('body').removeClass("mps_loading");
            });

            var data = {
                'action': 'ms_get_session_slots',
                'session_id': current_session
            };

            $.post(ajaxurl, data, function(response) {

                var slots = $.parseJSON(response);
                var last_parent;
                slot_table.empty();

                // Reverse the array to get the first child
                $.each(slots.reverse(), function(index1, slot1) {
                    if (slot1.parent_id == "0") {
                        last_parent = slot1;
                        return false;
                    }
                });

                // Reverse the array back to normal
                slots.reverse();

                // Wipe the current list of parent slots to select, build it later on
                select_slot_parent_id.empty();
                select_slot_parent_id.append('<option value="0">None</option>');

                // Start displaying the slots
                $.each(slots, function(index, slot) {
                    // We should only be grabbing slots with no parent at this point
                    if (slot.parent_id == "0") {

                        // If it's a parent, we want to put it in the list of parents to select from in the Slot add/edit modal
                        select_slot_parent_id.append('<option value="' + slot.id + '">' + slot.title + '</option>');

                        // Lets set some flags so we know what to render
                        var move_up = true;
                        var move_down = true;
                        if (index == 0) {
                            move_up = false;
                        }
                        if (last_parent.id == slot.id) {
                            move_down = false;
                        }
                        slot_table.append(drawSlotControls(move_up, move_down, slot));
                        var children = [];

                        // Create a list of children
                        $.each(slots, function(index_1, slot_1) {
                            if (slot_1.parent_id == slot.id) {
                                children.push(slot_1);
                            }
                        });

                        // Do we have any children? Let's do something with them
                        if (children.length) {

                            slot_table.append('<tr><td class="childseperator" colspan="2"></td></tr>');

                            $.each(children, function(index_ch, child) {
                                var move_up = true;
                                var move_down = true;
                                if (index_ch == 0) {
                                    move_up = false;
                                }
                                if (children.length -1 == index_ch) {
                                    move_down = false;
                                }
                                slot_table.append(drawSlotControls(move_up, move_down, child, true));
                            });

                            slot_table.append('<tr><td class="childseperator" colspan="2"></td></tr>');

                        }

                    }
                });

                $('body').removeClass("mps_loading");

            });

        }

        function populateSlotModal(slot_id) {

            $('body').addClass("mps_loading");


            var data = {
                'action': 'ms_get_slot_info',
                'slot_id': slot_id
            };

            $.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);
                $('body').removeClass("mps_loading");
                text_slot_title.val(response.title);
                tmce_setContent(response.content);
                $('#session_select_modal').val(response.session_id);
                select_slot_parent_id.val(response.parent_id);
                check_slot_ratable.prop('checked', false);
                if (response.ratable == '1') {
                    check_slot_ratable.prop('checked', true);
                }
                hidden_slot_id.val(slot_id);
            });
        }

        function drawSlotControls(move_up, move_down, slot, is_child = false) {
            if (is_child) {
                return '<tr class="is_child"><td><span class="dashicons dashicons-arrow-right"></span> ' + slot.title + '</td><td data-slot-id="' + slot.id + '" class="slot_controls"><button class="' + ((move_up == false) ? 'disabled ' : '') + 'button button-primary button-small move_up">&uarr;</button> <button class="' + ((move_down == false) ? 'disabled ' : '') + 'button button-primary button-small move_down">&darr;</button> <button class="button button-primary button-small edit_slot"><i class="fa fa-pencil"></i></button> <button class="button button-primary button-small delete_slot"><i class="fa fa-trash"></i></button></td></tr>';
            } else {
                return '<tr class="is_parent"><td>' + slot.title + '</td><td data-slot-id="' + slot.id + '" class="slot_controls"><button class="' + ((move_up == false) ? 'disabled ' : '') + 'button button-primary button-small move_up">&uarr;</button> <button class="' + ((move_down == false) ? 'disabled ' : '') + 'button button-primary button-small move_down">&darr;</button> <button class="button button-primary button-small edit_slot"><i class="fa fa-pencil"></i></button> <button class="button button-primary button-small delete_slot"><i class="fa fa-trash"></i></button></td></tr>';
            }
        }

        function addNewSlot(session_id) {
            alert();
        }

        function tmce_getContent(editor_id, textarea_id) {
            if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
            if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

            if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
                return tinyMCE.get(editor_id).getContent();
            } else {
                return jQuery('#'+textarea_id).val();
            }
        }

        function tmce_setContent(content, editor_id, textarea_id) {
            if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
            if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

            if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
                return tinyMCE.get(editor_id).setContent(content);
            } else {
                return jQuery('#'+textarea_id).val(content);
            }
        }

        function tmce_focus(editor_id, textarea_id) {
            if ( typeof editor_id == 'undefined' ) editor_id = wpActiveEditor;
            if ( typeof textarea_id == 'undefined' ) textarea_id = editor_id;

            if ( jQuery('#wp-'+editor_id+'-wrap').hasClass('tmce-active') && tinyMCE.get(editor_id) ) {
                return tinyMCE.get(editor_id).focus();
            } else {
                return jQuery('#'+textarea_id).focus();
            }
        }

    }


    function speakersConfig() {
        if (! $('body').hasClass('meeting-support_page_meeting-support-speakers')) {
            return;
        }

        // Enable dataTables
        $('#speakersTable').DataTable({
            "bPaginate": false,
            "aoColumns": [
            null,
            null,
            null,
            null,
            null,
            { "bSearchable": false, "bSortable": false }
            ],
            "order": [[ 3, "desc"]]
        });


        $('.edit-speaker-bio').click(function() {

            // Set values to make the post acceptable
            var speaker_id = $(this).data('speakerId');
            var bio_language = $(this).data('bioLanguage');

            $("input[name='speaker_id']").val(speaker_id);
            $("input[name='bio_language']").val(bio_language);
            $("textarea[name='speaker_bio']").val('');

            if (speaker_id > 0) {
                // We are editing an existing speaker
                if (bio_language != '') {
                    // We are editing an existing bio
                    // Enable spinner
                    $('body').addClass("mps_loading");

                    // Populate the bio textarea
                    var data = {
                        'action': 'ms_get_speaker',
                        'speaker_id': speaker_id
                    };

                    $.post(ajaxurl, data, function(response) {
                        var bio = response.bio_texts[bio_language];
                        var bio_draft = response.bio_texts_draft[bio_language];
                        if (typeof bio_draft !== 'undefined') {
                            $("textarea[name='speaker_bio']").val(bio_draft);
                            $('#bio-draft-warning').show();
                        } else {
                            $("textarea[name='speaker_bio']").val(bio);
                            $('#bio-draft-warning').hide();
                        }
                        $('body').removeClass("mps_loading");
                    }, "json");

                } else {
                    // We are adding a new bio
                    $("input[name='bio_language']").val('');
                }
            } else {
                // We are adding a new speaker
            }
        });

        $('.edit-speaker').click(function() {

            var speaker_id = $(this).data('speakerId');
            // Clear fields
            $("input[name='speaker_name']").val('');
            $("input[name='speaker_uuid']").val('');
            $("input[name='speaker_slug']").val('');
            $("input[name='speaker_tags']").val('');
            $("input[name='speaker_id']").val(speaker_id);

            if (speaker_id > 0) {
                // We are editing an existing speaker
                //
                // Enable spinner
                $('body').addClass("mps_loading");

                var data = {
                    'action': 'ms_get_speaker',
                    'speaker_id': speaker_id
                };

                $.post(ajaxurl, data, function(speaker) {
                    // Remove spinner
                    $('body').removeClass("mps_loading");
                    // Update values
                    $("input[name='speaker_name']").val(speaker.name);
                    $("input[name='speaker_uuid']").val(speaker.uuid);
                    $("input[name='speaker_tags']").val(speaker.tags);
                    $("input[name='speaker_slug']").val(speaker.slug);
                    if (speaker.allowed === '1') {
                        $("input[name='speaker_allowed']").prop("checked", true);
                    } else {
                        $("input[name='speaker_allowed']").prop("checked", false);
                    }
                }, "json");

            }
        });

        $('.delete-speaker').click(function(event) {
            var speaker_id = $(this).data('speakerId');
            event.preventDefault();

            if (confirm("Are you sure you want to delete this speaker?")) {

                var data = {
                    'action': 'ms_delete_speaker',
                    'speaker_id': speaker_id
                };

                $.post(ajaxurl, data, function(response) {
                    if (response.success == 1) {
                        location.reload();
                    }
                }, 'json');

            }



        })
    }

    function userConfig() {
        // This stuff should only be executed on the Meeting Support user config page
        if ($('body').hasClass('meeting-support_page_meeting-support-users')) {

            // Enable dataTables
            $('#usersTable').DataTable({
                "bPaginate": false,
                "aoColumns": [
                null,
                null,
                null,
                null,
                null,
                { "bSearchable": false, "bSortable": false }
                ],
                "order": [[ 3, "desc"]]
            });

            // Inputs
            var user_uuid = $("input[name='user_uuid']");
            var user_name = $("input[name='user_name']");
            var user_email = $("input[name='user_email']");

            // Checkboxes
            var is_active = $("input[name='is_active']");

            // Buttons
            var add_user = $(".btnAddUser");
            var edit_user = $(".btnEditUser");
            var delete_button = $("#delete");
            var password_reset_button = $(".btnResetUserPassword");

            password_reset_button.click(function(event) {
                if (! confirm( "Are you sure you want to reset the password for this user?" ) ) {
                    event.preventDefault();
                }
            });

            delete_button.click(function(event) {
                if (! confirm( "Are you sure you want to delete this user?" ) ) {
                    event.preventDefault();
                }
            });


            // Populate user data when editing an existing user
            edit_user.click(function() {

                // Show delete button
                delete_button.show();

                $('body').addClass("mps_loading");

                var userid = $(this).data('userId');
                var data = {
                    'action': 'ms_get_user',
                    'user_id': userid
                };

                $.post(ajaxurl, data, function(response) {
                    var user = $.parseJSON(response);
                    user_uuid.val(user.uuid)
                    user_name.val(user.name);
                    user_email.val(user.email);
                    if (user.is_active === '1') {
                        is_active.prop("checked", true);
                    } else {
                        is_active.prop("checked", false);
                    }
                    $('body').removeClass("mps_loading");
                });
            });

            add_user.click(function() {

                // Hide delete button
                delete_button.hide();

                user_uuid.val('0');
                user_name.val('');
                user_email.val('');
                is_active.prop("checked", true);

            });


        }
    }

    function agendaConfig() {

        // This stuff should only be executed on the Meeting Support agenda config page
        if ($('body').hasClass('meeting-support_page_meeting-support-agenda')) {
            // Configure variables to use in later statements

            // Inputs
            var session_id = $("input[name='session_id']");
            var session_name = $("input[name='session_name']");
            var session_start = $("input[name='session_start']");
            var session_end = $("input[name='session_end']");
            var session_room = $("select[name='session_room']");
            var session_url = $("input[name='session_url']");
            var sponsor_date = $("input[name='date']");

            // Checkboxes
            var is_intermission = $("input[name='is_intermission']");
            var is_rateable = $("input[name='is_rateable']");
            var is_streamed = $("input[name='is_streamed']");
            var is_social = $("input[name='is_social']");
            var hide_title = $("input[name='hide_title']");

            // Buttons
            var add_session = $(".btnAddSession");
            var edit_session = $(".btnEditSession");
            var delete_button = $("#delete-session");
            var edit_sponsor = $(".btnEditAgendaSponsor");
            var submit_session = $('#submit-session');
            var edit_chat = $('.edit-chat-log');
            var edit_steno = $('.edit-steno-log');

            edit_chat.click(function() {
                session_id.val($(this).data('sessionId'));
                $('body').addClass("mps_loading");

                var data = {
                    'action': 'ms_get_chat_log',
                    'session_id': $(this).data('sessionId')
                };

                $.post(ajaxurl, data, function(response) {
                    $("textarea[name='chat_content']").val(response);
                    tb_show("","#TB_inline?width=800&height=460&inlineId=editChatThickbox", null);
                    $('body').removeClass("mps_loading");

                }, "json");

            });

            edit_steno.click(function() {
                session_id.val($(this).data('sessionId'));
                $('body').addClass("mps_loading");

                var data = {
                    'action': 'ms_get_steno_log',
                    'session_id': $(this).data('sessionId')
                };

                $.post(ajaxurl, data, function(response) {
                    $("textarea[name='steno_content']").val(response);
                    tb_show("","#TB_inline?width=800&height=460&inlineId=editStenoThickbox", null);
                    $('body').removeClass("mps_loading");

                }, "json");

            });

            // Delete button clicked, make sure they want to do this before submitting the form
            delete_button.click(function(event) {
                if (!confirm( "Are you sure you want to delete this session?") ) {
                    event.preventDefault();
                }
            });

            // Fire to make above event happen on page load
            session_start.focusout(function() {
                if (session_end.val() == '') {
                    session_end.val($(this).val());
                }
            });

            edit_sponsor.click(function() {

                $('body').addClass("mps_loading");

                var date = $(this).data('date');
                sponsor_date.val(date);

                var data = {
                    'action': 'ms_get_day_sponsor',
                    'date': date
                };

                $.post(ajaxurl, data, function(response) {
                    $("input[name='sponsor_title']").val(response.title);
                    $("input[name='sponsor_title_url']").val(response.title_url);
                    $("textarea[name='sponsor_body']").val(response.body);
                    $('body').removeClass("mps_loading");

                }, "json");


            });

            // Auto populate session start with the meeting start, wipe everything clean
            add_session.click(function() {

                // Hide delete button
                delete_button.hide();

                session_id.val('0');
                session_name.val('');
                session_start.val(meeting_start);
                session_end.val('');
                session_room.val('');
                session_url.val('');
                is_intermission.prop("checked", false);
                is_rateable.prop("checked", true);
                is_streamed.prop("checked", false);
                is_social.prop("checked", false);
                hide_title.prop("checked", false);

            });

            // Populate session data when editing an existing session
            edit_session.click(function() {

                // Show delete button
                delete_button.show();

                $('body').addClass("mps_loading");
                var sessionid = $(this).data('sessionId');
                var data = {
                    'action': 'ms_get_session',
                    'session_id': sessionid
                };
                $.post(ajaxurl, data, function(response) {
                    var session = $.parseJSON(response);
                    session_id.val(session.id);
                    session_name.val(session.name);
                    session_start.val(session.start_time.replace(' ', 'T'));
                    session_end.val(session.end_time.replace(' ', 'T'));
                    session_room.val(session.room);
                    session_url.val(session.url);
                    if (session.is_intermission === '1') {
                        is_intermission.prop("checked", true);
                    } else {
                        is_intermission.prop("checked", false);
                    }
                    if (session.is_rateable === '1') {
                        is_rateable.prop("checked", true);
                    } else {
                        is_rateable.prop("checked", false);
                    }
                    if (session.is_streamed === '1') {
                        is_streamed.prop("checked", true);
                    } else {
                        is_streamed.prop("checked", false);
                    }
                    if (session.is_social === '1') {
                        is_social.prop("checked", true);
                    } else {
                        is_social.prop("checked", false);
                    }
                    if (session.hide_title === '1') {
                        hide_title.prop("checked", true);
                    } else {
                        hide_title.prop("checked", false);
                    }
                    $('body').removeClass("mps_loading");

                });


            });

        }

    }

    function PCUserConfig() {

        if ($('body').hasClass('meeting-support_page_meeting-support-pc_users')) {

            // Define buttons
            var delete_user = $('.btnDeletePCUser');
            var update_user = $('.btnEditPCUser');

            $('select[name=new_user_email]').select2();

            // Deleting a user from the table
            delete_user.click(function() {

                $('body').addClass("mps_loading");

                var uuid = $(this).parent().siblings(":first").text();

                // Build the data needed for the post
                var data = {
                    'action': 'ms_delete_pc_user',
                    'uuid': uuid
                };

                $.post(ajaxurl, data, function(response) {
                    $('body').removeClass("mps_loading");
                });

                table.row($(this).closest('tr')).remove().draw();

            });

            update_user.click(function() {

                $('body').addClass("mps_loading");

                var uuid = $(this).parent().siblings(":first").text();
                var access_level = $(this).closest('tr').find('.selectUserAccessLevel').val();

                var data = {
                    'action': 'ms_update_pc_user',
                    'uuid': uuid,
                    'access_level': access_level
                };

                $.post(ajaxurl, data, function(response) {
                    $('body').removeClass("mps_loading");
                });

            });

            var table = $('#PCUsersTable').DataTable({
                "bPaginate": false,
                "aoColumns": [
                    null,
                    null,
                    null,
                    { "bSearchable": true, "bSortable": false },
                    { "bSearchable": false, "bSortable": false },
                ]
            });

        }

    }

    function presentationsConfig() {

        function drawSlotSelect(slot_id = 0) {

            $('body').addClass("mps_loading");

            var session_id = $("select[name='presentation_session']").val();

            $('select[name="presentation_slot"]').empty();

            var data = {
                'action': 'ms_get_session_slots',
                'session_id': session_id
            };

            $.post(ajaxurl, data, function(response) {
                $('body').removeClass("mps_loading");
                $('select[name="presentation_slot"]').append('<option value="0">Other</option>');

                $.each(response, function(index, slot) {
                    if (slot.id == slot_id) {
                        $('select[name="presentation_slot"]').append('<option selected="selected" value="' + slot.id + '">' + slot.title + '</option>');
                    } else {
                        $('select[name="presentation_slot"]').append('<option value="' + slot.id + '">' + slot.title + '</option>');
                    }
                });
            }, 'json');

        }

        $('select[name="presentation_session"]').change(function() {
            drawSlotSelect();
        });


        if ($('body').hasClass('meeting-support_page_meeting-support-presentations')) {

            var btn_edit_presentation = $('.edit-presentation');
            var btn_delete_presentation = $('#delete-presentation');

            // Enable dataTables
            $('#presentationsTable').DataTable({
                "bPaginate": false,
                "aoColumns": [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                { "bSearchable": false, "bSortable": false }
                ],
                "order": [[ 6, "desc"]]
            });

            $('#delete-presentation').click(function(event) {
                return confirm('Are you sure you want to delete this presentation?');
            });

            // Populate modal
            btn_edit_presentation.click(function() {
                $('body').addClass("mps_loading");

                $('#presentation_files').empty();

                var data = {
                    'action': 'ms_get_presentation',
                    'presentation_id': $(this).data('presentationId')
                };



                $.post(ajaxurl, data, function(response) {
                    $('body').removeClass("mps_loading");
                    $("input[name='presentation_title']").val(response.title);
                    $("input[name='presentation_author']").val(response.author_name);
                    $("input[name='presentation_author_email']").val(response.author_email);
                    $("input[name='presentation_author_uuid']").val(response.author_uuid);
                    $("input[name='presentation_author_affiliation']").val(response.author_affiliation);
                    $("select[name='presentation_session']").val(response.session_id);
                    $("input[name='presentation_id']").val(response.id);

                    // Decode the files list to make a selection
                    var presentation_files = $.parseJSON(response.filename);
                    $.each(presentation_files, function(index, file) {
                        $('<input>').attr({
                            type: 'text',
                            name: 'presentation_files[]',
                            value: file
                        }).appendTo('#presentation_files');
                        $('<br>').appendTo('#presentation_files');
                    });

                    $('<input>').attr({
                        type: 'text',
                        name: 'presentation_files[]',
                        value: ''
                    }).appendTo('#presentation_files');
                    $('<br>').appendTo('#presentation_files');


                    // Create a dynamic slot selection based on the current session selected
                    drawSlotSelect(response.slot_id);
                }, 'json');
            });

        }

    }


    function sponsorConfig() {

        if ($('body').hasClass('meeting-support_page_meeting-support-sponsors')) {

            // Define buttons
            var add_sponsor_section = $('.btnAddSponsorSection');
            var add_sponsor = $('.btnAddSponsor');
            var edit_sponsor_section = $('.btnEditSponsorSection');
            var edit_sponsor = $('.btnEditSponsor');
            var move_sponsor_up = $('.btnMoveSponsorUp');
            var move_sponsor_down = $('.btnMoveSponsorDown');
            var delete_sponsor = $('.btnDeleteSponsor');

            // Define input fields
            var form_section_id = $("input[name='section_id']");
            var section_name = $("input[name='section_name']");
            var section_text_colour = $("input[name='section_text_colour']");
            var section_is_grayscale = $("input[name='section_is_grayscale']");
            var sponsor_name = $("input[name='sponsor_name']");
            var sponsor_logo_url = $("input[name='sponsor_logo_url']");
            var sponsor_url = $("input[name='sponsor_url']");

            var form_sponsor_id = $("input[name='sponsor_id']");
            var select_sponsor_section_id = $("select[name='sponsor_section_id']");


            // Editing an existing sponsor section
            edit_sponsor_section.click(function() {

                $('body').addClass("mps_loading");

                var section_id = $(this).data('sectionId');

                // Get the data needed to populate the modal
                var data = {
                    'action': 'ms_get_sponsor_section',
                    'id': section_id
                };

                $.post(ajaxurl, data, function(response) {
                    response = $.parseJSON(response);
                    // Set the window title
                    $('#TB_ajaxWindowTitle').html('Editing Section: ' + response.name);
                    // Set the form section id, in case we want to update
                    form_section_id.val(section_id);
                    section_name.val(response.name);
                    section_text_colour.val(response.text_colour);
                    if (response.is_grayscale == true) {
                        section_is_grayscale.prop("checked", true);
                    } else {
                        section_is_grayscale.prop("checked", false);
                    }
                    $('body').removeClass("mps_loading");
                });

            });

            // Adding a new sponsor section, reset the values
            add_sponsor_section.click(function() {
                form_section_id.val('-1');
                section_name.val('');
                section_text_colour.val('');
                section_is_grayscale.prop("checked", false);
            });

            // Adding a new sponsor, reset the values
            add_sponsor.click(function() {
                form_sponsor_id.val('-1');
                select_sponsor_section_id.val('');
                sponsor_name.val('');
                sponsor_logo_url.val('');
                sponsor_url.val('');
            });

            // Moving a sponsor up
            move_sponsor_up.click(function() {

                $('body').addClass("mps_loading");

                var sponsor_id = $(this).parent().data('id');

                var data = {
                    'action': 'ms_move_sponsor_up',
                    'id': sponsor_id
                };

                $.post(ajaxurl, data, function() {
                    location.reload();
                });

            })

            // Moving a sponsor down
            move_sponsor_down.click(function() {

                $('body').addClass("mps_loading");

                var sponsor_id = $(this).parent().data('id');

                var data = {
                    'action': 'ms_move_sponsor_down',
                    'id': sponsor_id
                };

                $.post(ajaxurl, data, function() {
                    location.reload();
                });

            })

            // Deleting a sponsor
            delete_sponsor.click(function() {

                if (! confirm('Are you sure you want to delete this sponsor?')) {
                    return false;
                }

                $('body').addClass("mps_loading");

                var sponsor_id = $(this).parent().data('id');

                var data = {
                    'action': 'ms_delete_sponsor',
                    'id': sponsor_id
                };

                $.post(ajaxurl, data, function() {
                    location.reload();
                });

            })


            // Editing an existing sponsor
            edit_sponsor.click(function() {

                $('body').addClass("mps_loading");

                var sponsor_id = $(this).parent().data('id');

                var data = {
                    'action': 'ms_get_sponsor',
                    'id': sponsor_id
                };

                $.post(ajaxurl, data, function(response) {

                    response = $.parseJSON(response);
                    form_sponsor_id.val(response.id);
                    select_sponsor_section_id.val(response.section_id);
                    sponsor_name.val(response.name);
                    sponsor_logo_url.val(response.image_url);
                    sponsor_url.val(response.link_url);
                    $('body').removeClass("mps_loading");

                });

            })

        }

    }

    $(function() {
        webShims();
        adminConfig();
        agendaConfig();
        userConfig();
        speakersConfig();
        PCUserConfig();
        sponsorConfig();
        slotConfig();
        presentationsConfig();
        presentationRatingsConfig();
        videoConfig();
        pcElectionsConfig();
    });

})( jQuery );

jQuery(document).ready(function($) {

    var custom_uploader;

    $('#upload_image_button').click(function(e) {

        e.preventDefault();

        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Sponsor Logo',
            button: {
                text: 'Choose Sponsor Logo'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#upload_image').val(attachment.url);
        });

        //Open the uploader dialog
        custom_uploader.open();

    });

});
