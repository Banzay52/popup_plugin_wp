<?php
namespace Sf\Popup;

$html = "";
if ( !empty($args['form_id']) ) {
    $html .= "<!--[if lte IE 8]>
                <script charset='utf-8' type='text/javascript' src='//js.hsforms.net/forms/v2-legacy.js'></script>
                <![endif]-->
                <script charset='utf-8' type='text/javascript' src='//js.hsforms.net/forms/v2.js'></script>
                <script>
                var sf_form_id = '" . $args['form_id'] . "';
                hbspt.forms.create({
                    region: 'na1',
                    portalId: '" . Options::getOption('popup_hubspot_portal_id') . "',
                    formId: sf_form_id,
                    submitButtonClass: 'sf_button hs-button ',
//                    onFormReady: function(form) {
//                    },
//                    onFormSubmit: function(form) {
//                    },
                    onFormSubmitted: function(form) {
                        const popup_modal = document.getElementById('dp-popup-modal-wrapper');
                        document.cookie = 'sf_popup_closed_' + btoa(sf_form_id) + '=1;expires=;path=/';
                        document.querySelector('body').style.overflow = 'auto';
                        popup_modal.style.display = 'none';                    
                    }
                });
                </script>";
}
?>

<div id="dp-popup-modal-wrapper">
    <div class="dp-popup-close"></div>
    <div class='popup-form-wrapper'>
        <div class="popup-title"><h2><?php echo $args['title']; ?></h2></div>
        <div class="popup-descr"><?php echo $args['descr']; ?></div>
        <?php echo $html; ?>
    </div>
    <div class="popup-right-side">

    </div>
</div>
