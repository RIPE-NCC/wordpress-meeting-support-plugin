(function( $ ) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
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

     function nl2br (str, is_xhtml) {
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
     }

     function isEmpty(str) {
        if (typeof str === 'string' && str.length === 0) return true;
        return false;
     }

     function handleProfileUpdate() {

        // This stuff should only be executed on the Meeting Support config page
        var current_password = $('#current_password');
        var new_password = $('#new_password');
        var new_password_confirmation = $('#new_password_confirmation');


        current_password.keydown(function(event) {
            clickPasswordChange(event);
        });

        new_password.keydown(function(event) {
            clickPasswordChange(event);
        });

        new_password_confirmation.keydown(function(event) {
            clickPasswordChange(event);
        });

        function clickPasswordChange(event) {

            var btn_update_password = $('#update_password');

            if (event.keyCode == 13) {
                event.preventDefault();
                btn_update_password.click();
            }

        }
    }

    function attendeeList() {

        $('#attendeeTable').DataTable( {
            bPaginate: false,
            responsive: {
                details: {
                    type: 'column',
                }
            },
            columnDefs: [ {
                className: 'control',
                orderable: false,
                targets:   0
            } ],
            order: [ 1, 'asc']
        } );

        $('#attendeeTableDetails').DataTable( {
            bPaginate: false,
            responsive: {
                details: {
                    type: 'column',
                }
            },
            columnDefs: [ {
                className: 'control',
                orderable: false,
                targets:   0
            } ],
            order: [ 1, 'asc']
        } );

        $('.attendeeimg').popover({
            'trigger': 'hover',
            'html': true,
            'content': function() {
                return '<img class="attendeeimglarge" src="' + $(this)[0].src + '"/>';
            }
        });

    }

    function speakersList() {
        $('#speakers-table').DataTable( {
            language: {
                "emptyTable": "No speakers currently available",
                "info": "Showing _START_ to _END_ of _TOTAL_ speakers",
                "infoEmpty": "",
                "infoFiltered": "(filtered from _MAX_ total speakers)",
                "lengthMenu": "Show _MENU_ speakers",
                "zeroRecords": "No matching speakers found",
            },
            bPaginate: false,
            columnDefs: [ {
                className: 'control',
                orderable: false,
                targets:   0
            } ],
            order: [ 0, 'asc']
        } );
    }

    function speakerBio() {
        var speaker_bio_textarea = $('#speaker-bio');
        if (speaker_bio_textarea.length > 0) {
            calculateBioWordsLeft();
            speaker_bio_textarea.on('keyup blur change paste cut keypress', function() {
                calculateBioWordsLeft();
            });
        }

    }

    function calculateBioWordsLeft(element) {
        var speaker_bio_textarea = $('#speaker-bio');
        var max_words = 200;
        var min_words = 60;
        var words = 0;
        var wordmatch = speaker_bio_textarea.val().match(/[^\s]+\s+/g);
        words = wordmatch ? wordmatch.length : 0;
        if (words > max_words) {
            // Split the string on first 200 words and rejoin on spaces
            var trimmed = speaker_bio_textarea.val().split(/(?=[^\s]\s+)/, max_words).join("");
            var last_char = speaker_bio_textarea.val()[trimmed.length];

            // Add a space at the end to make sure more typing creates new words
            speaker_bio_textarea.val(trimmed + last_char + ' ');
        } else {
            $('#bio-text-words-left').text(max_words - words);
        }

        if (words < min_words) {
            $('#update-bio').prop('disabled', true);
            $('#bio-text-words-to-go').parent().show();
            $('#bio-text-words-to-go').text(min_words - words);
        } else {
            $('#update-bio').prop('disabled', false);
            $('#bio-text-words-to-go').parent().hide();
        }

    }

    function sparkLinesCleanUp(values) {
        // setting up the sparklines values, in the correct value format
        var value = [];
        if (values.indexOf(',')) {
            value = values.split(',');
        } else {
            value = new Array(values);
        }
        return value;
    }

    function sparkSetup(ratings) {
        // requires a medium
        var rate = 0;
        for (rate; rate < ratings.length; ++rate) {
            var values = $(ratings[rate]).attr('value');
            var spark = sparkLinesCleanUp(values);
            var range_map = $.range_map({
                ':1': 'red',
                '2:3': 'blue',
                '4:': 'green'
            });
            $(ratings[rate]).sparkline(spark, {
                width: '4em',
                height: '2em',
                type: 'bar',
                chartRangeMin: '0',
                chartRangeMax: $('#ratings_range').val(),
                colorMap: range_map
            });
        }
    }

    function sparkLines() {
        //setting up the sparklines
        var ratings = $('.rating-results');
        sparkSetup(ratings);
        var ratings = $('.presenter-rating-results');
        sparkSetup(ratings);
    }

    function ratingsBoxClose() {
        $('#ratingModal').on('hidden.bs.modal', function() {
            clearRatingsBox();
        });
    }

    function deleteRating() {
        $('#modaldeleterating').click(function(event) {
            event.preventDefault();
            var ratingid = $('#modalownratingid').val();
            var subid = $('#modalsubmissionid')[0].value;

            var data = {
                'action': 'delete_submission_rating',
                'ratingid': ratingid
            };
            $.post(ajaxurl, data, function() {
                clearRatingsBox();
                refreshRatingsBox();
                redrawSub(subid);
            });
        });
    }

    function getRatingsBox() {
        $('.openRatingModal').click(function() {
            var subId = $(this).data('id');
            $('.modalsubid').text(subId);
            $("#modalsubmissionid").val(subId);
            var data = {
                'action': 'get_submission_info',
                'subid': subId
            };
            $.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);
                $('.modalsubname').text(response.submission_title);
            });

            ratings_html_refresh = true;

            refreshRatingsHtml(subId);
            //ratings_html_refresh = setInterval(refreshRatingsHtml,5000, subId);

            data = {
                'action': 'get_my_submission_rating',
                'subid': subId
            };
            $.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);
                $("#modalownratingid").val(response.id);
                $("#modalratingcontent").val(response.content);
                $("#modalratingpresenter").val(response.presenter);
                $("#modalratingcomments").val(response.comment);
            });
        });

        $('#ratingModal').on('hidden.bs.modal', function() {
            clearInterval(ratings_html_refresh);
        });

    }

    function refreshRatingsHtml(subId) {
        if (ratings_html_refresh) {
            var data = {
                'action': 'get_submission_ratings_html',
                'subid': subId
            };
            $.post(ajaxurl, data, function(response) {
                $("#ratingsTable").html(response);
            });
        }
    }

    function getFinalDBox() {
        $('.openFinalDecisionModal').click(function() {
            var subId = $(this).data('id');
            $('.modalsubid').text(subId);
            $("#modalsubmissionid").val(subId);

            var data = {
                'action': 'get_submission_info',
                'subid': subId
            };
            $.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);
                $('#modalsubmissionstatus').val(response.submission_status);
                $('#modalfinaldecision').val(response.final_decision);
            });
        });
    }

    function getSubmissionHistoryBox() {
        $('.open-submission-history-modal').click(function() {
            var archive_id = $(this).data('id');
            var data = {
                'action': 'get_sub_archive_html',
                'archive_id': archive_id,
                'diff_latest': $(this).data('diffLatest')
            };
            $.post(ajaxurl, data, function(response) {
                $('#submissionHistoryModal .modal-body').html(response);
            });
        });
    }

    function submitFinalDBox() {
        $("#modalfinaldecisionform").submit(function(event) {
            event.preventDefault();
            var subid = $('#modalsubmissionid')[0].value;
            var fields = {
                'status': $("#modalsubmissionstatus").val(),
                'final_decision': $("#modalfinaldecision").val()
            };
            var data = {
                'action': 'set_sub_final_decision',
                'subid': subid,
                'fields': fields
            };
            $.post(ajaxurl, data, function() {
                redrawSub(subid);
                $('#finalDecisionModal').modal('hide');
            });
        });
    }

    function clearOldFields() {
        $("#finalDecisionModal").on("hidden.bs.modal", function() {
            $('#modalfinaldecision').val('');
        });
    }

    function submitRatingsBox() {
        if(window.location.href.indexOf("pcss") > -1) {

            $("#modalratingform").submit(function(event) {

                var subId = $('#modalsubmissionid')[0].value;
                event.preventDefault();
                var fields = {
                    'content': $("#modalratingcontent").val(),
                    'presenter': $("#modalratingpresenter").val(),
                    'comments': $("#modalratingcomments").val()
                };
                var data = {
                    'action': 'set_my_submission_rating',
                    'subid': $('#modalsubmissionid')[0].value,
                    'fields': fields
                };
                $.post(ajaxurl, data, function(response) {
                    $('#modalownratingid').val(response);
                    refreshRatingsBox();
                    redrawSub(subId);
                });
            });
        }
    }

    function makeJsVisible() {
        $(".js_show").show();
        $(".js_hide").hide();
    }

    function redrawSub(subId) {
        var data = {
            'action': 'get_submission_info',
            'subid': subId
        }

        $.post(ajaxurl, data, function(response) {
            response = $.parseJSON(response);
            $('#sub' + subId + ' .substatusname').html(response.submission_status_name);
            $('#sub' + subId + ' .finald').html(nl2br(response.final_decision));
            $('#sub' + subId + ' .rating-results').empty();
            $('#sub' + subId + ' .rating-results').prop('title', 'Average: ' + response.ratinginfo.content.avg);
            $('#sub' + subId + ' .rating-results').attr('value', response.ratinginfo.content.values);
            $('#sub' + subId + ' .contentavg').text(response.ratinginfo.content.avg);
            $('#sub' + subId + ' .contentcount').text(response.ratinginfo.content.count);
            $('#sub' + subId + ' .presenter-rating-results').empty();
            $('#sub' + subId + ' .presenter-rating-results').prop('title', 'Average: ' + response.ratinginfo.presenter.avg);
            $('#sub' + subId + ' .presenter-rating-results').attr('value', response.ratinginfo.presenter.values);
            $('#sub' + subId + ' .presenteravg').text(response.ratinginfo.presenter.avg);
            $('#sub' + subId + ' .presentercount').text(response.ratinginfo.presenter.count);
            $('#sub' + subId).parent().children(":first").removeClass('status-accepted status-declined status-conditional').addClass('status-' + response.submission_status_name.toLowerCase());
            sparkLines();
        });
    }

    function refreshRatingsBox() {
        var subId = $('#modalsubmissionid')[0].value;
        $("#modalsubmissionid").val(subId);
        var data = {
            'action': 'get_submission_info',
            'subid': subId
        };
        $.post(ajaxurl, data, function(response) {
            response = $.parseJSON(response);
            $('.modalsubname').text(response.submission_title);
        });
        data = {
            'action': 'get_submission_ratings_html',
            'subid': subId
        };
        $.post(ajaxurl, data, function(response) {
            $("#ratingsTable").html(response);
        });
        data = {
            'action': 'get_my_submission_rating',
            'subid': subId
        };
        $.post(ajaxurl, data, function(response) {
            response = $.parseJSON(response);
            $("#modalratingcontent").val(response.content);
            $("#modalratingpresenter").val(response.presenter);
            $("#modalratingcomments").html(response.comment);
        });
    }

    function ratingsBoxClose() {
        $('#ratingModal').on('hidden.bs.modal', function() {
            clearRatingsBox();
        });
    }

    function clearRatingsBox() {
        $("#modalratingcontent").val('');
        $("#modalratingpresenter").val('');
        $("#modalratingcomments").val('');
        $("#ratingsTable").html('<div class="alert alert-info">Loading...</div>');
    }

    function clearOldFields() {
        $("#finalDecisionModal").on("hidden.bs.modal", function() {
            $('#modalfinaldecision').val('');
        });
    }


    function pcssMassEmailFunctions() {

        // Only show the widget if there are submissions showing in the current view
        if ($('.sub-checkbox').length) {
            $('#massEmailWidget').show();
        }

        var selectedids = [];

        $('#btnSelectAllSubs').click(function(event) {
            event.preventDefault();
            $('.sub-checkbox').prop('checked', true);
            $('#btnSelectAllSubs').hide();
            $('#btnDeselectAllSubs').show();
        });

        $('#btnDeselectAllSubs').click(function(event) {
            event.preventDefault();
            $('.sub-checkbox').prop('checked', false);
            $('#btnSelectAllSubs').show();
            $('#btnDeselectAllSubs').hide();
        });

        $('#btnSendMassEmail').click(function() {
            selectedids = [];
            $('.sub-checkbox:checked').each(function() {
                selectedids.push($(this).data('id'));
            });
            if (selectedids.length < 1) {
                alert('Please select at least one submission.');
            } else {
                var data = {
                    'action': 'get_mass_mail_info',
                    'submissions': selectedids
                };
                $.post(ajaxurl, data, function(table) {
                    $('#massMailTableHolder').html(table);
                });
                $('#massEmailModal').modal('show');
            }
        });

        $('#btnLoadBlankTemplate').click(function() {
            $('#massEmailSubject').val('');
            $('#massEmailBody').val('');
        });

        $('#btnLoadAcceptanceTemplate').click(function() {
            $('#massEmailSubject').prop('disabled', true);
            $('#massEmailBody').prop('disabled', true);
            var data = {
                'action': 'get_mass_mail_template',
                'template': 'acceptance'
            };
            $.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);
                $('#massEmailSubject').val(response.subject);
                $('#massEmailBody').val(response.body);

                $('#massEmailSubject').prop('disabled', false);
                $('#massEmailBody').prop('disabled', false);
            });
        });

        $('#btnLoadRejectionTemplate').click(function() {
            $('#massEmailSubject').prop('disabled', true);
            $('#massEmailBody').prop('disabled', true);
            var data = {
                'action': 'get_mass_mail_template',
                'template': 'rejection'
            };
            $.post(ajaxurl, data, function(response) {
                response = $.parseJSON(response);
                $('#massEmailSubject').val(response.subject);
                $('#massEmailBody').val(response.body);

                $('#massEmailSubject').prop('disabled', false);
                $('#massEmailBody').prop('disabled', false);
            });
        });

        $('#frmMassEmail').submit(function(event) {
            event.preventDefault();
            $('#massEmailSurround').slideUp();
            $('#massMailWaitingMessage').slideDown();
            var data = {
                'action': 'send_mass_mail',
                'ids': selectedids,
                'subject': $('#massEmailSubject').val(),
                'body': $('#massEmailBody').val()
            };
            $.post(ajaxurl, data, function(response) {
                $('#massMailSuccessMessage').slideDown();
                $('#massMailWaitingMessage').slideUp();
            });
        });

        $('#massEmailModal').on('hidden.bs.modal', function(e) {
            $('#massMailWaitingMessage').hide();
            $('#massMailSuccessMessage').hide();
            $('#massEmailSurround').show();
            $('#massEmailSubject').val('');
            $('#massEmailBody').val('');
        });
    }


    //******** Upload Presentation Form **********************************
    function initUploadPresentationPage() {
        var form, uploaded_obj, pr_files, delete_files, is_changed;

        function checkFileExtension(filename) {
            if (filename.length == 0) return false;
            // find dot
            var dot_index = filename.lastIndexOf('.');
            if (dot_index == -1) return false;
            // get extension of file
            var extension = filename.substr(dot_index + 1)
            // check if extension exist in gloabal array allowed_file_types
            if (allowed_file_types.indexOf(extension)>-1){
                return true;
            }
            return false;
        }

        function checkIfFileExist(filename) {
            var existing_presentations = [];
            // get all existing file names
            // delete presentation suffix number
            // example: 49-presentation1.pdf -> presentation1.pdf
            $('.presentation-upload-form .exist').each(function( index ) {
              var text = $(this).text();
              existing_presentations.push(text.replace(/[0-9]+-/, ''));
            });
            if (existing_presentations.indexOf(filename) == -1){
                return false;
            }
            return true;
        }

        function setSessionChoices() {

            var first_load;

            first_load = true;

            // Disable the Slot selection until a Session has been chosen
            //$('.presentation_slot').prop('disabled', true);
            // Wipe the existing slot selections
            $('.presentation_slot').empty();
            $('.presentation_slot').append('<option value="">Please select</option>');

            // Re-populate the Presentation Slot select options when we change the session
            $('.presentation_session').change(function() {

                // Disable the field until the AJAX call is complete
                $('.presentation_session').prop('disabled', true);
                $('.presentation_slot').prop('disabled', true);

                // Show the wait cursor
                $(document.body).css({'cursor' : 'wait'});

                var data = {
                    'action': 'ms_get_session_slots',
                    'session_id': $(this).val()
                };

                $.post(ajaxurl, data, function(response) {

                    var slots = $.parseJSON(response);

                    // Wipe the existing slot selections
                    $('.presentation_slot').empty();
                    $('.presentation_slot').append('<option value="">Please select</option>');

                    // Append the slots, with parents.
                    $.each(slots, function(index, slot) {
                        var slot_children = [];

                        // Don't process children at this level
                        if (slot.parent_id != 0) {
                            return true;
                        }
                        // Build a list of children for this slot.
                        $.each(slots, function(index_ch, slot_ch) {
                            if (slot.id == slot_ch.parent_id) {
                                slot_children.push(slot_ch);
                            }
                        });

                        // Are we dealing with a parent slot?
                        if (slot_children.length > 0) {
                            $('.presentation_slot').append('<optgroup label="' + slot.title + '"></optgroup>');
                            $.each(slot_children, function(index_ch, slot_ch) {
                                $('.presentation_slot optgroup[label="' + slot.title + '"]').append('<option value="' + slot_ch.id + '">' + slot_ch.title + '</option>');
                            });
                        } else {
                            $('.presentation_slot').append('<option value="' + slot.id + '">' + slot.title + '</option>');
                        }


                    });

                    // Add an 'Other' option, in case a slot is missing
                    if ($('.presentation_session').val() != '') {
                        $('.presentation_slot').append('<option value="0">Other</option>');
                    }

                    // If the slot select element has an originaValue data, let's set it to that
                    if (first_load == true) {
                        $('.presentation_slot').val($('.presentation_slot').data('originalValue'));
                        first_load = false;
                    }

                    // Remove the disabled props once the fields are populated 'prop'erly, hah.
                    $('.presentation_session').removeProp('disabled');
                    $('.presentation_slot').removeProp('disabled');

                    // Remove the wait cursor
                    $(document.body).css({'cursor' : 'default'});
                });
            });

            // If we're editing a presentation, let's populate the slot/title selects.
            if ( $('.presentation_session').data('originalValue') ) {
                $('.presentation_session').val($('.presentation_session').data('originalValue')).change();
            }

            $('.presentation_slot').val($('.presentation_slot').data('originalValue'));

            $('.presentation_slot').change(function() {
                // If a slot has been selected, let's try to suggest a Presentation Title based on the Slot title
                if ($(this).val() != '0' && $(this).val() != '') {
                    // Don't suggest a title if we're selecting "Other"
                    $('.presentation_title').val($('.presentation_slot option:selected').text());
                }
            });
        }

        function handleFileUpload(files, uploaded_obj) {

            function createStatusbar(uploaded_obj) {

                this.statusbar = $("<div class='statusbar'></div>");
                this.filename = $("<div class='filename'></div>").appendTo(this.statusbar);
                this.size = $("<span class='filesize'></span>").appendTo(this.statusbar);
                this.remove = $("<a href='#' class='remove'>Remove</a>").appendTo(this.statusbar);
                this.progressBar = $("<div class='progressBar'></div>").appendTo(this.statusbar);

                uploaded_obj.after(this.statusbar);

                this.setFileNameSize = function(name,size) {
                    var sizeStr="";
                    var sizeKB = size/1024;
                    if(parseInt(sizeKB) > 1024) {
                        var sizeMB = sizeKB/1024;
                        sizeStr = sizeMB.toFixed(2)+" MB";
                    } else {
                        sizeStr = sizeKB.toFixed(2)+" KB";
                    }

                    this.filename.html(name);
                    this.filename.attr('data-size', size);
                    this.size.html(sizeStr);
                }
                this.setProgress = function(progress) {
                    // var progressBarWidth =progress*this.progressBar.width()/ 100;
                    // this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
                    this.progressBar.addClass('full');
                }

                this.remove.bind("click", function(e){
                    e.stopPropagation();
                    e.preventDefault();

                    var filename = $(this).parent('.statusbar').children('.filename').text();
                    pr_files.splice(pr_files.indexOf(filename), 1);
                    $(this).parent('.statusbar').remove();
                })
            }

            for (var i = 0; i < files.length; i++) {

                if ( checkFileExtension(files[i].name) == false) {
                    alert("Format of this file is not allowed");
                    continue
                }

                if ( checkIfFileExist(files[i].name) == true) {
                    var r = confirm("Warning: A file with name '" + files[i].name + "' already uploaded ... It will be overwritten.");
                    if (r == false) {
                        continue;
                    }
                }

                pr_files.push(files[i]);
                is_changed = true;

                var status = new createStatusbar(uploaded_obj);
                status.setFileNameSize(files[i].name,files[i].size);
                status.setProgress(100);
            }
            // we need clear this field
            $('#presentation_upload').val(null);
        }

        function uploadPresentationHandler () {

            uploaded_obj.on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $(this).css('border', '2px solid grey');
            });

            uploaded_obj.on('dragover', function (e) {
                 e.stopPropagation();
                 e.preventDefault();
            });

            uploaded_obj.on('drop', function (e) {
                 $(this).css('border', '1px grey dashed');
                 e.preventDefault();
                 var files = e.originalEvent.dataTransfer.files;
                 //We need to send dropped files to Server
                 handleFileUpload(files, uploaded_obj);
            });

            $(document).on('dragenter', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

            $(document).on('dragover', function (e) {
              e.stopPropagation();
              e.preventDefault();
              uploaded_obj.css('border', '2px dashed grey');
            });

            $(document).on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
            });

        }

        function processUpload(){

            var formData = new FormData(form[0]);
            formData.append('action', 'upload_presentation');
            formData.append('security', ajax_nonce);
            for (var i = 0; i < pr_files.length; i++) {
                formData.append('presentation_upload[]', pr_files[i]);
            }

            for (var i = 0; i < delete_files.length; i++) {
                formData.append('delete_files[]', delete_files[i]);
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                async: false,
                success: function (response) {
                    if (response.success === true) {
                        location.reload();
                    } else {
                        console.log(response);
                        $('#presentation-errors-area').text(response.data.message).show();
                        $('.presentation-loading').hide();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log();
                    alert('Unknown Error');
                    $('#presentation-errors-area').text('Unknown error. Please try again!').show();
                    $('.presentation-loading').hide();
                },
                cache: false,
                contentType: false,
                processData: false
            });

        }

        function submitUpload() {
            $('.presentation-loading').show();
            setTimeout(function(){ processUpload(); }, 100);
        }

        function fetchPresentation(presentation_id) {
            var data = {
                'action': 'upload_presentation',
                'security': ajax_nonce,
                'fetch_form': true
            };

            if (presentation_id) {
                data['presentation_id'] = presentation_id;
            }

            $.post(ajaxurl, data, function(response) {
                if (response && response.data && response.data.html) {
                    $('#uploadPresentationModal .form-wrapper').html(response.data.html);
                    initUploadPresentationForm();
                    $('#uploadPresentationModal').modal('show');
                } else {
                    console.log(response);
                }
            }, "json");
        }

        // MPS-227 ajaxification of presentation upload
        $('.delete-presentation').click(function() {
            var presentation_id = $(this).closest('.existing-presentation').data('presentationId');
            if (isEmpty(presentation_id)) {
                console.log("Can't find presentation")
                return false;
            }
            var presentation_div = $(this).closest('.existing-presentation');

            if (confirm('Are you sure you want to delete this presentation?')) {

                // Send delete request
                var data = {
                    'action': 'delete_presentation',
                    'security': ajax_nonce,
                    'presentation_id': presentation_id
                }

                $.post(ajaxurl, data, function(response) {
                    presentation_div.remove();
                    // if no presentations we need to update DOM
                    if ($('.existing-presentation').length == 0) {
                        $('#presentations-box').hide();
                        $('#no-presentations-box').removeClass('hidden');
                    }
                }, "json");
            }
        });

        // MPS-227 ajaxification of presentation upload
        $('.update-presentation').click(function() {
            var presentation_id = $(this).closest('.existing-presentation').data('presentationId');
            if (isEmpty(presentation_id)) {
                console.log("Can't find presentation")
            }
            fetchPresentation(presentation_id);

        });

        // MPS-227 ajaxification of presentation upload
        $('.uploadNewPresentation').click(function() {
            fetchPresentation();
        });

        function initUploadPresentationForm(){
            // activate bootstrap popover
            form = $('#modal-presentation-upload-form');
            if (form.length != 1) {
                return false;
            }

            pr_files = [];
            delete_files = [];
            is_changed = false;

            // Show stuff which was hidden by .show_js
            $('.show_js').show();
            uploaded_obj = $("#modal-presentation-upload-zone");

            $('#modal-submit-upload').click(function(e){
                e.preventDefault();
                e.stopPropagation();

                if($('.presentation-upload-form .filename').length == 0){
                    alert("At least one file should be uploaded");
                    return false;
                }

                var files_size = 0;

                $(".modal .filename:not(.exist)").each(function() {
                    files_size = files_size + parseInt($(this).attr('data-size'));
                });

                if (files_size > max_file_upload_size){
                    alert("Your file(s) exceed the maximum upload limit");
                    return false;
                }

                var valid = document.forms['modal-presentation-upload-form'].reportValidity();
                if (valid) {
                    if (typeof(grecaptcha) !== 'undefined') {
                        captcha_callback = submitUpload;
                        grecaptcha.reset();
                        grecaptcha.execute();
                    } else {
                        submitUpload();
                    }
                }
            });

            $('#modal-presentation-upload-zone').click(function(e){
                $('#presentation_upload').click();
            });

            $('#presentation_upload').change(function(e){
                e.preventDefault();
                e.stopPropagation();
                handleFileUpload(e.target.files, uploaded_obj);
            });

            $('.ajax-remove').click(function(e){
                if (confirm('Are you sure you want to delete this file?')) {
                    var parent = $(this).parent('.statusbar');
                    var filename = parent.children('.filename').text();
                    parent.remove();
                    delete_files.push(filename);
                    is_changed = true;
                }

            });

            $("input[type='text']").change(function () {
                is_changed = true;
            });

            $(".close-upload-form").click(function () {
                if (is_changed == false) {
                    $('#uploadPresentationModal').modal('hide');
                    return false;
                }
                var response = confirm("You have unsaved changes. Do you really want to close this form?");
                if (response == true) {
                    $('#uploadPresentationModal').modal('hide');
                }
            });

            uploadPresentationHandler();
            setSessionChoices();
        }
        initUploadPresentationForm()
    }

    //******** END Upload Presentation Form **********************************
    function slotRatings() {

        var btn_slot_rate_delete = $('#modaldeletepresrating ');

        if ($('#modaldeletepresrating').length == 0) {
            return;
        }

        $('.slot_rate').live('click', function() {

            var slot_id = $(this).data('slotId');
            $('input[name="delete_rating"]').remove();
            // Are we logged in? If not we need to tell people to log in or register first.
            var data = {
                'action': 'check_rate_capabilities'
            };

            $.post(ajaxurl, data, function(response) {
                // Not logged in, lets show a message and give them the option to be redireted to the login page
                if (response.logged_in == false) {
                    if (confirm(response.message)) {
                        window.open(response.login_url, '_blank');
                    }
                } else {

                    // Fetch exiting rating for this slot & user
                    drawSlotRating(slot_id);

                    // They are logged in, let's show the modal
                    $('#rateSlotModal').modal('show');

                }
            }, "json");
        });

        btn_slot_rate_delete.click(function() {
            $('<input>').attr({type: 'hidden', name: 'delete_rating', value: true}).appendTo($('#modalratingform'));
        });

        $('#modalratingform').on('submit', function(e) {
            // Catch the form submission and turn it into an ajax request
            e.preventDefault();
            var form_data = $(this).serializeArray();

            var form_fields = [];
            $.each(form_data, function(i, field) {
                if (field.name != 'action') {
                    form_fields[field.name] = field.value;
                }
            });

            var data = {
                'action': 'set_my_slot_rating',
                'slot_id': form_fields.slot_id,
                'ratingcontent': form_fields.ratingcontent,
                'ratingpresenter': form_fields.ratingpresenter,
                'ratingcomments': form_fields.ratingcomments,
            };

            if (form_fields['delete_rating']) {
                data.delete_rating = true;
            }

            $.post(ajaxurl, data, function(response) {
                $('#rateSlotModal').modal('hide');
                if (response.success == true) {
                    var page_button = $('a[data-slot-id="' + data.slot_id + '"]');
                    page_button.parent().replaceWith(response.button_html);
                } else {
                    location.reload();
                }
            }, "json");

        });


    }

    function drawSlotRating(slot_id) {
            // Clear the form
            $('#modalratingcontent').val('');
            $('#modalratingpresenter').val('');
            $('#modalratingcomments').val('');

            // Set the slot_id in the modal form
            $('#modalslot').val(slot_id);

            var data = {
                'action': 'get_my_slot_rating',
                'slot_id': slot_id
            };

            $.post(ajaxurl, data, function(response) {
                if (response != null) {
                    $('#modalratingcontent').val(response.rating_content);
                    $('#modalratingpresenter').val(response.rating_presenter);
                    $('#modalratingcomments').val(response.rating_comment);
                }
            }, "json");
    }

    function tabRemember() {
        $("ul#archive-tabs > li").removeClass("active");

        $("ul#archive-tabs > li > a").on("shown.bs.tab", function(e) {
            var id = $(e.target).attr("href").substr(1);
            window.location.hash = id;
        });

        var hash = window.location.hash;
        $('a[href="' + hash + '"]').tab('show');
    }

    function presentationArchives() {
        $('#existing_presentations').DataTable( {
            bPaginate: false,
            responsive: {
                details: {
                    type: 'column',
                }
            },
            columnDefs: [ {
                className: 'control',
                orderable: false,
                targets:   0
            } ],
            order: [ 1, 'asc']
        } );
    }

    function useabilityImprovements() {

        // Close alerts with the class 'alert-autoclose' after 5 seconds
        window.setTimeout(function () {
            $(".alert-autoclose").fadeTo(500, 0).slideUp(500, function () {
                $(this).remove();
            });
        }, 5000);

        if ($('#user-max-votes')) {
            var max_votes = $('#user-max-votes').data('maxVotes');
            $('.candidate_select').click(function (event) {
                if ($('.candidate_select:checked').length > max_votes) {
                    event.preventDefault();
                    alert('You can only vote for a maximum of ' + max_votes + ' candidates');
                }
            });
        }

        // Agenda a bit prettier for JS enabled browsers
        $('.active-agenda-item').click(function() {
            var url = $(this).children('a').attr('href');
            if ($(this).children('a').attr('target') == '_blank') {
                window.open(url);
            } else {
                window.location = url;
            }
        });

        $.each($('.active-agenda-item'), function(index, value) {
            $(this).css({cursor: 'pointer'});
            $(this).children('a').addClass('nounderline');
        });

        $('#update-bio').click(function(event) {
            if (! confirm('Are you sure you want to submit this bio? You will not be able to edit it until it has been approved')) {
                event.preventDefault();
            }
        })

    }

    function pcssSubmissionTagging() {

        // Enable select2 on tag picker
        $(".submission-tagging-input").select2();

        $(".btn-open-submission-tag-modal").click(function() {
            current_submission = $(this).data('id');

            // Populate select box with this submissions's tags
            var data = {
                'action': 'get_submission_tags',
                'submission_id': current_submission
            };

            $.post(ajaxurl, data, function(response) {
                if (response != null) {
                    $('.submission-tagging-input').val(response).trigger("change");
                } else {
                    console.log('Error');
                }
            }, "json");
        });

        $("#btn-save-submission-tags").click(function() {
            var tags = $('#select-submission-tags').val();
            var data = {
                'action': 'set_submission_tags',
                'submission_id': current_submission,
                'tags': tags
            };

            $.post(ajaxurl, data, function(response) {
                if (response != null) {
                    location.reload();
                } else {
                    console.log('Error');
                }
            }, "json");
        });

    }

    function getPCSSUpdates() {

        // Poll interval in seconds
        var interval = 10;

        toastr.options = {
            "positionClass": "toast-bottom-right",
            "timeOut": false,
            "extendedTimeOut": false,
            "preventDuplicates": true,
            "closeButton": true
        }

        if (window.location.href.indexOf("pcss") > -1) {

            // Functionality to show toastr messages
            setInterval(function() {
                var data = {
                    'action': 'get_my_pcss_notifications',
                    'poll_interval': interval
                };
                $.post(ajaxurl, data, function(response) {
                    if (response != null) {
                        if (response.hasOwnProperty('updated_submissions')) {
                            $.each(response.updated_submissions, function(index, submission) {
                                toastr.options.onclick = function() {
                                    window.location.href = submission.url;
                                }
                                toastr.info(submission.submission_title, 'Updated Submission');
                            });
                        }
                        if (response.hasOwnProperty('new_submissions')) {
                            $.each(response.new_submissions, function(index, submission) {
                                toastr.options.onclick = function() {
                                    window.location.href = submission.url;
                                }
                                toastr.info(submission.submission_title, 'New Submission');
                            });
                        }
                        if (response.hasOwnProperty('new_ratings')) {
                            $.each(response.new_ratings, function(index, rating) {
                                toastr.options.onclick = function() {
                                    window.location.href = rating.url;
                                }
                                toastr.info(rating.submission_name, 'New Rating by ' + rating.rater_name);
                            });
                        }
                    } else {
                        console.log('Error');
                    }
                }, "json");
            }, interval * 1000);
            // Functionality to jump to opened accordion
            var submission_id = $.urlParam('submission_id');
            if (submission_id != null) {
                setTimeout(function() {
                    window.location.href = "#sub"+ submission_id;

                }, 500);
            }

            $('.btn-toggle-submission-history').click(function() {
                if ($(this).hasClass('collapsed')) {
                    $(this).text('Hide Submission History');
                } else {
                    $(this).text('Show Submission History');
                }
            });

        }

    }

    function pcssFilterResults() {
        $('[data-toggle="tooltip"]').tooltip({});
        $('[data-toggle="popover"]').popover({
            'html': true,
            'template': '<div class="popover" role="tooltip"><div class="arrow"></div><h4 class="popover-title"></h4><div class="popover-content"></div></div>',
            'trigger': 'hover',
            'delay': { "hide": 1500 }
        });

        $('[data-toggle="popover"]').on('hover', function (e) {
            $('[data-toggle="popover"]').not(this).popover('hide');
        });

        $('.btn-view-submission-tags').click(function(event) {
            event.preventDefault();
        });

        $('#pcss-submissions-quick-sort').click(function(event) {
            event.preventDefault();
            $('select[name=sort_by] option[value=avgcontent]').attr('selected','selected');
            $('input[name=sort_order][value=descending]').attr('checked', 'checked');
            $('#form-sort-submissions').submit();
        });

        $('#pcss-submissions-sort').click(function(event) {
            var tag_selected = $('select[name=tagged_with]').val();
            if(tag_selected) {
                event.preventDefault();
                $('#form-sort-submissions').attr('action', '/pcss/tagged/?tagged=' + encodeURIComponent(tag_selected));
                $('#form-sort-submissions').submit();
            }
        });

    }


    $.urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results != null) {
            return results[1] || 0;
        }
        return null;
    }

    // Load functions on document ready
    var ratings_html_refresh;
    var current_submission;

    $( function() {
        handleProfileUpdate();
        attendeeList();
        sparkLines();
        getRatingsBox();
        submitRatingsBox();
        makeJsVisible();
        getFinalDBox();
        getSubmissionHistoryBox();
        submitFinalDBox();
        deleteRating();
        ratingsBoxClose();
        clearOldFields();
        pcssMassEmailFunctions();
        pcssSubmissionTagging();
        initUploadPresentationPage();
        slotRatings();
        tabRemember();
        presentationArchives();
        useabilityImprovements();
        getPCSSUpdates();
        pcssFilterResults();
        speakersList();
        speakerBio();

    });

})( jQuery );
