jQuery(document).ready(function() {
    var post_type = jQuery("select[name='zl_post_type_get']").val();
    GetCategoryziai(post_type);

    jQuery("select[name='zl_post_type_get']").on("change", function(){
        var post_type = jQuery(this).val();
        GetCategoryziai(post_type);
    });

    function GetCategoryziai(post_type) {
        jQuery.ajax({
            type: "POST",
            url: wpAjax.ajaxUrl,
            data: { action: "ziai_category_get", post_type: post_type },
            beforeSend: function () {
                jQuery(".zl-ajax-loader").css({ display: "inline-block" });
            },
            success: function (result) {
                jQuery(".post_type_category").html(result);
                jQuery(".zl-ajax-loader").css({ display: "none" });
            },
        });
    }
});