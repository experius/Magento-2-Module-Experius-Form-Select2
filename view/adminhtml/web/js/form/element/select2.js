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

                var searchType = (typeof options.ajax.search === 'undefined') ? '' : '/search/' + options.ajax.search;

                ajaxOptions.ajax.url = options.ajax.url + searchType;
                options = $.extend(options,ajaxOptions);

            }

            if (options.initialValue) {
                $element.select2().val(options.initialValue).trigger('change');
            }

            $element.select2(options);

            $element.on("select2:select", function (e) {

            });

            $element.on("select2:unselect", function (e) {

            });

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

        /** @inheritdoc */
        setInitialValue: function () {
            var value = this.getInitialValue();

            if (!_.isString(value)) {
                this._super();
            }

            this.select2().initialValue = value;

            return this;
        },

        normalizeData: function (value) {

            this.getCurrentValue(value);

            return value;
        },

        getCurrentValue: function(value){

            if(value && this.select2().ajax) {
                var self = this;

                $.post(this.select2().ajax.url, { id: value, form_key: window.FORM_KEY},function (data) {
                    self.addCurrentValueToOptions(data.items, value);
                });
            }
        },

        addCurrentValueToOptions: function(items,value){

            var self = this;

            var options = [];

            $.each(items, function(key,item) {
                options.push({'label': item.text, 'labeltitle': item.text, 'value': item.id});
            });

            this.setOptions(options);

            if(value) {
                this.value(value);
            }

        },

        getPreview: function () {
            var value = this.value(),
                option = this.indexedOptions[value],
                preview = option ? option.label : '';

            this.preview(preview);

            return preview;
        },

        /* Preview fix for use in filters */
        change: function(att, event){

            var $element = $(event.target);

            if(this.select2().ajax) {

                var items = [];
                var values = $element.val();

                if(values) {
                    $.each(values, function(index,value) {
                        var label = $element.find("option[value="+value+"]").text();
                        items.push({'text':label,'id':value})
                    });
                }

                this.addCurrentValueToOptions(items,false);

            }

        }

    });
});
