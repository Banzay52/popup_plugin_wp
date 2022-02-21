(function() {
    let time_to_show = sf_popup.time_to_show * 1000;
    let time_to_show_after_close = sf_popup.time_to_show_after_close * 1000;
    let sf_popup_debug_mode = sf_popup.debug_mode;
    let popup_modal = document.getElementById('dp-popup-modal-wrapper');
    let close_popup_btn = document.querySelector('.dp-popup-close');

    if ( typeof sf_form_id === 'undefined' ) { return; }

    if ( dpGetCookie('sf_popup_closed_' + btoa(sf_form_id)) != 1  || sf_popup_debug_mode ) {
        setTimeout(function () {
                        popup_open(popup_modal);
                    }, time_to_show);
    }

    popup_modal.addEventListener('click', function (e) {
        // e.stopPropagation();
        if (e.target == this || e.target == close_popup_btn) {
            popup_close(this);
        }
    });

    var popup_close = function (popup_element) {
        dpSetCookie('sf_popup_closed_' + btoa(sf_form_id), '1');
        // document.querySelector('body').style.overflow = 'auto';
        // popup_element.style.transform = 'translate(0%, 0%)';
        popup_element.style.bottom = '-100%';
    }

    var popup_open = function (popup_element) {
        popup_element.style.bottom = '0%';
        // document.querySelector('body').style.overflow = 'hidden';
    }

    function dpSetCookie(cname, cvalue, exdays) {
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + d.toUTCString();
        let cookie_str = cname + "=" + cvalue + ";" + expires + ";path=/; secure";
        document.cookie = cookie_str;
    }

    function dpGetCookie(cname) {
        let name = cname + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }
})();
