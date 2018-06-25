/* global cookie_pop_text */
jQuery(document).ready(function($) {
    'use strict';

    if (Cookies.get('cookie-pop') != 1) {
        $('body').prepend(
            '<div class="cookie-pop" style="color: '+invoptions.cookietextcolor+'; background-color: '+invoptions.backgroundcolor+'"><p>'+invoptions.domain+ ' '+invoptions.cookietext+' <span class="button" id="accept-cookie" style="background-color: '+invoptions.buttoncolor+'; color: '+invoptions.buttontextcolor+'">'+invoptions.buttontext+'</span> <a href="'+invoptions.privacylink+'" style="text-decoration: underline; margin-left: 10px;">'+invoptions.privacylinktext+'</a></p></div>'
        );
        $('#accept-cookie').click(function () {
            Cookies.set('cookie-pop', '1', { expires: 365 });
            $('.cookie-pop').remove();
        });
        //Cookies.set('cookie-pop', '1', { expires: 365 });
    }
});