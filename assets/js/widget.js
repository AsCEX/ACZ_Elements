(function () {
  'use strict';

  var EFFECTS_SINGLE_SLIDE = ['fade', 'cube', 'flip', 'cards', 'creative'];

  function toNumber(value, fallback) {
    var number = Number(value);
    return Number.isFinite(number) ? number : fallback;
  }

  function parseConfig(root) {
    var raw = root.getAttribute('data-cec-config');
    if (!raw) return {};
    try {
      return JSON.parse(raw);
    } catch (error) {
      console.error('ACZ Carousel: invalid config', error);
      return {};
    }
  }

  function makeOptions(root, config) {
    var effect = config.effect || 'slide';
    var isSingleSlideEffect = EFFECTS_SINGLE_SLIDE.indexOf(effect) !== -1;
    var showArrows = !!config.showArrows;
    var showPagination = !!config.showPagination;
    var allowMulti = !isSingleSlideEffect && effect !== 'coverflow';

    var desktopSlides = allowMulti ? Math.max(1, toNumber(config.slidesPerView, 1)) : 1;
    var tabletSlides = allowMulti ? Math.max(1, toNumber(config.slidesPerViewTablet, desktopSlides)) : 1;
    var mobileSlides = allowMulti ? Math.max(1, toNumber(config.slidesPerViewMobile, 1)) : 1;

    var desktopSpace = toNumber(config.spaceBetween, 24);
    var tabletSpace = toNumber(config.spaceBetweenTablet, desktopSpace);
    var mobileSpace = toNumber(config.spaceBetweenMobile, tabletSpace);
    var layout = config.layout || 'grid';

    if (layout === 'edge_overlap') {
      var offset = toNumber(config.edgeOverlapOffset, 0.8);
      desktopSlides += offset;
      tabletSlides += offset;
      mobileSlides += offset;
    }
    var slideCount = root.querySelectorAll('.swiper .swiper-slide').length;
    var maxSlidesPerView = Math.max(desktopSlides, tabletSlides, mobileSlides);
    var minSlidesForLoop = Math.max(2, maxSlidesPerView + 1);
    var canLoop = slideCount >= minSlidesForLoop;

    var options = {
      effect: effect,
      speed: Math.max(100, toNumber(config.speed, 600)),
      loop: !!config.loop && canLoop,
      autoHeight: !!config.autoHeight,
      grabCursor: !!config.grabCursor,
      watchOverflow: true,
      observer: true,
      observeParents: true,
      slidesPerView: desktopSlides,
      slidesPerGroup: 1,
      spaceBetween: desktopSpace,
      breakpoints: {
        0: {
          slidesPerView: mobileSlides,
          spaceBetween: mobileSpace
        },
        768: {
          slidesPerView: tabletSlides,
          spaceBetween: tabletSpace
        },
        1025: {
          slidesPerView: desktopSlides,
          spaceBetween: desktopSpace
        }
      },
      navigation: showArrows ? {
        nextEl: root.querySelector('.swiper-button-next'),
        prevEl: root.querySelector('.swiper-button-prev')
      } : false,
      pagination: showPagination ? {
        el: root.querySelector('.swiper-pagination'),
        clickable: true
      } : false,
      autoplay: config.autoplay ? {
        delay: Math.max(500, toNumber(config.autoplayDelay, 3000)),
        disableOnInteraction: false,
        pauseOnMouseEnter: false
      } : false,
      fadeEffect: {
        crossFade: true
      },
      cubeEffect: {
        shadow: false,
        slideShadows: false
      },
      coverflowEffect: {
        rotate: 30,
        stretch: 0,
        depth: 120,
        modifier: 1,
        scale: 0.95,
        slideShadows: false
      },
      flipEffect: {
        slideShadows: false
      },
      cardsEffect: {
        slideShadows: false,
        perSlideRotate: 2,
        perSlideOffset: 8
      },
      creativeEffect: {
        prev: {
          shadow: false,
          translate: ['-120%', 0, -400],
          rotate: [0, 0, -8]
        },
        next: {
          translate: ['120%', 0, -400],
          rotate: [0, 0, 8]
        }
      }
    };

    if (effect === 'coverflow' || layout === 'edge_overlap') {
      options.centeredSlides = true;
    }

    return options;
  }

  function calculateYoutubeCoverScale(width, height) {
    if (width <= 0 || height <= 0) {
      return 1.35;
    }

    var containerRatio = width / height;
    var videoRatio = 16 / 9;
    var scaleX = videoRatio / containerRatio;
    var scaleY = containerRatio / videoRatio;

    return Math.max(1.35, scaleX, scaleY);
  }

  function updateYoutubeBackgroundCover(root) {
    if (!root || !root.querySelectorAll) {
      return;
    }

    root.querySelectorAll('.cec-background-video-embed').forEach(function (embed) {
      var iframe = embed.querySelector('.cec-background-video-iframe');
      if (!iframe) {
        return;
      }

      var rect = embed.getBoundingClientRect();
      var scale = calculateYoutubeCoverScale(rect.width, rect.height);
      embed.style.setProperty('--cec-bg-iframe-scale', scale.toFixed(4));
    });
  }

  function initCarousel(root) {
    if (!root || root.dataset.cecInitialized === '1') {
      return;
    }

    var swiperElement = root.querySelector('.swiper');
    if (!swiperElement || typeof window.Swiper === 'undefined') {
      return;
    }

    var config = parseConfig(root);
    var swiper = new window.Swiper(swiperElement, makeOptions(root, config));
    updateYoutubeBackgroundCover(root);

    if (swiper && typeof swiper.on === 'function') {
      ['resize', 'breakpoint', 'observerUpdate', 'setTranslate'].forEach(function (eventName) {
        swiper.on(eventName, function () {
          updateYoutubeBackgroundCover(root);
        });
      });
    }

    if (!root.__cecYoutubeCoverResizeBound) {
      root.__cecYoutubeCoverResizeBound = true;
      window.addEventListener('resize', function () {
        updateYoutubeBackgroundCover(root);
      });
    }

    if (config.autoplay && config.pauseOnHover && swiper.autoplay) {
      root.addEventListener('mouseenter', function () {
        swiper.autoplay.stop();
      });
      root.addEventListener('mouseleave', function () {
        swiper.autoplay.start();
      });
    }

    root.dataset.cecInitialized = '1';
  }

  function collectRoots(scope) {
    var roots = [];
    if (!scope) {
      return roots;
    }

    if (scope.matches && scope.matches('.cec-effects-carousel')) {
      roots.push(scope);
    }

    if (scope.querySelectorAll) {
      scope.querySelectorAll('.cec-effects-carousel').forEach(function (node) {
        roots.push(node);
      });
    }

    return roots;
  }

  function initInScope(scope) {
    collectRoots(scope).forEach(function (root) {
      initCarousel(root);
    });
  }

  function activateTab(root, button) {
    var buttons = root.querySelectorAll('.acz-post-tabs-button');
    var panels = root.querySelectorAll('.acz-post-tabs-panel');
    var targetId = button.getAttribute('data-target');

    buttons.forEach(function (node) {
      var isActive = node === button;
      node.classList.toggle('is-active', isActive);
      node.setAttribute('aria-selected', isActive ? 'true' : 'false');
      node.setAttribute('tabindex', isActive ? '0' : '-1');
    });

    panels.forEach(function (panel) {
      var isActive = panel.id === targetId;
      panel.classList.toggle('is-active', isActive);
      if (isActive) {
        panel.removeAttribute('hidden');
      } else {
        panel.setAttribute('hidden', 'hidden');
      }
    });
  }

  function initPostTabs(root) {
    if (!root || root.dataset.aczTabsInitialized === '1') {
      return;
    }

    var buttons = root.querySelectorAll('.acz-post-tabs-button');
    if (!buttons.length) {
      return;
    }

    buttons.forEach(function (button, index) {
      button.addEventListener('click', function () {
        activateTab(root, button);
      });

      button.addEventListener('keydown', function (event) {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
          return;
        }

        event.preventDefault();
        var direction = event.key === 'ArrowRight' ? 1 : -1;
        var nextIndex = (index + direction + buttons.length) % buttons.length;
        var nextButton = buttons[nextIndex];

        if (nextButton) {
          activateTab(root, nextButton);
          nextButton.focus();
        }
      });
    });

    root.dataset.aczTabsInitialized = '1';
  }

  function initPostTabsInScope(scope) {
    if (!scope) {
      return;
    }

    var roots = [];

    if (scope.matches && scope.matches('.acz-post-tabs')) {
      roots.push(scope);
    }

    if (scope.querySelectorAll) {
      scope.querySelectorAll('.acz-post-tabs').forEach(function (node) {
        roots.push(node);
      });
    }

    roots.forEach(function (root) {
      initPostTabs(root);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initInScope(document);
    initPostTabsInScope(document);
  });

  window.addEventListener('elementor/frontend/init', function () {
    if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
      initInScope(document);
      return;
    }

    window.elementorFrontend.hooks.addAction('frontend/element_ready/acz_carousel.default', function ($scope) {
      initInScope($scope && $scope[0] ? $scope[0] : $scope);
    });

    window.elementorFrontend.hooks.addAction('frontend/element_ready/acz_post_carousel.default', function ($scope) {
      initInScope($scope && $scope[0] ? $scope[0] : $scope);
    });

    window.elementorFrontend.hooks.addAction('frontend/element_ready/acz_post_tabs.default', function ($scope) {
      initPostTabsInScope($scope && $scope[0] ? $scope[0] : $scope);
    });

    window.elementorFrontend.hooks.addAction('frontend/element_ready/acz_media_gallery.default', function ($scope) {
      initInScope($scope && $scope[0] ? $scope[0] : $scope);
    });

    window.elementorFrontend.hooks.addAction('frontend/element_ready/acz_logo_carousel.default', function ($scope) {
      initInScope($scope && $scope[0] ? $scope[0] : $scope);
    });
  });
})();
