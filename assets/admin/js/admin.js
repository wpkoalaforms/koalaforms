jQuery(function ($) {


jQuery('.kf-nav-tabs li a').on('click', function(){
    var target = jQuery(this).attr('data-rel');
 jQuery('.kf-nav-tabs li a').removeClass('kf-tab-active');
    jQuery(this).addClass('kf-tab-active');
    jQuery("#"+target).fadeIn('slow').siblings(".kf-tabs-box").hide();
    return false;
});



// General Modal Functions

$.fn.openPopup = function (settings, editData = null) {
    var modal = $(this);

    // Merge user settings with defaults
    var defaultSettings = $.extend({
        anim: 'kf-modal-',
        overlayAnim: 'kf-modal-overlay-fade-'
    }, settings);

    // Show the modal and apply animation classes
    modal.show();

    modal.find('.popup-content').addClass(defaultSettings.anim + 'in').removeClass(defaultSettings.anim + 'out');
    modal.find('.kf-modal-overlay').addClass(defaultSettings.overlayAnim + 'in').removeClass(defaultSettings.overlayAnim + 'out');

    // If editData exists, modify modal title and button attributes
    if (editData) {
        if (editData.title) {
            modal.find('.kf-modal-title').html(editData.title);
        }
        if (editData.row_id) {
            modal.find('button').attr('data-edit_row_id', editData.row_id);
        }
    }
};

$.fn.closePopup = function (settings) {
    var modal = $(this);

    // Merge user settings with defaults
    var defaultSettings = $.extend({
        anim: 'kf-modal-',
        overlayAnim: 'kf-modal-overlay-fade-'
    }, settings);

    // Apply closing animations
    modal.find('.popup-content').removeClass(defaultSettings.anim + 'in').addClass(defaultSettings.anim + 'out');
    modal.find('.kf-modal-overlay').removeClass(defaultSettings.overlayAnim + 'in').addClass(defaultSettings.overlayAnim + 'out');

    // Hide modal after animation
    setTimeout(function () {
        modal.hide();
        modal.find('.popup-content').removeClass(defaultSettings.anim + 'out');
        modal.find('button').removeAttr('data-edit_row_id');
    }, 400);
};

// Open modal on button click
$(document).on('click', '.kf-open-modal', function () {
    var modalId = $(this).data('id');
    var anim = $(this).data('animation') || 'kf-modal-';
    $('#' + modalId).openPopup({ anim: anim });

    $('body').addClass('kf-modal-open-body');
});

// Close modal on close button click
$(document).on('click', '.kf-close-popup', function () {
    var modalId = $(this).data('id');
    var anim = $(this).data('animation') || 'kf-modal-';
    $('#' + modalId).closePopup({ anim: anim });

    $('body').removeClass('kf-modal-open-body');
});



// Handle Create form submission
$('#kf-create-form').on('submit', function(e) {
    e.preventDefault();

    // Clear previous messages
    $('#response-message').empty();

    const formData = new FormData(e.target);
    // Convert the FormData object to a plain object (key-value pairs)
    const formObject = {};
    formData.forEach((value, key) => {
        formObject[key] = value;
    });

    // Make the AJAX request
    $.ajax({
        url: koalaforms_ajax_object.ajax_url,
        type: 'POST',
        data: formObject,
        success: function(response) {
            var data = JSON.parse(response);
            
            if (data.status === 'success') {
                // Show success message and redirect
                $('#response-message').html('<div class="updated notice"><p>' + data.message + '</p></div>');
                window.location.href = data.redirect_url;
            } else {
                // Show error message
                $('#response-message').html('<div class="error notice"><p>' + data.message + '</p></div>');
            }
        },
        error: function() {
            $('#response-message').html('<div class="error notice"><p>Something went wrong. Please try again.</p></div>');
        }
    });
});
// Handle Create form submission Ends here


// Submission Output for Admin Page
$('#submission_meta_box').show();
$('#submission_meta_box').removeClass('hide-if-js'); 


/******** Submission Stage related functions  */

var pendingIdx   = -1;
var confirmedIdx = (typeof koalaformsStageData !== 'undefined') ? koalaformsStageData.confirmedIdx : -1;

function applyStepStyles(steps, activeIdx) {
    steps.forEach(function (step, i) {
        step.classList.remove('is-active', 'is-complete');
        if (i === activeIdx)    step.classList.add('is-active');
        else if (i < activeIdx) step.classList.add('is-complete');
    });
}

window.koalaformsSelectStage = function (el) {
    if (el.classList.contains('is-active')) return;

    var steps = Array.from(document.querySelectorAll('#kf-stage-path .kf-step'));
    pendingIdx = steps.indexOf(el);

    applyStepStyles(steps, pendingIdx);

    $('#koalaforms_stage_note_input').val('');
    $('#kf-stage-note-modal').openPopup({ anim: 'kf-modal-' }, {
        title: (typeof koalaformsStageData !== 'undefined' ? koalaformsStageData.movingTo : 'Moving to') + ': ' + el.getAttribute('data-stage')
    });
    $('body').addClass('kf-modal-open-body');
};

function revert() {
    var steps = Array.from(document.querySelectorAll('#kf-stage-path .kf-step'));
    applyStepStyles(steps, confirmedIdx);
    document.getElementById('koalaforms_submission_stage_value').value =
        confirmedIdx >= 0 ? steps[confirmedIdx].getAttribute('data-stage') : '';
    pendingIdx = -1;
}

function closeModal() {
    $('#kf-stage-note-modal').closePopup({ anim: 'kf-modal-' });
    $('body').removeClass('kf-modal-open-body');
}

$('#kf-stage-modal-cancel, #kf-stage-modal-close, #kf-stage-modal-overlay').on('click', function () {
    revert();
    closeModal();
});

$('#kf-stage-modal-apply').on('click', function () {
    var steps = Array.from(document.querySelectorAll('#kf-stage-path .kf-step'));
    var stage = pendingIdx >= 0 ? steps[pendingIdx].getAttribute('data-stage') : '';
    document.getElementById('koalaforms_submission_stage_value').value = stage;
    document.getElementById('koalaforms_stage_note').value = $('#koalaforms_stage_note_input').val();
    confirmedIdx = pendingIdx;

    $(this).prop('disabled', true).text(typeof koalaformsStageData !== 'undefined' ? koalaformsStageData.saving : 'Saving...');
    document.getElementById('post').submit();
});

/** Submission stage related actions ends here */

});