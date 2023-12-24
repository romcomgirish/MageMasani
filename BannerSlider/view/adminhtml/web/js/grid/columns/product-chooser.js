define([
    'Magento_Ui/js/grid/columns/column',
    'Magento_Catalog/js/components/product-chooser'
], function (Column, ProductChooser) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-product-chooser': true
            },
            productChooserConfig: {
                'button': {
                    'open' : 'Select Product...'
                }
            }
        },

        /**
         * Render the column for each row
         *
         * @param {Object} row - Row data
         * @returns {string}
         */
        getLabel: function (row) {
            // Return the label for the product chooser field here
            return row.product_name;
        },

        /**
         * Render the content for the cell
         *
         * @param {Object} row - Row data
         * @returns {string}
         */
        getBody: function (row) {
            var productChooser = new ProductChooser(this.productChooserConfig);
            productChooser.value(row.product_id);
            return productChooser.element;
        }
    });
});
