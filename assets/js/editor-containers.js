(function () {
  'use strict';

  var WIDGET_TYPE = 'karice_carousel';
  var patchedRun = false;
  var patchedRepeaterDefaults = false;
  var addClickInterceptorBound = false;

  function isTargetContainer(container) {
    return !!(
      container &&
      container.settings &&
      typeof container.settings.get === 'function' &&
      container.settings.get('widgetType') === WIDGET_TYPE
    );
  }

  function getSlidesCount(container) {
    var slides = container.settings.get('carousel_items');

    if (!slides) {
      slides = container.settings.get('slides');
    }

    if (slides && typeof slides.length === 'number') {
      return slides.length;
    }

    if (Array.isArray(slides)) {
      return slides.length;
    }

    return 0;
  }

  function getChildrenCount(container) {
    return Array.isArray(container.children) ? container.children.length : 0;
  }

  function getChildContainer(container, index) {
    if (Array.isArray(container.children) && container.children[index]) {
      return container.children[index];
    }

    if (
      container.view &&
      container.view.children &&
      typeof container.view.children.findByIndex === 'function'
    ) {
      var childView = container.view.children.findByIndex(index);
      if (childView && typeof childView.getContainer === 'function') {
        return childView.getContainer();
      }
    }

    return null;
  }

  function createChildContainer(container, index) {
    $e.run('document/elements/create', {
      container: container,
      model: {
        elType: 'container',
        isLocked: true,
        _title: 'Slide #' + (index + 1),
        settings: {
          content_width: 'full'
        }
      },
      options: {
        edit: false
      }
    });
  }

  function syncSlideChildren(container) {
    if (!isTargetContainer(container) || typeof $e === 'undefined') {
      return;
    }

    var slidesCount = getSlidesCount(container);
    var childrenCount = getChildrenCount(container);
    var i;

    for (i = childrenCount; i < slidesCount; i++) {
      createChildContainer(container, i);
    }

    for (i = getChildrenCount(container) - 1; i >= slidesCount; i--) {
      var child = getChildContainer(container, i);
      if (!child) {
        continue;
      }

      $e.run('document/elements/delete', {
        container: child,
        force: true
      });
    }
  }

  function patchERun() {
    if (patchedRun || typeof $e === 'undefined' || typeof $e.run !== 'function') {
      return false;
    }

    var originalRun = $e.run;

    $e.run = function (command, args) {
      var result = originalRun.apply(this, arguments);

      try {
        if (
          command === 'document/repeater/insert' ||
          command === 'document/repeater/remove' ||
          command === 'document/repeater/duplicate' ||
          command === 'document/repeater/move'
        ) {
          var container = args && args.container;
          if (isTargetContainer(container)) {
            window.setTimeout(function () {
              syncSlideChildren(container);
            }, 0);
          }
        }

        if (command === 'panel/editor/open') {
          var view = args && args.view;
          if (view && typeof view.getContainer === 'function') {
            var editorContainer = view.getContainer();
            if (isTargetContainer(editorContainer)) {
              window.setTimeout(function () {
                syncSlideChildren(editorContainer);
              }, 0);
            }
          }
        }
      } catch (e) {
        // Keep editor stable if sync fails.
      }

      return result;
    };

    patchedRun = true;
    return true;
  }

  function patchRepeaterDefaults() {
    if (
      patchedRepeaterDefaults ||
      !window.elementor ||
      !elementor.modules ||
      !elementor.modules.controls
    ) {
      return false;
    }

    var Repeater = elementor.modules.controls.Repeater;

    if (!Repeater || !Repeater.prototype) {
      return false;
    }

    if (typeof Repeater.prototype.getDefaults === 'function' && !Repeater.prototype.__cecGuardedDefaults) {
      var originalGetDefaults = Repeater.prototype.getDefaults;

      Repeater.prototype.getDefaults = function () {
        try {
          return originalGetDefaults.apply(this, arguments);
        } catch (error) {
          var msg = (error && error.message) || '';

          if (msg.indexOf('defaults') === -1) {
            throw error;
          }

          var model = { _id: '' };
          var fields = this.model && this.model.get && this.model.get('fields');

          if (fields) {
            Object.keys(fields).forEach(function (key) {
              var field = fields[key] || {};
              var fieldName = field.name || key;
              model[fieldName] = typeof field.default === 'undefined' ? '' : field.default;
            });
          }

          if (typeof model.slide_title === 'undefined') {
            model.slide_title = '';
          }

          return model;
        }
      };

      Repeater.prototype.__cecGuardedDefaults = true;
    }

    if (typeof Repeater.prototype.onButtonAddRowClick === 'function' && !Repeater.prototype.__cecGuardedAddRow) {
      var originalAddRow = Repeater.prototype.onButtonAddRowClick;

      Repeater.prototype.onButtonAddRowClick = function () {
        try {
          return originalAddRow.apply(this, arguments);
        } catch (error) {
          var msg = (error && error.message) || '';
          if (msg.indexOf('defaults') === -1) {
            throw error;
          }

          var newModel = $e.run('document/repeater/insert', {
            container: this.options.container,
            name: this.model.get('name'),
            model: this.getDefaults()
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

      Repeater.prototype.__cecGuardedAddRow = true;
    }

    patchedRepeaterDefaults = true;
    return true;
  }

  function getCurrentEditedWidgetContainer() {
    try {
      if (!window.elementor || typeof elementor.getPanelView !== 'function') {
        return null;
      }

      var panelView = elementor.getPanelView();
      var pageView = panelView && typeof panelView.getCurrentPageView === 'function' ? panelView.getCurrentPageView() : null;
      var editedElementView = pageView && typeof pageView.getOption === 'function' ? pageView.getOption('editedElementView') : null;

      if (!editedElementView || typeof editedElementView.getContainer !== 'function') {
        return null;
      }

      var container = editedElementView.getContainer();
      return isTargetContainer(container) ? container : null;
    } catch (e) {
      return null;
    }
  }

  function bindAddSlideClickInterceptor() {
    if (addClickInterceptorBound) {
      return;
    }

    document.addEventListener(
      'click',
      function (event) {
        var addButton = event.target && event.target.closest
          ? event.target.closest('.elementor-control-carousel_items .elementor-repeater-add, .elementor-control-slides .elementor-repeater-add')
          : null;

        if (!addButton) {
          return;
        }

        var container = getCurrentEditedWidgetContainer();
        if (!container || typeof $e === 'undefined') {
          return;
        }

        // Prevent Elementor's default repeater handler that crashes on missing `config.defaults`.
        event.preventDefault();
        event.stopImmediatePropagation();

        $e.run('document/repeater/insert', {
          container: container,
          name: 'carousel_items',
          model: {
            _id: '',
            slide_title: ''
          }
        });

        window.setTimeout(function () {
          syncSlideChildren(container);
        }, 0);
      },
      true
    );

    addClickInterceptorBound = true;
  }

  function forceSyncCurrentEditedWidget() {
    var container = getCurrentEditedWidgetContainer();
    if (!container) {
      return;
    }

    syncSlideChildren(container);
  }

  function bindEditorSyncHooks() {
    if (!window.elementor || !elementor.channels || !elementor.channels.editor) {
      return false;
    }

    elementor.channels.editor.on('change:' + WIDGET_TYPE + ':carousel_items', function () {
      forceSyncCurrentEditedWidget();
    });

    elementor.channels.editor.on('change', function (controlView) {
      if (!controlView || !controlView.model || controlView.model.get('name') !== 'carousel_items') {
        return;
      }
      forceSyncCurrentEditedWidget();
    });

    return true;
  }

  function warmupForcedSync() {
    var tries = 0;
    var timer = window.setInterval(function () {
      tries++;
      forceSyncCurrentEditedWidget();
      if (tries >= 20) {
        window.clearInterval(timer);
      }
    }, 300);
  }

  function init() {
    if (patchERun() && patchRepeaterDefaults()) {
      bindAddSlideClickInterceptor();
      bindEditorSyncHooks();
      warmupForcedSync();
      return;
    }

    var retries = 0;
    var timer = window.setInterval(function () {
      retries++;
      patchERun();
      patchRepeaterDefaults();
      bindAddSlideClickInterceptor();
      bindEditorSyncHooks();
      warmupForcedSync();

      if ((patchedRun && patchedRepeaterDefaults) || retries > 30) {
        window.clearInterval(timer);
      }
    }, 250);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
