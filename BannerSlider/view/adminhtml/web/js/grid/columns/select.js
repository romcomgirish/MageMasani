define([
    'underscore',
    'Magento_Ui/js/grid/columns/select'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'MageMasani_BannerSlider/ui/grid/cells/text'
        },
        getStatusColor: function (row) {
            if (row.is_enabled == '1') {
                return 'grid-severity-complete';
            }else if(row.is_enabled == '0') {
                return 'grid-severity-closed';
            }
            return '#303030';
        }
    });
});
