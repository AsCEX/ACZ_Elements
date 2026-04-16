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
      console.error('Karice Carousel: invalid config', error);
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

    if (effect === 'coverflow') {
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

  document.addEventListener('DOMContentLoaded', function () {
    initInScope(document);
  });

  window.addEventListener('elementor/frontend/init', function () {
    if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
      initInScope(document);
      return;
    }

    window.elementorFrontend.hooks.addAction('frontend/element_ready/karice_carousel.default', function ($scope) {
      initInScope($scope && $scope[0] ? $scope[0] : $scope);
    });

    window.elementorFrontend.hooks.addAction('frontend/element_ready/karice_post_carousel.default', function ($scope) {
      initInScope($scope && $scope[0] ? $scope[0] : $scope);
    });
  });
})();
