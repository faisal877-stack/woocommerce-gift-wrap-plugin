jQuery(document).ready(function($) {
    function toggleGiftWrapFields() {
        if ($('#add_gift_wrap').is(':checked')) {
            $('.gift-wrap-field').show();
        } else {
            $('.gift-wrap-field').hide();
        }
    }

    toggleGiftWrapFields();

    $('#add_gift_wrap').change(function() {
        toggleGiftWrapFields();

        $('body').trigger('update_checkout');
    });
});