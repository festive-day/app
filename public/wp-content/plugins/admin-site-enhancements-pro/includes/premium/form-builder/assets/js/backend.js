var formAdmin = formAdmin || {};

(function ($) {
    'use strict';
    let $buildForm = $('#fb-fields-form'),
        $formMeta = $('#fb-meta-form'),
        $formSettings = $('#fb-settings-form'),
        $styleSettings = $('#fb-style-form'),
        copyHelper = false,
        fieldsUpdated = 0;
    var isCheckedField = false;

    formAdmin = {
        init: function () {
            if ($formSettings.length > 0) {
                this.initFormSettings();

            } else if ($styleSettings.length > 0) {
                this.initStyleSettings();

            } else if ($buildForm.length > 0) {
                $('.formbuilder-ajax-udpate-button').on('click', formAdmin.submitBuild);

            } else {
                this.initOtherSettings();
            }

            formAdmin.liveChanges();

            formAdmin.setupFieldOptionSorting($('.fb-option-list'));

            formAdmin.initBulkOptionsOverlay();

            formAdmin.initNewFormModal();


            $(document).find('.fb-color-picker').wpColorPicker();

            $(document).on('click', '#fb-fields-tabs a', formAdmin.clickNewTab);
            $(document).on('input', '.fb-search-fields-input', formAdmin.searchContent);
            $(document).on('click', '.fb-settings-tab a', formAdmin.clickNewTabSettings);

            /* Image */
            $(document).on('click', '.fb-image-preview .fb-choose-image', formAdmin.addImage);
            $(document).on('click', '.fb-image-preview .fb-remove-image', formAdmin.removeImage);

            /* Add field attr to form in Settings page */
            $(document).on('click', '.fb-add-field-attr-to-form li', formAdmin.addFieldAttrToForm);

            /* Open/Close embed popup */
            $(document).on('click', '.fb-embed-button', function () {
                $('#fb-shortcode-form-modal').addClass('fb-open');
            });

            $(document).on('click', '.formbuilder-close-form-modal', function () {
                $('#fb-shortcode-form-modal').removeClass('fb-open');
            });

            $('.fb-add-more-condition').on('click', formAdmin.addConditionRepeaterBlock);
            $(document).on('click', '.fb-condition-remove', formAdmin.removeConditionRepeaterBlock);

            $(document).on('change', '.fb-fields-type-time .default-value-field', formAdmin.addTimeDefaultValue);
            $(document).on('change', '.fb-fields-type-time .min-value-field, .fb-fields-type-time .max-value-field, .fb-fields-type-time .fb-default-value-field', formAdmin.validateTimeValue);

            $('.fb-fields-type-date .fb-default-value-field').datepicker({
                changeMonth: true,
            });

            document.addEventListener(
                "formbuilder_added_field", (e) => {
                    if (e.hfType == 'date') {
                        $(document).find('.fb-fields-type-date .fb-default-value-field').datepicker({
                            changeMonth: true,
                        });
                    }
                }, false,
            );
        },

        clickNewTab: function () {
            var href = $(this).attr('href'),
                $link = $(this);
            if (typeof href === 'undefined') {
                return false;
            }

            $link.closest('li').addClass('fb-active-tab').siblings('li').removeClass('fb-active-tab');
            $link.closest('.fb-fields-container').find('.fb-fields-panel').hide();
            $(href).show();
            return false;
        },

        searchContent: function () {
            $('.fields-list-accordion').addClass('search-is-filtering');
            
            var i,
                searchText = $(this).val().toLowerCase(),
                toSearch = $(this).attr('data-tosearch'),
                $items = $('.' + toSearch);

            $items.each(function () {
                if ($(this).attr('data-field-name').toLowerCase().indexOf(searchText) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            // Restore all results when the x button on search input field is clicked. 
            // The click triggers a 'search' event we're listening to below
            if ( searchText.length === 0 ) {
                $('.fields-list-accordion').removeClass('search-is-filtering');
            }
        },

        clickNewTabSettings: function () {
            var id = this.getAttribute('href'),
                $link = $(this);

            if (typeof id === 'undefined') {
                return false;
            }

            $link.closest('li').addClass('fb-active').siblings('li').removeClass('fb-active');
            $(id).removeClass('fb-hidden').siblings().addClass('fb-hidden');
            return false;
        },

        addImage: function (e) {
            e.preventDefault();
            const imagePreview = $(this).closest('.fb-image-preview');
            const fileFrame = wp.media({
                multiple: false,
                library: {
                    type: ['image']
                }
            });

            fileFrame.on('select', function () {
                const attachment = fileFrame.state().get('selection').first().toJSON();
                imagePreview.find('img').attr('src', attachment.url);
                imagePreview.find('input.fb-image-id').val(attachment.id);
                imagePreview.find('.fb-image-preview-wrap').removeClass('fb-hidden');
                imagePreview.find('.fb-choose-image').addClass('fb-hidden');

                const frontImagePreview = imagePreview.find('input.fb-image-id').attr('id');
                $('.' + frontImagePreview).append('<img src="' + attachment.url + '"/>');
                $('.' + frontImagePreview).find('.fb-no-image-field').addClass('fb-hidden');
            });
            fileFrame.open();
        },

        removeImage: function (e) {
            const imagePreview = $(this).closest('.fb-image-preview');
            e.preventDefault();
            imagePreview.find('img').attr('src', '');
            imagePreview.find('.fb-image-preview-wrap').addClass('fb-hidden');
            imagePreview.find('.fb-choose-image').removeClass('fb-hidden');
            imagePreview.find('input.fb-image-id').val('');

            const frontImagePreview = imagePreview.find('input.fb-image-id').attr('id');
            $('.' + frontImagePreview).find('.fb-no-image-field').removeClass('fb-hidden');
            $('.' + frontImagePreview).find('img').remove();
        },

        addFieldAttrToForm: function (e) {
            const fieldId = $(this).attr('data-value');
            const inputChange = $(this).closest('.fb-form-row').find('input');
            const textAreaChange = $(this).closest('.fb-form-row').find('textarea');

            if (fieldId && inputChange.length > 0) {
                inputChange.val(inputChange.val() + ' ' + fieldId);
            }

            if (fieldId && textAreaChange.length > 0) {
                textAreaChange.val(textAreaChange.val() + ' ' + fieldId);
            }
        },

        submitBuild: function (e) {
            e.preventDefault();
            var $thisEle = this;
            formAdmin.preFormSave(this);
            /*
            var fieldsData = $buildForm.serializeArray(); 
            var settingsData = $formMeta.serializeArray(); 

            var formbuilder_fields = {};
            $.each(fieldsData, function () {
                formbuilder_fields[this.name] = this.value;
            });

            var formbuilder_settings = {};
            $.each(settingsData, function () {
                formbuilder_settings[this.name] = this.value;
            });
            */
            var formbuilder_fields = JSON.stringify($buildForm.serializeArray());
            var formbuilder_settings = JSON.stringify($formMeta.serializeArray());

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'formbuilder_update_form',
                    formbuilder_fields: formbuilder_fields,
                    formbuilder_settings: formbuilder_settings,
                    nonce: formbuilder_backend_js.nonce
                },
                success: function (msg) {
                    formAdmin.afterFormSave($thisEle);
                    // var $postStuff = document.getElementById('fb-form-panel');
                    // var $html = document.createElement('div');
                    // $html.setAttribute('class', 'fb-updated-info');
                    // $html.innerHTML = msg;
                    // $postStuff.insertBefore($html, $postStuff.firstChild);
                }
            });
        },

        addImageToOption: function (e) {
            e.preventDefault();
            const imagePreview = e.target.closest('li');
            const fileFrame = wp.media({
                multiple: false,
                library: {
                    type: ['image']
                }
            });

            fileFrame.on('select', function () {
                const attachment = fileFrame.state().get('selection').first().toJSON();
                const $imagePreview = $(imagePreview);
                $imagePreview.find('.fb-is-image-holder').html('<img src="' + attachment.url + '"/>');
                $imagePreview.find('.fb-is-image-preview-box').addClass('fb-image-added');

                $imagePreview.find('input.fb-image-id').val(attachment.id).trigger('change');
                var fieldId = $imagePreview.closest('.fb-fields-settings').data('fid');
                formAdmin.resetDisplayedOpts(fieldId);
            });
            fileFrame.open();
        },

        removeImageFromOption: function (e) {
            var $this = $(this),
                previewWrapper = $this.closest('li');
            e.preventDefault();
            e.stopPropagation();

            previewWrapper.find('.fb-is-image-holder').html('');
            previewWrapper.find('.fb-is-image-preview-box').removeClass('fb-image-added');
            previewWrapper.find('input.fb-image-id').val('').trigger('change');
            var fieldId = previewWrapper.closest('.fb-fields-settings').data('fid');
            formAdmin.resetDisplayedOpts(fieldId);
        },

        liveChanges: function () {
            $('#fb-meta-panel').on('input', '[data-changeme]', formAdmin.liveChangesInput);
            $('#fb-meta-panel').on('change', 'select[name="submit_btn_alignment"]', formAdmin.liveChangeButtonPosition);

            // $buildForm.on('input, change', '[data-changeme]', formAdmin.liveChangesInput); // Original. No live update for keyboard input.
            $buildForm.on('change', 'select[data-changeme]', formAdmin.liveChangesInput);
            $buildForm.on('keyup', '[data-changeme]', formAdmin.liveChangesInput); // Will instantly update preview pane on keyboard input.

            $buildForm.on('click', 'input.fb-form-field-required', formAdmin.markRequired);

            $buildForm.on('click', '.fb-add-option', formAdmin.addFieldOption);

            // Default, row, column options in choice / matrix fields
            $buildForm.on('input', '.fb-option-list-type-default .fb-single-option input[type="text"]', formAdmin.resetOptOnChange);
            $buildForm.on('input', '.fb-option-list-type-rows .fb-single-option input[type="text"]', formAdmin.resetMatrixOptOnChange);
            $buildForm.on('input', '.fb-option-list-type-columns .fb-single-option input[type="text"]', formAdmin.resetMatrixOptOnChange);

            $buildForm.on('mousedown', '.fb-single-option input[type=radio]', formAdmin.maybeUncheckRadio);
            $buildForm.on('click', '.fb-single-option .fb-choice-input', formAdmin.resetOptOnChange);
            $buildForm.on('change', '.fb-image-id', formAdmin.resetOptOnChange);

            $buildForm.on('click', '.fb-single-option a[data-removeid]', formAdmin.deleteFieldOption);

            $buildForm.on('click', '.fb-is-image-preview-box', formAdmin.addImageToOption);
            $buildForm.on('click', '.fb-is-remove-image', formAdmin.removeImageFromOption);

            $buildForm.on('input', '[data-changeheight]', formAdmin.liveChangeHeight);
            $buildForm.on('input', '[data-changerows]', formAdmin.liveChangeRows);
            $buildForm.on('input', '[data-changestars]', formAdmin.liveChangeStars);

            $buildForm.on('change', 'select[name^="field_options[label_position"]', formAdmin.liveChangeLabelPosition);
            $buildForm.on('change', 'select[name^="field_options[label_alignment"]', formAdmin.liveChangeLabelAlignment);

            $buildForm.on('change', 'select[name^="field_options[options_layout"]', formAdmin.liveChangeOptionsLayout);
            $buildForm.on('change', 'select[name^="field_options[heading_type"]', formAdmin.liveChangeHeadingType);
            $buildForm.on('change', 'select[name^="field_options[text_alignment"]', formAdmin.liveChangeTextAlignment);
            $buildForm.on('change', 'select.fb-select-image-type', formAdmin.liveChangeSelectImageType);

            $buildForm.on('change', '[data-changebordertype]', formAdmin.liveChangeBorderType);
            $buildForm.on('input', '[data-changeborderwidth]', formAdmin.liveChangeBorderWidth);

            $buildForm.on('input', 'input[name^="field_options[field_max_width"]', formAdmin.liveChangeFieldMaxWidth);
            $buildForm.on('change', 'select[name^="field_options[field_max_width_unit"]', formAdmin.liveChangeFieldMaxWidth);

            $buildForm.on('input', 'input[name^="field_options[image_max_width"]', formAdmin.liveChangeImageMaxWidth);
            $buildForm.on('change', 'select[name^="field_options[image_max_width_unit"]', formAdmin.liveChangeImageMaxWidth);

            $buildForm.on('change click', '[data-disablefield]', formAdmin.liveChangeAddressFields);

            $buildForm.on('change click', 'input[name^="field_options[auto_width"]', formAdmin.liveChangeAutoWidth);

            $buildForm.on('change', 'select[name^="field_options[field_alignment"]', formAdmin.liveChangeFieldAlignment);

            $buildForm.on('change', '[data-row-show-hide]', formAdmin.liveChangeHideShowRow);
            $buildForm.on('input', '[data-label-show-hide]', formAdmin.liveChangeHideShowLabel);
            $buildForm.on('change', '[data-label-show-hide-checkbox]', formAdmin.liveChangeHideShowLabelCheckbox);
        },

        liveChangesInput: function () {
            var option,
                newValue = this.value,
                changes = document.getElementById(this.getAttribute('data-changeme')),
                att = this.getAttribute('data-changeatt'),
                fieldAttrType = this.getAttribute('type'),
                parentField = $(changes).closest('.fb-editor-form-field');

            if (att == 'value' && fieldAttrType == "email") {
                $(this).closest('div').find('.fb-error').remove();
                if (newValue && !formAdmin.isEmail(newValue)) {
                    $(this).closest('div').append('<p class="fb-error">Invalid Email Value</p>');
                }
            }

            if (att == 'value' && parentField.attr('data-type') == 'url') {
                $(this).closest('div').find('.fb-error').remove();
                if (newValue && !formAdmin.isUrl(newValue)) {
                    $(this).closest('div').append('<p class="fb-error">Invalid Website / URL Value. Please add full URL value</p>');
                }
            }

            if (parentField.attr('data-type') == 'range_slider') {
                setTimeout(function () {
                    var newSlider = parentField.find('.formbuilder-range-input-selector');
                    var sliderValue = newSlider.val();
                    var sliderMinValue = parseFloat(newSlider.attr('min'));
                    var sliderMaxValue = parseFloat(newSlider.attr('max'));
                    var sliderStepValue = parseFloat(newSlider.attr('step'));
                    sliderValue = sliderValue < sliderMinValue ? sliderMinValue : sliderValue;
                    sliderValue = sliderValue > sliderMaxValue ? sliderMaxValue : sliderValue;
                    var remainder = sliderValue % sliderStepValue;
                    sliderValue = sliderValue - remainder;
                    newSlider.prev('.formbuilder-range-slider').slider({
                        value: sliderValue,
                        min: sliderMinValue,
                        max: sliderMaxValue,
                        step: sliderStepValue,
                        range: 'min',
                        slide: function (e, ui) {
                            $(this).next().val(ui.value).trigger('change');
                        }
                    });
                }, 100)
            }

            if (changes === null) {
                return;
            }

            if (att !== null) {
                if (changes.tagName === 'SELECT' && att === 'placeholder') {
                    option = changes.options[0];
                    if (option.value === '') {
                        option.innerHTML = newValue;
                    } else {
                        // Create a placeholder option if there are no blank values.
                        formAdmin.addBlankSelectOption(changes, newValue);
                    }
                } else if (att === 'class') {
                    formAdmin.changeFieldClass(changes, this);
                } else {
                    if ('TEXTAREA' === changes.nodeName && att == 'value') {
                        changes.innerHTML = newValue;
                    } else {
                        changes.setAttribute(att, newValue);
                    }
                }
            } else if (changes.id.indexOf('setup-message') === 0) {
                if (newValue !== '') {
                    changes.innerHTML = '<input type="text" value="" disabled />';
                }
            } else {
                changes.innerHTML = newValue;

                if ('TEXTAREA' === changes.nodeName && changes.classList.contains('wp-editor-area')) {
                    $(changes).trigger('change');
                }

                if (changes.classList.contains('fb-form-label') && 'break' === changes.nextElementSibling.getAttribute('data-type')) {
                    changes.nextElementSibling.querySelector('.fb-editor-submit-button').textContent = newValue;
                }
            }
        },

        liveChangeButtonPosition: function (e) {
            $('.fb-editor-submit-button-wrap').removeClass('fb-submit-btn-align-left fb-submit-btn-align-right fb-submit-btn-align-center').addClass('fb-submit-btn-align-' + e.target.value);
        },

        markRequired: function () {
            var thisid = this.id.replace('fb-', ''),
                fieldId = thisid.replace('req-field-', ''),
                checked = this.checked,
                label = $('#fb-editor-field-required-' + fieldId);

            formAdmin.toggleValidationBox(checked, '.fb-required-detail-' + fieldId);

            if (checked) {
                var $reqBox = $('input[name="field_options[required_indicator_' + fieldId + ']"]');
                if ($reqBox.val() === '') {
                    $reqBox.val('*');
                }
                label.removeClass('fb-hidden');
            } else {
                label.addClass('fb-hidden');
            }
        },

        //Add new option or "Other" option to radio / checkbox / dropdown / scale / likert_matrix_scale
        addFieldOption: function () {
            /*jshint validthis:true */
            var fieldId = $(this).closest('.fb-fields-settings').data('fid'),
                fieldType = $(this).closest('.fb-fields-settings').data('field-type'), // e.g. 'radio', 'likert_matrix_scale', 'matrix_of_dropdowns'
                fieldKey = $(this).closest('.fb-fields-settings').data('field-key'), // e.g. 9ziztc
                optionsId = $(this).data('options-id'), // e.g. for Likert / Matrix Scale field, can be 'rows' or 'columns'
                newOption = '',         
                optType = $(this).data('opttype'),
                optKey = 0,
                oldKey = '000',
                lastKey = formAdmin.getHighestOptKey(fieldId,optionsId);
                
            if ( 'default' == optionsId ) {
                // e.g. for radio / checkbox / dropdown / scale
                newOption = $('#fb-field-options-' + fieldId + ' .fb-option-template').prop('outerHTML');
            } else {
                // e.g. for likert_matrix_scale, can be 'rows', 'columns', 'dropdowns', 'first_dropdown', etc.
                newOption = $('#fb-field-options-' + fieldId + '-' + optionsId + ' .fb-option-template').prop('outerHTML');
            }

            if (lastKey !== oldKey) {
                optKey = lastKey + 1;
            }

            //Update hidden field
            if (optType === 'other') {
                document.getElementById('other_input_' + fieldId).value = 1;

                //Hide "Add Other" option now if this is radio field
                var ftype = $(this).data('ftype');
                if (ftype === 'radio' || ftype === 'select') {
                    $(this).fadeOut('slow');
                }

                var data = {
                    action: 'fb-add-field_option',
                    field_id: fieldId,
                    opt_key: optKey,
                    opt_type: optType,
                    nonce: formbuilder_backend_js.nonce
                };

                jQuery.post(ajaxurl, data, function (msg) {
                    $('#fb-field-options-' + fieldId).append(msg);
                    formAdmin.resetDisplayedOpts(fieldId,optionsId,fieldType,fieldKey);
                });

            } else {
                // here, optType is most likely 'single'
                newOption = newOption.replace(new RegExp('optkey="' + oldKey + '"', 'g'), 'optkey="' + optKey + '"');
                newOption = newOption.replace(new RegExp('-' + oldKey + '_', 'g'), '-' + optKey + '_');
                newOption = newOption.replace(new RegExp('-' + oldKey + '"', 'g'), '-' + optKey + '"');
                newOption = newOption.replace(new RegExp('\\[' + oldKey + '\\]', 'g'), '[' + optKey + ']');
                newOption = newOption.replace('fb-hidden fb-option-template', '');
                newOption = {newOption};

                if ( 'default' == optionsId ) {
                    // e.g. for radio / checkbox / dropdown / scale
                    $('#fb-field-options-' + fieldId).append(newOption.newOption);
                } else {
                    // e.g. for likert_matrix_scale, can be 'rows', 'columns', 'dropdowns', 'first_dropdown', etc.
                    $('#fb-field-options-' + fieldId + '-' + optionsId).append(newOption.newOption);
                }
                formAdmin.resetDisplayedOpts(fieldId,optionsId,fieldType,fieldKey);
            }
        },

        resetOptOnChange: function () {
            var field, thisOpt;
            var check = $(this);

            field = formAdmin.getFieldKeyFromOpt(this);
            if (!field) {
                return;
            }

            thisOpt = $(this).closest('li');
            formAdmin.resetSingleOpt(field.fieldId, field.fieldKey, thisOpt);

            setTimeout(function () {
                check.next('input').trigger('change');
            }, 100);
        },

        // For matrix fields options
        resetMatrixOptOnChange: function () {
            var field, thisOpt;
            var check = $(this);

            field = formAdmin.getFieldKeyFromOpt(this);
            if (!field) {
                return;
            }

            thisOpt = $(this).closest('li');
            formAdmin.resetMatrixSingleOpt(field.fieldId, field.fieldKey, thisOpt);

            setTimeout(function () {
                check.next('input').trigger('change');
            }, 100);
        },
        
        maybeUncheckRadio: function () {
            var $self, uncheck, unbind, up;

            $self = $(this);
            if ($self.is(':checked')) {
                uncheck = function () {
                    setTimeout(function () {
                        $self.prop('checked', false);
                    }, 0);
                };

                unbind = function () {
                    $self.off('mouseup', up);
                };

                up = function () {
                    uncheck();
                    unbind();
                };

                $self.on('mouseup', up);
                $self.one('mouseout', unbind);
            } else {
                $self.closest('li').siblings().find('.fb-choice-input').prop('checked', false);
            }
        },

        deleteFieldOption: function () {
            var otherInput,
                parentLi = this.closest('li'),
                parentUl = parentLi.parentNode,
                fieldId = this.getAttribute('data-fid'),
                optionsId = $(this).data('options-id'), // e.g. for Likert / Matrix Scale field, can be 'rows' or 'columns'
                fieldType,
                fieldKey;

            if ( 'default' == optionsId ) {
                fieldType = this.getAttribute('data-field-type');
                fieldKey = this.getAttribute('data-field-key');
            } else {
                fieldType = parentUl.getAttribute('data-field-type');
                fieldKey = parentUl.getAttribute('data-key');                
            }

            $(parentLi).fadeOut('slow', function () {
                $(parentLi).remove();
                var hasOther = $(parentUl).find('.formbuilder_other_option');
                if (hasOther.length < 1) {
                    otherInput = document.getElementById('other_input_' + fieldId);
                    if (otherInput !== null) {
                        otherInput.value = 0;
                    }
                    $('#other_button_' + fieldId).fadeIn('slow');
                }
                formAdmin.resetDisplayedOpts(fieldId,optionsId,fieldType,fieldKey);
            });
        },

        liveChangeHeight: function () {
            var newValue = this.value,
                changes = document.getElementById(this.getAttribute('data-changeheight'));

            if (changes === null) {
                return;
            }

            $(changes).css("height", newValue);
        },

        liveChangeRows: function () {
            var newValue = this.value,
                changes = document.getElementById(this.getAttribute('data-changerows'));

            if (changes === null) {
                return;
            }

            $(changes).attr("rows", newValue);
        },

        liveChangeStars: function () {
            var newValue = this.value,
                stars = '',
                changes = document.getElementById(this.getAttribute('data-changestars'));

            if (changes === null) {
                return;
            }

            for (var i = 0; i < newValue; i++) {
                stars = stars + '<label class="fb-star-rating"><input type="radio"><span class="fb-star-outline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.5" d="m12 2l3.104 6.728l7.358.873l-5.44 5.03l1.444 7.268L12 18.28L5.534 21.9l1.444-7.268L1.538 9.6l7.359-.873z"/></svg></span></label>';
            }
            $(changes).html(stars);
        },

        liveChangeLabelPosition: function (e) {
            const fieldId = $(this).closest('.fb-fields-settings').data('fid');
            $('#fb-editor-field-id-' + fieldId).removeClass('fb-label-position-top').removeClass('fb-label-position-left').removeClass('fb-label-position-right').removeClass('fb-label-position-hide').addClass('fb-label-position-' + e.target.value);
        },

        liveChangeLabelAlignment: function (e) {
            const fieldId = $(this).closest('.fb-fields-settings').data('fid');
            $('#fb-editor-field-id-' + fieldId).removeClass('fb-label-alignment-left').removeClass('fb-label-alignment-right').removeClass('fb-label-alignment-center').addClass('fb-label-alignment-' + e.target.value);
        },

        liveChangeOptionsLayout: function (e) {
            const fieldId = $(this).closest('.fb-fields-settings').data('fid');
            $('#fb-editor-field-id-' + fieldId).removeClass('fb-options-layout-inline').removeClass('fb-options-layout-1').removeClass('fb-options-layout-2').removeClass('fb-options-layout-3').removeClass('fb-options-layout-4').removeClass('fb-options-layout-5').removeClass('fb-options-layout-6').addClass('fb-options-layout-' + e.target.value);
        },

        liveChangeHeadingType: function (e) {
            const fieldId = $(this).closest('.fb-fields-settings').data('fid');
            $('#fb-field-' + fieldId).replaceWith(function () {
                return '<' + e.target.value + ' id="' + 'fb-field-' + fieldId + '">' + $(this).html() + '</' + e.target.value + '>';
            });
        },

        liveChangeTextAlignment: function (e) {
            const fieldId = $(this).closest('.fb-fields-settings').data('fid');
            $('#fb-editor-field-id-' + fieldId).removeClass('fb-text-alignment-left').removeClass('fb-text-alignment-right').removeClass('fb-text-alignment-center').addClass('fb-text-alignment-' + e.target.value);
        },

        liveChangeSelectImageType: function () {
            var option = $(this).val();
            var id = $(this).attr('data-is-id');
            $('#fb-field-options-' + id).find('.fb-choice-input').prop('checked', false);
            $('#fb-editor-field-container-' + id).find('input').prop('checked', false);
            $('#fb-field-options-' + id).find('.fb-choice-input').attr('type', option);
            $('#fb-editor-field-container-' + id).find('input').attr('type', option);
        },

        liveChangeBorderType: function (e) {
            $('#' + this.getAttribute('data-changebordertype')).css("border-bottom-style", this.value);
        },

        liveChangeBorderWidth: function (e) {
            $('#' + this.getAttribute('data-changeborderwidth')).css("border-bottom-width", this.value + 'px');
        },

        liveChangeFieldMaxWidth: function () {
            const settings = $(this).closest('.fb-fields-settings');
            const fieldId = settings.data('fid');
            const fieldMaxWidth = settings.find('input[name^="field_options[field_max_width"]').val();
            const fieldMaxWidthUnit = settings.find('select[name^="field_options[field_max_width_unit"]').val();
            if (parseInt(fieldMaxWidth) > 0) {
                $('#fb-editor-field-container-' + fieldId).css('--fb-width', parseInt(fieldMaxWidth) + fieldMaxWidthUnit);
            } else {
                $('#fb-editor-field-container-' + fieldId).prop('style').removeProperty('--fb-width');
            }
        },

        liveChangeImageMaxWidth: function () {
            const settings = $(this).closest('.fb-fields-settings');
            const fieldId = settings.data('fid');
            const imageMaxWidth = settings.find('input[name^="field_options[image_max_width"]').val();
            const imageMaxWidthUnit = settings.find('select[name^="field_options[image_max_width_unit"]').val();
            if (parseInt(imageMaxWidth) > 0) {
                $('#fb-editor-field-container-' + fieldId).css('--fb-image-width', parseInt(imageMaxWidth) + imageMaxWidthUnit);
            } else {
                $('#fb-editor-field-container-' + fieldId).prop('style').removeProperty('--fb-image-width');
            }
        },

        liveChangeAddressFields: function () {
            const disableField = $(this).attr('data-disablefield');
            if ($(this).is(":checked")) {
                $(document).find('#' + disableField).addClass('fb-hidden');
            } else {
                $(document).find('#' + disableField).removeClass('fb-hidden');
            }
        },

        liveChangeAutoWidth: function (e) {
            const fieldId = $(this).closest('.fb-fields-settings').data('fid');
            if ($(this).is(":checked")) {
                $('#fb-editor-field-id-' + fieldId).addClass('fb-auto-width');
            } else {
                $('#fb-editor-field-id-' + fieldId).removeClass('fb-auto-width');
            }
        },

        liveChangeFieldAlignment: function (e) {
            const fieldId = $(this).closest('.fb-fields-settings').data('fid');
            $('#fb-editor-field-id-' + fieldId).removeClass('fb-field-alignment-left').removeClass('fb-field-alignment-right').removeClass('fb-field-alignment-center').addClass('fb-field-alignment-' + e.target.value);
        },

        initFormSettings: function () {
            $('.formbuilder-ajax-udpate-button').on('click', formAdmin.submitSettingsBuild);
            $('.fb-multiple-rows').on('click', '.fb-add-email', function () {
                $(this).closest('.fb-multiple-rows').find('.fb-multiple-email').append('<div class="fb-email-row"><input type="email" name="email_to[]" value=""/><span class="fb fb-trash-can-outline fb-delete-email-row"><svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" viewBox="0 0 24 24"><path fill="currentColor" d="M6.225 4.811a1 1 0 0 0-1.414 1.414L10.586 12L4.81 17.775a1 1 0 1 0 1.414 1.414L12 13.414l5.775 5.775a1 1 0 0 0 1.414-1.414L13.414 12l5.775-5.775a1 1 0 0 0-1.414-1.414L12 10.586z"/></svg></span></div>');
            })
            $(document).on('click', '.fb-multiple-rows .fb-delete-email-row', function () {
                $(this).closest('.fb-email-row').remove();
            })
            $('.fb-multiple-rows').on('click', '.fb-add-webhook', function () {
                $(this).closest('.fb-multiple-rows').find('.fb-multiple-webhook').append('<div class="fb-webhook-row"><input type="text" name="webhook_urls[]" value=""/><span class="fb fb-trash-can-outline fb-delete-webhook-row"><svg xmlns="http://www.w3.org/2000/svg" width="16px" height="16px" viewBox="0 0 24 24"><path fill="currentColor" d="M6.225 4.811a1 1 0 0 0-1.414 1.414L10.586 12L4.81 17.775a1 1 0 1 0 1.414 1.414L12 13.414l5.775 5.775a1 1 0 0 0 1.414-1.414L13.414 12l5.775-5.775a1 1 0 0 0-1.414-1.414L12 10.586z"/></svg></span></div>');
            })
            $(document).on('click', '.fb-multiple-rows .fb-delete-webhook-row', function () {
                $(this).closest('.fb-webhook-row').remove();
            })
        },

        addConditionRepeaterBlock: async function (e) {
            e.preventDefault();
            const parentBlock = $(this).closest('.fb-form-row');
            const parentRepeaterBlock = parentBlock.find('.fb-condition-repeater-blocks');
            await $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'formbuilder_add_more_condition_block',
                    form_id: $("#form_id").val()
                },
                success: function (msg) {
                    parentRepeaterBlock.append(msg);
                }
            })
        },

        removeConditionRepeaterBlock: function () {
            const parentBlock = $(this).closest('.fb-condition-repeater-block');
            parentBlock.remove();
        },

        submitSettingsBuild: function (e) {
            e.preventDefault();
            var $thisEle = this;
            formAdmin.preFormSave(this);
            var v = JSON.stringify($formSettings.serializeArray());
            $('#formbuilder_compact_fields').val(v);
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'formbuilder_save_form_settings',
                    formbuilder_compact_fields: v,
                    nonce: formbuilder_backend_js.nonce
                },
                success: function (msg) {
                    formAdmin.afterFormSave($thisEle);
                    // var $postStuff = document.getElementById('fb-form-panel');
                    // var $html = document.createElement('div');
                    // $html.setAttribute('class', 'fb-updated-info');
                    // $html.innerHTML = msg;
                    // $postStuff.insertBefore($html, $postStuff.firstChild);
                }
            });
        },

        initStyleSettings: function () {
            $('.formbuilder-ajax-udpate-button').on('click', formAdmin.submitStylesBuild);
            $('#fb-form-style-template').on('change', function (e) {
                e.preventDefault();
                const templateID = $(this).val();
                var style = '';
                if (templateID) {
                    style = $(document).find('option[value="' + templateID + '"]').attr('data-style');
                }
                $('style.fb-style-content').text(style);
            });
            $('#fb-form-style-select').on('change', function (e) {
                e.preventDefault();
                const styleClass = $(this).find(":selected").val();
                $(document).find('form.formbuilder-form').removeClass('fb-form-no-style').removeClass('fb-form-default-style').removeClass('fb-form-custom-style').addClass('fb-form-' + styleClass);
            });
        },

        submitStylesBuild: function (e) {
            e.preventDefault();
            var $thisEle = this;
            formAdmin.preFormSave(this);
            var v = JSON.stringify($styleSettings.serializeArray());
            $('#formbuilder_compact_fields').val(v);
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'formbuilder_save_form_style',
                    'formbuilder_compact_fields': v,
                    nonce: formbuilder_backend_js.nonce
                },
                success: function (msg) {
                    formAdmin.afterFormSave($thisEle);
                    // var $postStuff = document.getElementById('fb-form-panel');
                    // var $html = document.createElement('div');
                    // $html.setAttribute('class', 'fb-updated-info');
                    // $html.innerHTML = msg;
                    // $postStuff.insertBefore($html, $postStuff.firstChild);
                }
            });
        },

        initOtherSettings: function () {
            $(document).on('click', '#fb-test-email-button', function (e) {
                e.preventDefault();
                const testEmailButton = $(this);
                const testEmail = $(document).find('#fb-test-email').val();
                $(document).find('.fb-error').remove();
                if (!formAdmin.isEmail(testEmail)) {
                    testEmailButton.closest('.fb-grid-3').append('<div class="fb-error">Invalid Email</div>');
                    return;
                }
                testEmailButton.addClass('fb-loading-button');
                var emailTemplate = $('#fb-settings-email-template').val();
                $('.fb-test-email-notice').html('');
                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'formbuilder_test_email_template',
                        email_template: emailTemplate,
                        test_email: testEmail,
                        nonce: formbuilder_backend_js.nonce
                    },
                    success: function (res) {
                        testEmailButton.removeClass('fb-loading-button');
                        const response = JSON.parse(res);
                        if (response.success) {
                            testEmailButton.closest('.fb-settings-row').find('.fb-test-email-notice').html('<div class="fb-success">' + response.message + '</div>');
                        } else {
                            testEmailButton.closest('.fb-settings-row').find('.fb-test-email-notice').html('<div class="fb-error">' + response.message + '</div>');
                        }
                    }
                });
            })
        },

        isEmail: function (email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        },

        isUrl: function (url) {
            var regex = /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
            return regex.test(url);
        },

        setupFieldOptionSorting: function (sort) {
            var opts = {
                items: 'li',
                axis: 'y',
                opacity: 0.65,
                forcePlaceholderSize: false,
                handle: '.fb-drag',
                helper: function (e, li) {
                    if (li.find('input[type="radio"]:checked, input[type="checkbox"]:checked').length > 0) {
                        isCheckedField = true;
                    }
                    // copyHelper = li.clone().insertAfter(li);
                    return li.clone();
                },
                stop: function (e, ui) {
                    // copyHelper && copyHelper.remove();
                    var fieldId = ui.item.attr('id').replace('fb-option-list-', '').replace('-' + ui.item.data('optkey'), '');
                    var optionsId = ui.item.closest('.fb-option-list').attr('data-options-id');
                    var fieldType = ui.item.closest('.fb-option-list').attr('data-field-type');
                    var fieldKey = ui.item.closest('.fb-option-list').attr('data-key');
                    formAdmin.resetDisplayedOpts(fieldId,optionsId,fieldType,fieldKey);
                    var uiSortField = ui.item.find('input[type="radio"], input[type="checkbox"]');

                    if (isCheckedField) {
                        uiSortField.prop('checked', true);
                        ui.item.find('input[type="radio"]').trigger('click');
                        isCheckedField = false;
                    }
                }
            };
            $(sort).sortable(opts);
        },

        getFieldKeyFromOpt: function (object) {
            var allOpts, fieldId, fieldKey;

            allOpts = $(object).closest('.fb-option-list');
            if (!allOpts.length) {
                return false;
            }

            fieldId = allOpts.attr('id').replace('fb-field-options-', '');
            fieldKey = allOpts.data('key');

            return {
                fieldId: fieldId,
                fieldKey: fieldKey
            };
        },

        usingSeparateValues: function (fieldId) {
            var field = document.getElementById('separate_value_' + fieldId);
            if (field === null) {
                return false;
            } else {
                return field.checked;
            }
        },

        resetSingleOpt: function (fieldId, fieldKey, thisOpt) {
            var saved, text, defaultVal, previewInput,
                optionsId = thisOpt.data('options-id'),
                optKey = thisOpt.data('optkey'),
                separateValues = formAdmin.usingSeparateValues(fieldId),
                single = $('label[for="field_' + fieldKey + '-' + optKey + '"]'),
                baseName = 'field_options[options_' + fieldId + '][' + optKey + ']',
                label = $('input[name="' + baseName + '[label]"]');
                
            if (single.length < 1) {
                formAdmin.resetDisplayedOpts(fieldId,optionsId);

                // Set the default value.
                defaultVal = thisOpt.find('input[name^="default_value_"]');
                if (defaultVal.is(':checked') && label.length > 0) {
                    $('select[name^="item_meta[' + fieldId + ']"]').val(label.val());
                }
                return;
            }

            previewInput = single.children('input');

            if (label.length < 1) {
                // Check for other label.
                label = $('input[name="' + baseName + '"]');
                saved = label.val();
            } else if (separateValues) {
                saved = $('input[name="' + baseName + '[value]"]').val();
            } else {
                saved = label.val();
            }

            if (label.length < 1) {
                return;
            }

            // Set the displayed value.
            text = single[0].childNodes;
            text[text.length - 1].nodeValue = ' ' + label.val();
            previewInput.closest('.fb-choice').find('.fb-field-is-label').text(saved);

            // Set saved value.
            previewInput.val(saved);

            // Set the default value.
            defaultVal = thisOpt.find('input[name^="default_value_"]');
            previewInput.prop('checked', defaultVal.is(':checked') ? true : false);
        },

        resetMatrixSingleOpt: function (fieldId, fieldKey, thisOpt) {
            var fieldIdElements, fieldNumber, optInput, optInputVal, previewDivId,
                optionsId = thisOpt.data('options-id'), // rows, columns, dropdowns, first_dropdown, etc.
                optKey = thisOpt.data('optkey'); // 0, 1, 2, etc.
                
            fieldIdElements = fieldId.split('-');
            fieldNumber = fieldIdElements[0]; // e.g. 123

            optInput = $('input[name="field_options[options_' + fieldNumber +']['+optionsId+']['+optKey+'][label]');
            optInputVal = optInput.val(); // The default row / column option text or the one being input

            previewDivId = 'fb-field-' + fieldKey + '-' + optionsId + '-' + optKey; // e.g. fb-field-xwaprh-rows-0

            // Perform the live update of row and column headings
            $('#'+previewDivId).text(optInputVal);
        },

        resetDisplayedOpts: function (fieldId,optionsId,fieldType,fieldKey) {
            var i, opts, type, placeholder, fieldInfo;

            if (optionsId == 'default') {
                var input = $('[name^="item_meta[' + fieldId + ']"]'); // The input in the builder preview
            } else if (optionsId == 'rows') {
                // var input = [];
                var input = $('[id^="fb-matrix-choice-row-' + fieldKey + '"]');
            } else if (optionsId == 'columns') {
                // var input = [];
                var input = $('[id^="fb-field-' + fieldKey + '-columns"]');
            } else {
                var input = [];
            }

            if (input.length < 1) {
                return;
            }

            if (input.is('select')) {
                const selectedValDefault = input.val();
                placeholder = document.getElementById('fb-placeholder-' + fieldId);

                if (placeholder !== null && placeholder.value === '') {
                    formAdmin.fillDropdownOpts(input[0], {sourceID: fieldId});
                } else {
                    formAdmin.fillDropdownOpts(input[0], {
                        sourceID: fieldId,
                        placeholder: placeholder.value
                    });
                }

                if ($('[name^="item_meta[' + fieldId + ']"]').length > 0 && $('[name^="item_meta[' + fieldId + ']"]')[0].contains(selectedValDefault)) {
                    $('[name^="item_meta[' + fieldId + ']"]').val(selectedValDefault);
                }
            } else {
                if (optionsId == 'default') {
                    type = input.attr('type'); // e.g. radio
                    opts = formAdmin.getMultipleOpts(fieldId,optionsId);
                    $('#fb-editor-field-container-' + fieldId + ' .fb-choice-container').html('');
                    fieldInfo = formAdmin.getFieldKeyFromOpt($('#fb-option-list-' + fieldId + '-000'));

                    var container = $('#fb-editor-field-container-' + fieldId + ' .fb-choice-container');

                    for (i = 0; i < opts.length; i++) {
                        container.append(formAdmin.addRadioCheckboxOpt(type, opts[i], fieldId, fieldInfo.fieldKey));
                    }
                } else if (optionsId == 'rows') {
                    var headerRow = $('#fb-matrix-header-row-' + fieldKey);
                    var rowOpts = formAdmin.getMultipleOpts(fieldId,optionsId);
                    var columnOpts = formAdmin.getMultipleOpts(fieldId,'columns');
                    $('#fb-editor-field-container-' + fieldId + ' .fb-choice-container').html('');
                    fieldInfo = formAdmin.getFieldKeyFromOpt($('#fb-option-list-' + fieldId + '-000'));

                    var container = $('#fb-editor-field-container-' + fieldId + ' .fb-choice-container');

                    container.append(headerRow);

                    for (i = 0; i < rowOpts.length; i++) {
                        container.append(formAdmin.addMatrixRowOpt(rowOpts[i], fieldType, fieldId, fieldInfo.fieldKey, optionsId, columnOpts.length));
                    }
                } else if (optionsId == 'columns') {
                    fieldInfo = formAdmin.getFieldKeyFromOpt($('#fb-option-list-' + fieldId + '-000'));

                    var headerRow = $('#fb-matrix-header-row-' + fieldKey);
                    $('#fb-matrix-header-row-' + fieldKey).html('');
                    
                    var columnOpts = formAdmin.getMultipleOpts(fieldId,'columns');

                    headerRow = '<div id="fb-matrix-header-row-' + fieldKey + '" class="fb-matrix-header-row">' +
                        '<div class="fb-matrix-row-title"></div>' +
                        '<div class="fb-columns">';

                    for (i = 0; i < columnOpts.length; i++) {
                        headerRow += '<div id="fb-field-' + fieldKey + '-columns-' + i + '" class="fb-column-item fb-column-item-' + fieldKey + '">' + columnOpts[i]['saved'] + '</div>';
                    }

                    headerRow += '</div>' +
                    '</div>';

                    $('#fb-editor-field-container-' + fieldId + ' .fb-choice-container').html('');
                    var container = $('#fb-editor-field-container-' + fieldId + ' .fb-choice-container');

                    container.append(headerRow);

                    var rowOpts = formAdmin.getMultipleOpts(fieldId,'rows');
                    for (i = 0; i < rowOpts.length; i++) {
                        container.append(formAdmin.addMatrixRowOpt(rowOpts[i], fieldType, fieldId, fieldInfo.fieldKey, optionsId, columnOpts.length));
                    }
                }

            }

            formAdmin.adjustConditionalLogicOptionOrders(fieldId,optionsId);
        },

        fillDropdownOpts: function (field, atts) {
            if (field === null) {
                return;
            }
            var sourceID = atts.sourceID,
                placeholder = atts.placeholder,
                showOther = atts.other;

            formAdmin.removeDropdownOpts(field);
            var opts = formAdmin.getMultipleOpts(sourceID),
                hasPlaceholder = (typeof placeholder !== 'undefined');

            for (var i = 0; i < opts.length; i++) {
                var label = opts[i].label,
                    isOther = opts[i].key.indexOf('other') !== -1;

                if (hasPlaceholder && label !== '') {
                    formAdmin.addBlankSelectOption(field, placeholder);
                } else if (hasPlaceholder) {
                    label = placeholder;
                }
                hasPlaceholder = false;

                if (!isOther || showOther) {
                    var opt = document.createElement('option');
                    opt.value = opts[i].saved;
                    opt.innerHTML = label;
                    field.appendChild(opt);
                }
            }
        },
        
        addBlankSelectOption: function (field, placeholder) {
            var label = placeholder;
            var opt = document.createElement('option');
            opt.value = '';
            opt.innerHTML = label;
            field.appendChild(opt);
        },

        addRadioCheckboxOpt: function (type, opt, fieldId, fieldKey) {
            var single,
                id = 'fb-field-' + fieldKey + '-' + opt.key;

            single = '<div class="fb-choice fb-' + type + '" id="fb-' + type + '-' + fieldId + '-' + opt.key + '"><label for="' + id +
                '"><input type="' + type +
                '" name="item_meta[' + fieldId + ']' + (type === 'checkbox' ? '[]' : '') +
                '" value="' + opt.saved + '" id="' + id + '"' + (opt.checked ? ' checked="checked"' : '') + '> ' + opt.label + '</label>' +
                '</div>';

            return single;
        },

        addMatrixRowOpt: function (opt, fieldType, fieldId, fieldKey, optionsId, columnOptsLength) {
            var single,
                optKey,
                id,
                i;
                
            optKey = opt.key;
            id = 'fb-field-' + fieldKey + '-' + optKey;

            var columnOpts = '';
            for (i = 0; i < columnOptsLength; i++) {
                if ( 'likert_matrix_scale' == fieldType ) {
                    columnOpts += '<div class="fb-choice fb-checkbox"><input type="radio"><label></label></div>';
                }
                if ('matrix_of_dropdowns' == fieldType) {
                    columnOpts += '<div class="fb-choice fb-checkbox"><select><option>' + formbuilder_backend_js.matrix_select_placeholder + '</option></select></div>';
                }
                if (fieldType.indexOf('matrix_of_variable_dropdowns') >= 0) {
                    columnOpts += '<div class="fb-choice fb-checkbox"><select><option>' + formbuilder_backend_js.matrix_select_placeholder + '</option></select></div>';
                }
            }

            single = '<div id="fb-matrix-choice-row-' + fieldKey + '-' + optKey + '" class="fb-matrix-choice-row">' + 
                '<div id="fb-field-' + fieldKey + '-rows-' + optKey + '" class="fb-matrix-row-title">' + opt.saved + '</div>' +
                '<div class="fb-row-choices">' +
                columnOpts + 
                '</div>' +
                '</div>';

            return single;
        },
                
        adjustConditionalLogicOptionOrders: function (fieldId,optionsId) {
            var row, rowIndex, opts, logicId, valueSelect, rowOptions, expectedOrder, optionLength, optionIndex, expectedOption, optionMatch,
                rows = document.getElementById('fb-wrap').querySelectorAll('.formbuilder_logic_row'),
                rowLength = rows.length,
                fieldOptions = formAdmin.getFieldOptions(fieldId,optionsId),
                optionLength = fieldOptions.length;

            for (rowIndex = 0; rowIndex < rowLength; rowIndex++) {
                row = rows[rowIndex];
                opts = row.querySelector('.formbuilder_logic_field_opts');

                if (opts.value != fieldId) {
                    continue;
                }

                logicId = row.id.split('_')[2];
                valueSelect = row.querySelector('select[name="field_options[hide_opt_' + logicId + '][]"]');

                for (optionIndex = optionLength - 1; optionIndex >= 0; optionIndex--) {
                    expectedOption = fieldOptions[optionIndex];
                    optionMatch = valueSelect.querySelector('option[value="' + expectedOption + '"]');

                    if (optionMatch === null) {
                        optionMatch = document.createElement('option');
                        optionMatch.setAttribute('value', expectedOption);
                        optionMatch.textContent = expectedOption;
                    }

                    valueSelect.prepend(optionMatch);
                }

                optionMatch = valueSelect.querySelector('option[value=""]');
                if (optionMatch !== null) {
                    valueSelect.prepend(optionMatch);
                }
            }
        },

        initBulkOptionsOverlay: function () {
            var $info = formAdmin.initModal('#fb-bulk-edit-modal', '700px');
            if ($info === false)
                return;
            $('.fb-insert-preset').on('click', function (event) {
                var opts = JSON.parse(this.getAttribute('data-opts'));
                event.preventDefault();
                document.getElementById('fb-bulk-options').value = opts.join('\n');
                return false;
            });

            $buildForm.on('click', 'a.fb-bulk-edit-link', function (event) {
                event.preventDefault();
                var i, key, label,
                    content = '',
                    optList,
                    opts,
                    fieldId = $(this).closest('[data-fid]').data('fid'),
                    fieldType = $(this).closest('[data-field-type]').data('field-type'),
                    fieldKey = $(this).data('key'),
                    optionsId = $(this).data('options-id'), // e.g. for Likert / Matrix Scale field, can be 'rows' or 'columns'
                    separate = formAdmin.usingSeparateValues(fieldId);

                if (optionsId == 'default') {
                    optList = document.getElementById('fb-field-options-' + fieldId);                
                } else {
                    optList = document.getElementById('fb-field-options-' + fieldId + '-' + optionsId);
                }
                if (!optList)
                    return;

                opts = optList.getElementsByTagName('li');
                document.getElementById('bulk-field-id').value = fieldId;

                for (i = 0; i < opts.length; i++) {
                    key = opts[i].getAttribute('data-optkey');
                    if (key !== '000') {
                        if (optionsId == 'default') {
                            label = document.getElementsByName('field_options[options_' + fieldId + '][' + key + '][label]')[0];
                        } else {
                            label = document.getElementsByName('field_options[options_' + fieldId + '][' + optionsId + '][' + key + '][label]')[0];
                        }
                        if (typeof label !== 'undefined') {
                            content += label.value;
                            if (separate) {
                                if (optionsId == 'default') {
                                    content += '|' + document.getElementsByName('field_options[options_' + fieldId + '][' + key + '][value]')[0].value;
                                } else {
                                    content += '|' + document.getElementsByName('field_options[options_' + fieldId + '][' + optionsId + '][' + key + '][value]')[0].value;                                    
                                }
                            }
                            content += '\r\n';
                        }
                    }

                    if (i >= opts.length - 1) {
                        document.getElementById('fb-bulk-options').value = content;
                    }
                }
                $info.dialog('open');
                $('#fb-update-bulk-options').attr('data-options-id',optionsId);
                $('#fb-update-bulk-options').attr('data-field-type',fieldType);
                $('#fb-update-bulk-options').attr('data-key',fieldKey);
                return false;
            });

            $(document).on('click', '#fb-update-bulk-options', function () {
                var fieldId = document.getElementById('bulk-field-id').value;
                var optionsId = $(this).attr('data-options-id');
                var fieldType = $(this).attr('data-field-type');
                var fieldKey = $(this).attr('data-key');
                var optionType = document.getElementById('bulk-option-type').value;
                if (optionType)
                    return;
                this.classList.add('fb-loading-button');
                var separate = formAdmin.usingSeparateValues(fieldId),
                    action = 'formbuilder_import_options';

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: action,
                        field_id: fieldId,
                        field_type: fieldType,
                        options_id: optionsId,
                        opts: document.getElementById('fb-bulk-options').value,
                        separate: separate,
                        nonce: formbuilder_backend_js.nonce
                    },
                    success: function (html) {
                        if (optionsId == 'default') {
                            document.getElementById('fb-field-options-' + fieldId).innerHTML = html;
                        } else {
                            document.getElementById('fb-field-options-' + fieldId + '-' + optionsId).innerHTML = html;
                        }
                        formAdmin.resetDisplayedOpts(fieldId,optionsId,fieldType,fieldKey);
                        if (typeof $info !== 'undefined') {
                            $info.dialog('close');
                            document.getElementById('fb-update-bulk-options').classList.remove('fb-loading-button');
                        }
                    }
                });
            });
        },

        initModal: function (id, width) {
            const $info = $(id);
            if (!$info.length)
                return false;
            if (typeof width === 'undefined')
                width = '550px';
            const dialogArgs = {
                dialogClass: 'fb-dialog',
                modal: true,
                autoOpen: false,
                closeOnEscape: true,
                width: width,
                resizable: false,
                draggable: false,
                open: function () {
                    $('.ui-dialog-titlebar').addClass('fb-hidden').removeClass('ui-helper-clearfix');
                    $('#wpwrap').addClass('formbuilder_overlay');
                    $('.fb-dialog').removeClass('ui-widget ui-widget-content ui-corner-all');
                    $info.removeClass('ui-dialog-content ui-widget-content');
                    formAdmin.bindClickForDialogClose($info);
                },
                close: function () {
                    $('#wpwrap').removeClass('formbuilder_overlay');
                    $('.spinner').css('visibility', 'hidden');

                    this.removeAttribute('data-option-type');
                    const optionType = document.getElementById('bulk-option-type');
                    if (optionType) {
                        optionType.value = '';
                    }
                }
            };
            $info.dialog(dialogArgs);
            return $info;
        },

        initNewFormModal: function () {
            $(document).on('click', '.fb-trigger-modal', () => {
                $('#fb-add-form-modal').addClass('fb-open');
            });

            $(document).on('click', '.formbuilder-close-form-modal', () => {
                $('#fb-add-form-modal').removeClass('fb-open');
            });

            $(document).on('submit', '#fb-add-template', function (event) {
                event.preventDefault();
                const addTemplateButton = $(this).closest('#fb-add-template').find('button');
                if (!addTemplateButton.hasClass('formbuilder-updating')) {
                    var template_name = $(this).closest('#fb-add-template').find('input[name=template_name]').val();
                    addTemplateButton.addClass('formbuilder-updating');
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            action: 'formbuilder_create_form',
                            name: template_name,
                            nonce: formbuilder_backend_js.nonce
                        },
                        success: function (response) {
                            const res = JSON.parse(response)
                            if (typeof res.redirect !== 'undefined') {
                                const redirect = res.redirect;
                                window.location = redirect;
                            }
                        }
                    });
                }
            });
        },

        preFormSave: function (b) {
            formBuilder.removeWPUnload();
            if ($('form.inplace_form').length) {
                $('.inplace_save, .postbox').trigger('click');
            }

            if (b.classList.contains('formbuilder-ajax-udpate-button')) {
                b.classList.add('formbuilder-updating');
            } else {
                b.classList.add('formbuilder_loading_button');
            }
            b.setAttribute('aria-busy', 'true');
        },

        afterFormSave: function (button) {
            button.classList.remove('formbuilder-updating');
            button.classList.remove('formbuilder_loading_button');
            button.classList.add('formbuilder-updated');
            formBuilder.resetOptionTextDetails();
            fieldsUpdated = 0;
            button.setAttribute('aria-busy', 'false');

            setTimeout(function () {
                // $('.fb-updated-info').fadeOut('slow', function () {
                //     this.parentNode.removeChild(this);
                // });
                button.classList.remove('formbuilder-updated');
            }, 2000);
        },

        toggleValidationBox: function (hasValue, messageClass) {
            var $msg = $(messageClass);
            if (hasValue) {
                $msg.removeClass('fb-hidden');
                $msg.closest('.fb-form-container').find('.fb-validation-header').removeClass('fb-hidden');
            } else {
                $msg.addClass('fb-hidden');
                $msg.closest('.fb-form-container').find('.fb-validation-header').addClass('fb-hidden');
            }
        },

        addTimeDefaultValue: function () {
            const that = $(this);
            if (that.val() && !that.val().match(/^(2[0-3]|[01][0-9]):[0-5][0-9]$/)) {
                that.val('00:00');
            }
            const fieldId = that.closest('.fb-fields-settings').data('fid');
            const [hourString, minute] = that.val().split(":");
            const hour = +hourString % 24;
            $('#fb-editor-field-container-' + fieldId + ' .fb-timepicker').val(minute && (hour % 12 || 12) + ':' + minute + (hour < 12 ? "am" : "pm"));
        },

        validateTimeValue: function () {
            const that = $(this);
            if (that.val() && !that.val().match(/^(2[0-3]|[01][0-9]):[0-5][0-9]$/)) {
                that.val('00:00');
            }
            that.trigger('input');
        },

        removeDropdownOpts: function (field) {
            var i;
            if (typeof field.options === 'undefined') {
                return;
            }

            for (i = field.options.length - 1; i >= 0; i--) {
                field.remove(i);
            }
        },

        getMultipleOpts: function (fieldId,optionsId) {
            var i, saved, labelName, label, key, optObj,
                image, savedLabel, input, field, checkbox, fieldType,
                checked = false,
                opts = [],
                imageUrl = '',
                hasImageOptions = document.getElementsByName('field_options[select_option_type_' + fieldId + ']').length > 0,
                optVals,
                separateValues = formAdmin.usingSeparateValues(fieldId);

            if ( 'default' == optionsId || '' == optionsId || typeof optionsId === 'undefined' ) {
                optVals = $('input[name^="field_options[options_' + fieldId + ']"]');
            } else {
                optVals = $('input[name^="field_options[options_' + fieldId + '][' + optionsId + ']"]');
            }

            for (i = 0; i < optVals.length; i++) {
                if (optVals[i].name.indexOf('[000]') > 0 || optVals[i].name.indexOf('[value]') > 0 || optVals[i].name.indexOf('[image_id]') > 0 || optVals[i].name.indexOf('[price]') > 0) {
                    continue;
                }
                saved = optVals[i].value;
                label = saved;
                if ( 'default' == optionsId || '' == optionsId || typeof optionsId === 'undefined'  ) {
                    key = optVals[i].name.replace('field_options[options_' + fieldId + '][', '').replace('[label]', '').replace(']', '');
                } else {
                    key = optVals[i].name.replace('field_options[options_' + fieldId + '][' + optionsId + '][', '').replace('[label]', '').replace(']', '');                    
                }

                if (separateValues) {
                    labelName = optVals[i].name.replace('[label]', '[value]');
                    saved = $('input[name="' + labelName + '"]').val();
                }

                checked = formBuilder.getChecked(optVals[i].getAttribute('class'));

                if (hasImageOptions) {
                    imageUrl = formBuilder.getImageUrlFromInput(optVals[i]);
                    fieldType = document.getElementsByName('field_options[select_option_type_' + fieldId + ']').value;
                    label = formBuilder.getImageLabel(label, false, imageUrl, fieldType);
                }

                optObj = {
                    saved: saved,
                    label: label,
                    checked: checked,
                    key: key
                };
                opts.push(optObj);
            }
            return opts;
        },

        getFieldOptions: function (fieldId,optionsId) {
            var index, input, li,
                options = [];

            if (optionsId == 'default') {
                var listItems = document.getElementById('fb-field-options-' + fieldId).querySelectorAll('.formbuilder_single_option');
            } else {
                var listItems = document.getElementById('fb-field-options-' + fieldId + '-' + optionsId).querySelectorAll('.formbuilder_single_option');
            }
            
            var length = listItems.length;
                            
            for (index = 0; index < length; index++) {
                li = listItems[index];

                if (li.classList.contains('fb-hidden')) {
                    continue;
                }

                input = li.querySelector('.field_' + fieldId + '_option');
                options.push(input.value);
            }
            return options;
        },

        getHighestOptKey: function (fieldId,optionsId) {
            var i = 0,
                optKey = 0,
                lastKey = 0;
            
            if (optionsId == 'default') {
                var opts = $('#fb-field-options-' + fieldId + ' li');
            } else {
                var opts = $('#fb-field-options-' + fieldId + '-' + optionsId + ' li');
            }

            for (i; i < opts.length; i++) {
                optKey = opts[i].getAttribute('data-optkey');
                if (opts.length === 1) {
                    return optKey;
                }
                if (optKey !== '000') {
                    optKey = optKey.replace('other_', '');
                    optKey = parseInt(optKey, 10);
                }

                if (!isNaN(lastKey) && (optKey > lastKey || lastKey === '000')) {
                    lastKey = optKey;
                }
            }
            return lastKey;
        },

        liveChangeHideShowRow: function () {
            const that = $(this),
                parentRow = that.closest('.fb-form-container');
            var val = that.val();
            parentRow.find('.fb-row-show-hide').addClass('fb-hidden');
            var valArray = val.split('_');
            $.each(valArray, function (index, value) {
                parentRow.find('.fb-row-show-hide.fb-sub-field-' + value).removeClass('fb-hidden');
            });
        },

        liveChangeHideShowLabel: function () {
            const that = $(this);
            var val = that.val();
            const parentFieldSetting = $(this).closest('.fb-fields-settings'),
                fieldId = parentFieldSetting.data('fid'),
                fieldLabel = $('#fb-editor-field-id-' + fieldId).find('label.fb-label-show-hide');

            if (!val || (parentFieldSetting.find('[data-label-show-hide-checkbox]').is(':checked'))) {
                fieldLabel.addClass('fb-hidden');
            } else {
                fieldLabel.removeClass('fb-hidden');
            }
        },

        liveChangeHideShowLabelCheckbox: function () {
            const that = $(this);
            const parentFieldSetting = $(this).parents('.fb-fields-settings'),
                fieldId = parentFieldSetting.data('fid'),
                fieldLabel = $('#fb-editor-field-id-' + fieldId).find('label.fb-label-show-hide');

            // if (that.is(':checked') || !parentFieldSetting.find('[data-label-show-hide]').val()) {
            if (that.is(':checked') ) {
                fieldLabel.addClass('fb-hidden');
            } else {
                fieldLabel.removeClass('fb-hidden');
            }
        },

        bindClickForDialogClose: function ($modal) {
            const closeModal = function () {
                $modal.dialog('close');
            };
            $('.ui-widget-overlay').on('click', closeModal);
            $modal.on('click', 'a.dismiss', closeModal);
        },

    };

    $(function () {
        formAdmin.init();
    });

    $(document).ready( function() { 
        // Copy shortcode button on forms listing page
        // https://tippyjs.bootcss.com/
        tippy('.copy-shortcode-button', {
            content: 'Copied!',
            placement: 'left',
            arrow: true,
            theme: 'light',
            trigger: 'click',
            onShow(instance) {
                setTimeout(() => {
                    instance.hide();
                }, 1000);
            }
        });

        // https://clipboardjs.com/
        var clipboard = new ClipboardJS('.copy-shortcode-button', {
            text: function( trigger ) {
                return '[formbuilder id="' + trigger.getAttribute('data-clipboard-text') + '"]';
            }
        });    
    });

})(jQuery);


HTMLSelectElement.prototype.contains = function (value) {
    for (var i = 0, l = this.options.length; i < l; i++) {
        if (this.options[i].value == value) {
            return true;
        }
    }
    return false;
}