(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var data = window.AczThemeOptions || {};
    var aczIncludeChoices = data.includeChoices || {};
    var aczSinglePostChoices = data.singlePostChoices || {};
    var aczSamplePostChoices = data.samplePostChoices || {};
    var aczArchiveTermChoices = data.archiveTermChoices || {};
    var aczSampleTermChoices = data.sampleTermChoices || {};
    var optionsWrap = document.querySelector('.acz-options-wrap');
    var tabs = document.querySelectorAll('.acz-options-wrap .nav-tab');
    var contents = document.querySelectorAll('.acz-options-wrap .acz-tab-content');
    var optionsReady = false;

    function revealOptionsForm() {
      if (optionsReady || !optionsWrap) {
        return;
      }

      optionsReady = true;

      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          optionsWrap.classList.remove('is-loading');
          optionsWrap.classList.add('is-ready');
        });
      });
    }

    function scheduleOptionsReveal() {
      if (window.acf && typeof window.acf.addAction === 'function') {
        window.acf.addAction('ready', function () {
          syncAllIncludeFields();
          revealOptionsForm();
        });
      }

      window.setTimeout(function () {
        syncAllIncludeFields();
        revealOptionsForm();
      }, 350);

      window.setTimeout(revealOptionsForm, 2500);
    }

    if (tabs.length > 0) {
      tabs.forEach(function (tab) {
        tab.addEventListener('click', function (event) {
          var target;

          event.preventDefault();
          target = this.getAttribute('href').substring(1);

          tabs.forEach(function (node) {
            node.classList.remove('nav-tab-active');
          });
          contents.forEach(function (node) {
            node.classList.remove('active');
          });

          this.classList.add('nav-tab-active');

          if (document.getElementById(target)) {
            document.getElementById(target).classList.add('active');
          }
        });
      });
    }

    function getRepeaterRow(element) {
      return element.closest('.acf-row, .acz-repeater-row, tr');
    }

    function getRowSelect(row, name) {
      if (!row) {
        return null;
      }

      return row.querySelector('.acf-field[data-name="' + name + '"] select');
    }

    function triggerSelectChange(select) {
      if (window.jQuery) {
        window.jQuery(select).trigger('change');
      } else {
        select.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    function syncIncludeFields(row) {
      var locationSelect = getRowSelect(row, 'location');
      var includeSelect = getRowSelect(row, 'include_fields');
      var location;
      var choices;
      var previousValues;
      var nextValues = [];
      var disabled;

      if (!locationSelect || !includeSelect) {
        return;
      }

      location = locationSelect.value || 'entire_site';
      choices = aczIncludeChoices[location] || {};
      previousValues = Array.from(includeSelect.selectedOptions || []).map(function (option) {
        return option.value;
      });

      includeSelect.innerHTML = '';

      Object.keys(choices).forEach(function (value) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = choices[value];

        if (previousValues.indexOf(value) !== -1) {
          option.selected = true;
          nextValues.push(value);
        }

        includeSelect.appendChild(option);
      });

      disabled = Object.keys(choices).length === 0 || location === 'entire_site';
      includeSelect.disabled = disabled;

      if (disabled) {
        includeSelect.value = '';
      } else {
        Array.from(includeSelect.options).forEach(function (option) {
          option.selected = nextValues.indexOf(option.value) !== -1;
        });
      }

      triggerSelectChange(includeSelect);
    }

    function syncSinglePostIncludePosts(row) {
      var postTypeSelect = getRowSelect(row, 'post_type');
      var includeSelect = getRowSelect(row, 'include_posts');
      var postType;
      var choices;
      var previousValues;
      var nextValues = [];
      var allPostOption;

      if (!postTypeSelect || !includeSelect) {
        return;
      }

      postType = postTypeSelect.value || 'post';
      choices = aczSinglePostChoices[postType] || {};
      previousValues = Array.from(includeSelect.selectedOptions || []).map(function (option) {
        return option.value;
      });

      includeSelect.innerHTML = '';

      Object.keys(choices).forEach(function (value) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = choices[value];

        if (previousValues.indexOf(value) !== -1 || (previousValues.indexOf('all_post') !== -1 && value === 'all_post')) {
          option.selected = true;
          nextValues.push(value);
        }

        includeSelect.appendChild(option);
      });

      allPostOption = includeSelect.querySelector('option[value="all_post"]');
      if (nextValues.length === 0 && allPostOption) {
        allPostOption.selected = true;
        nextValues = ['all_post'];
      }

      if (nextValues.indexOf('all_post') !== -1) {
        Array.from(includeSelect.options).forEach(function (option) {
          option.selected = option.value === 'all_post';
        });
      }

      triggerSelectChange(includeSelect);
    }

    function syncSinglePostSamplePost(row) {
      var postTypeSelect = getRowSelect(row, 'post_type');
      var sampleSelect = getRowSelect(row, 'sample_post');
      var postType;
      var choices;
      var previousValue;

      if (!postTypeSelect || !sampleSelect) {
        return;
      }

      postType = postTypeSelect.value || 'post';
      choices = aczSamplePostChoices[postType] || {};
      previousValue = sampleSelect.value || '';
      sampleSelect.innerHTML = '';

      sampleSelect.appendChild(new Option('', ''));

      Object.keys(choices).forEach(function (value) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = choices[value];
        option.selected = previousValue === value;
        sampleSelect.appendChild(option);
      });

      triggerSelectChange(sampleSelect);
    }

    function syncArchiveIncludeTerms(row) {
      var taxonomySelect = getRowSelect(row, 'taxonomy');
      var includeSelect = getRowSelect(row, 'include_terms');
      var taxonomy;
      var choices;
      var previousValues;
      var nextValues = [];
      var allTermsOption;

      if (!taxonomySelect || !includeSelect) {
        return;
      }

      taxonomy = taxonomySelect.value || 'category';
      choices = aczArchiveTermChoices[taxonomy] || {};
      previousValues = Array.from(includeSelect.selectedOptions || []).map(function (option) {
        return option.value;
      });

      includeSelect.innerHTML = '';

      Object.keys(choices).forEach(function (value) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = choices[value];

        if (previousValues.indexOf(value) !== -1 || (previousValues.indexOf('all_terms') !== -1 && value === 'all_terms')) {
          option.selected = true;
          nextValues.push(value);
        }

        includeSelect.appendChild(option);
      });

      allTermsOption = includeSelect.querySelector('option[value="all_terms"]');
      if (nextValues.length === 0 && allTermsOption) {
        allTermsOption.selected = true;
        nextValues = ['all_terms'];
      }

      if (nextValues.indexOf('all_terms') !== -1) {
        Array.from(includeSelect.options).forEach(function (option) {
          option.selected = option.value === 'all_terms';
        });
      }

      triggerSelectChange(includeSelect);
    }

    function syncArchiveSampleTerm(row) {
      var taxonomySelect = getRowSelect(row, 'taxonomy');
      var sampleSelect = getRowSelect(row, 'sample_term');
      var taxonomy;
      var choices;
      var previousValue;

      if (!taxonomySelect || !sampleSelect) {
        return;
      }

      taxonomy = taxonomySelect.value || 'category';
      choices = aczSampleTermChoices[taxonomy] || {};
      previousValue = sampleSelect.value || '';
      sampleSelect.innerHTML = '';

      sampleSelect.appendChild(new Option('', ''));

      Object.keys(choices).forEach(function (value) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = choices[value];
        option.selected = previousValue === value;
        sampleSelect.appendChild(option);
      });

      triggerSelectChange(sampleSelect);
    }

    function syncAllIncludeFields() {
      document.querySelectorAll('.acz-options-wrap .acf-row, .acz-options-wrap .acz-repeater-row').forEach(function (row) {
        syncIncludeFields(row);
        syncSinglePostIncludePosts(row);
        syncSinglePostSamplePost(row);
        syncArchiveIncludeTerms(row);
        syncArchiveSampleTerm(row);
      });
    }

    document.body.addEventListener('change', function (event) {
      var select;
      var values;

      if (event.target && event.target.matches('.acf-field[data-name="location"] select')) {
        syncIncludeFields(getRepeaterRow(event.target));
      }

      if (event.target && event.target.matches('.acf-field[data-name="post_type"] select')) {
        syncSinglePostIncludePosts(getRepeaterRow(event.target));
        syncSinglePostSamplePost(getRepeaterRow(event.target));
      }

      if (event.target && event.target.matches('.acf-field[data-name="include_posts"] select')) {
        select = event.target;
        values = Array.from(select.selectedOptions || []).map(function (option) {
          return option.value;
        });

        if (values.indexOf('all_post') !== -1 && values.length > 1) {
          Array.from(select.options).forEach(function (option) {
            option.selected = option.value === 'all_post';
          });

          if (window.jQuery) {
            window.jQuery(select).trigger('change');
          }
        }
      }

      if (event.target && event.target.matches('.acf-field[data-name="taxonomy"] select')) {
        syncArchiveIncludeTerms(getRepeaterRow(event.target));
        syncArchiveSampleTerm(getRepeaterRow(event.target));
      }

      if (event.target && event.target.matches('.acf-field[data-name="include_terms"] select')) {
        select = event.target;
        values = Array.from(select.selectedOptions || []).map(function (option) {
          return option.value;
        });

        if (values.indexOf('all_terms') !== -1 && values.length > 1) {
          Array.from(select.options).forEach(function (option) {
            option.selected = option.value === 'all_terms';
          });

          if (window.jQuery) {
            window.jQuery(select).trigger('change');
          }
        }
      }
    });

    syncAllIncludeFields();
    scheduleOptionsReveal();

    if (window.acf && typeof window.acf.addAction === 'function') {
      window.acf.addAction('append', function ($el) {
        var root = $el && $el[0] ? $el[0] : document;
        root.querySelectorAll('.acf-row, .acz-repeater-row').forEach(function (row) {
          syncIncludeFields(row);
          syncSinglePostIncludePosts(row);
          syncSinglePostSamplePost(row);
          syncArchiveIncludeTerms(row);
          syncArchiveSampleTerm(row);
        });
      });
    }

    document.body.addEventListener('click', function (event) {
      var tableId;
      var table;
      var tbody;
      var rows;
      var newIndex;
      var firstRow;
      var newRow;
      var row;

      if (event.target.classList.contains('acz-add-row')) {
        tableId = event.target.getAttribute('data-table');
        table = document.getElementById(tableId);

        if (!table) {
          return;
        }

        tbody = table.querySelector('.acz-repeater-tbody');
        rows = tbody ? tbody.querySelectorAll('.acz-repeater-row') : [];
        newIndex = rows.length;
        firstRow = rows[0];

        if (!tbody || !firstRow) {
          return;
        }

        newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('select, input').forEach(function (element) {
          element.name = element.name.replace(/\[\d+\]/, '[' + newIndex + ']');
          element.value = element.tagName === 'SELECT' ? '0' : '';
          if (element.name.indexOf('[location]') !== -1) {
            element.value = 'entire_site';
          }
        });

        tbody.appendChild(newRow);
        syncIncludeFields(newRow);
      }

      if (event.target.classList.contains('acz-remove-row')) {
        row = event.target.closest('.acz-repeater-row');
        tbody = row ? row.closest('.acz-repeater-tbody') : null;

        if (!row || !tbody) {
          return;
        }

        if (tbody.querySelectorAll('.acz-repeater-row').length > 1) {
          row.remove();
        } else {
          row.querySelectorAll('select, input').forEach(function (element) {
            element.value = element.tagName === 'SELECT' ? '0' : '';
            if (element.name.indexOf('[location]') !== -1) {
              element.value = 'entire_site';
            }
          });
          syncIncludeFields(row);
        }
      }
    });
  });
})();
