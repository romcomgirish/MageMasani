/**
 * Custom textarea component with dynamic notice text.
 * Updates the notice/comment based on the custom_event dropdown selection.
 */
define([
    'Magento_Ui/js/form/element/textarea'
], function (Textarea) {
    'use strict';

    return Textarea.extend({
        defaults: {
            /** Map of event_constant => notice text */
            noticeMap: {},
            /** Default notice to show when no event-specific notice applies */
            defaultNotice: '',
            /** Track current notification type */
            currentNotificationType: '',
            /** Track current custom event */
            currentCustomEvent: '',
            imports: {
                currentNotificationType: '${ $.provider }:data.notification_type',
                currentCustomEvent: '${ $.provider }:data.custom_event'
            }
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            this.updateNotice();
            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this.defaultNotice = this.notice;
            this._super()
                .observe([
                    'currentNotificationType',
                    'currentCustomEvent'
                ]);

            this.on('currentNotificationType', this.updateNotice.bind(this));
            this.on('currentCustomEvent', this.updateNotice.bind(this));

            return this;
        },

        /**
         * Update notice message based on current type and event values
         */
        updateNotice: function () {
            var type = typeof this.currentNotificationType === 'function' ? this.currentNotificationType() : '';
            var event = typeof this.currentCustomEvent === 'function' ? this.currentCustomEvent() : '';

            if (type === 'event' && event && this.noticeMap[event]) {
                this.notice(this.noticeMap[event]);
            } else {
                this.notice(this.defaultNotice);
            }
        }
    });
});
