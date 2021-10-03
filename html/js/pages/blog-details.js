$(document).ready(function() {
    // HANDLE SHARING LINKS
    var currentPageURL = window.location.href;

    $("#facebook_link").attr("href", 'https://www.facebook.com/share.php?u=' + currentPageURL);
    $("#whatsapp_link").attr("href", 'https://wa.me/?text' + currentPageURL);
    $("#twitter_link").attr("href", 'https://twitter.com/intent/tweet?url=' + currentPageURL);
    $("#linkedin_link").attr("href", 'https://www.linkedin.com/sharing/share-offsite/?url=' + currentPageURL);
    $("#pinterest_link").attr("href", 'http://pinterest.com/pin/create/button/?url=' + currentPageURL);
});