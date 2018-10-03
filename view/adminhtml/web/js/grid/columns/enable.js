define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var label = this._super(record);
            if (label !== '') {
                switch (record.enabled) {
                    case '1':
                        label = '<span class="grid-severity-notice"><span>' + label + '</span></span>';
                        break;
                    case '0':
                        label = '<span class="grid-severity-critical"><span>' + label + '</span></span>';
                        break;
                }
            }
            return label;
        }
    });
});