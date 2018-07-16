/* global cookie_pop_text */
jQuery(document).ready(function($) {
    'use strict';

    if (Cookies.get('inv-cookie-pop') != 1) {
        $('body').prepend(
            '<div class="inv-cookie-pop" style="color: '+invcookienoticeoptions.cookietextcolor+'; background-color: '+invcookienoticeoptions.backgroundcolor+'"><p>'+invcookienoticeoptions.domain+ ' '+invcookienoticeoptions.cookietext+' <span class="button" id="accept-cookie" style="background-color: '+invcookienoticeoptions.buttoncolor+'; color: '+invcookienoticeoptions.buttontextcolor+'">'+invcookienoticeoptions.buttontext+'</span> <a href="'+invcookienoticeoptions.privacylink+'" class="privacy-link">'+invcookienoticeoptions.privacylinktext+'</a></p></div>'
        );
        $('#accept-cookie').click(function () {
            Cookies.set('inv-cookie-pop', '1', { expires: parseInt(invcookienoticeoptions.cookieduration) });
            $('.inv-cookie-pop').remove();
        });
        //Cookies.set('cookie-pop', '1', { expires: 365 });
    }
});