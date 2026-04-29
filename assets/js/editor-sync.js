(function ($) {
  'use strict';

  var WIDGET_TYPE = 'acz_carousel';
  var isSyncing = false;
  var repeaterDefaultsPatched = false;
  var controlRegistrarPatched = false;
  var repeaterRemoveRunPatched = false;

  function getWidgetContainer(editedElementView) {
    if (!editedElementView || typeof editedElementView.getContainer !== 'function') {
      return null;
    }

    var container = editedElementView.getContainer();

    if (!container || !container.model || container.model.get('widgetType') !== WIDGET_TYPE) {
      return null;
    }

    return container;
  }

  function getSlidesCount(container) {
    var slides = container.settings && container.settings.get('slides');

    return Array.isArray(slides) ? slides.length : 0;
  }

  function getChildrenCount(container) {
    return Array.isArray(container.children) ? container.children.length : 0;
  }

  function ensureContainerNestedDefaults(container) {
    if (!container) {
      return;
    }

    var model = (container.view && container.view.model) || container.model;
    if (!model) {
      return;
    }

    model.config = model.config || {};
    model.config.defaults = model.config.defaults || {};

    if (!Array.isArray(model.config.defaults.elements)) {
      model.config.defaults.elements = [];
    }

    if (!model.config.defaults.elements_title) {
      model.config.defaults.elements_title = 'Slide #%d';
    }

    if (!model.config.defaults.repeater_title_setting) {
      model.config.defaults.repeater_title_setting = 'title';
    }

    if (!model.config.defaults.elements_placeholder_selector) {
      model.config.defaults.elements_placeholder_selector = '.swiper-wrapper';
    }

    if (!model.config.defaults.child_container_placeholder_selector) {
      model.config.defaults.child_container_placeholder_selector = '.swiper-slide .cec-slide-content';
    }
  }

  function findChildContainerByIndex(container, index) {
    if (
      container &&
      container.view &&
      container.view.children &&
      typeof container.view.children.findByIndex === 'function'
    ) {
      var childView = container.view.children.findByIndex(index);
      if (childView && typeof childView.getContainer === 'function') {
        return childView.getContainer();
      }
    }

    if (Array.isArray(container.children) && container.children[index]) {
      return container.children[index];
    }

    return null;
  }

  function ensureChildContainerAtIndex(container, index) {
    ensureContainerNestedDefaults(container);

    if (typeof index !== 'number' || index < 0) {
      return;
    }

    while (getChildrenCount(container) <= index) {
      var nextIndex = getChildrenCount(container);

      $e.run('document/elements/create', {
        container: container,
        model: {
          elType: 'container',
          isLocked: true,
          _title: 'Slide #' + (nextIndex + 1),
          settings: {
            content_width: 'full'
          }
        },
        options: {
          edit: false
        }
      });
    }
  }

  function addMissingSlideContainers(container, slidesCount, childrenCount) {
    if (childrenCount >= slidesCount) {
      return;
    }

    for (var i = childrenCount; i < slidesCount; i++) {
      $e.run('document/elements/create', {
        container: container,
        model: {
          elType: 'container',
          isLocked: true,
          _title: 'Slide #' + (i + 1),
          settings: {
            content_width: 'full'
          }
        },
        options: {
          edit: false
        }
      });
    }
  }

  function removeExtraSlideContainers(container, slidesCount, childrenCount) {
    if (childrenCount <= slidesCount) {
      return;
    }

    for (var i = childrenCount - 1; i >= slidesCount; i--) {
      var childContainer = findChildContainerByIndex(container, i);

      if (!childContainer) {
        continue;
      }

      $e.run('document/elements/delete', {
        container: childContainer,
        force: true
      });
    }
  }

  function syncSlideContainers(container) {
    var slidesCount = getSlidesCount(container);
    var childrenCount = getChildrenCount(container);

    removeExtraSlideContainers(container, slidesCount, childrenCount);
    addMissingSlideContainers(container, slidesCount, getChildrenCount(container));
  }

  function sync(editedElementView) {
    var container = getWidgetContainer(editedElementView);

    if (!container || isSyncing || typeof $e === 'undefined') {
      return;
    }

    isSyncing = true;

    window.setTimeout(function () {
      try {
        ensureContainerNestedDefaults(container);
        syncSlideContainers(container);
      } finally {
        isSyncing = false;
      }
    }, 0);
  }

  function bindSyncHandlers() {
    if (!window.elementor || !elementor.channels || !elementor.channels.editor || !elementor.hooks) {
      return false;
    }

    elementor.hooks.addAction('panel/open_editor/widget/' + WIDGET_TYPE, function (panel, model, view) {
      sync(view);
    });

    elementor.channels.editor.on('change:' + WIDGET_TYPE + ':slides', function (controlView, editedElementView) {
      sync(editedElementView);
    });

    elementor.channels.editor.on('change', function (controlView, editedElementView) {
      if (!controlView || !controlView.model || controlView.model.get('name') !== 'slides') {
        return;
      }

      sync(editedElementView);
    });

    return true;
  }

  function makeFallbackDefaults(context) {
    var fallbackDefaults = { _id: '' };
    var fields = context && context.model && context.model.get && context.model.get('fields');

    if (fields) {
      Object.keys(fields).forEach(function (key) {
        var field = fields[key] || {};
        var fieldName = field.name || key;
        fallbackDefaults[fieldName] = typeof field.default === 'undefined' ? '' : field.default;
      });
    }

    if (typeof fallbackDefaults.title === 'undefined') {
      fallbackDefaults.title = '';
    }

    return fallbackDefaults;
  }

  function patchRepeaterDefaultsGuard() {
    if (repeaterDefaultsPatched || !window.elementor || !elementor.modules || !elementor.modules.controls) {
      return;
    }

    var controls = elementor.modules.controls;
    var patchedAny = false;

    Object.keys(controls).forEach(function (controlName) {
      var ControlCtor = controls[controlName];

      if (!ControlCtor || !ControlCtor.prototype || typeof ControlCtor.prototype.getDefaults !== 'function') {
        return;
      }

      if (ControlCtor.prototype.__cecDefaultsGuarded) {
        patchedAny = true;
        return;
      }

      var originalGetDefaults = ControlCtor.prototype.getDefaults;

      ControlCtor.prototype.getDefaults = function () {
        try {
          return originalGetDefaults.apply(this, arguments);
        } catch (error) {
          var msg = (error && error.message) || '';
          if (msg.indexOf('defaults') !== -1 || msg.indexOf('repeater_title_setting') !== -1) {
            return makeFallbackDefaults(this);
          }
          throw error;
        }
      };

      ControlCtor.prototype.__cecDefaultsGuarded = true;
      patchedAny = true;
    });

    if (patchedAny) {
      repeaterDefaultsPatched = true;
    }
  }

  function patchControlConstructor(ControlCtor, controlName) {
    if (!ControlCtor || !ControlCtor.prototype) {
      return;
    }

    if (
      !/repeater/i.test(controlName || '') &&
      !/repeater/i.test((ControlCtor.name || '') + '')
    ) {
      return;
    }

    if (typeof ControlCtor.prototype.getDefaults === 'function' && !ControlCtor.prototype.__cecDefaultsGuarded) {
      var originalGetDefaults = ControlCtor.prototype.getDefaults;

      ControlCtor.prototype.getDefaults = function () {
        try {
          if (this && this.options && this.options.container) {
            ensureContainerNestedDefaults(this.options.container);
          }
          return originalGetDefaults.apply(this, arguments);
        } catch (error) {
          var msg = (error && error.message) || '';
          if (msg.indexOf('defaults') !== -1 || msg.indexOf('repeater_title_setting') !== -1) {
            return makeFallbackDefaults(this);
          }
          throw error;
        }
      };

      ControlCtor.prototype.__cecDefaultsGuarded = true;
    }

    if (typeof ControlCtor.prototype.onButtonAddRowClick === 'function' && !ControlCtor.prototype.__cecAddRowGuarded) {
      var originalAddRow = ControlCtor.prototype.onButtonAddRowClick;

      ControlCtor.prototype.onButtonAddRowClick = function () {
        try {
          if (this && this.options && this.options.container) {
            ensureContainerNestedDefaults(this.options.container);
          }
          return originalAddRow.apply(this, arguments);
        } catch (error) {
          var msg = (error && error.message) || '';
          if (msg.indexOf('defaults') === -1 && msg.indexOf('repeater_title_setting') === -1) {
            throw error;
          }

          var model = makeFallbackDefaults(this);
          ensureContainerNestedDefaults(this.options && this.options.container);

          var newModel = $e.run('document/repeater/insert', {
            container: this.options.container,
            name: this.model.get('name'),
            model: model
          });

          if (this.children && typeof this.children.findByModel === 'function') {
            var newChild = this.children.findByModel(newModel);
            if (newChild && typeof this.editRow === 'function') {
              this.editRow(newChild);
            }
          }

          if (typeof this.toggleClasses === 'function') {
            this.toggleClasses();
          }
        }
      };

      ControlCtor.prototype.__cecAddRowGuarded = true;
    }

    if (typeof ControlCtor.prototype.onChildviewClickRemove === 'function' && !ControlCtor.prototype.__cecRemoveGuarded) {
      var originalRemoveRow = ControlCtor.prototype.onChildviewClickRemove;

      ControlCtor.prototype.onChildviewClickRemove = function (childView) {
        try {
          if (this && this.options && this.options.container) {
            ensureContainerNestedDefaults(this.options.container);
          }

          return originalRemoveRow.apply(this, arguments);
        } catch (error) {
          var msg = (error && error.message) || '';
          if (msg.indexOf('Child container was not found') === -1) {
            throw error;
          }

          var removeIndex = childView && typeof childView._index === 'number' ? childView._index : 0;
          var container = this && this.options ? this.options.container : null;

          if (!container) {
            throw error;
          }

          ensureChildContainerAtIndex(container, removeIndex);

          if (this.currentEditableChild === childView) {
            delete this.currentEditableChild;
          }

          $e.run('document/repeater/remove', {
            container: container,
            name: this.model.get('name'),
            index: removeIndex
          });

          if (typeof this.updateActiveRow === 'function') {
            this.updateActiveRow();
          }

          if (typeof this.updateChildIndexes === 'function') {
            this.updateChildIndexes();
          }

          if (typeof this.toggleClasses === 'function') {
            this.toggleClasses();
          }
        }
      };

      ControlCtor.prototype.__cecRemoveGuarded = true;
    }
  }

  function patchRepeaterAddRowGuard() {
    if (!window.elementor || !elementor.modules || !elementor.modules.controls) {
      return;
    }

    var controls = elementor.modules.controls;

    Object.keys(controls).forEach(function (name) {
      patchControlConstructor(controls[name], name);
    });
  }

  function patchControlRegistrationHook() {
    if (controlRegistrarPatched || !window.elementor || typeof elementor.addControlView !== 'function') {
      return;
    }

    var originalAddControlView = elementor.addControlView;

    elementor.addControlView = function (controlID, ControlView) {
      patchControlConstructor(ControlView, controlID);
      return originalAddControlView.apply(this, arguments);
    };

    controlRegistrarPatched = true;
  }

  function patchRepeaterRemoveRunGuard() {
    if (repeaterRemoveRunPatched || typeof $e === 'undefined' || typeof $e.run !== 'function') {
      return;
    }

    var originalRun = $e.run;

    $e.run = function (command, args) {
      if (command === 'document/repeater/remove') {
        try {
          var container = args && args.container;
          if (container && container.model && container.model.get('widgetType') === WIDGET_TYPE) {
            var removeIndex = typeof args.index === 'number' ? args.index : 0;
            ensureChildContainerAtIndex(container, removeIndex);
          }
        } catch (e) {
          // Non-fatal: continue with original command flow.
        }
      }

      return originalRun.apply(this, arguments);
    };

    repeaterRemoveRunPatched = true;
  }

  function init() {
    patchControlRegistrationHook();
    patchRepeaterDefaultsGuard();
    patchRepeaterAddRowGuard();
    patchRepeaterRemoveRunGuard();

    if (bindSyncHandlers()) {
      return;
    }

    var retries = 0;
    var maxRetries = 20;
    var timer = window.setInterval(function () {
      retries++;

      patchControlRegistrationHook();
      patchRepeaterDefaultsGuard();
      patchRepeaterAddRowGuard();
      patchRepeaterRemoveRunGuard();

      if (bindSyncHandlers() || retries >= maxRetries) {
        window.clearInterval(timer);
      }
    }, 300);
  }

  $(window).on('elementor:init', init);
  init();
})(jQuery);
