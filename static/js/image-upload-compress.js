(function () {
    'use strict';

    var MAX_EDGE = 1920;
    var QUALITY = 0.82;
    var MAX_BYTES = 1200 * 1024;
    var MIN_COMPRESS_BYTES = 80 * 1024;

    function isImageFile(file) {
        return file && file.type && file.type.indexOf('image/') === 0;
    }

    function shouldCompress(file) {
        return isImageFile(file) && file.size >= MIN_COMPRESS_BYTES;
    }

    function loadImageFromFile(file) {
        return new Promise(function (resolve, reject) {
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                URL.revokeObjectURL(url);
                resolve(img);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('Could not read image.'));
            };
            img.src = url;
        });
    }

    function canvasToBlob(canvas, type, quality) {
        return new Promise(function (resolve) {
            canvas.toBlob(function (blob) {
                resolve(blob);
            }, type, quality);
        });
    }

    async function compressFile(file) {
        if (!shouldCompress(file)) {
            return file;
        }

        var img = await loadImageFromFile(file);
        var width = img.naturalWidth || img.width;
        var height = img.naturalHeight || img.height;
        var scale = Math.min(1, MAX_EDGE / Math.max(width, height));
        var targetW = Math.max(1, Math.round(width * scale));
        var targetH = Math.max(1, Math.round(height * scale));

        var canvas = document.createElement('canvas');
        canvas.width = targetW;
        canvas.height = targetH;

        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, targetW, targetH);

        var outputType = 'image/jpeg';
        var outputName = file.name.replace(/\.[^.]+$/, '') + '.jpg';

        if (file.type === 'image/png' && file.size < 512 * 1024) {
            outputType = 'image/png';
            outputName = file.name.replace(/\.[^.]+$/, '') + '.png';
        }

        var quality = QUALITY;
        var blob = await canvasToBlob(canvas, outputType, quality);

        if (!blob) {
            return file;
        }

        while (blob.size > MAX_BYTES && quality > 0.45) {
            quality -= 0.08;
            blob = await canvasToBlob(canvas, outputType, quality);
        }

        if (blob.size >= file.size) {
            return file;
        }

        return new File([blob], outputName, {
            type: outputType,
            lastModified: Date.now()
        });
    }

    function replaceInputFile(input, file) {
        var dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
    }

    async function compressFormFiles(form) {
        var inputs = form.querySelectorAll('input[type="file"]');
        var tasks = [];

        inputs.forEach(function (input) {
            if (!input.files || !input.files[0]) {
                return;
            }

            tasks.push(
                compressFile(input.files[0]).then(function (compressed) {
                    replaceInputFile(input, compressed);
                })
            );
        });

        await Promise.all(tasks);
    }

    async function handleSubmit(event) {
        var form = event.target;

        if (!form || !form.matches || !form.matches('form[data-compress-images]')) {
            return;
        }

        if (form.dataset.imagesCompressed === '1') {
            form.dataset.imagesCompressed = '0';
            return;
        }

        var hasFile = false;
        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            if (input.files && input.files[0]) {
                hasFile = true;
            }
        });

        if (!hasFile) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        var submitter = event.submitter;
        var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        buttons.forEach(function (btn) { btn.disabled = true; });

        try {
            await compressFormFiles(form);
            form.dataset.imagesCompressed = '1';

            if (typeof htmx !== 'undefined' && form.matches('[hx-post], [hx-put], [hx-patch]')) {
                htmx.trigger(form, 'submit');
            } else if (submitter && typeof form.requestSubmit === 'function') {
                form.requestSubmit(submitter);
            } else if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        } catch (err) {
            console.error(err);
            if (typeof OmniToast === 'function') {
                OmniToast('Could not prepare image for upload. Try a different file.', 'error');
            } else {
                alert('Could not prepare image for upload. Try a different file.');
            }
        } finally {
            buttons.forEach(function (btn) { btn.disabled = false; });
        }
    }

    function bindPreviewInputs(root) {
        (root || document).querySelectorAll('input[type="file"][data-image-preview]').forEach(function (input) {
            if (input.dataset.previewBound === '1') {
                return;
            }
            input.dataset.previewBound = '1';

            input.addEventListener('change', function () {
                var file = input.files && input.files[0];
                var selector = input.getAttribute('data-image-preview');
                var preview = selector ? document.querySelector(selector) : null;

                if (!file || !preview) {
                    return;
                }

                compressFile(file).then(function (compressed) {
                    if (compressed !== file) {
                        replaceInputFile(input, compressed);
                    }

                    var url = URL.createObjectURL(compressed);
                    preview.classList.remove('main-image-preview--empty');
                    preview.innerHTML = '<img src="' + url + '" alt="" decoding="async" onload="URL.revokeObjectURL(this.src)">';
                }).catch(function () {
                    var url = URL.createObjectURL(file);
                    preview.classList.remove('main-image-preview--empty');
                    preview.innerHTML = '<img src="' + url + '" alt="" decoding="async" onload="URL.revokeObjectURL(this.src)">';
                });
            });
        });
    }

    function bindForms(root) {
        (root || document).querySelectorAll('form[data-compress-images]').forEach(function (form) {
            if (form.dataset.compressBound === '1') {
                return;
            }
            form.dataset.compressBound = '1';
            form.addEventListener('submit', handleSubmit, true);
            bindPreviewInputs(form);
        });
    }

    window.OmniImageUpload = {
        compressFile: compressFile,
        bindForms: bindForms,
        bindPreviewInputs: bindPreviewInputs
    };

    document.addEventListener('DOMContentLoaded', function () {
        bindForms(document);
        bindPreviewInputs(document);
    });

    document.body.addEventListener('htmx:afterSwap', function (evt) {
        if (evt.detail.target) {
            bindForms(evt.detail.target);
            bindPreviewInputs(evt.detail.target);
        }
    });
})();
