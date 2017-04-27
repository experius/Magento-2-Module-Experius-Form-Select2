/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'ko',
    'jquery',
    '../../lib/select2'
], function (_, registry, Abstract, ko, $, select2) {
    'use strict';

    ko.bindingHandlers.select2 = {
        init: function(element, valueAccessor, allBindings, viewModel, bindingContext){
            var $element = $(element);
            var options = ko.unwrap(valueAccessor());

            if(options.ajax){

                var ajaxOptions = {
                    ajax: {
                        url: "/define_url_in_xml",
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                page: params.page,
                                form_key: window.FORM_KEY
                            };
                        },
                        processResults: function (data, params) {
                            params.page = params.page || 1;
                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },
                        cache: false
                    },
                    minimumInputLength: 1,
                }

                ajaxOptions.ajax.url = options.ajax.url;
                options = $.extend(options,ajaxOptions);
            }

            $element.select2(options);

        }
    }

    return Abstract.extend({

        defaults: {
            select2: {}
        },

        initObservable: function () {
            this._super();

            this.observe('select2');

            return this;
        },

        normalizeData: function (value) {
            console.log(value);

            this.getCurrentValue(value);

            return value;
        },

        getCurrentValue: function(value){

            if(value && this.select2().ajax) {
                var self = this;

                $.post(this.select2().ajax.url, { id: value, form_key: window.FORM_KEY},function (data) {
                    self.addCurrentValueToOptions(data, value);
                });
            }
        },

        addCurrentValueToOptions: function(data,value){

            var self = this;

            $.each(data.items, function(key,item) {
                self.options.push({'label': item.text, 'labeltitle': item.text, 'value': item.id});
            });

            this.value(value);

        }

    });
});