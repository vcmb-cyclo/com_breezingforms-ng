// Generated from quickmode.html.php — DO NOT EDIT MANUALLY
// Requires: BFQMConfig (inline), BFQMElements (quickmode-elements.js)
/* global BFQMConfig, BFQMElements, JQuery, Joomla, bootstrap */
(function () {
    'use strict';

    var jQuery = window.JQuery;

            var app = null;

            function BF_QuickModeApp() {

                JQuery("link").each(function () {
                    // jquery easy workaround
                    var _xj = 'j';
                    var _xq = 'q';
                    var _xu = 'u';
                    var _xe = 'e';
                    var _xr = 'r';
                    var _xy = 'y';
                    if (JQuery(this).attr('href').bfendsWith(_xj + _xq + _xu + _xe + _xr + _xy + '-ui.css')) {
                        JQuery(this).attr('disabled', 'disabled');
                        JQuery(this).remove();
                    }
                });

                var selectedTreeElement = null;
                var copyTreeElement = null;
                var appScope = this;
                this.elementScripts = BFQMConfig.elementScripts;
                this.dataObject = BFQMConfig.dataObject;
                this.quickModeIconBase = BFQMConfig.iconBase;

                this.normalizeQuickModeIcons = function (item) {
                    if (!item) {
                        return;
                    }

                    if (item.data && item.data.icon) {
                        item.data.icon = item.data.icon.replace(/\\/g, '/');

                        var iconFile = item.data.icon.match(/icon_[^/'"\\]+\.png$/);
                        if (iconFile) {
                            item.data.icon = appScope.quickModeIconBase + iconFile[0];
                        }
                    }

                    if (item.children) {
                        for (var i = 0; i < item.children.length; i++) {
                            appScope.normalizeQuickModeIcons(item.children[i]);
                        }
                    }
                };

                this.normalizeQuickModeIcons(this.dataObject);

                Object.assign(this, BFQMElements);

                /**
                 Helper methods
                 */
                this.getNodeClass = function (node) {
                    if (JQuery(node).attr('class')) {
                        var splitted = JQuery(appScope.selectedTreeElement).attr('class').split(' ');
                        if (splitted.length != 0) {
                            return splitted[0];
                        }
                    }
                    return '';
                };

                this.setProperties = function (node, props) {
                    var item = this.findDataObjectItem(JQuery(node).attr('id'), appScope.dataObject);
                    item.properties = props;
                };

                this.getProperties = function (node) {

                    var item = this.findDataObjectItem(JQuery(node).attr('id'), appScope.dataObject)
                    return item.properties;
                };

                /**
                 searches for the id in a given object item.
                 */
                this.findDataObjectItem = function (id, startObj) {
                    if (id && startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.attributes.id == id) {
                            return startObj;
                        } else {
                            if (startObj.children) {
                                var child = null;
                                for (var i = 0; i < startObj.children.length; i++) {
                                    child = appScope.findDataObjectItem(id, startObj.children[i]);
                                    if (child) {
                                        return child;
                                    }
                                }
                            }
                        }
                        return null;
                    }
                    return null;
                };

                this.getItemsFlattened = function (startObj, arr) {
                    if (startObj && startObj.properties && startObj.properties.type == 'element') {
                        arr.push(startObj);

                    }
                    if (startObj.children) {
                        var child = null;
                        for (var i = 0; i < startObj.children.length; i++) {
                            appScope.getItemsFlattened(startObj.children[i], arr);
                        }
                    }
                };

                this.replaceDataObjectItem = function (id, replacement, startObj) {
                    if (id && startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.children) {
                            var child = null;
                            for (var i = 0; i < startObj.children.length; i++) {
                                if (startObj.children[i].attributes.id == id) {
                                    startObj.children[i] = replacement;
                                    break;
                                }
                                appScope.replaceDataObjectItem(id, replacement, startObj.children[i]);
                            }
                        }
                    }
                }

                /**
                 searches for the id in a given object item and deletes it.
                 returns the deleted child.
                 */
                this.deleteDataObjectItem = function (id, startObj, previous) {
                    if (id && startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.attributes.id == id) {
                            if (previous) {
                                var newChildren = new Array();
                                for (var j = 0; j < previous.children.length; j++) {
                                    if (previous.children[j].attributes.id != startObj.attributes.id) {
                                        newChildren.push(previous.children[j]);
                                    }
                                }
                                previous.children = newChildren;
                            }
                            return startObj;
                        } else {
                            if (startObj.children) {
                                var child = null;
                                for (var i = 0; i < startObj.children.length; i++) {
                                    child = appScope.deleteDataObjectItem(id, startObj.children[i], startObj);
                                    if (child) {
                                        return child;
                                    }
                                }
                            }
                        }
                        return null;
                    }
                    return null;
                };

                this.moveDataObjectItem = function (sourceId, targetId, index, obj) {
                    var source = appScope.deleteDataObjectItem(sourceId, obj);
                    var target = appScope.findDataObjectItem(targetId, obj);
                    if (target && !target.children && (target.attributes['class'] == 'bfQuickModePageClass' || target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModeRootClass')) {
                        target.children = new Array();
                    }
                    if (target && target.children) {
                        target.children.splice(index, 0, source);
                        if (target.attributes['class'] == 'bfQuickModeRootClass') {
                            for (var i = 0; i < target.children.length; i++) {
                                var mdata = appScope.getProperties(JQuery('#' + target.children[i].attributes.id));
                                if (mdata) {
                                    if (target.children[i].attributes['class'] == 'bfQuickModePageClass') {
                                        target.children[i].attributes.id = 'bfQuickModePage' + (i + 1);
                                        target.children[i].data.title = BFQMConfig.labels['COM_BREEZINGFORMSNG_PAGE'] + (i + 1);
                                        target.children[i].properties.pageNumber = i + 1;
                                    }
                                }
                            }
                        }
                        return true;
                    }
                    return false;
                };

                this.insertElementInto = function (source, target) {
                    if (target && target.children) {
                        if (target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModePageClass') {
                            this.recreatedIds(source);
                            target.children.push(source);
                        }
                    }
                };

                this.recreatedIds = function (startObj) {
                    if (startObj && startObj.attributes && startObj.attributes.id) {
                        if (startObj.attributes['class'] == 'bfQuickModeSectionClass') {
                            type = 'bfQuickModeSection';
                        } else {
                            type = 'bfQuickMode';
                        }
                        var id = type + (Math.floor(Math.random() * 100000));
                        startObj.attributes.id = id;
                        if (startObj.attributes['class'] == 'bfQuickModeSectionClass') {
                            startObj.properties.name = id;
                        } else {
                            startObj.properties.bfName = id;
                            startObj.properties.dbId = 0;
                        }
                        startObj.properties.name = id;
                        if (startObj.children) {
                            var child = null;
                            for (var i = 0; i < startObj.children.length; i++) {
                                child = appScope.recreatedIds(startObj.children[i]);
                                if (child) {
                                    return child;
                                }
                            }
                        }
                        return null;
                    }
                    return null;
                };

                /**
                 Element properties
                 */

                // TEXTFIELD
                window.BFQM.installPropertyHandlers(this);


                /**
                 Main application
                 */
                this.toggleProperties = function (property) {
                    JQuery('.bfProperties').css('display', 'none');
                    JQuery('#' + property).css('display', '');
                };

                this.toggleAdvanced = function (property) {
                    JQuery('.bfAdvanced').css('display', 'none');
                    JQuery('#' + property).css('display', '');
                };

                JQuery('#bfElementExplorer').tree(
                    {
                        ui: {
                            theme_name: "apple",
                            theme_path: BFQMConfig.siteRootPath + '/administrator/components/com_breezingformsng/libraries/jquery/jtree/themes/',
                            context: [
                                {
                                    id: 'copy',
                                    label: 'Copy',
                                    visible: function (NODE, TREE_OBJ) {
                                        var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                        if (source.attributes['class'] == 'bfQuickModeSectionClass' || source.attributes['class'] == 'bfQuickModeElementClass') {
                                            return true;
                                        }
                                        return false;
                                    },
                                    action: function (NODE, TREE_OBJ) {
                                        var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                        if (source.attributes['class'] == 'bfQuickModeSectionClass' || source.attributes['class'] == 'bfQuickModeElementClass') {
                                            if (source && source.attributes && source.attributes.id) {
                                                appScope.copyTreeElement = source;
                                            }
                                        }
                                    }
                                },
                                {
                                    id: 'paste',
                                    label: 'Paste',
                                    visible: function (NODE, TREE_OBJ) {
                                        if (appScope.copyTreeElement) {
                                            var target = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                            if (target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModePageClass') {
                                                return true;
                                            }
                                            return false;
                                        }
                                        return false;
                                    },
                                    action: function (NODE, TREE_OBJ) {
                                        if (appScope.copyTreeElement) {
                                            var target = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                            if (target.attributes['class'] == 'bfQuickModeSectionClass' || target.attributes['class'] == 'bfQuickModePageClass') {
                                                appScope.insertElementInto(clone_obj(appScope.copyTreeElement), target);
                                                setTimeout("JQuery.tree_reference('bfElementExplorer').refresh()", 10); // give it time to close the context menu
                                            }
                                        }
                                    }
                                },
                                {
                                    id: "delete",
                                    label: "Delete",
                                    icon: "remove.png",
                                    visible: function (NODE, TREE_OBJ) {
                                        var ok = true;
                                        JQuery.each(NODE, function () {
                                            if (TREE_OBJ.check("deletable", this) == false)
                                                ok = false;
                                            return false;
                                        });
                                        return ok;
                                    },
                                    action: function (NODE, TREE_OBJ) {
                                        JQuery.each(NODE, function () {
                                            TREE_OBJ.remove(this);
                                        });
                                    }
                                }

                            ]

                        },
                        selected: 'bfQuickModeRoot',
                        callback: {
                            onselect: function (node, obj) {
                                appScope.selectedTreeElement = node;
                                JQuery('#bfPropertySaveButton').css('display', '');
                                JQuery('#bfPropertySaveButtonTop').css('display', '');
                                JQuery('#bfAdvancedSaveButton').css('display', '');
                                JQuery('#bfAdvancedSaveButtonTop').css('display', '');
                                switch (appScope.getNodeClass(node)) {
                                    case 'bfQuickModeRootClass':
                                        appScope.toggleProperties('bfFormProperties');
                                        appScope.toggleAdvanced('bfFormAdvanced');
                                        appScope.populateFormProperties();
                                        break;
                                    case 'bfQuickModeSectionClass':
                                        appScope.toggleProperties('bfSectionProperties');
                                        appScope.toggleAdvanced('bfSectionAdvanced');
                                        appScope.populateSectionProperties();
                                        //JQuery('#bfAdvancedSaveButton').css('display','none');
                                        //JQuery('#bfAdvancedSaveButtonTop').css('display','none');
                                        break;
                                    case 'bfQuickModeElementClass':
                                        appScope.toggleProperties('bfElementProperties');
                                        appScope.toggleAdvanced('bfElementAdvanced');
                                        appScope.populateSelectedElementProperties();
                                        break;
                                    case 'bfQuickModePageClass':
                                        appScope.toggleProperties('bfPageProperties');
                                        appScope.toggleAdvanced('bfPageAdvanced');
                                        appScope.populatePageProperties();
                                        JQuery('#bfAdvancedSaveButton').css('display', 'none');
                                        JQuery('#bfAdvancedSaveButtonTop').css('display', 'none');
                                        break;
                                }
                            },
                            onload: function (obj) {

                            },
                            onopen: function (NODE, TREE_OBJ) {
                                var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                source.state = 'open';
                            },
                            onclose: function (NODE, TREE_OBJ) {
                                var source = appScope.findDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                source.state = 'close';
                            },
                            ondelete: function (NODE, TREE_OBJ, RB) {
                                appScope.selectedTreeElement = null;
                                appScope.deleteDataObjectItem(JQuery(NODE).attr('id'), appScope.dataObject);
                                var target = appScope.findDataObjectItem(JQuery('#bfQuickModeRoot').attr('id'), appScope.dataObject);
                                if (target && !target.children) {
                                    target.children = new Array();
                                }
                                // restoring page numbers
                                if (target && target.children) {
                                    if (target.attributes['class'] == 'bfQuickModeRootClass') {
                                        for (var i = 0; i < target.children.length; i++) {
                                            if (target.children[i].attributes['class'] == 'bfQuickModePageClass') {
                                                var mdata = appScope.getProperties(JQuery('#' + target.children[i].attributes.id));
                                                if (mdata) {
                                                    target.children[i].attributes.id = 'bfQuickModePage' + (i + 1);
                                                    target.children[i].data.title = BFQMConfig.labels['COM_BREEZINGFORMSNG_PAGE'] + (i + 1);
                                                    target.children[i].properties.pageNumber = i + 1;
                                                }
                                            }
                                        }
                                        // taking care of last page as thank you page
                                        var pagesSize = target.children.length;
                                        if (target.properties.lastPageThankYou && pagesSize > 1) {
                                            target.properties.submittedScriptCondidtion = 2;
                                            target.properties.submittedScriptCode = 'function ff_' + target.properties.name + '_submitted(status, message){if(status==0){ff_switchpage(' + pagesSize + ');}else{alert(message);}}';
                                        } else {
                                            target.properties.submittedScriptCondidtion = -1;
                                        }
                                    }
                                }
                                setTimeout("JQuery.tree_reference('bfElementExplorer').refresh()", 10); // give it time to close the context menu
                            },
                            onmove: function (NODE, REF_NODE, TYPE, TREE_OBJ, RB) {
                                var parent = JQuery.tree_reference('bfElementExplorer').parent(NODE);
                                if (!parent) {
                                    parent = '#bfQuickModeRoot';
                                }
                                children = parent.children("ul").children("li");
                                if (children && children.length && children.length > 0) {
                                    for (var i = 0; i < children.length; i++) {
                                        if (JQuery(NODE).attr('id') == children[i].id) {
                                            appScope.moveDataObjectItem(JQuery(NODE).attr('id'), JQuery(parent).attr('id'), i, appScope.dataObject);
                                            break;
                                        }
                                    }
                                }
                                JQuery.tree_reference('bfElementExplorer').refresh();
                            }
                        },
                        rules: {
                            metadata: 'mdata',
                            use_inline: true,
                            deletable: 'none',
                            creatable: 'none',
                            renameable: 'none',

                            draggable: ['section', 'element', 'page'],
                            dragrules: [
                                'element inside section',
                                'section inside section',
                                'element inside page',
                                'section inside page',
                                'element after element',
                                'element before element',
                                'element after section',
                                'element before section',
                                'section after element',
                                'section before element',
                                'section after section',
                                'section before section',
                                'page before page',
                                'page after page'
                            ]
                        },
                        data: {
                            type: "json",
                            json: [appScope.dataObject]
                        }
                    }
                );

                this.saveButton = function () {
                    var error = false;
                    if (appScope.selectedTreeElement) {

                        switch (appScope.getNodeClass(appScope.selectedTreeElement)) {
                            case 'bfQuickModeRootClass':
                                if (JQuery.trim(JQuery('#bfFormTitle').val()) == '') {
                                    alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_ENTER_TITLE']);
                                    error = true;
                                }
                                if (JQuery.trim(JQuery('#bfFormName').val()) == '') {
                                    alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_ENTER_NAME']);
                                    error = true;
                                }
                                var myRegxp = /^([a-zA-Z0-9_]+)$/;
                                if (!myRegxp.test(JQuery('#bfFormName').val())) {
                                    alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_ENTER_NAME_CHARACTERS']);
                                    error = true;
                                }
                                if (!error) {
                                    appScope.saveFormProperties();
                                }
                                break;
                            case 'bfQuickModeSectionClass':
                                if (JQuery.trim(JQuery('#bfSectionName').val()) == '') {
                                    alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_ENTER_NAME']);
                                    error = true;
                                }
                                if (!error) {
                                    appScope.saveSectionProperties();
                                }
                                break;
                            case 'bfQuickModeElementClass':
                                if (JQuery.trim(JQuery('#bfElementLabel').val()) == '') {
                                    alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_ENTER_LABEL']);
                                    error = true;
                                }
                                if (JQuery.trim(JQuery('#bfElementName').val()) == '') {
                                    alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_ENTER_NAME']);
                                    error = true;
                                }
                                var myRegxp = /^([a-zA-Z0-9_]+)$/;
                                if (!myRegxp.test(JQuery('#bfElementName').val())) {
                                    alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_ENTER_NAME_CHARACTERS']);
                                    error = true;
                                }

                                var items = new Array();
                                appScope.getItemsFlattened(appScope.dataObject, items);
                                for (var i = 0; i < items.length; i++) {
                                    if (JQuery(appScope.selectedTreeElement).attr('id') != items[i].attributes.id && JQuery.trim(items[i].properties.bfName) == JQuery.trim(JQuery('#bfElementName').val())) {
                                        alert(BFQMConfig.labels['COM_BREEZINGFORMSNG_ERROR_NAME_EXISTS'] + " " + JQuery.trim(JQuery('#bfElementName').val()) + " (" + JQuery.trim(JQuery('#bfElementLabel').val()) + ")");
                                        error = true;
                                    }
                                }


                                if (!error) {
                                    appScope.saveSelectedElementProperties();
                                }
                            case 'bfQuickModePageClass':
                                appScope.savePageProperties();
                                break;
                        }
                        if (!error) {
                            // TODO: remove the 2nd refresh if found out why this works only on the 2nd
                            JQuery.tree_reference('bfElementExplorer').refresh();
                            JQuery.tree_reference('bfElementExplorer').refresh();

                            JQuery(".bfFadingMessage").html(BFQMConfig.labels['COM_BREEZINGFORMSNG_SETTINGS_UPDATED']);
                            JQuery(".bfFadingMessage").fadeIn(1000);
                            setTimeout('JQuery(".bfFadingMessage").fadeOut(1000);', 1500);
                        }
                    }
                    return !error;
                };

                JQuery('#bfPropertySaveButton').click(
                    appScope.saveButton
                );

                JQuery('#bfPropertySaveButtonTop').click(
                    appScope.saveButton
                );

                JQuery('#bfAdvancedSaveButton').click(
                    appScope.saveButton
                );

                JQuery('#bfAdvancedSaveButtonTop').click(
                    appScope.saveButton
                );

                JQuery('#bfNewSectionButton').click(
                    function () {
                        var id = "bfQuickModeSection" + (Math.floor(Math.random() * 100000));
                        var obj = {
                            attributes: {
                                "class": 'bfQuickModeSectionClass',
                                id: id,
                                mdata: JSON.stringify({ deletable: true, type: 'section' })
                            },
                            properties: {
                                bfType: 'normal',
                                type: 'section',
                                displayType: 'breaks',
                                title: "untitled section",
                                name: id,
                                description: '',
                                off: false
                            }
                            ,
                            state: "open",
                            data: { title: "untitled section", icon: BFQMConfig.iconBase + 'icon_section.png' },
                            children: []
                        };
                        appScope.createTreeItem(obj);
                        JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                    }
                );

                JQuery('#bfElementType').change(
                    function () {
                        var obj = null;
                        var id = "bfQuickMode" + (Math.floor(Math.random() * 10000000));
                        var selected = JQuery('#bfElementType').val();
                        switch (selected) {
                            case 'bfElementTypeText':
                                obj = appScope.createTextfield(id);
                                break;
                            case 'bfElementTypeRadioGroup':
                                obj = appScope.createRadioGroup(id);
                                break;
                            case 'bfElementTypeCheckboxGroup':
                                obj = appScope.createCheckboxGroup(id);
                                break;
                            case 'bfElementTypeCheckbox':
                                obj = appScope.createCheckbox(id);
                                break;
                            case 'bfElementTypeSelect':
                                obj = appScope.createSelect(id);
                                break;
                            case 'bfElementTypeTextarea':
                                obj = appScope.createTextarea(id);
                                break;
                            case 'bfElementTypeFile':
                                obj = appScope.createFile(id);
                                break;
                            case 'bfElementTypeSubmitButton':
                                obj = appScope.createSubmitButton(id);
                                break;
                            case 'bfElementTypeNumberInput':
                                obj = appScope.createNumberInput(id);
                                break;
                            case 'bfElementTypeSlider':
                                obj = appScope.createNumberInput(id);
                                obj.properties.range = true;
                                break;
                            case 'bfElementTypeHidden':
                                obj = appScope.createHidden(id);
                                break;
                            case 'bfElementTypeSummarize':
                                obj = appScope.createSummarize(id);
                                break;
                            case 'bfElementTypeCaptcha':
                                obj = appScope.createCaptcha(id);
                                break;
                            case 'bfElementTypeReCaptcha':
                                obj = appScope.createReCaptcha(id);
                                break;
                            case 'bfElementTypeCalendar':
                                obj = appScope.createCalendar(id);
                                break;
                            case 'bfElementTypeCalendarResponsive':
                                obj = appScope.createCalendarResponsive(id);
                                break;
                            case 'bfElementTypeStripe':
                                obj = appScope.createStripe(id);
                                break;
                            case 'bfElementTypeSignature':
                                obj = appScope.createSignature(id);
                                break;
                            case 'bfElementTypePayPal':
                                obj = appScope.createPayPal(id);
                                break;
                            case 'bfElementTypeSofortueberweisung':
                                obj = appScope.createSofortueberweisung(id);
                                break;
                        }
                        if (obj) {
                            appScope.replaceDataObjectItem(JQuery(appScope.selectedTreeElement).attr('id'), obj, appScope.dataObject);
                            JQuery.tree_reference('bfElementExplorer').refresh();
                            JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                        }
                    }
                );

                this.formatScriptDescription = function (description) {
                    if (typeof description !== 'string') {
                        return '';
                    }

                    return description
                        .replace(/\r\n/g, '\n')
                        .split('\n')
                        .filter(function (line) {
                            return !/^\s*\/\//.test(line);
                        })
                        .join('\n')
                        .replace(/^\n+|\n+$/g, '');
                };

                this.setActionScriptDescription = function () {
                    for (var i = 0; i < appScope.elementScripts.action.length; i++) {
                        if (JQuery('#bfActionsScriptSelection').val() == appScope.elementScripts.action[i].id) {
                            JQuery('#bfActionsScriptSelectionDescription').text(appScope.formatScriptDescription(appScope.elementScripts.action[i].description));
                        }
                    }
                };

                JQuery('#bfActionsScriptSelection').change(
                    function () {
                        appScope.setActionScriptDescription();
                    }
                );

                this.setInitScriptDescription = function () {
                    for (var i = 0; i < appScope.elementScripts.init.length; i++) {
                        if (JQuery('#bfInitScriptSelection').val() == appScope.elementScripts.init[i].id) {
                            JQuery('#bfInitSelectionDescription').text(appScope.formatScriptDescription(appScope.elementScripts.init[i].description));
                        }
                    }
                };

                JQuery('#bfInitScriptSelection').change(
                    function () {
                        appScope.setInitScriptDescription();
                    }
                );

                this.setValidationScriptDescription = function () {
                    for (var i = 0; i < appScope.elementScripts.validation.length; i++) {
                        if (JQuery('#bfValidationScriptSelection').val() == appScope.elementScripts.validation[i].id) {
                            JQuery('#bfValidationScriptSelectionDescription').text(appScope.formatScriptDescription(appScope.elementScripts.validation[i].description));
                        }
                    }
                };

                JQuery('#bfValidationScriptSelection').change(
                    function () {
                        appScope.setValidationScriptDescription();
                    }
                );

                JQuery('#bfNewElementButton').click(
                    function () {
                        var id = "bfQuickMode" + (Math.floor(Math.random() * 10000000));
                        var obj = appScope.createTextfield(id);
                        appScope.createTreeItem(obj);
                        JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                    }
                );

                JQuery('#bfNewPageButton').click(
                    function () {
                        var pageNumber = JQuery('#bfQuickModeRoot').children("ul").children("li").size() == 0 ? 1 : JQuery('#bfQuickModeRoot').children("ul").children("li").size() + 1;
                        var id = "bfQuickModePage" + pageNumber;

                        // taking care of thank you page if a new page is added
                        var item = appScope.findDataObjectItem('bfQuickModeRoot', appScope.dataObject);
                        var pagesSize = JQuery('#bfQuickModeRoot').children("ul").children("li").size();
                        if (item.properties.lastPageThankYou && pagesSize > 0) {
                            item.properties.submittedScriptCondidtion = 2;
                            item.properties.submittedScriptCode = 'function ff_' + item.properties.name + '_submitted(status, message){if(status==0){ff_switchpage(' + (pagesSize + 1) + ');}else{alert(message);}}';
                        } else {
                            item.properties.submittedScriptCondidtion = -1;
                        }

                        var obj = {
                            attributes: {
                                "class": 'bfQuickModePageClass',
                                id: id,
                                mdata: JSON.stringify({ deletable: true, type: 'page' })
                            },
                            properties: { type: 'page', pageNumber: pageNumber, pageIntro: '' },
                            state: "open",
                            data: {
                                title: BFQMConfig.labels['COM_BREEZINGFORMSNG_PAGE'] + pageNumber,
                                icon: BFQMConfig.iconBase + 'icon_page.png'
                            },
                            children: []
                        };
                        appScope.createTreeItem(obj);
                        JQuery.tree_reference('bfElementExplorer').select_branch(JQuery('#' + id));
                    }
                );

                // #menutab is now a native Bootstrap 5 nav-tabs/tab-content
                // pair (see QuickmodeHtml::render()) - no JS init call
                // needed, data-bs-toggle="tab" wires it up on its own.
            }

            window.onload = function () {

                // works around a bug in Firefox 40.0 that prevents you from selecting anything in the editor
                if (JQuery.browser.mozilla) {
                    JQuery("option").live('click', function () {
                        var options = JQuery(this).closest("select").get(0).options;
                        for (var i = 0; i < options.length; i++) {
                            if (options[i] == JQuery(this).get(0)) {
                                JQuery(this).closest("select").get(0).selectedIndex = i;
                                JQuery(this).closest("select").trigger('change');
                                JQuery(this).closest("select").blur();
                                break;
                            }
                        }
                    });
                }

                app = new BF_QuickModeApp();
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    var item = app.findDataObjectItem('bfQuickModeRoot', app.dataObject);
                    if (item) {
                        mdata.title = BFQMConfig.formTitle;
                        mdata.name = BFQMConfig.formName;
                        mdata.description = BFQMConfig.formDesc;
                        mdata.mailRecipient = BFQMConfig.formEmailadr;
                        mdata.mailNotification = BFQMConfig.formEmailntf;
                        item.properties = mdata;
                    }
                }
            };

            function createInitCode() {
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    form = document.bfForm;
                    name = mdata.bfName;
                    if (name == '') {
                        alert('Please enter the element name first.');
                        return;
                    } // if
                    if (!confirm(BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_CREAINIT'] + "\n" + BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_EXISTAPP']))
                        return;
                    code =
                        "function ff_" + name + "_init(element, condition)\n" +
                        "{\n" +
                        "    switch (condition) {\n";
                    if (form.bfInitFormEntry.checked)
                        code +=
                            "        case 'formentry':\n" +
                            "            break;\n";
                    if (form.bfInitPageEntry.checked)
                        code +=
                            "        case 'pageentry':\n" +
                            "            break;\n";
                    code +=
                        "        default:;\n" +
                        "    } // switch\n" +
                        "} // ff_" + name + "_init\n";
                    oldcode = Joomla.editors.instances["bfInitCode"].getValue();
                    if (oldcode != '')
                        Joomla.editors.instances["bfInitCode"].setValue(
                            code +
                            "\n// -------------- " + BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_OLDBELOW'] + " --------------\n\n" +
                            oldcode);
                    else
                        Joomla.editors.instances["bfInitCode"].setValue(code);
                }
            } // createInitCode

            function createValidationCode() {
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    form = document.bfForm;
                    name = mdata.bfName;
                    if (name == '') {
                        alert('Please enter the element name first.');
                        return;
                    } // if
                    if (!confirm(BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_CREAVALID'] + "\n" + BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_EXISTAPP']))
                        return;
                    code =
                        "function ff_" + name + "_validation(element, message)\n" +
                        "{\n" +
                        "    if (element_fails_my_test) {\n" +
                        "        if (message=='') message = element.name+\" faild in my test.\\n\"\n" +
                        "        ff_validationFocus(element.name);\n" +
                        "        return message;\n" +
                        "    } // if\n" +
                        "    return '';\n" +
                        "} // ff_" + name + "_validation\n";
                    oldcode = Joomla.editors.instances["bfValidationCode"].getValue();
                    if (oldcode != '')
                        Joomla.editors.instances["bfValidationCode"].setValue(
                            code +
                            "\n// -------------- " + BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_OLDBELOW'] + " --------------\n\n" +
                            oldcode);
                    else
                        Joomla.editors.instances["bfValidationCode"].setValue(code);
                }
            } // createValidationCode

            function createActionCode(element) {
                var mdata = app.getProperties(app.selectedTreeElement);
                if (mdata) {
                    form = document.bfForm;
                    name = mdata.bfName;
                    if (name == '') {
                        alert('Please enter the element name first.');
                        return;
                    } // if
                    if (!confirm(BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_CREAACTION'] + "\n" + BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_EXISTAPP']))
                        return;
                    code =
                        "function ff_" + name + "_action(element, action)\n" +
                        "{\n" +
                        "    switch (action) {\n";
                    if (form.bfActionClick)
                        if (form.bfActionClick.checked)
                            code +=
                                "        case 'click':\n" +
                                "            break;\n";
                    if (form.bfActionBlur)
                        if (form.bfActionBlur.checked)
                            code +=
                                "        case 'blur':\n" +
                                "            break;\n";
                    if (form.bfActionChange)
                        if (form.bfActionChange.checked)
                            code +=
                                "        case 'change':\n" +
                                "            break;\n";
                    if (form.bfActionFocus)
                        if (form.bfActionFocus.checked)
                            code +=
                                "        case 'focus':\n" +
                                "            break;\n";
                    if (form.bfActionSelect)
                        if (form.bfActionSelect.checked)
                            code +=
                                "        case 'select':\n" +
                                "            break;\n";
                    code +=
                        "        default:;\n" +
                        "    } // switch\n" +
                        "} // ff_" + name + "_action\n";

                    oldcode = Joomla.editors.instances["bfActionCode"].getValue();
                    if (oldcode != '')
                        Joomla.editors.instances["bfActionCode"].setValue(
                            code +
                            "\n// -------------- " + BFQMConfig.labels['COM_BREEZINGFORMSNG_ELEMENTS_OLDBELOW'] + " --------------\n\n" +
                            oldcode);
                    else
                        Joomla.editors.instances["bfActionCode"].setValue(code);
                }
            } // createActionCode

            function postTheStuff() {
                var postData = {
                    option: 'com_breezingformsng',
                    task: "quickmode.doAjaxSave",
                    form: document.adminForm.form.value,
                    chunksLength: chunks.length,
                    chunkIdx: chunki,
                    chunk: chunks[chunki],
                    rndAdd: rndAdd
                };
                postData[BFQMConfig.csrfToken] = '1';

                JQuery.ajax({
                    type: 'POST',
                    url: 'index.php',
                    data: postData,
                    success: function (data) {

                        if (data != '' && data != 0 && !isNaN(data)) {

                            document.adminForm.form.value = data;
                            location.href = "index.php?option=com_breezingformsng&task=quickmode.display&form=" + encodeURIComponent(data) + "&active_language_code=" + encodeURIComponent(document.adminForm.active_language_code.value);

                        } else if (JQuery.trim(data) == '') {
                            JQuery("#bfSaveQueue").get(0).innerHTML = BFQMConfig.labels['COM_BREEZINGFORMSNG_LOAD_PACKAGE'] + (chunki + 1) + BFQMConfig.labels['COM_BREEZINGFORMSNG_LOAD_PACKAGE_OF'] + (chunks.length - 1);
                            chunki++;
                            setTimeout(postTheStuff, 100);

                        }
                    },
                    error: function () {
                        JQuery("#bfSaveQueue").get(0).innerHTML = 'connection problem, trying again in 120 seconds, please wait...';
                        var secs = 120;
                        var clear = null;
                        clear = setInterval(
                            function () {
                                JQuery("#bfSaveQueue").get(0).innerHTML = 'connection problem, trying again in ' + secs + ' seconds, please wait...';
                                secs--;
                                if (secs <= 0) {
                                    clearInterval(clear);
                                    setTimeout(postTheStuff, 100);
                                }
                            }
                            , 1000);

                    },
                    async: false
                });
            }

            var chunki = 0;
            var rndAdd = Math.random();
            var chunks = new Array();
            var saveButtonClicked = false;

            JQuery(document).ready(function () {

                JQuery('#adminForm').get(0).onsubmit = function () {
                    return false;
                };

                JQuery('joomla-toolbar-button').click(function (e) {

                    e.preventDefault();

                    let pressbutton = JQuery(this).attr('task');

                    var form = document.adminForm;

                    switch (pressbutton) {

                        case 'close':
                            JQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };
                            location.href = "index.php?option=com_breezingformsng&view=forms";
                            break;
                        case 'save':

                            JQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };

                            if (!app.saveButton()) {
                                saveButtonClicked = false;
                                return;
                            }

                            if (saveButtonClicked) {
                                return;
                            }

                            saveButtonClicked = true;

                            form.task.value = 'quickmode.display';

                            var base = 'base';
                            var sixty_four = '64Encode';

                            var cVal = JQuery[base + sixty_four](JSON.stringify(app.dataObject));
                            JQuery.ajaxSetup({ async: false });
                            rndAdd = Math.random();
                            chunks = new Array();
                            var chunk = '';
                            if (cVal.length > 10000) {
                                var cnt = 0;
                                for (var i = 0; i < cVal.length; i++) {
                                    chunk += cVal[i];
                                    cnt++;
                                    if (cnt == 20000 || (i + 1 == cVal.length && cnt + 1 < 20000)) {
                                        chunks.push(chunk);
                                        chunk = '';
                                        cnt = 0;
                                    }
                                }
                            } else {
                                chunks.push(cVal);
                            }

                            if (chunks.length > 1) {
                                JQuery("#bfSaveQueue").css("display", "");
                                JQuery("#bfSaveQueue").bfcenter();
                                JQuery("#bfSaveQueue").css("visibility", "visible");
                            }

                            postTheStuff();

                            break;
                        case 'preview':

                            jQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('bfPreviewModal')).show();

                            break;
                        case 'preview_site':

                            jQuery('#adminForm').get(0).onsubmit = function () {
                                return false;
                            };
                            bootstrap.Modal.getOrCreateInstance(document.getElementById('bfPreviewModal2')).show();

                            break;
                    }

                    return false;
                });

            });

            function addslashes(str) {
                return (str + '').replace(/([\\"'])/g, "\\$1").replace(/\0/g, "\\0");
            }

            function clone_obj(obj) {
                var c = obj instanceof Array ? [] : {};

                for (var i in obj) {
                    var prop = obj[i];

                    if (typeof prop == 'object') {
                        if (prop instanceof Array) {
                            c[i] = [];

                            for (var j = 0; j < prop.length; j++) {
                                if (typeof prop[j] != 'object') {
                                    c[i].push(prop[j]);
                                } else {
                                    c[i].push(clone_obj(prop[j]));
                                }
                            }
                        } else {
                            c[i] = clone_obj(prop);
                        }
                    } else {
                        c[i] = prop;
                    }
                }

                return c;
            }

            jQuery(document).ready(function () {

                let validationCodeVisible = false;
                let initCodeVisible = false;
                let actionCodeVisible = false;

                setInterval(function () {

                    if (!actionCodeVisible && jQuery('#bfActionScriptCustom').is(':visible')) {
                        actionCodeVisible = true;
                        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
                        //Joomla.editors.instances["bfActionCode"].refresh();
                    } else if (initCodeVisible && jQuery('#bfActionScriptCustom').is(':hidden')) {
                        actionCodeVisible = false;
                    }

                    if (!initCodeVisible && jQuery('#bfInitScriptCustom').is(':visible')) {
                        initCodeVisible = true;
                        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
                        //Joomla.editors.instances["bfInitCode"].refresh();
                    } else if (initCodeVisible && jQuery('#bfInitScriptCustom').is(':hidden')) {
                        initCodeVisible = false;
                    }

                    if (!validationCodeVisible && jQuery('#bfValidationScriptCustom').is(':visible')) {
                        validationCodeVisible = true;
                        // XDA-GIL - 20240112 - refresh seems to not exit with CodeMirror v6.  
                        //Joomla.editors.instances["bfValidationCode"].refresh();
                    } else if (validationCodeVisible && jQuery('#bfValidationScriptCustom').is(':hidden')) {
                        validationCodeVisible = false;
                    }

                }, 500);

            });


}());
