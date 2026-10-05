(function() {
    'use strict';

    function reindexContainer(container, arrayName) {
        var items = container.querySelectorAll('.repeater-item');
        items.forEach(function(item, idx) {
            item.setAttribute('data-index', idx);
            var num = item.querySelector('.item-number');
            if (num) {
                num.textContent = idx + 1;
            }
            var titleSpan = item.querySelector('.item-title-preview');
            if (titleSpan) {
                var input = item.querySelector('input[type="text"]');
                if (input && input.value) {
                    titleSpan.textContent = ': ' + input.value;
                }
            }
            item.querySelectorAll('input, select, textarea').forEach(function(input) {
                var name = input.getAttribute('name');
                if (name) {
                    var regex = new RegExp(arrayName + '\\[\\d+\\]');
                    input.setAttribute('name', name.replace(regex, arrayName + '[' + idx + ']'));
                }
            });
        });
    }

    function resolveAdminImgSrc(path) {
        if (!path) return '';
        var clean = path.trim();
        if (clean.indexOf('http://') === 0 || clean.indexOf('https://') === 0 || clean.indexOf('data:') === 0 || clean.indexOf('blob:') === 0) {
            return clean;
        }
        if (clean.indexOf('../') === 0) {
            return clean;
        }
        return '../' + clean.replace(/^\/+/, '');
    }

    function initImagePickers() {
        document.querySelectorAll('.image-picker-field').forEach(function(field) {
            if (field.dataset.initialized) return;
            field.dataset.initialized = 'true';

            var pathInput = field.querySelector('.image-path-input');
            var fileInput = field.querySelector('.image-file-input');
            var uploadBtn = field.querySelector('.image-upload-btn');
            var clearBtn = field.querySelector('.btn-clear-image');
            var previewImg = field.querySelector('.image-preview-thumb img');
            var previewEmpty = field.querySelector('.image-preview-empty');
            var statusBadge = field.querySelector('.image-status-badge');
            var folder = field.getAttribute('data-folder') || 'directivos';

            function updatePreview(src) {
                if (!previewImg) return;
                var clean = (src || '').trim();
                if (!clean) {
                    previewImg.style.display = 'none';
                    if (previewEmpty) previewEmpty.style.display = 'flex';
                    return;
                }
                previewImg.src = resolveAdminImgSrc(clean);
                previewImg.style.display = 'block';
                if (previewEmpty) previewEmpty.style.display = 'none';
                previewImg.onerror = function() {
                    if (folder === 'servicios') {
                        previewImg.style.display = 'none';
                        if (previewEmpty) previewEmpty.style.display = 'flex';
                    } else {
                        previewImg.src = '../images/directivos/sin-foto.jpg';
                    }
                };
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (pathInput) {
                        pathInput.value = '';
                        updatePreview('');
                    }
                    if (fileInput) {
                        fileInput.value = '';
                    }
                    if (statusBadge) {
                        statusBadge.className = 'image-status-badge';
                        statusBadge.textContent = 'Foto quitada';
                    }
                });
            }

            if (pathInput) {
                updatePreview(pathInput.value);
                pathInput.addEventListener('input', function() {
                    updatePreview(this.value);
                });
            }

            if (uploadBtn && fileInput) {
                uploadBtn.addEventListener('click', function() {
                    fileInput.click();
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', function() {
                    var file = this.files && this.files[0];
                    if (!file) return;

                    // 1. Validar Peso Máximo (2 MB)
                    var maxBytes = 2 * 1024 * 1024;
                    if (file.size > maxBytes) {
                        if (statusBadge) {
                            statusBadge.className = 'image-status-badge error';
                            statusBadge.textContent = '⚠ El archivo supera los 2 MB (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB). Reducí su peso antes de subir.';
                        }
                        this.value = '';
                        return;
                    }

                    // 2. Validar Formato (JPG, PNG, WebP)
                    var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    if (allowedTypes.indexOf(file.type) === -1) {
                        if (statusBadge) {
                            statusBadge.className = 'image-status-badge error';
                            statusBadge.textContent = '⚠ Formato no válido. Solo se admiten JPG, PNG o WebP.';
                        }
                        this.value = '';
                        return;
                    }

                    // 3. Previsualización instantánea local
                    var objectUrl = URL.createObjectURL(file);
                    updatePreview(objectUrl);

                    // 4. Leer dimensiones reales
                    var imgTest = new Image();
                    imgTest.src = objectUrl;
                    imgTest.onload = function() {
                        var w = this.naturalWidth;
                        var h = this.naturalHeight;
                        var kb = (file.size / 1024).toFixed(0);

                        if (statusBadge) {
                            statusBadge.className = 'image-status-badge loading';
                            statusBadge.textContent = 'Subiendo ' + w + '×' + h + ' px (' + kb + ' KB)...';
                        }

                        // 5. Subida Asíncrona a upload.php
                        var csrfToken = document.querySelector('input[name="csrf_token"]');
                        var formData = new FormData();
                        formData.append('image', file);
                        formData.append('folder', folder);
                        if (csrfToken) {
                            formData.append('csrf_token', csrfToken.value);
                        }

                        fetch('upload.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(function(res) {
                            return res.json().then(function(json) {
                                return { ok: res.ok, data: json };
                            });
                        })
                        .then(function(result) {
                            if (result.ok && result.data.success) {
                                if (pathInput) {
                                    pathInput.value = result.data.path;
                                    updatePreview(result.data.path);
                                }
                                if (statusBadge) {
                                    statusBadge.className = 'image-status-badge';
                                    statusBadge.textContent = '✓ ' + result.data.width + '×' + result.data.height + ' px · ' + (result.data.size / 1024).toFixed(0) + ' KB subida exitosa';
                                }
                            } else {
                                var err = (result.data && result.data.error) ? result.data.error : 'Error al procesar la imagen.';
                                if (statusBadge) {
                                    statusBadge.className = 'image-status-badge error';
                                    statusBadge.textContent = '⚠ ' + err;
                                }
                            }
                        })
                        .catch(function(e) {
                            if (statusBadge) {
                                statusBadge.className = 'image-status-badge error';
                                statusBadge.textContent = '⚠ Error de red al subir la imagen.';
                            }
                        });
                    };
                });
            }
        });
    }

    function initRepeaters() {
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;

            // Remove button
            if (target.matches('.btn-remove') || target.closest('.btn-remove')) {
                var btn = target.matches('.btn-remove') ? target : target.closest('.btn-remove');
                var container = btn.closest('.repeater-list');
                if (!container) return;

                var items = container.querySelectorAll('.repeater-item');
                if (items.length <= 1) {
                    alert('Debe haber al menos un elemento en la lista.');
                    return;
                }

                var item = btn.closest('.repeater-item');
                if (item) {
                    var arrayName = container.getAttribute('data-array-name') || 'items';
                    item.remove();
                    reindexContainer(container, arrayName);
                }
            }

            // Add button
            if (target.matches('.btn-add') || target.closest('.btn-add')) {
                var addBtn = target.matches('.btn-add') ? target : target.closest('.btn-add');
                var templateId = addBtn.getAttribute('data-template');
                var targetContainerId = addBtn.getAttribute('data-target');

                if (templateId && targetContainerId) {
                    var template = document.getElementById(templateId);
                    var list = document.getElementById(targetContainerId);
                    if (template && list) {
                        var count = list.querySelectorAll('.repeater-item').length;
                        var arrayName = list.getAttribute('data-array-name') || 'items';
                        var clone = template.content.cloneNode(true);
                        var tempDiv = document.createElement('div');
                        tempDiv.appendChild(clone);
                        
                        var html = tempDiv.innerHTML.replace(/__INDEX__/g, count).replace(/__NUMBER__/g, count + 1);
                        list.insertAdjacentHTML('beforeend', html);
                        reindexContainer(list, arrayName);
                        initImagePickers();
                    }
                }
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target && e.target.matches('input[name$="[title]"], input[name$="[name]"]')) {
                var item = e.target.closest('.repeater-item');
                if (item) {
                    var titleSpan = item.querySelector('.item-title-preview');
                    if (titleSpan) {
                        titleSpan.textContent = e.target.value ? ': ' + e.target.value : '';
                    }
                }
            }
        });
    }

    function initIconPickers() {
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;

            // Botón para abrir/cerrar catálogo de iconos
            var toggleBtn = target.closest('.btn-toggle-icon-palette');
            if (toggleBtn) {
                var field = toggleBtn.closest('.icon-picker-field');
                if (!field) return;
                var palette = field.querySelector('.icon-picker-palette');
                if (!palette) return;

                var isVisible = palette.style.display !== 'none';
                document.querySelectorAll('.icon-picker-palette').forEach(function(p) {
                    if (p !== palette) p.style.display = 'none';
                });
                palette.style.display = isVisible ? 'none' : 'block';
                return;
            }

            // Botón para cerrar paleta (cruz)
            var closeBtn = target.closest('.btn-close-palette');
            if (closeBtn) {
                var paletteToClose = closeBtn.closest('.icon-picker-palette');
                if (paletteToClose) paletteToClose.style.display = 'none';
                return;
            }

            // Clic en una opción de icono
            var optionBtn = target.closest('.icon-option');
            if (optionBtn) {
                var iconField = optionBtn.closest('.icon-picker-field');
                if (!iconField) return;

                var iconClass = optionBtn.getAttribute('data-icon');
                var iconName = optionBtn.getAttribute('data-name');
                var input = iconField.querySelector('.icon-picker-input');
                var previewIcon = iconField.querySelector('.icon-preview-box i');
                var previewName = iconField.querySelector('.icon-preview-name');
                var previewCode = iconField.querySelector('.icon-preview-code');
                var paletteContainer = iconField.querySelector('.icon-picker-palette');

                if (input) {
                    input.value = iconClass;
                }
                if (previewIcon) {
                    previewIcon.className = 'fa ' + iconClass;
                }
                if (previewName) {
                    previewName.textContent = iconName;
                }
                if (previewCode) {
                    previewCode.textContent = iconClass;
                }

                iconField.querySelectorAll('.icon-option').forEach(function(btn) {
                    btn.classList.remove('is-selected');
                });
                optionBtn.classList.add('is-selected');

                if (paletteContainer) {
                    paletteContainer.style.display = 'none';
                }
                return;
            }

            // Clic fuera cierra las paletas abiertas
            if (!target.closest('.icon-picker-field')) {
                document.querySelectorAll('.icon-picker-palette').forEach(function(p) {
                    p.style.display = 'none';
                });
            }
        });
    }

    function init() {
        initRepeaters();
        initImagePickers();
        initIconPickers();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
