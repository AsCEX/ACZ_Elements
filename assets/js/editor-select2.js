(function($) {
    $(window).on('elementor/frontend/init', function() {
        if (typeof elementor === 'undefined') return;

        elementor.hooks.addAction('panel/open_editor/widget/acz_post_carousel', function(panel, model, view) {
            const controls = ['include_post_ids', 'exclude_post_ids'];
            
            controls.forEach(controlName => {
                const $control = panel.$el.find('.elementor-control-' + controlName + '.acz-select2-posts select');
                if (!$control.length) return;

                // Configure Select2 with AJAX
                $control.select2({
                    ajax: {
                        url: AczPostCarouselEditor.ajaxUrl,
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                action: 'acz_get_posts_by_query',
                                nonce: AczPostCarouselEditor.nonce,
                                post_type: model.getSetting('post_types') || ['post']
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.data.results
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2,
                    multiple: true
                });

                // Load initial values if any
                const currentValues = model.getSetting(controlName);
                if (currentValues && currentValues.length) {
                    $.ajax({
                        url: AczPostCarouselEditor.ajaxUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'acz_get_posts_value_titles',
                            id: currentValues,
                            nonce: AczPostCarouselEditor.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                for (const id in response.data) {
                                    if (!$control.find("option[value='" + id + "']").length) {
                                        const newOption = new Option(response.data[id], id, true, true);
                                        $control.append(newOption);
                                    }
                                }
                                $control.trigger('change');
                            }
                        }
                    });
                }
            });
        });
    });
})(jQuery);
