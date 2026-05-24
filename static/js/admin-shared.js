(function () {
    'use strict';

    var STAGGER_STEP = 0.025;
    var STAGGER_MAX = 0.35;

    function initPage(root) {
        root = root || document.getElementById('admin-content');
        if (!root) return;

        var container = root.querySelector('.container');
        if (container) {
            container.classList.remove('admin-page-enter');
            void container.offsetWidth;
            container.classList.add('admin-page-enter');
        }

        root.querySelectorAll('tbody tr').forEach(function (row, index) {
            row.classList.remove('admin-stagger-row');
            row.style.animationDelay = '';
            void row.offsetWidth;
            row.classList.add('admin-stagger-row');
            row.style.animationDelay = Math.min(index * STAGGER_STEP, STAGGER_MAX) + 's';
        });

        root.querySelectorAll('.prod-thumb--has-img:not(.skeleton) img').forEach(function (img) {
            if (!img.complete) {
                img.closest('.prod-thumb').classList.add('skeleton');
            }
        });

        initImagesLazyGrid(root);
        initInstantSearch(root);
        initPackingChecks(root);
    }

    function initPackingChecks(root) {
        root = root || document;
        var scope = root.querySelector('[data-packing-storage-key]') || root;
        var prefix = scope.getAttribute && scope.getAttribute('data-packing-storage-key');

        if (!prefix) {
            prefix = root.querySelector('[data-packing-storage-key]');
            prefix = prefix ? prefix.getAttribute('data-packing-storage-key') : null;
        }

        if (!prefix) {
            return;
        }

        scope.querySelectorAll('.pack-check__input[data-check-key]').forEach(function (input) {
            if (input.dataset.packingBound === '1') {
                return;
            }

            input.dataset.packingBound = '1';
            var storageKey = prefix + ':' + input.getAttribute('data-check-key');

            try {
                input.checked = localStorage.getItem(storageKey) === '1';
            } catch (e) {
                input.checked = false;
            }

            input.addEventListener('change', function () {
                try {
                    if (input.checked) {
                        localStorage.setItem(storageKey, '1');
                    } else {
                        localStorage.removeItem(storageKey);
                    }
                } catch (err) {
                    /* ignore quota / private mode */
                }
            });
        });
    }

    function adminNormalize(str) {
        return String(str || '').toLowerCase().trim().replace(/\s+/g, ' ');
    }

    function adminFuzzyMatch(haystack, needle) {
        haystack = adminNormalize(haystack);
        needle = adminNormalize(needle);
        if (!needle) return true;
        if (haystack.indexOf(needle) !== -1) return true;
        var i = 0;
        for (var j = 0; j < haystack.length && i < needle.length; j++) {
            if (haystack.charAt(j) === needle.charAt(i)) i++;
        }
        return i === needle.length;
    }

    function adminFieldMatchScore(haystack, term, weight) {
        haystack = adminNormalize(haystack);
        term = adminNormalize(term);
        if (!term || !haystack) return 0;
        if (haystack.indexOf(term) === 0) return (weight * 1000) + Math.max(0, 50 - term.length);
        if (haystack.indexOf(term) !== -1) return (weight * 500) + Math.max(0, 50 - term.length);
        if (adminFuzzyMatch(haystack, term)) return weight * 100;
        return 0;
    }

    function adminProductMatchScore(record, query) {
        query = adminNormalize(query);
        if (!query) return Number.MAX_SAFE_INTEGER;
        var terms = query.split(/\s+/).filter(Boolean);
        var total = 0;
        for (var t = 0; t < terms.length; t++) {
            var term = terms[t];
            var codeScore = adminFieldMatchScore(record.code || '', term, 10);
            var nameScore = adminFieldMatchScore(record.name || '', term, 3);
            var termScore = Math.max(codeScore, nameScore);
            if (!termScore) return 0;
            total += termScore;
        }
        return total;
    }

    function adminMatchesProduct(record, query) {
        return adminProductMatchScore(record, query) > 0;
    }

    function adminRecordFuzzyMatch(record, fields, query) {
        query = adminNormalize(query);
        if (!query) return true;
        var terms = query.split(/\s+/).filter(Boolean);
        var haystacks = fields.map(function (field) {
            return String(record[field] || '');
        });
        var combined = adminNormalize(haystacks.join(' '));
        for (var t = 0; t < terms.length; t++) {
            var term = terms[t];
            var matched = haystacks.some(function (h) {
                return adminFuzzyMatch(h, term);
            });
            if (!matched && !adminFuzzyMatch(combined, term)) return false;
        }
        return true;
    }

    function initInstantSearch(root) {
        root = root || document;
        root.querySelectorAll('form[data-admin-instant-search]').forEach(function (form) {
            if (form._instantSearchInit) return;

            var debounce = null;
            var lastSearchValue = null;

            function submitInstantSearch() {
                if (typeof htmx === 'undefined') {
                    form.submit();
                    return;
                }
                htmx.trigger(form, 'submit');
            }

            var searchInput = form.querySelector('input[name="search"]');
            if (searchInput) {
                lastSearchValue = searchInput.value;
                searchInput.addEventListener('input', function () {
                    if (searchInput.value === lastSearchValue) return;
                    lastSearchValue = searchInput.value;
                    clearTimeout(debounce);
                    debounce = setTimeout(submitInstantSearch, 250);
                });
            }

            form.querySelectorAll('select').forEach(function (select) {
                select.addEventListener('change', submitInstantSearch);
            });

            form._instantSearchInit = true;
        });
    }

    function setLoading(isLoading) {
        var content = document.getElementById('admin-content');
        var bar = document.getElementById('loading-bar');
        if (content) {
            content.classList.toggle('admin-content-loading', isLoading);
        }
        if (bar) {
            bar.classList.toggle('is-active', isLoading);
        }
    }

    function updateActiveNav() {
        var path = window.location.pathname;
        document.querySelectorAll('.nav a').forEach(function (link) {
            var href = link.getAttribute('href');
            var isActive = href === path
                || ((path === '/admin' || path === '/admin/') && href === '/admin/orders');
            link.classList.toggle('active', isActive);
        });
    }

    function initSortable(root) {
        if (typeof Sortable === 'undefined') return;
        (root || document).querySelectorAll('[data-sortable]').forEach(function (el) {
            if (el._sortable) return;
            el._sortable = Sortable.create(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                handle: '.drag-handle',
                onEnd: function (evt) {
                    el.dispatchEvent(new CustomEvent('sort-end', {
                        detail: { oldIndex: evt.oldIndex, newIndex: evt.newIndex }
                    }));
                }
            });
        });
    }

    function adminEsc(str) {
        if (str == null) return '';
        var div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function readAdminJsonEl(el) {
        if (!el) {
            return null;
        }
        var raw = typeof el.value === 'string' ? el.value : el.textContent;
        raw = (raw || '').trim();
        if (!raw) {
            return null;
        }
        return JSON.parse(raw);
    }

    function imageSetForCode(images, code) {
        if (!code) {
            return null;
        }
        return images[code] || images[String(code).toUpperCase()] || images[String(code).toLowerCase()] || null;
    }

    function initImagesLazyGrid(root) {
        root = root || document;
        var grid = root.querySelector('#product-grid[data-lazy-images]');
        if (!grid) return;

        var productsEl = root.querySelector('#admin-images-products');
        var imagesEl = root.querySelector('#admin-images-map');
        var versionsEl = root.querySelector('#admin-images-versions');
        if (!productsEl || !imagesEl) return;

        var products = [];
        var images = {};
        var imageVersions = {};
        try {
            products = readAdminJsonEl(productsEl) || [];
            images = readAdminJsonEl(imagesEl) || {};
            imageVersions = versionsEl ? (readAdminJsonEl(versionsEl) || {}) : {};
        } catch (e) {
            return;
        }

        if (grid._imagesLazyInit) {
            grid._imagesLazyInit.destroy();
        }

        var CARD_BATCH = 24;
        var visibleCount = CARD_BATCH;
        var renderedCount = 0;
        var gridObserver = null;
        var searchDebounce = null;
        var currentStatus = 'all';

        var searchInput = root.querySelector('#p-search');
        var catSelect = root.querySelector('#p-cat');
        var countEl = root.querySelector('#showing-count');
        var statusBtns = root.querySelectorAll('.filter-btns .fbtn[data-status]');
        var placeholderImage = grid.getAttribute('data-placeholder-image') || '/static/images/omnispace-logo.jpg';

        function hasImage(code) {
            return !!imageSetForCode(images, code);
        }

        function getColorName(product, colorId) {
            if (colorId === 'default') return 'Default';
            var colors = product.colors || [];
            for (var i = 0; i < colors.length; i++) {
                if (String(colors[i].id) === String(colorId)) {
                    return colors[i].name;
                }
            }
            return colorId;
        }

        function getFiltered() {
            var query = searchInput ? searchInput.value.toLowerCase().trim() : '';
            var cat = catSelect ? catSelect.value : '';
            var filtered = products.filter(function (p) {
                var uploaded = hasImage(p.code);
                var status = uploaded ? 'uploaded' : 'missing';
                var matchesSearch = !query || adminMatchesProduct(p, query);
                var matchesCat = !cat || String(p.category_id) === String(cat);
                var matchesStatus = currentStatus === 'all' || status === currentStatus;
                return matchesSearch && matchesCat && matchesStatus;
            });
            if (query) {
                filtered.sort(function (a, b) {
                    return adminProductMatchScore(b, query) - adminProductMatchScore(a, query);
                });
            }
            return filtered;
        }

        function buildSentinelHtml(filteredLength, shown) {
            if (shown < filteredLength) {
                return '<div id="imagesGridSentinel" class="grid-sentinel" aria-hidden="true"><div class="grid-sentinel__spinner"></div></div>';
            }
            if (filteredLength > CARD_BATCH) {
                return '<div class="grid-end-note">All ' + filteredLength + ' products loaded</div>';
            }
            return '';
        }

        function finalizeCardImages() {
            grid.querySelectorAll('.img-wrap.skeleton .main-img').forEach(function (img) {
                if (img.complete && img.naturalWidth > 0) {
                    img.parentElement.classList.remove('skeleton');
                }
            });
        }

        function productImgUrl(file, preferThumb) {
            if (!file) {
                return placeholderImage;
            }

            var stem = file.replace(/^thumb_/, '').replace(/\.[^.]+$/, '');
            var thumbFile = 'thumb_' + stem + '.webp';
            var displayFile = file;

            if (preferThumb !== false && imageVersions[thumbFile]) {
                displayFile = thumbFile;
            }

            var url = '/static/images/products/' + displayFile;
            var version = imageVersions[displayFile] || imageVersions[file] || '';

            return version ? url + '?v=' + version : url;
        }

        function buildCardHtml(p, index) {
            var code = p.code || '';
            var productImages = imageSetForCode(images, code);
            var uploaded = !!productImages;
            var delay = (index % CARD_BATCH) * 0.035;
            var html = '<div class="img-card card-enter" id="card-' + adminEsc(p.id) + '" style="animation-delay:' + delay.toFixed(3) + 's"'
                + (uploaded ? ' data-active-color="default"' : '') + '>';

            html += '<div class="img-wrap' + (uploaded ? ' skeleton' : '') + '">';
            if (uploaded) {
                var defaultFile = productImages.default || productImages[Object.keys(productImages)[0]];
                var loadingAttr = index < 6 ? 'loading="eager"' : 'loading="lazy" decoding="async"';
                html += '<img src="' + adminEsc(productImgUrl(defaultFile)) + '" alt="" class="main-img" ' + loadingAttr
                    + ' onload="this.parentElement.classList.remove(\'skeleton\')"'
                    + ' onerror="this.parentElement.classList.remove(\'skeleton\');this.style.display=\'none\'">';
                html += '<span class="badge-uploaded">✓ Uploaded</span>';
            } else {
                html += '<img src="' + adminEsc(placeholderImage) + '" alt="" class="main-img" loading="lazy" decoding="async">';
            }
            html += '</div>';

            html += '<div class="img-body">';
            html += '<div style="display:flex;justify-content:space-between;align-items:flex-start">';
            html += '<div><div class="p-code">' + adminEsc(code) + '</div><div class="p-name">' + adminEsc(p.name) + '</div></div>';
            html += '<button type="button" class="fbtn" style="padding:4px 8px;font-size:10px" data-edit-id="' + adminEsc(p.id) + '">✏️ Edit</button>';
            html += '</div>';

            html += '<div style="margin-top:10px;display:flex;gap:6px;flex-wrap:wrap">';
            if (uploaded) {
                Object.keys(productImages).forEach(function (cid) {
                    var fname = productImages[cid];
                    var isDefault = cid === 'default';
                    var label = isDefault ? 'D' : adminEsc(cid);
                    var colorName = getColorName(p, cid);
                    html += '<div class="thumb' + (isDefault ? ' active' : '') + '" title="' + adminEsc(colorName) + '"'
                        + ' data-src="' + adminEsc(productImgUrl(fname)) + '"'
                        + ' data-color="' + adminEsc(colorName) + '"'
                        + ' data-color-id="' + adminEsc(cid) + '"'
                        + ' data-file="' + adminEsc(fname) + '"'
                        + ' style="width:24px;height:24px;border-radius:4px;border:1px solid #ddd;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;cursor:pointer;background:#fff">'
                        + label + '</div>';
                });
            }
            html += '</div>';

            var defaultFileName = uploaded && productImages.default ? productImages.default : '';
            html += '<div class="p-meta" style="margin-top:12px;border-top:1px solid #f0f0f0;padding-top:8px">';
            html += '<span class="current-color-label">Default</span>';
            html += '<span style="color:#aaa" class="current-file-label">' + (defaultFileName ? 'File: ' + adminEsc(defaultFileName) : '') + '</span>';
            if (uploaded) {
                html += '<button type="button" class="fbtn img-delete-btn" data-delete-card'
                    + ' style="padding:3px 8px;font-size:10px;color:#dc2626;border-color:#fecaca">🗑 Remove</button>';
            }
            html += '</div>';
            html += '</div></div>';
            return html;
        }

        function disconnectGridObserver() {
            if (gridObserver) {
                gridObserver.disconnect();
                gridObserver = null;
            }
        }

        function observeGridSentinel() {
            disconnectGridObserver();
            var sentinel = grid.querySelector('#imagesGridSentinel');
            if (!sentinel) return;

            gridObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var filtered = getFiltered();
                    if (visibleCount >= filtered.length) return;
                    visibleCount = Math.min(visibleCount + CARD_BATCH, filtered.length);
                    renderGrid(false);
                });
            }, { root: null, rootMargin: '240px 0px', threshold: 0.01 });

            gridObserver.observe(sentinel);
        }

        function renderGrid(isRefresh) {
            var filtered = getFiltered();
            var shown = Math.min(visibleCount, filtered.length);

            if (!countEl) return;

            if (filtered.length === 0) {
                countEl.textContent = 'Showing 0 products';
                grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#aaa;"><p style="font-size:48px;margin-bottom:12px;">🔍</p><p>No products match your filters</p></div>';
                renderedCount = 0;
                disconnectGridObserver();
                return;
            }

            countEl.textContent = 'Showing ' + shown + ' of ' + filtered.length + ' products'
                + (shown < filtered.length ? ' — scroll for more' : '');

            if (isRefresh) {
                renderedCount = 0;
                grid.classList.remove('grid-refresh');
                void grid.offsetWidth;
                grid.classList.add('grid-refresh');
                grid.style.opacity = '1';
                var html = '';
                for (var i = 0; i < shown; i++) {
                    html += buildCardHtml(filtered[i], i);
                }
                html += buildSentinelHtml(filtered.length, shown);
                grid.innerHTML = html;
                renderedCount = shown;
                observeGridSentinel();
                finalizeCardImages();
                return;
            }

            if (shown <= renderedCount) return;

            var sentinel = grid.querySelector('#imagesGridSentinel');
            var endNote = grid.querySelector('.grid-end-note');
            if (sentinel) sentinel.remove();
            if (endNote) endNote.remove();

            var batchHtml = '';
            for (var j = renderedCount; j < shown; j++) {
                batchHtml += buildCardHtml(filtered[j], j - renderedCount);
            }
            grid.insertAdjacentHTML('beforeend', batchHtml);
            grid.insertAdjacentHTML('beforeend', buildSentinelHtml(filtered.length, shown));
            renderedCount = shown;
            observeGridSentinel();
            finalizeCardImages();
        }

        function resetGrid() {
            visibleCount = CARD_BATCH;
            renderedCount = 0;
            renderGrid(true);
        }

        function deleteImageFromCard(card) {
            var codeEl = card.querySelector('.p-code');
            if (!codeEl) return;
            var code = codeEl.textContent.trim();
            var colorId = card.dataset.activeColor || 'default';
            var colorLabel = card.querySelector('.current-color-label');
            var variantLabel = colorLabel ? colorLabel.textContent.trim() : 'Default';

            OmniConfirm({
                title: 'Remove image?',
                text: 'Delete the ' + variantLabel + ' image for ' + code + '? This cannot be undone.',
                danger: true,
                confirm: 'Remove'
            }).then(function (result) {
                if (!result.isConfirmed) return;

                fetch('/admin/images/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ product_code: code, color_id: colorId })
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (res) {
                        if (!res.ok) {
                            OmniToast(res.data.error || 'Delete failed', 'error');
                            return;
                        }

                        var codeKey = code.toUpperCase();
                        if (images[codeKey]) {
                            delete images[codeKey][colorId];
                            if (Object.keys(images[codeKey]).length === 0) {
                                delete images[codeKey];
                            }
                        }

                        resetGrid();
                        OmniToast('Image removed', 'success');
                    })
                    .catch(function () {
                        OmniToast('Delete failed', 'error');
                    });
            });
        }

        function onGridClick(evt) {
            var deleteBtn = evt.target.closest('[data-delete-card]');
            if (deleteBtn) {
                var card = deleteBtn.closest('.img-card');
                if (card) deleteImageFromCard(card);
                return;
            }
            var editBtn = evt.target.closest('[data-edit-id]');
            if (editBtn) {
                AdminImages.editProductImage(editBtn.getAttribute('data-edit-id'));
                return;
            }
            var thumb = evt.target.closest('.thumb[data-src]');
            if (thumb) {
                AdminImages.switchCardImg(thumb);
            }
        }

        function onSearchInput() {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(resetGrid, 180);
        }

        function onStatusClick(evt) {
            var btn = evt.target.closest('.fbtn[data-status]');
            if (!btn) return;
            currentStatus = btn.getAttribute('data-status');
            statusBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            resetGrid();
        }

        grid.addEventListener('click', onGridClick);
        if (searchInput) searchInput.addEventListener('input', onSearchInput);
        if (catSelect) catSelect.addEventListener('change', resetGrid);
        statusBtns.forEach(function (btn) { btn.addEventListener('click', onStatusClick); });

        grid._imagesLazyInit = {
            destroy: function () {
                disconnectGridObserver();
                clearTimeout(searchDebounce);
                grid.removeEventListener('click', onGridClick);
                if (searchInput) searchInput.removeEventListener('input', onSearchInput);
                if (catSelect) catSelect.removeEventListener('change', resetGrid);
                statusBtns.forEach(function (btn) { btn.removeEventListener('click', onStatusClick); });
                delete grid._imagesLazyInit;
            }
        };

        resetGrid();
    }

    window.AdminImages = {
        editProductImage: function (id) {
            var select = document.getElementById('product_id');
            if (!select) return;
            select.value = id;
            AdminImages.updateColors();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        switchCardImg: function (thumb) {
            var card = thumb.closest('.img-card');
            if (!card) return;
            var wrap = card.querySelector('.img-wrap');
            var img = card.querySelector('.main-img');
            if (!wrap || !img) return;
            wrap.classList.add('skeleton');
            img.onload = function () { wrap.classList.remove('skeleton'); };
            img.src = thumb.getAttribute('data-src');
            var colorLabel = card.querySelector('.current-color-label');
            var fileLabel = card.querySelector('.current-file-label');
            if (colorLabel) colorLabel.textContent = thumb.getAttribute('data-color') || 'Default';
            if (fileLabel) fileLabel.textContent = 'File: ' + (thumb.getAttribute('data-file') || '');
            card.dataset.activeColor = thumb.getAttribute('data-color-id') || 'default';
            card.querySelectorAll('.thumb').forEach(function (t) { t.classList.remove('active'); });
            thumb.classList.add('active');
        },
        updateColors: function () {
            var select = document.getElementById('product_id');
            var colorSelect = document.getElementById('color_id');
            if (!select || !colorSelect) return;
            var option = select.options[select.selectedIndex];
            colorSelect.innerHTML = '<option value="default">Default (all colours)</option>';
            if (option.value) {
                var colors = JSON.parse(option.getAttribute('data-colors') || '[]');
                colors.forEach(function (c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    colorSelect.appendChild(opt);
                });
            }
        },
        updateFileName: function (input) {
            var name = input.files[0] ? input.files[0].name : 'No file chosen';
            var fileNameEl = document.getElementById('file-name');
            var fileLabelEl = document.getElementById('file-label');
            if (fileNameEl) fileNameEl.textContent = name;
            if (fileLabelEl) fileLabelEl.textContent = input.files[0] ? '📁 Change file' : '📁 Choose image file';
        }
    };

    window.OmniToast = function (msg, type) {
        if (typeof Swal === 'undefined') {
            alert(msg);
            return;
        }
        Swal.mixin({
            toast: true,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: function (toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        }).fire({
            icon: type === 'error' ? 'error' : 'success',
            title: msg
        });
    };

    window.OmniConfirm = function (opts) {
        if (typeof Swal === 'undefined') {
            return Promise.resolve({
                isConfirmed: confirm((opts.title || '') + '\n\n' + (opts.text || ''))
            });
        }
        return Swal.fire({
            title: opts.title || 'Are you sure?',
            text: opts.text || '',
            icon: opts.icon || 'warning',
            showCancelButton: true,
            confirmButtonColor: opts.danger ? '#dc2626' : '#0A9696',
            confirmButtonText: opts.confirm || 'Yes',
            cancelButtonText: opts.cancel || 'Cancel'
        });
    };

    function scrollAdminToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    window.AdminUI = {
        initPage: initPage,
        initSortable: initSortable,
        scrollToTop: scrollAdminToTop
    };

    document.body.addEventListener('htmx:beforeRequest', function (evt) {
        if (evt.detail.target && evt.detail.target.id === 'admin-content') {
            setLoading(true);
        }
    });

    document.body.addEventListener('htmx:afterSwap', function (evt) {
        if (evt.detail.target && evt.detail.target.id === 'admin-content') {
            setLoading(false);
            updateActiveNav();
            initPage(evt.detail.target);
            initSortable(evt.detail.target);
            var fromInstantSearch = evt.detail.requestConfig
                && evt.detail.requestConfig.elt
                && evt.detail.requestConfig.elt.closest
                && evt.detail.requestConfig.elt.closest('[data-admin-instant-search]');
            if (!fromInstantSearch) {
                scrollAdminToTop();
            }
        }
    });

    document.body.addEventListener('htmx:responseError', function () {
        setLoading(false);
    });

    document.addEventListener('DOMContentLoaded', function () {
        initPage();
        initSortable();
        updateActiveNav();
    });
})();
