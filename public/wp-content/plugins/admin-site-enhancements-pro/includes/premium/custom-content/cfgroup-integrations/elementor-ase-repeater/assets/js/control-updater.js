(function($) {
    'use strict';

    var EARControlUpdater = {
        init: function() {
            this.bindEvents();
            this.initializeRepeaterField();
        },

        bindEvents: function() {
            $(document).on('change', '#elementor-panel-page-settings-controls [data-setting="asenha_loop_repeater_field"]', this.onRepeaterFieldChange.bind(this));
            elementor.channels.editor.on('change:asenha_loop_repeater_field', this.onRepeaterFieldChange.bind(this));
        },

        onRepeaterFieldChange: function(e) {
            var selectedRepeater = e.target ? $(e.target).val() : e;
            this.updateDynamicTagControls(selectedRepeater);
        },

        initializeRepeaterField: function() {
            var self = this;
            elementor.on('preview:loaded', function() {
                var postId = elementor.config.document.id;
                self.fetchSavedRepeaterField(postId);
            });
        },

        fetchSavedRepeaterField: function(postId) {            
            var restUrl = wpApiSettings.root;
            
            if (!restUrl) {
                console.error('REST URL not found');
                return;
            }
        
            var fullUrl = restUrl + 'elementor-ase-repeater/v1/get-saved-repeater-field';
        
            $.ajax({
                url: fullUrl,
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                },
                data: {
                    post_id: postId
                },
                success: function(response) {
                    if (response && response.repeater_field) {
                        EARControlUpdater.updateDynamicTagControls({
                            key: response.repeater_field,
                            name: response.repeater_field_name || ''
                        });
                    } 
                },
                error: function(xhr, status, error) {
                    console.error('Failed to retrieve saved repeater field', status, error);
                }
            });
        },

        updateDynamicTagControls: function(selectedRepeater) {
            try {
                var postId = elementor.config.document.id;
                        
                if (!selectedRepeater) {
                    return;
                }   
        
                var repeaterKey = typeof selectedRepeater === 'string' ? selectedRepeater : selectedRepeater.key;
        
                if (this.lastSelectedRepeater === repeaterKey) {
                    return;
                }
                this.lastSelectedRepeater = repeaterKey;
        
                var dynamicTags = elementor.dynamicTags.getConfig('tags');
                var tagsToUpdate = {};
                
                Object.keys(dynamicTags).forEach(function(tagName) {
                    if (tagName.startsWith('ase-repeater-')) {
                        tagsToUpdate[tagName] = dynamicTags[tagName];
                    }
                });
                
                $.ajax({
                    url: wpApiSettings.root + 'elementor-ase-repeater/v1/update-dynamic-tag-controls',
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
                    },
                    data: {
                        post_id: postId,
                        selected_repeater: repeaterKey,
                        tags: JSON.stringify(tagsToUpdate)
                    },
                    success: function(response) {
                        if (response && response.tags) {
                            EARControlUpdater.updateTagControls(response.tags, response.selected_repeater);
                        } else {
                            console.error('Failed to update dynamic tag controls:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', status, error);
                    }
                });
            } catch (error) {
                console.error('Error in updateDynamicTagControls:', error);
            }
        },
        
        updateTagControls: function(updatedTags, selectedRepeater) {
        
            if (!selectedRepeater) {
                console.warn('No repeater selected, skipping tag control update');
                return;
            }

            var currentTags = elementor.dynamicTags.getConfig('tags');

            if (!currentTags) {
                console.error('Unable to get current tags configuration');
                return;
            }

            Object.keys(currentTags).forEach(function(tagName) {
                if (tagName.startsWith('ase-repeater-')) {
                    if (currentTags[tagName].controls && currentTags[tagName].controls.repeater_field) {
                        currentTags[tagName].controls.repeater_field.default = selectedRepeater;
                    }
                }
            });

            Object.keys(updatedTags).forEach(function(tagName) {
                if (currentTags[tagName] && updatedTags[tagName].controls) {
                    Object.keys(updatedTags[tagName].controls).forEach(function(controlName) {
                        if (!currentTags[tagName].controls[controlName]) {
                            currentTags[tagName].controls[controlName] = {};
                        }
                        Object.assign(currentTags[tagName].controls[controlName], updatedTags[tagName].controls[controlName]);
                    });
                }
            });

            if (elementor.dynamicTags.config) {
                elementor.dynamicTags.config.tags = currentTags;
            } else if (elementor.config && elementor.config.dynamicTags) {
                elementor.config.dynamicTags.tags = currentTags;
            } else {
                console.warn('Unable to update dynamic tags configuration');
            }


            elementor.channels.editor.trigger('change:dynamic');

            if (elementor.getPreviewView && typeof elementor.getPreviewView().renderOnChange === 'function') {
                elementor.getPreviewView().renderOnChange();
            } else {
                console.warn('Unable to force update of controls, Elementor structure not as expected');
            }
        },
    };

    // Initialize only when we're sure we're in the Elementor editor for a loop item
    elementor.on('panel:init', function() {
        if (elementor.config.document.type === 'loop-item') {
            EARControlUpdater.init();
        }
    });

})(jQuery);