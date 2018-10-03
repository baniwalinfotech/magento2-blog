define([
        'jquery'
    ], function ($) {
        var parentCategory = $(".baniwal-blog-expand-tree-2");
        var childCategory = $(".baniwal-blog-expand-tree-3");

        parentCategory.click(function () {
            if ($(this).hasClass("baniwal-blog-expand-tree-2")) {
                $(this).parent().find(".category-level3").slideDown("fast");
                $(this).removeClass("baniwal-blog-expand-tree-2 fa fa-plus-square-o")
                    .addClass("baniwal-blog-narrow-tree-2 fa fa-minus-square-o");
            } else {
                $(this).parent().find(".category-level4").slideUp("fast");
                $(this).parent().find(".category-level3").slideUp("fast");
                $(this).removeClass("baniwal-blog-narrow-tree-2 fa fa-minus-square-o")
                    .addClass("baniwal-blog-expand-tree-2 fa fa-plus-square-o");
                $(this).parent().find(".baniwal-blog-narrow-tree-3").removeClass("baniwal-blog-narrow-tree-3 fa fa-minus-square-o")
                    .addClass("baniwal-blog-expand-tree-3 fa fa-plus-square-o");
            }

        });

        childCategory.click(function () {
            if ($(this).hasClass("baniwal-blog-expand-tree-3")) {
                $(this).parent().find(".category-level4").slideDown("fast");
                $(this).removeClass("baniwal-blog-expand-tree-3 fa fa-plus-square-o")
                    .addClass("baniwal-blog-narrow-tree-3 fa fa-minus-square-o");
            } else {
                $(this).parent().find(".category-level4").slideUp("fast");
                $(this).removeClass("baniwal-blog-narrow-tree-3 fa fa-minus-square-o")
                    .addClass("baniwal-blog-expand-tree-3 fa fa-plus-square-o");
            }
        });
    }
);