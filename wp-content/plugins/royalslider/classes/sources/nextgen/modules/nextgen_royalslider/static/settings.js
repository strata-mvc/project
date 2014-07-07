jQuery(function($) {
        $('input[name="ds-nextgen_royalslider[override_thumbnail_settings]"]')
        .nextgen_radio_toggle_tr('1', $('#tr_ds-nextgen_royalslider_thumbnail_dimensions'))
        .nextgen_radio_toggle_tr('1', $('#tr_ds-nextgen_royalslider_thumbnail_quality'))
        .nextgen_radio_toggle_tr('1', $('#tr_ds-nextgen_royalslider_thumbnail_crop'))
        .nextgen_radio_toggle_tr('1', $('#tr_ds-nextgen_royalslider_thumbnail_watermark'));
});