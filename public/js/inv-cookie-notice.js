/* global cookie_pop_text */
jQuery(document).ready(function($) {
    'use strict';

    //alert(invoptions.domain);

    if (Cookies.get('cookie-pop') != 1) {

        $('body').prepend(
            '<div class="cookie-pop" style="color: '+invoptions.cookietextcolor+'; background-color: '+invoptions.backgroundcolor+'">'+invoptions.domain+ ' '+invoptions.cookietext+' <a href="'+invoptions.privacylink+'" style="text-decoration: underline;">Weitere Infos</a> <button id="accept-cookie" style="background-color: '+invoptions.buttoncolor+'; color: '+invoptions.buttontextcolor+'">'+invoptions.buttontext+'</button></div>'
        );

        $('#accept-cookie').click(function () {
            Cookies.set('cookie-pop', '1', { expires: 365 });
            $('.cookie-pop').remove();
        });

        Cookies.set('cookie-pop', '1', { expires: 365 });
    }
});