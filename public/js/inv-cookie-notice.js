/* global cookie_pop_text */
jQuery(document).ready(function($) {
    'use strict';

    if (Cookies.get('inv-cookie-pop') != 1) {
        $('body').prepend(
            '<div class="inv-cookie-pop" style="color: '+invoptions.cookietextcolor+'; background-color: '+invoptions.backgroundcolor+'"><p>'+invoptions.domain+ ' '+invoptions.cookietext+' <span class="button" id="accept-cookie" style="background-color: '+invoptions.buttoncolor+'; color: '+invoptions.buttontextcolor+'">'+invoptions.buttontext+'</span> <a href="'+invoptions.privacylink+'" class="privacy-link">'+invoptions.privacylinktext+'</a></p></div>'
        );
        $('#accept-cookie').click(function () {
            Cookies.set('inv-cookie-pop', '1', { expires: parseInt(invoptions.cookieduration) });
            $('.inv-cookie-pop').remove();
        });
        //Cookies.set('cookie-pop', '1', { expires: 365 });
    }
});