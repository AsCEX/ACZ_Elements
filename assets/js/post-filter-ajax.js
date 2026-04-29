(function () {
  'use strict';

  function parseHTML(html) {
    var parser = new DOMParser();
    return parser.parseFromString(html, 'text/html');
  }

  function buildUrlFromForm(form) {
    var action = form.getAttribute('action') || window.location.href;
    var url = new URL(action, window.location.origin);
    var currentParams = new URLSearchParams(window.location.search || '');
    var filterParam = form.getAttribute('data-acz-filter-param') || '';
    var data = new FormData(form);
    var nextValues = [];

    currentParams.forEach(function (value, key) {
      if (key.indexOf('acz_pg_') === 0) {
        currentParams.delete(key);
      }
    });

    if (!filterParam) {
      url.search = currentParams.toString();
      return url.toString();
    }

    data.forEach(function (value, key) {
      if (key !== filterParam) {
        return;
      }
      if (typeof value === 'string' && value !== '') {
        nextValues.push(value);
      }
    });

    currentParams.delete(filterParam);
    nextValues.forEach(function (value) {
      currentParams.append(filterParam, value);
    });

    url.search = currentParams.toString();
    return url.toString();
  }

  function getTargetGalleries(filterParam) {
    var selector = '.acz-post-gallery-widget[data-acz-ajax-enabled="yes"]';
    var galleries = Array.prototype.slice.call(document.querySelectorAll(selector));

    if (!filterParam) {
      return galleries;
    }

    var matched = galleries.filter(function (gallery) {
      var singleParam = gallery.getAttribute('data-acz-filter-param') || '';
      if (singleParam === filterParam) {
        return true;
      }

      var multiParams = gallery.getAttribute('data-acz-filter-params') || '';
      if (!multiParams) {
        return false;
      }

      return multiParams
        .split(',')
        .map(function (item) { return item.trim(); })
        .indexOf(filterParam) !== -1;
    });

    // Fallback: if no explicit mapping matched, update all ajax-enabled galleries.
    return matched.length ? matched : galleries;
  }

  function setLoading(galleries, isLoading) {
    galleries.forEach(function (gallery) {
      if (isLoading) {
        gallery.classList.add('is-loading');
      } else {
        gallery.classList.remove('is-loading');
      }
    });
  }

  function playExitTransition(galleries) {
    var maxDuration = 0;

    galleries.forEach(function (gallery) {
      var effect = gallery.getAttribute('data-acz-exit-effect') || 'none';
      var slot = gallery.querySelector('.acz-post-gallery-ajax-slot');
      if (!slot || effect !== 'fade-out') {
        return;
      }

      var duration = parseInt(gallery.getAttribute('data-acz-exit-duration') || '220', 10);
      if (!Number.isFinite(duration) || duration < 0) {
        duration = 220;
      }

      slot.style.transition = 'opacity ' + duration + 'ms ease';
      slot.style.opacity = '0';
      if (duration > maxDuration) {
        maxDuration = duration;
      }
    });

    if (maxDuration <= 0) {
      return Promise.resolve();
    }

    return new Promise(function (resolve) {
      window.setTimeout(resolve, maxDuration + 20);
    });
  }

  function resetExitTransition(galleries) {
    galleries.forEach(function (gallery) {
      var slot = gallery.querySelector('.acz-post-gallery-ajax-slot');
      if (!slot) {
        return;
      }
      slot.style.opacity = '';
      slot.style.transition = '';
    });
  }

  function replaceGalleryContent(currentGallery, nextGallery) {
    if (!currentGallery || !nextGallery) {
      return;
    }

    var currentSlot = currentGallery.querySelector('.acz-post-gallery-ajax-slot');
    var nextSlot = nextGallery.querySelector('.acz-post-gallery-ajax-slot');

    if (!currentSlot || !nextSlot) {
      return;
    }

    currentSlot.innerHTML = nextSlot.innerHTML;
  }

  function shuffleArray(items) {
    var arr = items.slice();
    for (var i = arr.length - 1; i > 0; i--) {
      var j = Math.floor(Math.random() * (i + 1));
      var tmp = arr[i];
      arr[i] = arr[j];
      arr[j] = tmp;
    }
    return arr;
  }

  function rearrangeTiles(galleryRoot) {
    if (!galleryRoot || (galleryRoot.getAttribute('data-acz-rearrange') || 'no') !== 'yes') {
      return;
    }

    var grid = galleryRoot.querySelector('.acz-post-gallery');
    if (!grid) {
      return;
    }

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.acz-post-card'));
    if (cards.length < 2) {
      return;
    }

    var duration = parseInt(galleryRoot.getAttribute('data-acz-rearrange-duration') || '700', 10);
    if (!Number.isFinite(duration) || duration <= 0) {
      duration = 700;
    }

    var firstRects = new Map();
    cards.forEach(function (card) {
      firstRects.set(card, card.getBoundingClientRect());
    });

    var shuffled = shuffleArray(cards);
    shuffled.forEach(function (card) {
      grid.appendChild(card);
    });

    cards.forEach(function (card) {
      var first = firstRects.get(card);
      var last = card.getBoundingClientRect();
      if (!first || !last) {
        return;
      }

      var dx = first.left - last.left;
      var dy = first.top - last.top;

      card.style.transition = 'none';
      card.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)';
      card.style.willChange = 'transform';
    });

    // Force reflow before playing the transition.
    grid.offsetHeight;

    cards.forEach(function (card) {
      card.style.transition = 'transform ' + duration + 'ms cubic-bezier(0.22, 1, 0.36, 1)';
      card.style.transform = 'translate(0, 0)';
    });

    window.setTimeout(function () {
      cards.forEach(function (card) {
        card.style.transition = '';
        card.style.transform = '';
        card.style.willChange = '';
      });
    }, duration + 60);
  }

  function fetchAndReplace(url, galleries, updateHistory) {
    if (!galleries.length) {
      window.location.href = url;
      return;
    }

    setLoading(galleries, true);

    fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Request failed');
        }
        return response.text();
      })
      .then(function (html) {
        var doc = parseHTML(html);

        return playExitTransition(galleries).then(function () {
          galleries.forEach(function (gallery) {
            var id = gallery.getAttribute('id');
            if (!id) {
              return;
            }

            var replacement = doc.getElementById(id);
            if (replacement) {
              replaceGalleryContent(gallery, replacement);
              rearrangeTiles(gallery);
            }
          });

          if (updateHistory) {
            window.history.pushState({}, '', url);
          }
        });
      })
      .catch(function () {
        window.location.href = url;
      })
      .finally(function () {
        resetExitTransition(galleries);
        setLoading(galleries, false);
      });
  }

  document.addEventListener('change', function (event) {
    var select = event.target.closest('.acz-post-filter-form .acz-post-filter-select');
    if (!select) {
      return;
    }

    var form = select.closest('.acz-post-filter-form');
    if (!form) {
      return;
    }

    var autoSubmit = (form.getAttribute('data-acz-auto-submit') || 'no') === 'yes';
    if (!autoSubmit) {
      return;
    }

    event.preventDefault();
    var filterParam = form.getAttribute('data-acz-filter-param') || '';
    var galleries = getTargetGalleries(filterParam);
    var url = buildUrlFromForm(form);
    fetchAndReplace(url, galleries, true);
  });

  document.addEventListener('submit', function (event) {
    var form = event.target.closest('.acz-post-filter-form');
    if (!form) {
      return;
    }

    event.preventDefault();
    var filterParam = form.getAttribute('data-acz-filter-param') || '';
    var galleries = getTargetGalleries(filterParam);
    var url = buildUrlFromForm(form);
    fetchAndReplace(url, galleries, true);
  });

  document.addEventListener('click', function (event) {
    var link = event.target.closest('.acz-post-gallery-widget .acz-post-gallery-pagination a');
    if (!link) {
      return;
    }

    var gallery = link.closest('.acz-post-gallery-widget[data-acz-ajax-enabled="yes"]');
    if (!gallery) {
      return;
    }

    var href = link.getAttribute('href');
    if (!href) {
      return;
    }

    event.preventDefault();
    fetchAndReplace(href, [gallery], true);
  });

  document.addEventListener('DOMContentLoaded', function () {
    var galleries = Array.prototype.slice.call(document.querySelectorAll('.acz-post-gallery-widget[data-acz-rearrange="yes"]'));
    galleries.forEach(function (gallery) {
      rearrangeTiles(gallery);
    });
  });
})();
