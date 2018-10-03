/*jshint jquery:true browser:true*/
/*global Ajax:true alert:true*/
define([
    "jquery",
    "mage/backend/form",
    "jquery/ui",
    "prototype"
], function ($) {
    "use strict";

    $.widget("mage.categoryForm", $.mage.form, {
        options: {
            categoryIdSelector : 'input[name="category[category_id]"]',
            categoryPathSelector : 'input[name="category[path]"]'
        },

        /**
         * Form creation
         * @protected
         */
        _create: function () {
            this._super();
            $('body').on('categoryMove.tree', $.proxy(this.refreshPath, this));
        },

        /**
         * Sending ajax to server to refresh field 'category[path]'
         * @protected
         */
        refreshPath: function () {
            var that = this;
            if (!this.element.find(this.options.categoryIdSelector).prop('value')) {
                return false;
            }
            $.ajax({
                type: 'POST',
                url: this.options.refreshUrl,
                dataType: 'json',
                data: {
                    form_key: FORM_KEY
                }
            }).success(function (data) {
                that._refreshPathSuccess(data);
            });
        },
        _refreshPathSuccess: function (response) {
            if (response.error) {
                alert(response.message);
            } else {
                if (this.element.find(this.options.categoryIdSelector).prop('value') == response.id) {
                    this.element.find(this.options.categoryPathSelector)
                        .prop('value', response.path);
                }
            }
        }
    });

    return $.mage.categoryForm;
});
