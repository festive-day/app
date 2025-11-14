var formBuilder = formBuilder || {};

(function ($) {
    'use strict';
    let $editorFieldsWrap = $('#fb-editor-fields'),
        $editorWrap = $('#fb-editor-wrap'),
        $buildForm = $('#fb-fields-form'),
        $optionsPanel = $('#fb-options-panel'),
        $formLabelPosition = $('select[name="form_label_position"]'),
        $formLabelAlignment = $('select[name="form_label_alignment"]'),
        $requiredFieldIndicator = $('input[name="required_field_indicator"]'),
        buildForm = document.getElementById('fb-fields-form'),
        $formMeta = $('#fb-meta-form'),
        $formSettings = $('#fb-settings-form'),
        currentFormId = $('#fb-form-id').val(),
        copyHelper = false,
        // fieldsUpdated = 0,
        autoId = 0;

    $.fn.simpleAccordion = function() {
        this.on("click", ".accordion__control", function() {
            // Toggle the panel next to the item that was clicked
            $(this).toggleClass("accordion__control--active").next().slideToggle(100);
        });
    
        // Return jQuery object for method chaining
        return this;
    }

    const wysiwyg = {
        init(editor, {setupCallback, height, addFocusEvents} = {}) {
            if (isTinyMceActive()) {
                setTimeout(resetTinyMce, 0);
            } else {
                initQuickTagsButtons();
            }

            setUpTinyMceVisualButtonListener();
            setUpTinyMceHtmlButtonListener();

            function initQuickTagsButtons() {
                if ('function' !== typeof window.quicktags || typeof window.QTags.instances[editor.id] !== 'undefined') {
                    return;
                }

                const id = editor.id;
                window.quicktags({
                    name: 'qt_' + id,
                    id: id,
                    canvas: editor,
                    settings: {id},
                    toolbar: document.getElementById('qt_' + id + '_toolbar'),
                    theButtons: {}
                });
            }

            function initRichText() {
                const key = Object.keys(tinyMCEPreInit.mceInit)[0];
                const orgSettings = tinyMCEPreInit.mceInit[key];

                const settings = Object.assign(
                    {},
                    orgSettings,
                    {
                        selector: '#' + editor.id,
                        body_class: orgSettings.body_class.replace(key, editor.id)
                    }
                );

                settings.setup = editor => {
                    if (addFocusEvents) {
                        function focusInCallback() {
                            $(editor.targetElm).trigger('focusin');
                            editor.off('focusin', '**');
                        }

                        editor.on('focusin', focusInCallback);

                        editor.on('focusout', function () {
                            editor.on('focusin', focusInCallback);
                        });
                    }
                    if (setupCallback) {
                        setupCallback(editor);
                    }
                };

                if (height) {
                    settings.height = height;
                }

                tinymce.init(settings);
            }

            function removeRichText() {
                tinymce.EditorManager.execCommand('mceRemoveEditor', true, editor.id);
            }

            function resetTinyMce() {
                removeRichText();
                initRichText();
            }

            function isTinyMceActive() {
                const id = editor.id;
                const wrapper = document.getElementById('wp-' + id + '-wrap');
                return null !== wrapper && wrapper.classList.contains('tmce-active');
            }

            function setUpTinyMceVisualButtonListener() {
                $(document).on(
                    'click', '#' + editor.id + '-html',
                    function () {
                        editor.style.visibility = 'visible';
                        initQuickTagsButtons(editor);
                    }
                );
            }

            function setUpTinyMceHtmlButtonListener() {
                $('#' + editor.id + '-tmce').on('click', handleTinyMceHtmlButtonClick);
            }

            function handleTinyMceHtmlButtonClick() {
                if (isTinyMceActive()) {
                    resetTinyMce();
                } else {
                    initRichText();
                }

                const wrap = document.getElementById('wp-' + editor.id + '-wrap');
                wrap.classList.add('tmce-active');
                wrap.classList.remove('html-active');
            }
        }
    };

    formBuilder = {
        init: function () {
            formBuilder.initBuild();

        },

        initBuild: function () {
            $('ul.fb-fields-list, .fb-fields-list li').disableSelection();
            $('.fields-list-accordion').simpleAccordion();
            $('.advanced-field-options-accordion').simpleAccordion().addClass('accordion-initialized');
            $('.form-options-accordion').simpleAccordion();

            formBuilder.setupSortable('ul.fb-editor-sorting');
            document.querySelectorAll('.fb-fields-list > li').forEach(formBuilder.makeDraggable);

            $editorFieldsWrap.on('click', 'li.fb-editor-field-box.ui-state-default', formBuilder.clickField);
            $editorFieldsWrap.on('click', '.fb-editor-delete-action', formBuilder.clickDeleteField);
            $editorFieldsWrap.on('mousedown', 'input, textarea, select', formBuilder.stopFieldFocus);
            $editorFieldsWrap.on('click', 'input[type=radio], input[type=checkbox]', formBuilder.stopFieldFocus);
            $formLabelPosition.on('change', formBuilder.setFormLabelPosition);
            $formLabelAlignment.on('change', formBuilder.setFormLabelAlignment);
            $requiredFieldIndicator.on('keyup', formBuilder.setRequiredFieldIndicator)

            // $(document).ready( function() {
                // Do something here...
            // });

            $('#fb-add-fields-panel').on('click', '.fb-add-field', formBuilder.addFieldClick);
            $('.fb-form-title-span, .fb-edit-form-title').on('click', function() {
                $('#fb-design-tab').trigger('click');
            });

            $('input[name="title"]').bind('keypress keyup blur', function() {
                var newTitle = $(this).val();
                $('#fb-form-title span.fb-form-title-span').text(newTitle);
            });

            formBuilder.renumberMultiSteps();
            $editorWrap.on('click', '.fb-step-item', formBuilder.reorderStep);

            formBuilder.resetToFirstStep();
        },

        setupSortable: function (sortableSelector) {
            document.querySelectorAll(sortableSelector).forEach(
                list => {
                    formBuilder.makeDroppable(list);
                    Array.from(list.children).forEach(
                        child => formBuilder.makeDraggable(child, '.fb-editor-move-action')
                    );
                }
            );
        },

        makeDroppable: function (list) {
            $(list).droppable({
                accept: '.fb-field-box, .fb-editor-field-box',
                deactivate: formBuilder.handleFieldDrop,
                over: formBuilder.onDragOverDroppable,
                out: formBuilder.onDraggableLeavesDroppable,
                tolerance: 'pointer'
            });
        },

        makeDraggable: function (draggable, handle) {
            const settings = {
                helper: function (event) {
                    const draggable = event.delegateTarget;

                    if (draggable.classList.contains('fb-editor-field-box') && !draggable.classList.contains('fb-editor-form-field')) {
                        const newTextFieldClone = '';
                        newTextFieldClone.querySelector('span').textContent = 'Field Group';
                        newTextFieldClone.classList.add('fb-editor-field-box');
                        newTextFieldClone.classList.add('ui-sortable-helper');
                        return newTextFieldClone;
                    }

                    let copyTarget;
                    const isNewField = draggable.classList.contains('fb-field-box');
                    if (isNewField) {
                        copyTarget = draggable.cloneNode(true);
                        copyTarget.classList.add('ui-sortable-helper');
                        draggable.classList.add('fb-added-field');
                        return copyTarget;
                    }

                    if (draggable.hasAttribute('data-type')) {
                        const fieldType = draggable.getAttribute('data-type');
                        copyTarget = document.getElementById('fb-add-fields-panel').querySelector('.formbuilder_' + fieldType);
                        copyTarget = copyTarget.cloneNode(true);
                        copyTarget.classList.add('fb-editor-form-field');

                        copyTarget.classList.add('ui-sortable-helper');

                        if (copyTarget) {
                            return copyTarget.cloneNode(true);
                        }
                    }

                    return formBuilder.div({className: 'fb-field-box'});
                },
                revert: 'invalid',
                delay: 10,
                start: function (event, ui) {
                    document.body.classList.add('fb-dragging');
                    ui.helper.addClass('fb-sortable-helper');

                    event.target.classList.add('fb-drag-fade');

                    formBuilder.unselectFieldGroups();
                    formBuilder.deleteEmptyDividerWrappers();
                    formBuilder.maybeRemoveGroupHoverTarget();
                },
                stop: function () {
                    document.body.classList.remove('fb-dragging');

                    const fade = document.querySelector('.fb-drag-fade');
                    if (fade) {
                        fade.classList.remove('fb-drag-fade');
                    }
                },
                drag: function (event, ui) {
                    // maybeScrollBuilder( event );
                    const draggable = event.target;
                    const droppable = formBuilder.getDroppableTarget();

                    let placeholder = document.getElementById('fb-placeholder');

                    if (!formBuilder.allowDrop(draggable, droppable)) {
                        if (placeholder) {
                            placeholder.remove();
                        }
                        return;
                    }

                    if (!placeholder) {
                        placeholder = formBuilder.tag('li', {
                            id: 'fb-placeholder',
                            className: 'sortable-placeholder'
                        });
                    }
                    const hfSortableHelper = ui.helper.get(0);

                    if ('fb-editor-fields' === droppable.id || droppable.classList.contains('start_divider')) {
                        placeholder.style.left = 0;
                        formBuilder.handleDragOverYAxis({droppable, y: event.clientY, placeholder});
                        return;
                    }

                    placeholder.style.top = '';
                    formBuilder.handleDragOverFieldGroup({droppable, x: event.clientX, placeholder});
                },
                cursor: 'grabbing',
                refreshPositions: true,
                cursorAt: {
                    top: 0,
                    left: 90 // The width of draggable button is 180. 90 should center the draggable on the cursor.
                }
            };
            if ('string' === typeof handle) {
                settings.handle = handle;
            }
            $(draggable).draggable(settings);
        },

        div: function (args) {
            return formBuilder.tag('div', args);
        },

        tag: function (type, args = {}) {
            const output = document.createElement(type);
            if ('string' === typeof args) {
                output.textContent = args;
                return output;
            }

            const {id, className, children, child, text, data} = args;

            if (id) {
                output.id = id;
            }
            if (className) {
                output.className = className;
            }
            if (children) {
                children.forEach(child => output.appendChild(child));
            } else if (child) {
                output.appendChild(child);
            } else if (text) {
                output.textContent = text;
            }
            if (data) {
                Object.keys(data).forEach(function (dataKey) {
                    output.setAttribute('data-' + dataKey, data[dataKey]);
                });
            }
            return output;
        },

        deleteEmptyDividerWrappers: function () {
            const dividers = document.querySelectorAll('ul.start_divider');
            if (!dividers.length) {
                return;
            }
            dividers.forEach(
                function (divider) {
                    const children = [].slice.call(divider.children);
                    children.forEach(
                        function (child) {
                            if (0 === child.children.length) {
                                child.remove();
                            } else if (1 === child.children.length && 'ul' === child.firstElementChild.nodeName.toLowerCase() && 0 === child.firstElementChild.children.length) {
                                child.remove();
                            }
                        }
                    );
                }
            );
        },

        maybeRemoveGroupHoverTarget: function () {
            var controls, previousHoverTarget;

            controls = document.getElementById('formbuilder_field_group_controls');
            if (null !== controls) {
                controls.style.display = 'none';
            }

            previousHoverTarget = document.querySelector('.fb-field-group-hover-target');
            if (null === previousHoverTarget) {
                return false;
            }

            $('#wpbody-content').off('mousemove', formBuilder.maybeRemoveHoverTargetOnMouseMove);
            previousHoverTarget.classList.remove('fb-field-group-hover-target');
            return previousHoverTarget;
        },

        getDroppableTarget: function () {
            let droppable = document.getElementById('fb-editor-fields');
            while (droppable.querySelector('.fb-dropabble')) {
                droppable = droppable.querySelector('.fb-dropabble');
            }
            if ('fb-editor-fields' === droppable.id && !droppable.classList.contains('fb-dropabble')) {
                droppable = false;
            }
            return droppable;
        },

        handleDragOverYAxis: function ({droppable, y, placeholder}) {
            const $list = $(droppable);
            let top;

            const $children = $list.children().not('.fb-editor-field-type-end_divider');
            if (0 === $children.length) {
                $list.prepend(placeholder);
                top = 0;
            } else {
                const insertAtIndex = formBuilder.determineIndexBasedOffOfMousePositionInList($list, y);
                if (insertAtIndex === $children.length) {
                    const $lastChild = $($children.get(insertAtIndex - 1));
                    top = $lastChild.offset().top + $lastChild.outerHeight();
                    $list.append(placeholder);

                    // Make sure nothing gets inserted after the end divider.
                    const $endDivider = $list.children('.fb-editor-field-type-end_divider');
                    if ($endDivider.length) {
                        $list.append($endDivider);
                    }
                } else {
                    top = $($children.get(insertAtIndex)).offset().top;
                    $($children.get(insertAtIndex)).before(placeholder);
                }
            }
            top -= $list.offset().top;
            placeholder.style.top = top + 'px';
        },

        handleDragOverFieldGroup: function ({droppable, x, placeholder}) {
            const $row = $(droppable);
            const $children = formBuilder.getFieldsInRow($row);
            if (!$children.length) {
                return;
            }
            let left;
            const insertAtIndex = formBuilder.determineIndexBasedOffOfMousePositionInRow($row, x);

            if (insertAtIndex === $children.length) {
                const $lastChild = $($children.get(insertAtIndex - 1));
                left = $lastChild.offset().left + $lastChild.outerWidth();
                $row.append(placeholder);
            } else {
                left = $($children.get(insertAtIndex)).offset().left;
                $($children.get(insertAtIndex)).before(placeholder);

                const amountToOffsetLeftBy = 0 === insertAtIndex ? 4 : 8; // Offset by 8 in between rows, but only 4 for the first item in a group.
                left -= amountToOffsetLeftBy; // Offset the placeholder slightly so it appears between two fields.
            }
            left -= $row.offset().left;
            placeholder.style.left = left + 'px';
        },

        determineIndexBasedOffOfMousePositionInRow: function ($row, x) {
            var $inputs = formBuilder.getFieldsInRow($row),
                length = $inputs.length,
                index, input, inputLeft, returnIndex;
            returnIndex = 0;
            for (index = length - 1; index >= 0; --index) {
                input = $inputs.get(index);
                inputLeft = $(input).offset().left;
                if (x > inputLeft) {
                    returnIndex = index;
                    if (x > inputLeft + ($(input).outerWidth() / 2)) {
                        returnIndex = index + 1;
                    }
                    break;
                }
            }
            return returnIndex;
        },

        getFieldsInRow: function ($row) {
            let $fields = $();
            const row = $row.get(0);
            if (!row.children) {
                return $fields;
            }

            Array.from(row.children).forEach(
                child => {
                    if ('none' === child.style.display) {
                        return;
                    }
                    const classes = child.classList;
                    if (!classes.contains('fb-editor-form-field') || classes.contains('fb-editor-field-type-end_divider') || classes.contains('fb-sortable-helper')) {
                        return;
                    }
                    $fields = $fields.add(child);
                }
            );
            return $fields;
        },

        allowDrop: function (draggable, droppable) {
            if (false === droppable) {
                return false;
            }

            if (droppable.closest('.fb-sortable-helper')) {
                return false;
            }

            if ('fb-editor-fields' === droppable.id) {
                return true;
            }

            if (!droppable.classList.contains('start_divider')) {
                const $fieldsInRow = formBuilder.getFieldsInRow($(droppable));
                if (!formBuilder.groupCanFitAnotherField($fieldsInRow, $(draggable))) {
                    // Field group is full and cannot accept another field.
                    return false;
                }
            }

            const isNewField = draggable.classList.contains('fb-added-field');
            if (isNewField) {
                return formBuilder.allowNewFieldDrop(draggable, droppable);
            }
            return formBuilder.allowMoveField(draggable, droppable);
        },

        groupCanFitAnotherField: function (fieldsInRow, $field) {
            var fieldId;
            if (fieldsInRow.length < 6) {
                return true;
            }
            if (fieldsInRow.length > 6) {
                return false;
            }
            fieldId = $field.attr('data-fid');
            // allow 6 if we're not changing field groups.
            return 1 === $(fieldsInRow).filter('[data-fid="' + fieldId + '"]').length;
        },

        allowNewFieldDrop: function (draggable, droppable) {
            const classes = draggable.classList;
            const newPageBreakField = classes.contains('formbuilder_break');
            const newHiddenField = classes.contains('formbuilder_hidden');
            const newSectionField = classes.contains('formbuilder_divider');
            const newEmbedField = classes.contains('formbuilder_form');

            const newFieldWillBeAddedToAGroup = !('fb-editor-fields' === droppable.id || droppable.classList.contains('start_divider'));
            if (newFieldWillBeAddedToAGroup) {
                if (formBuilder.groupIncludesBreakOrHidden(droppable)) {
                    return false;
                }
                return !newHiddenField && !newPageBreakField;
            }

            const fieldTypeIsAlwaysAllowed = !newPageBreakField && !newHiddenField && !newSectionField && !newEmbedField;
            if (fieldTypeIsAlwaysAllowed) {
                return true;
            }

            const newFieldWillBeAddedToASection = droppable.classList.contains('start_divider') || null !== droppable.closest('.start_divider');
            if (newFieldWillBeAddedToASection) {
                return !newEmbedField && !newSectionField;
            }

            return true;
        },

        allowMoveField: function (draggable, droppable) {
            if (draggable.classList.contains('fb-editor-field-box') && !draggable.classList.contains('fb-editor-form-field')) {
                return formBuilder.allowMoveFieldGroup(draggable, droppable);
            }

            const isPageBreak = draggable.classList.contains('fb-editor-field-type-break');
            if (isPageBreak) {
                return false;
            }

            if (droppable.classList.contains('start_divider')) {
                return formBuilder.allowMoveFieldToSection(draggable);
            }

            const isHiddenField = draggable.classList.contains('fb-editor-field-type-hidden');
            if (isHiddenField) {
                return false;
            }
            return formBuilder.allowMoveFieldToGroup(draggable, droppable);
        },

        allowMoveFieldGroup: function (fieldGroup, droppable) {
            if (droppable.classList.contains('start_divider') && null === fieldGroup.querySelector('.start_divider')) {
                // Allow a field group with no section inside of a section.
                return true;
            }
            return false;
        },

        allowMoveFieldToSection: function (draggable) {
            const draggableIncludeEmbedForm = draggable.classList.contains('fb-editor-field-type-form') || draggable.querySelector('.fb-editor-field-type-form');
            if (draggableIncludeEmbedForm) {
                // Do not allow an embedded form inside of a section.
                return false;
            }

            const draggableIncludesSection = draggable.classList.contains('fb-editor-field-type-divider') || draggable.querySelector('.fb-editor-field-type-divider');
            if (draggableIncludesSection) {
                // Do not allow a section inside of a section.
                return false;
            }

            return true;
        },

        allowMoveFieldToGroup: function (draggable, group) {
            if (formBuilder.groupIncludesBreakOrHidden(group)) {
                // Never allow any field beside a page break or a hidden field.
                return false;
            }

            const isFieldGroup = $(draggable).children('ul.fb-editor-sorting').not('.start_divider').length > 0;
            if (isFieldGroup) {
                // Do not allow a field group directly inside of a field group unless it's in a section.
                return false;
            }

            const draggableIncludesASection = draggable.classList.contains('fb-editor-field-type-divider') || draggable.querySelector('.fb-editor-field-type-divider');
            const draggableIsEmbedField = draggable.classList.contains('fb-editor-field-type-form');
            const groupIsInASection = null !== group.closest('.start_divider');
            if (groupIsInASection && (draggableIncludesASection || draggableIsEmbedField)) {
                // Do not allow a section or an embed field inside of a section.
                return false;
            }

            return true;
        },

        groupIncludesBreakOrHidden: function (group) {
            return null !== group.querySelector('.fb-editor-field-type-multi_step, .fb-editor-field-type-hidden');
        },

        unselectFieldGroups: function (event) {
            if ('undefined' !== typeof event) {
                if (null !== event.originalEvent.target.closest('#fb-editor-fields')) {
                    return;
                }
                if (event.originalEvent.target.classList.contains('fb-merge-fields-into-row')) {
                    return;
                }
                if (null !== event.originalEvent.target.closest('.fb-merge-fields-into-row')) {
                    return;
                }
                if (event.originalEvent.target.classList.contains('fb-custom-field-group-layout')) {
                    return;
                }
                if (event.originalEvent.target.classList.contains('fb-cancel-custom-field-group-layout')) {
                    return;
                }
            }
            $('.fb-selected-field-group').removeClass('fb-selected-field-group');
            $(document).off('click', formBuilder.unselectFieldGroups);
        },

        clickField: function (e) {
            /*jshint validthis:true */
            var currentClass, originalList;

            currentClass = e.target.classList;

            if (currentClass.contains('fb-collapse-page') || currentClass.contains('fb-sub-label') || e.target.closest('.dropdown') !== null) {
                return;
            }

            if (this.closest('.start_divider') !== null) {
                e.stopPropagation();
            }

            if (this.classList.contains('fb-editor-field-type-divider')) {
                originalList = e.originalEvent.target.closest('ul.fb-editor-sorting');
                if (null !== originalList) {
                    // prevent section click if clicking a field group within a section.
                    if (originalList.classList.contains('fb-editor-field-type-divider') || originalList.parentNode.parentNode.classList.contains('start_divider')) {
                        return;
                    }
                }
            }

            formBuilder.clickAction(this);
            
            // Close current "Advanced Options" panel on clicking another field in the builder preview
            // $('.accordion__control.accordion__control--active').toggleClass('accordion__control--active').next().slideToggle(200);
        },

        clickAction: function (obj) {
            var $thisobj = $(obj);
            if (obj.className.indexOf('selected') !== -1)
                return;
            if (obj.className.indexOf('fb-editor-field-type-end_divider') !== -1 && $thisobj.closest('.fb-editor-field-type-divider').hasClass('no_repeat_section'))
                return;
            formBuilder.deselectFields();
            $thisobj.addClass('selected');
            formBuilder.showFieldOptions(obj);
        },

        showFieldOptions: function (obj) {
            var i, singleField,
                fieldId = obj.getAttribute('data-fid'),
                fieldType = obj.getAttribute('data-type'),
                allFieldSettings = document.querySelectorAll('.fb-fields-settings:not(.fb-hidden)');

            for (i = 0; i < allFieldSettings.length; i++) {
                allFieldSettings[i].classList.add('fb-hidden');
            }

            singleField = document.getElementById('fb-fields-settings-' + fieldId);
            formBuilder.moveFieldSettings(singleField);

            singleField.classList.remove('fb-hidden');
            document.getElementById('fb-options-tab').click();
            
            $('#fb-fields-settings-' + fieldId + ' .advanced-field-options-accordion:not(.accordion-initialized)').simpleAccordion().addClass('accordion-initialized');

            const editor = singleField.querySelector('.wp-editor-area');
            if (editor) {
                wysiwyg.init(editor, {setupCallback: formBuilder.setupTinyMceEventHandlers});
            }
        },

        setFormLabelPosition: function(e) {
            var labelPosition = $(this).val();

            // Click each field container in the builder preview
            // This triggers the relevant options in the Edit panel to be available in the DOM
            $editorFieldsWrap.find('li.fb-editor-field-box.ui-state-default').each( function() {
                $(this).trigger('click');
            });
            
            // Return to the Design tab
            $('#fb-design-tab').trigger('click');
            
            // Change the selected option for Label Position in Edit tab
            $('select[name^="field_options[label_position"]').each( function() {
                $(this).val(labelPosition).trigger('change');
            });
        },

        setFormLabelAlignment: function(e) {
            var labelAlignment = $(this).val();

            // Click each field container in the builder preview
            // This triggers the relevant options in the Edit panel to be available in the DOM
            $editorFieldsWrap.find('li.fb-editor-field-box.ui-state-default').each( function() {
                $(this).trigger('click');
            });
            
            // Return to the Design tab
            $('#fb-design-tab').trigger('click');
            
            // Change the selected option for Label Alignment in Edit tab
            $('select[name^="field_options[label_alignment"]').each( function() {
                $(this).val(labelAlignment).trigger('change');
            });
        },
        
        setRequiredFieldIndicator: function(e) {
            var requiredFieldIndicator = $(this).val();

            // Click each field container in the builder preview
            // This triggers the relevant options in the Edit panel to be available in the DOM
            $editorFieldsWrap.find('li.fb-editor-field-box.ui-state-default').each( function() {
                $(this).trigger('click');
            });
            
            // Return to the Design tab
            $('#fb-design-tab').trigger('click');
            
            // Change the value for Required Field Indicator in Edit tab
            $('input[name^="field_options[required_indicator_"').each( function() {
                $(this).val(requiredFieldIndicator).trigger('keyup');
            });

        },

        clickDeleteField: function () {
            if (confirm("Are you sure?")) {
                formBuilder.deleteFields($(this).attr('data-deletefield'));
            }
            return false;
        },

        deleteFields: function (fieldId) {
            var field = $('#fb-editor-field-id-' + fieldId);

            formBuilder.deleteField(fieldId);
            if (field.hasClass('fb-editor-field-type-divider')) {
                field.find('li.fb-editor-field-box').each(function () {
                    formBuilder.deleteField(this.getAttribute('data-fid'));
                });
            }
            formBuilder.toggleSectionHolder();
        },

        deleteField: function (fieldId) {
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'formbuilder_delete_field',
                    field_id: fieldId,
                    nonce: formbuilder_backend_js.nonce
                },
                success: function () {
                    var $thisField = $('#fb-editor-field-id-' + fieldId),
                        type = $thisField.data('type'),
                        settings = $('#fb-fields-settings-' + fieldId);

                    // Remove settings from sidebar.
                    if (settings.is(':visible')) {
                        document.getElementById('fb-add-fields-tab').click();
                    }
                    settings.remove();

                    $thisField.fadeOut('fast', function () {
                        var $section = $thisField.closest('.start_divider'),
                            type = $thisField.data('type'),
                            $adjacentFields = $thisField.siblings('li.fb-editor-form-field'),
                            $liWrapper;

                        if (!$adjacentFields.length) {
                            if ($thisField.is('.fb-editor-field-type-end_divider')) {
                                $adjacentFields.length = $thisField.closest('li.fb-editor-form-field').siblings();
                            } else {
                                $liWrapper = $thisField.closest('ul.fb-editor-sorting').parent();
                            }
                        }

                        $thisField.remove();
                        if ($('#fb-editor-fields li').length === 0) {
                            document.getElementById('fb-editor-wrap').classList.remove('fb-editor-has-fields');
                        } else if ($section.length) {
                            formBuilder.toggleOneSectionHolder($section);
                        }
                        if ($adjacentFields.length) {
                            formBuilder.syncLayoutClasses($adjacentFields.first());
                        } else {
                            $liWrapper.remove();
                        }

                        if (type === 'multi_step') {
                            formBuilder.renumberMultiSteps();
                        }
                    });
                }
            });
        },

        toggleSectionHolder: function () {
            document.querySelectorAll('.start_divider').forEach(
                function (divider) {
                    formBuilder.toggleOneSectionHolder($(divider));
                }
            );
        },

        addFieldClick: function () {
            /*jshint validthis:true */
            const $thisObj = $(this);
            // there is no real way to disable a <a> (with a valid href attribute) in HTML - https://css-tricks.com/how-to-disable-links/
            if ($thisObj.hasClass('disabled')) {
                return false;
            }

            $thisObj.parent('.fb-field-box').addClass('fb-added-field');

            const $button = $thisObj.closest('.fb-field-box');
            const fieldType = $button.attr('id');

            let hasBreak = 0;
            if ('summary' === fieldType) {
                hasBreak = $editorFieldsWrap.children('li[data-type="break"]').length > 0 ? 1 : 0;
            }

            var formId = document.getElementById('fb-form-id').value;
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'formbuilder_insert_field',
                    form_id: formId,
                    field_type: fieldType,
                    nonce: formbuilder_backend_js.nonce,
                },
                success: function (msg) {
                    document.getElementById('fb-editor-wrap').classList.add('fb-editor-has-fields');
                    const replaceWith = formBuilder.wrapFieldLi(msg);
                    var fieldID = replaceWith[0].childNodes[0].childNodes[0].dataset.fid; // e.g. 121
                    var fieldType = replaceWith[0].childNodes[0].childNodes[0].dataset.type; // e.g. checkbox
                    $editorFieldsWrap.append(replaceWith);
                    $formLabelPosition.trigger('change');
                    $formLabelAlignment.trigger('change');
                    $('#fb-add-fields-tab').trigger('click');
                    $('.formbuilder-ajax-udpate-button').trigger('click');
                    formBuilder.afterAddField(msg, true);

                    if (fieldType == 'select'
                        || fieldType == 'radio'
                        || fieldType == 'checkbox'
                        || fieldType == 'image_select'
                    ) {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID));
                    }

                    if (fieldType == 'likert_matrix_scale'
                        || fieldType == 'matrix_of_dropdowns'                    
                    ) {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-rows'));
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-columns'));
                    }

                    if (fieldType == 'matrix_of_dropdowns') {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-dropdowns'));
                    }

                    if (fieldType == 'matrix_of_variable_dropdowns_two'
                        || fieldType == 'matrix_of_variable_dropdowns_three'
                        || fieldType == 'matrix_of_variable_dropdowns_four'
                        || fieldType == 'matrix_of_variable_dropdowns_five'                    ) {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-rows'));
                    }

                    if (fieldType == 'matrix_of_variable_dropdowns_two'
                        || fieldType == 'matrix_of_variable_dropdowns_three'
                        || fieldType == 'matrix_of_variable_dropdowns_four'
                        || fieldType == 'matrix_of_variable_dropdowns_five'
                    ) {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-first_dropdown'));
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-second_dropdown'));
                    }

                    if (fieldType == 'matrix_of_variable_dropdowns_three'
                        || fieldType == 'matrix_of_variable_dropdowns_four'
                        || fieldType == 'matrix_of_variable_dropdowns_five'
                    ) {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-third_dropdown'));
                    }

                    if (fieldType == 'matrix_of_variable_dropdowns_four'
                        || fieldType == 'matrix_of_variable_dropdowns_five'
                    ) {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-fourth_dropdown'));
                    }

                    if (fieldType == 'matrix_of_variable_dropdowns_five') {
                       formBuilder .setupFieldOptionSorting($('#fb-field-options-' + fieldID + '-fifth_dropdown'));
                    }
                                        
                    replaceWith.each(
                        function () {
                            formBuilder.makeDroppable(this.querySelector('ul.fb-editor-sorting'));
                            formBuilder.makeDraggable(this.querySelector('.fb-editor-form-field'), '.fb-editor-move-action');
                        }
                    );
                    formBuilder.maybeFixRangeSlider();
                    setTimeout(function () {
                        $(document).find('.fb-color-picker').wpColorPicker();
                    }, 1000);
                },
                error: formBuilder.handleInsertFieldError
            });
            return false;
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
                    return li.clone();
                },
                stop: function (e, ui) {
                    var fieldId = ui.item.attr('id').replace('fb-option-list-', '').replace('-' + ui.item.data('optkey'), '');
                    var optionsId = ui.item.closest('.fb-option-list').attr('data-options-id');
                    var fieldType = ui.item.closest('.fb-option-list').attr('data-field-type');
                    var fieldKey = ui.item.closest('.fb-option-list').attr('data-key');
                    formAdmin.resetDisplayedOpts(fieldId,optionsId,fieldType,fieldKey);
                }
            };
            $(sort).sortable(opts);
        },

        stopFieldFocus: function (e) {
            e.preventDefault();
        },

        deselectFields: function (preventFieldGroups) {
            $('li.ui-state-default.selected').removeClass('selected');
            if (!preventFieldGroups) {
                formBuilder.unselectFieldGroups();
            }
        },

        moveFieldSettings: function (singleField) {
            if (singleField === null)
                return;
            var classes = singleField.parentElement.classList;
            if (classes.contains('fb-editor-field-box') || classes.contains('divider_section_only')) {
                var endMarker = document.getElementById('fb-end-form-marker');
                buildForm.insertBefore(singleField, endMarker);
            }
        },

        debounce: function (func, wait = 100) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(
                    () => func.apply(this, args),
                    wait
                );
            };
        },

        infoModal: function (msg) {
            var $info = formBuilder.initModal('#formbuilder_info_modal', '400px');
            if ($info === false) {
                return false;
            }
            $('.fb-info-msg').html(msg);
            $info.dialog('open');
            return false;
        },

        handleFieldDrop: function (_, ui) {
            const draggable = ui.draggable[0];
            const placeholder = document.getElementById('fb-placeholder');

            if (!placeholder) {
                ui.helper.remove();
                formBuilder.syncAfterDragAndDrop();
                return;
            }
            const $previousFieldContainer = ui.helper.parent();
            const previousSection = ui.helper.get(0).closest('ul.start_divider');
            const newSection = placeholder.closest('ul.fb-editor-sorting');

            if (draggable.classList.contains('fb-added-field')) {
                formBuilder.insertNewFieldByDragging(draggable.id);
            } else {
                formBuilder.moveFieldThatAlreadyExists(draggable, placeholder);
            }

            const previousSectionId = previousSection ? parseInt(previousSection.closest('.fb-editor-field-type-divider').getAttribute('data-fid')) : 0;
            const newSectionId = newSection.classList.contains('start_divider') ? parseInt(newSection.closest('.fb-editor-field-type-divider').getAttribute('data-fid')) : 0;

            placeholder.remove();
            ui.helper.remove();

            const $previousContainerFields = $previousFieldContainer.length ? formBuilder.getFieldsInRow($previousFieldContainer) : [];
            formBuilder.maybeUpdatePreviousFieldContainerAfterDrop($previousFieldContainer, $previousContainerFields);
            formBuilder.maybeUpdateDraggableClassAfterDrop(draggable, $previousContainerFields);

            if (previousSectionId !== newSectionId) {
                formBuilder.updateFieldAfterMovingBetweenSections($(draggable), previousSection);
            }
            formBuilder.syncAfterDragAndDrop();
        },

        syncAfterDragAndDrop: function () {
            formBuilder.fixUnwrappedListItems();
            formBuilder.toggleSectionHolder();
            formBuilder.maybeFixEndDividers();
            formBuilder.maybeDeleteEmptyFieldGroups();
            formBuilder.updateFieldOrder();

            const event = new Event('formbuilder_sync_after_drag_and_drop', {bubbles: false});
            document.dispatchEvent(event);
            formBuilder.maybeFixRangeSlider();
            setTimeout(function () {
                $(document).find('.fb-color-picker').wpColorPicker();
            }, 1000)
        },

        fixUnwrappedListItems: function () {
            const lists = document.querySelectorAll('ul#fb-editor-fields, ul.start_divider');
            lists.forEach(
                list => {
                    list.childNodes.forEach(
                        child => {
                            if ('undefined' === typeof child.classList) {
                                return;
                            }

                            if (child.classList.contains('fb-editor-field-type-end_divider')) {
                                // Never wrap end divider in place.
                                return;
                            }

                            if ('undefined' !== typeof child.classList && child.classList.contains('fb-editor-form-field')) {
                                formBuilder.wrapFieldLiInPlace(child);
                            }
                        }
                    );
                }
            );
        },

        toggleOneSectionHolder: function ($section) {
            var noSectionFields, $rows, length, index, sectionHasFields;
            if (!$section.length) {
                return;
            }

            $rows = $section.find('ul.fb-editor-sorting');
            sectionHasFields = false;
            length = $rows.length;
            for (index = 0; index < length; ++index) {
                if (0 !== formBuilder.getFieldsInRow($($rows.get(index))).length) {
                    sectionHasFields = true;
                    break;
                }
            }

            noSectionFields = $section.parent().children('.formbuilder_no_section_fields').get(0);
            noSectionFields.classList.toggle('formbuilder_block', !sectionHasFields);
        },

        maybeFixEndDividers: function () {
            document.querySelectorAll('.fb-editor-field-type-end_divider').forEach(
                endDivider => endDivider.parentNode.appendChild(endDivider)
            );
        },

        maybeDeleteEmptyFieldGroups: function () {
            document.querySelectorAll('li.form_field_box:not(.fb-editor-form-field)').forEach(
                fieldGroup => !fieldGroup.children.length && fieldGroup.remove()
            );
        },

        updateFieldOrder: function () {
            var fields, fieldId, field, currentOrder, newOrder;
            $('#fb-editor-fields').each(function (i) {
                fields = $('li.fb-editor-field-box', this);
                for (i = 0; i < fields.length; i++) {
                    fieldId = fields[i].getAttribute('data-fid');
                    field = $('input[name="field_options[field_order_' + fieldId + ']"]');
                    currentOrder = field.val();
                    newOrder = i + 1;

                    if (currentOrder != newOrder) {
                        field.val(newOrder);
                        var singleField = document.getElementById('fb-fields-settings-' + fieldId);
                        formBuilder.moveFieldSettings(singleField);
                        // formBuilder.fieldUpdated();
                    }
                }
            });
        },

        setupTinyMceEventHandlers: function (editor) {
            editor.on('Change', function () {
                formBuilder.handleTinyMceChange(editor);
            });
        },

        handleTinyMceChange: function (editor) {
            if (!formBuilder.isTinyMceActive() || tinyMCE.activeEditor.isHidden()) {
                return;
            }

            editor.targetElm.value = editor.getContent();
            $(editor.targetElm).trigger('change');
        },

        isTinyMceActive: function () {
            var activeSettings, wrapper;

            activeSettings = document.querySelector('.fb-fields-settings:not(.fb-hidden)');
            if (!activeSettings) {
                return false;
            }

            wrapper = activeSettings.querySelector('.wp-editor-wrap');
            return null !== wrapper && wrapper.classList.contains('tmce-active');
        },

        // fieldUpdated: function () {
        //     if (!fieldsUpdated) {
        //         fieldsUpdated = 1;
        //         window.addEventListener('beforeunload', formBuilder.confirmExit);
        //     }
        // },

        // confirmExit: function (event) {
        //     if (fieldsUpdated) {
        //         event.preventDefault();
        //         event.returnValue = '';
        //     }
        // },

        maybeFixRangeSlider: function () {
            setTimeout(() => {
                $(document).find('.formbuilder-range-input-selector').each(function () {
                    var newSlider = $(this);
                    var sliderValue = newSlider.val();
                    var sliderMinValue = parseFloat(newSlider.attr('min'));
                    var sliderMaxValue = parseFloat(newSlider.attr('max'));
                    var sliderStepValue = parseFloat(newSlider.attr('step'));
                    newSlider.prev('.formbuilder-range-slider').slider({
                        value: sliderValue,
                        min: sliderMinValue,
                        max: sliderMaxValue,
                        step: sliderStepValue,
                        range: 'min',
                        slide: function (e, ui) {
                            $(this).next().val(ui.value);
                        }
                    });
                })
            }, 1000);
        },

        wrapFieldLiInPlace: function (li) {
            const ul = formBuilder.tag('ul', {
                className: 'fb-editor-grid-container fb-editor-sorting'
            });
            const wrapper = formBuilder.tag('li', {
                className: 'fb-editor-field-box',
                child: ul
            });

            li.replaceWith(wrapper);
            ul.appendChild(li);

            formBuilder.makeDroppable(ul);
            formBuilder.makeDraggable(wrapper, '.fb-editor-move-action');
        },

        maybeUpdatePreviousFieldContainerAfterDrop: function ($previousFieldContainer, $previousContainerFields) {
            if (!$previousFieldContainer.length) {
                return;
            }

            if ($previousContainerFields.length) {
                formBuilder.syncLayoutClasses($previousContainerFields.first());
            } else {
                formBuilder.maybeDeleteAnEmptyFieldGroup($previousFieldContainer.get(0));
            }
        },

        maybeUpdateDraggableClassAfterDrop: function (draggable, $previousContainerFields) {
            if (0 !== $previousContainerFields.length || 1 !== formBuilder.getFieldsInRow($(draggable.parentNode)).length) {
                formBuilder.syncLayoutClasses($(draggable));
            }
        },

        maybeDeleteAnEmptyFieldGroup: function (previousFieldContainer) {
            const closestFieldBox = previousFieldContainer.closest('li.fb-editor-field-box');
            if (closestFieldBox && !closestFieldBox.classList.contains('fb-editor-field-type-divider')) {
                closestFieldBox.remove();
            }
        },

        determineIndexBasedOffOfMousePositionInList: function ($list, y) {
            const $items = $list.children().not('.fb-editor-field-type-end_divider');
            const length = $items.length;
            let index, item, itemTop, returnIndex;
            returnIndex = 0;
            for (index = length - 1; index >= 0; --index) {
                item = $items.get(index);
                itemTop = $(item).offset().top;
                if (y > itemTop) {
                    returnIndex = index;
                    if (y > itemTop + ($(item).outerHeight() / 2)) {
                        returnIndex = index + 1;
                    }
                    break;
                }
            }
            return returnIndex;
        },

        onDragOverDroppable: function (event, ui) {
            const droppable = event.target;
            const draggable = ui.draggable[0];
            if (!formBuilder.allowDrop(draggable, droppable)) {
                droppable.classList.remove('fb-dropabble');
                $(droppable).parents('ul.fb-editor-sorting').addClass('fb-dropabble');
                return;
            }
            document.querySelectorAll('.fb-dropabble').forEach(droppable => droppable.classList.remove('fb-dropabble'));
            droppable.classList.add('fb-dropabble');
            $(droppable).parents('ul.fb-editor-sorting').addClass('fb-dropabble');
        },

        onDraggableLeavesDroppable: function (event) {
            const droppable = event.target;
            droppable.classList.remove('fb-dropabble');
        },

        syncLayoutClasses: function ($item, type) {
            var $fields, size, layoutClasses, classToAddFunction;
            if ('undefined' === typeof type) {
                type = 'even';
            }
            $fields = $item.parent().children('li.fb-editor-form-field, li.fb-field-loading').not('.fb-editor-field-type-end_divider');
            size = $fields.length;
            layoutClasses = formBuilder.getLayoutClasses();

            if ('even' === type && 5 !== size) {
                $fields.each(formBuilder.getSyncLayoutClass(layoutClasses, formBuilder.getEvenClassForSize(size)));
            } else if ('clear' === type) {
                $fields.each(formBuilder.getSyncLayoutClass(layoutClasses, ''));
            } else {
                if (-1 !== ['left', 'right', 'middle', 'even'].indexOf(type)) {
                    classToAddFunction = function (index) {
                        return formBuilder.getClassForBlock(size, type, index);
                    };
                } else {
                    classToAddFunction = function (index) {
                        var size = type[index];
                        return formBuilder.getLayoutClassForSize(size);
                    };
                }
                $fields.each(formBuilder.getSyncLayoutClass(layoutClasses, classToAddFunction));
            }
        },

        getSyncLayoutClass: function (layoutClasses, classToAdd) {
            return function (itemIndex) {
                var currentClassToAdd, length, layoutClassIndex, currentClass, activeLayoutClass, fieldId, layoutClassesInput;
                currentClassToAdd = 'function' === typeof classToAdd ? classToAdd(itemIndex) : classToAdd;
                length = layoutClasses.length;
                activeLayoutClass = false;
                for (layoutClassIndex = 0; layoutClassIndex < length; ++layoutClassIndex) {
                    currentClass = layoutClasses[layoutClassIndex];
                    if (this.classList.contains(currentClass)) {
                        activeLayoutClass = currentClass;
                        break;
                    }
                }

                fieldId = this.dataset.fid;
                if ('undefined' === typeof fieldId) {
                    // we are syncing the drag/drop placeholder before the actual field has loaded.
                    // this will get called again afterward and the input will exist then.
                    this.classList.add(currentClassToAdd);
                    return;
                }

                formBuilder.moveFieldSettings(document.getElementById('fb-fields-settings-' + fieldId));
                var gridClassInput = document.getElementById('fb-grid-class-' + fieldId);

                if (null === gridClassInput) {
                    // not every field type has a layout class input.
                    return;
                }

                gridClassInput.value = currentClassToAdd;
                formBuilder.changeFieldClass(document.getElementById('fb-editor-field-id-' + fieldId), currentClassToAdd);
            };
        },

        getLayoutClasses: function () {
            return ['fb-grid-1', 'fb-grid-2', 'fb-grid-3', 'fb-grid-4', 'fb-grid-5', 'fb-grid-6', 'fb-grid-7', 'fb-grid-8', 'fb-grid-9', 'fb-grid-10', 'fb-grid-11', 'fb-grid-12'];
        },

        getSectionForFieldPlacement: function (currentItem) {
            var section = '';
            if (typeof currentItem !== 'undefined' && !currentItem.hasClass('fb-editor-field-type-divider')) {
                section = currentItem.closest('.fb-editor-field-type-divider');
            }
            return section;
        },

        getFormIdForFieldPlacement: function (section) {
            var formId = '';
            if (typeof section[0] !== 'undefined') {
                var sDivide = section.children('.start_divider');
                sDivide.children('.fb-editor-field-type-end_divider').appendTo(sDivide);
                if (typeof section.attr('data-formid') !== 'undefined') {
                    var fieldId = section.attr('data-fid');
                    formId = $('input[name="field_options[form_select_' + fieldId + ']"]').val();
                }
            }
            if (typeof formId === 'undefined' || formId === '') {
                formId = currentFormId;
            }
            return formId;
        },

        getSectionIdForFieldPlacement: function (section) {
            var sectionId = 0;
            if (typeof section[0] !== 'undefined') {
                sectionId = section.attr('id').replace('fb-editor-field-id-', '');
            }

            return sectionId;
        },

        updateFieldAfterMovingBetweenSections: function (currentItem, previousSection) {
            if (!currentItem.hasClass('fb-editor-form-field')) {
                formBuilder.getFieldsInRow($(currentItem.get(0).firstChild)).each(
                    function () {
                        formBuilder.updateFieldAfterMovingBetweenSections($(this), previousSection);
                    }
                );
                return;
            }
            const fieldId = currentItem.attr('id').replace('fb-editor-field-id-', '');
            const section = formBuilder.getSectionForFieldPlacement(currentItem);
            const formId = formBuilder.getFormIdForFieldPlacement(section);
            const sectionId = formBuilder.getSectionIdForFieldPlacement(section);
            const previousFormId = previousSection ? formBuilder.getFormIdForFieldPlacement($(previousSection.parentNode)) : 0;

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'formbuilder_update_field_after_move',
                    form_id: formId,
                    field: fieldId,
                    section_id: sectionId,
                    previous_form_id: previousFormId,
                    nonce: formbuilder_backend_js.nonce
                },
                success: function () {
                    formBuilder.toggleSectionHolder();
                    formBuilder.updateInSectionValue(fieldId, sectionId);
                }
            });
        },

        insertNewFieldByDragging: function (fieldType) {
            const placeholder = document.getElementById('fb-placeholder');
            const loadingID = fieldType.replace('|', '-') + '_' + formBuilder.getAutoId();
            const loading = formBuilder.tag('li', {
                id: loadingID,
                className: 'fb-wait fb-field-loading'
            });
            const $placeholder = $(loading);
            const currentItem = $(placeholder);
            const section = formBuilder.getSectionForFieldPlacement(currentItem);
            const formId = formBuilder.getFormIdForFieldPlacement(section);
            const sectionId = formBuilder.getSectionIdForFieldPlacement(section);
            placeholder.parentNode.insertBefore(loading, placeholder);
            placeholder.remove();
            formBuilder.syncLayoutClasses($placeholder);
            let hasBreak = 0;
            if ('summary' === fieldType) {
                hasBreak = $('.fb-field-loading#' + loadingID).prevAll('li[data-type="break"]').length ? 1 : 0;
            }
            jQuery.ajax({
                type: 'POST', url: ajaxurl,
                data: {
                    action: 'formbuilder_insert_field',
                    form_id: formId,
                    field_type: fieldType,
                    nonce: formbuilder_backend_js.nonce,
                },
                success: function (msg) {
                    let replaceWith;
                    document.getElementById('fb-editor-wrap').classList.add('fb-editor-has-fields');
                    const $siblings = $placeholder.siblings('li.fb-editor-form-field').not('.fb-editor-field-type-end_divider');
                    if (!$siblings.length) {
                        replaceWith = formBuilder.wrapFieldLi(msg);
                    } else {
                        replaceWith = formBuilder.msgAsObject(msg);
                        if (!$placeholder.get(0).parentNode.parentNode.classList.contains('ui-draggable')) {
                            formBuilder.makeDraggable($placeholder.get(0).parentNode.parentNode, '.fb-editor-move-action');
                        }
                    }
                    $placeholder.replaceWith(replaceWith);
                    formBuilder.updateFieldOrder();
                    formBuilder.afterAddField(msg, false);
                    if ($siblings.length) {
                        formBuilder.syncLayoutClasses($siblings.first());
                    }
                    formBuilder.toggleSectionHolder();
                    if (!$siblings.length) {
                        formBuilder.makeDroppable(replaceWith.get(0).querySelector('ul.fb-editor-sorting'));
                        formBuilder.makeDraggable(replaceWith.get(0).querySelector('li.fb-editor-form-field'), '.fb-editor-move-action');
                    } else {
                        formBuilder.makeDraggable(replaceWith.get(0), '.fb-editor-move-action');
                    }
                },
                error: formBuilder.handleInsertFieldError
            });
        },

        moveFieldThatAlreadyExists: function (draggable, placeholder) {
            placeholder.parentNode.insertBefore(draggable, placeholder);
        },

        msgAsObject: function (msg) {
            const element = formBuilder.div();
            element.innerHTML = msg;
            return $(element.innerHTML);
        },

        handleInsertFieldError: function (jqXHR, _, errorThrown) {
            formBuilder.maybeShowInsertFieldError(errorThrown, jqXHR);
        },

        maybeShowInsertFieldError: function (errorThrown, jqXHR) {
            if (!jqXHRAborted(jqXHR)) {
                formBuilder.infoModal(errorThrown + '. Please try again.');
            }
        },

        jqXHRAborted: function (jqXHR) {
            return jqXHR.status === 0 || jqXHR.readyState === 0;
        },

        getAutoId: function () {
            return ++autoId;
        },

        maybeRemoveHoverTargetOnMouseMove: function (event) {
            var elementFromPoint = document.elementFromPoint(event.clientX, event.clientY);
            if (null !== elementFromPoint && null !== elementFromPoint.closest('#fb-editor-fields')) {
                return;
            }
            formBuilder.maybeRemoveGroupHoverTarget();
        },

        wrapFieldLi: function (field) {
            const wrapper = formBuilder.div();
            if ('string' === typeof field) {
                wrapper.innerHTML = field;
            } else {
                wrapper.appendChild(field);
            }

            let result = $();
            Array.from(wrapper.children).forEach(
                li => {
                    result = result.add(
                        $('<li>')
                            .addClass('fb-editor-field-box')
                            .html($('<ul>').addClass('fb-editor-grid-container fb-editor-sorting').append(li))
                    );
                }
            );
            return result;
        },

        afterAddField: function (msg, addFocus) {
            var regex = /id="(\S+)"/,
                match = regex.exec(msg),
                field = document.getElementById(match[1]), // match[1] e.g. // e.g. fb-editor-field-id-123
                section = '#' + match[1] + '.fb-editor-field-type-divider ul.fb-editor-sorting.start_divider',
                $thisSection = $(section),
                toggled = false,
                $parentSection;
            var type = field.getAttribute('data-type');

            formBuilder.setupSortable(section);
            if ($thisSection.length) {
                $thisSection.parent('.fb-editor-field-box').children('.formbuilder_no_section_fields').addClass('formbuilder_block');
            } else {
                $parentSection = $(field).closest('ul.fb-editor-sorting.start_divider');
                if ($parentSection.length) {
                    formBuilder.toggleOneSectionHolder($parentSection);
                    toggled = true;
                }
            }

            $(field).addClass('fb-newly-added');
            setTimeout(function () {
                field.classList.remove('fb-newly-added');
            }, 1000);

            if (addFocus) {
                var bounding = field.getBoundingClientRect(),
                    container = document.getElementById('fb-form-panel'),
                    inView = (bounding.top >= 0 &&
                        bounding.left >= 0 &&
                        bounding.right <= (window.innerWidth || document.documentElement.clientWidth) &&
                        bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight)
                    );

                if (!inView) {
                    container.scroll({
                        top: container.scrollHeight,
                        left: 0,
                        behavior: 'smooth'
                    });
                }

                if (toggled === false) {
                    formBuilder.toggleOneSectionHolder($thisSection);
                }
            }

            formBuilder.deselectFields();

            const addedEvent = new Event('formbuilder_added_field', {bubbles: false});
            addedEvent.hfField = field;
            addedEvent.hfSection = section;
            addedEvent.hfType = type;
            addedEvent.hfToggles = toggled;
            document.dispatchEvent(addedEvent);
            if (type = "multi_step") {
                formBuilder.resetToFirstStep();
                formBuilder.renumberMultiSteps();
            }
        },

        getClassForBlock: function (size, type, index) {
            if ('even' === type) {
                return formBuilder.getEvenClassForSize(size, index);
            } else if ('middle' === type) {
                if (3 === size) {
                    return 1 === index ? 'fb-grid-6' : 'fb-grid-3';
                }
                if (5 === size) {
                    return 2 === index ? 'fb-grid-4' : 'fb-grid-2';
                }
            } else if ('left' === type) {
                return 0 === index ? formBuilder.getLargeClassForSize(size) : formBuilder.getSmallClassForSize(size);
            } else if ('right' === type) {
                return index === size - 1 ? formBuilder.getLargeClassForSize(size) : formBuilder.getSmallClassForSize(size);
            }
            return 'fb-grid-12';
        },

        getEvenClassForSize: function (size, index) {
            if (-1 !== [2, 3, 4, 6].indexOf(size)) {
                return formBuilder.getLayoutClassForSize(12 / size);
            }
            if (5 === size && 'undefined' !== typeof index) {
                return 0 === index ? 'fb-grid-4' : 'fb-grid-2';
            }
            return 'fb-grid-12';
        },

        getSmallClassForSize: function (size) {
            switch (size) {
                case 2:
                case 3:
                    return 'fb-grid-3';
                case 4:
                    return 'fb-grid-2';
                case 5:
                    return 'fb-grid-2';
                case 6:
                    return 'fb-grid-1';
            }
            return 'fb-grid-12';
        },

        getLargeClassForSize: function (size) {
            switch (size) {
                case 2:
                    return 'fb-grid-9';
                case 3:
                case 4:
                    return 'fb-grid-6';
                case 5:
                    return 'fb-grid-4';
                case 6:
                    return 'fb-grid-7';
            }
            return 'fb-grid-12';
        },

        getLayoutClassForSize: function (size) {
            return 'fb-grid-' + size;
        },

        resetOptionTextDetails: function () {
            $('.fb-fields-settings ul input[type="text"][name^="field_options[options_"]').filter('[data-value-on-load]').removeAttr('data-value-on-load');
            $('input[type="hidden"][name^=optionmap]').remove();
        },

        addBlankSelectOption: function (field, placeholder) {
            var opt = document.createElement('option'),
                firstChild = field.firstChild;

            opt.value = '';
            opt.innerHTML = placeholder;
            if (firstChild !== null) {
                field.insertBefore(opt, firstChild);
                field.selectedIndex = 0;
            } else {
                field.appendChild(opt);
            }
        },

        getImageLabel: function (label, showLabelWithImage, imageUrl, fieldType) {
            var imageLabelClass, fullLabel,
                originalLabel = label;

            fullLabel = '<div class="fb-field-is-image">';
            fullLabel += '<span class="fb-field-is-checked"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m-2 15l-5-5l1.41-1.41L10 14.17l7.59-7.59L19 8z"/></svg></span>';
            if (imageUrl) {
                fullLabel += '<img src="' + imageUrl + '" alt="' + originalLabel + '" />';
            }
            fullLabel += '</div>';
            fullLabel += '<div class="fb-field-is-label">' + originalLabel + '</div>';

            imageLabelClass = showLabelWithImage ? ' fb-field-is-has-label' : '';

            return ('<div class="fb-field-is-container' + imageLabelClass + '">' + fullLabel + '</div>');
        },

        getImageUrlFromInput: function (optVal) {
            var img, wrapper = $(optVal).closest('li').find('.fb-is-image-preview');

            if (!wrapper.length) {
                return '';
            }

            img = wrapper.find('img');
            if (!img.length) {
                return '';
            }

            return img.attr('src');
        },

        getChecked: function (id) {
            var field = $('.' + id);

            if (field.length === 0) {
                return false;
            }

            var checkbox = field.siblings('.fb-choice-input');
            return checkbox.length && checkbox.prop('checked');
        },

        changeFieldClass: function (field, setting) {
            var classes = field.className.split(' ');
            var filteredClasses = classes.filter(function (value, index, arr) {
                return value.indexOf('fb-grid-');
            });
            filteredClasses.push(setting);
            field.className = filteredClasses.join(' ');
        },

        removeWPUnload: function () {
            window.onbeforeunload = null;
            var w = $(window);
            w.off('beforeunload.widgets');
            w.off('beforeunload.edit-post');
        },

        maybeAddSaveAndDragIcons: function (fieldId) {
            var fieldOptions = document.querySelectorAll(`[id^=fb-option-list-${fieldId}-]`);

            if (fieldOptions.length < 2) {
                return;
            }

            let options = [...fieldOptions].slice(1);
            options.forEach((li, _key) => {
                if (li.classList.contains('formbuilder_other_option')) {
                    return;
                }
            });
        },

        renumberMultiSteps: function () {
            var i, containerClass,
                steps = $('.fb-step-num');

            if (steps.length > 1) {
                $('#fb-first-step').removeClass('fb-hidden');
                for (i = 0; i < steps.length; i++) {
                    steps[i].textContent = (i + 1);
                }
            } else {
                $('#fb-first-step').addClass('fb-hidden');
            }
        },

        toggleCollapseStep: function (field) {
            var toCollapse = formBuilder.getAllFieldsForStep(field.get(0).parentNode.closest('li.fb-editor-field-box').nextElementSibling);
            formBuilder.toggleStep(field, toCollapse);
        },

        reorderStep: function () {
            var field = $(this).closest('.fb-editor-form-field[data-type="multi_step"]');
            if (field.length) {
                formBuilder.toggleCollapseStep(field);
            } else {
                formBuilder.toggleCollapseFirstStep();
            }
        },

        toggleCollapseFirstStep: function () {
            var topLevel = document.getElementById('fb-first-step'),
                firstField = document.getElementById('fb-editor-fields').firstElementChild,
                toCollapse = formBuilder.getAllFieldsForStep(firstField);

            if (firstField.getAttribute('data-type') === 'multi_step') {
                return;
            }
            formBuilder.toggleStep(jQuery(topLevel), toCollapse);
        },

        toggleStep: function (field, toCollapse) {
            var i,
                fieldCount = toCollapse.length;

            jQuery('ul#fb-editor-fields > li.fb-editor-field-box').each(function () {
                const tfield = $(this),
                    isStepField = tfield.find('.fb-editor-form-field[data-type="multi_step"]');

                if (isStepField.length < 1) {
                    tfield.slideUp(150, function () {
                        tfield.hide();
                    });
                }
            });

            for (i = 0; i < fieldCount; i++) {
                const stepItem = $(toCollapse[i]);
                if (stepItem.find('.fb-editor-form-field[data-type="multi_step"]').length > 0) {
                    break;
                }
                if (i === fieldCount - 1) {
                    stepItem.slideDown(150, function () {
                        toCollapse.show();
                    });
                } else {
                    stepItem.slideDown(150);
                }
            }
        },

        getAllFieldsForStep: function (firstWrapper) {
            var $fieldsForPage, currentWrapper;
            $fieldsForPage = jQuery();
            if (null === firstWrapper) {
                return $fieldsForPage;
            }

            currentWrapper = firstWrapper;
            do {
                if (null !== currentWrapper.querySelector('.edit_field_type_break')) {
                    break;
                }
                $fieldsForPage = $fieldsForPage.add(jQuery(currentWrapper));
                currentWrapper = currentWrapper.nextElementSibling;
            } while (null !== currentWrapper);
            return $fieldsForPage;
        },

        resetToFirstStep: function () {
            if ($('.fb-step-item').length > 1) {
                $('.fb-step-item#fb-first-step').trigger('click');
            }
        }
    }

    $(function () {
        formBuilder.init();
    });

    $(document).ready( function() {
        // $('.fields-list-accordion .accordion__control').trigger('click');
        // $('.fields-list-accordion.identity-fields .accordion__control').trigger('click');
    });

})(jQuery);
