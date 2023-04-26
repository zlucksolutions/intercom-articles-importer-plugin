jQuery(document).ready(function($) {
    var post_type = $("select[name='zl_post_type_get']").val();
    var progressBar = $("#progressbar");
    var bulkImportBtn = $('#bulk-import');
    GetCategoryziai(post_type);

    $("select[name='zl_post_type_get']").on("change", function() {
        var post_type = $(this).val();
        GetCategoryziai(post_type);
    });

    $(progressBar).progressbar({
        value: 0
    });

    $(bulkImportBtn).click(function() {
        fireBulkImporter();
    });


    function GetCategoryziai(post_type) {
        $.ajax({
            type: "POST",
            url: wpAjax.ajaxUrl,
            data: { action: "ziai_category_get", post_type: post_type },
            beforeSend: function() {
                $(".zl-ajax-loader").css({ display: "inline-block" });
            },
            success: function(result) {
                $(".post_type_category").html(result);
                $(".zl-ajax-loader").css({ display: "none" });
            },
        });
    }

    function fireBulkImporter() {
        let author = jQuery("#zl_default_author").val();
        let category = jQuery(".post_type_category [name='zl_taxonomie']").val();
        let api_key = jQuery("#ziai_access_token").val();
        $.ajax({
            type: "POST",
            url: wpAjax.ajaxUrl,
            data: { action: "ziai_bulk_importer_init", api_key: api_key, post_type: post_type, author: author, category: category },
            beforeSend: function() {
                $(bulkImportBtn).toggleClass('disabled');
                $(bulkImportBtn).prop('disabled', true);
                $('.zl-button .submit span').addClass('zl-spinner');
            },
            success: function(result) {
                // makes it <result.pages.total_pages irl
                result = JSON.parse(result);
                let currentPage = result.pages.page + 1;
                let maxPage = result.pages.total_pages; // set the maximum number of pages here
                $(progressBar).progressbar({
                    value: (result.pages.page / result.pages.total_pages) * 100
                });

                function nextPage() {
                    if (currentPage <= maxPage) {
                        bulkImporterNextPage(currentPage, currentPage + 1, function() {
                            currentPage++;
                            nextPage();
                        });
                    }
                }

                nextPage();
            },
        });
    }

    function bulkImporterNextPage(currentPage, nextPage, callback) {
        $.ajax({
            type: "POST",
            url: wpAjax.ajaxUrl,
            data: {
                action: "ziai_bulk_importer_get_page",
                currentPage: currentPage,
                nextPage: nextPage
            },
            success: function(result) {
                result = JSON.parse(result);
                if (callback) {
                    callback();
                }
                $(progressBar).progressbar({
                    value: (result.pages.page / result.pages.total_pages) * 100
                });
                if (result.pages.page === result.pages.total_pages) {
                    bulkImporterComplete();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log("Error:", textStatus, errorThrown);
            }
        });
    }

    function bulkImporterComplete() {
        $(bulkImportBtn).toggleClass('disabled');
        $(bulkImportBtn).prop('disabled', false);
        $('.zl-button .submit span').removeClass('zl-spinner');
        $(progressBar).progressbar({
            value: 0
        });
    }
});