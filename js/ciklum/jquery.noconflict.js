;
if (typeof jQuery == "undefined") {
    document.write("\<script src='//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js' type='text/javascript'>\<\/script>");
    document.write("\<script type='text/javascript'>$j = jQuery.noConflict();\<\/script>");
} else {
    var $j = jQuery.noConflict();
}
