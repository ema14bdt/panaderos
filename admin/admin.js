(function() {
    'use strict';

    function triggerChange(el) {
        if (!el) return;
        try {
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        } catch(e) {}
    }

    function reindexContainer(container, arrayName) {
        var items = container.querySelectorAll('.repeater-item');
        var total = items.length;
        items.forEach(function(item, idx) {
            item.setAttribute('data-index', idx);
            var num = item.querySelector('.item-number');
            if (num) {
                num.textContent = idx + 1;
            }
            var titleSpan = item.querySelector('.item-title-preview');
            if (titleSpan) {
                var input = item.querySelector('input[name$="[title]"], input[name$="[name]"], input[type="text"]');
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

            var upBtn = item.querySelector('.btn-move-up');
            var downBtn = item.querySelector('.btn-move-down');
            if (upBtn) {
                upBtn.disabled = (idx === 0);
            }
            if (downBtn) {
                downBtn.disabled = (idx === total - 1);
            }
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
                        triggerChange(pathInput);
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
                                    triggerChange(pathInput);
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

    function initVideoPickers() {
        document.querySelectorAll('.video-picker-field').forEach(function(field) {
            if (field.dataset.initialized) return;
            field.dataset.initialized = 'true';

            var pathInput = field.querySelector('.video-path-input');
            var posterInput = field.querySelector('.video-poster-input');
            var fileInput = field.querySelector('.video-file-input');
            var uploadBtn = field.querySelector('.video-upload-btn');
            var clearBtn = field.querySelector('.btn-clear-video');
            var previewVideo = field.querySelector('.video-preview-thumb video');
            var previewEmpty = field.querySelector('.video-preview-empty');
            var statusBadge = field.querySelector('.video-status-badge');

            function updatePreview(videoSrc, posterSrc) {
                if (!previewVideo) return;
                var cleanVideo = (videoSrc || '').trim();
                if (!cleanVideo) {
                    previewVideo.style.display = 'none';
                    previewVideo.src = '';
                    if (previewEmpty) previewEmpty.style.display = 'flex';
                    return;
                }
                var resolvedVideo = cleanVideo.indexOf('http') === 0 ? cleanVideo : '../' + cleanVideo.replace(/^\/+/, '');
                previewVideo.src = resolvedVideo;
                if (posterSrc) {
                    var resolvedPoster = posterSrc.indexOf('http') === 0 ? posterSrc : '../' + posterSrc.replace(/^\/+/, '');
                    previewVideo.poster = resolvedPoster;
                }
                previewVideo.style.display = 'block';
                if (previewEmpty) previewEmpty.style.display = 'none';
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (pathInput) pathInput.value = '';
                    if (posterInput) posterInput.value = '';
                    if (fileInput) fileInput.value = '';
                    updatePreview('', '');
                    triggerChange(pathInput);
                    if (statusBadge) {
                        statusBadge.className = 'video-status-badge';
                        statusBadge.textContent = 'Video quitado';
                    }
                });
            }

            if (pathInput) {
                updatePreview(pathInput.value, posterInput ? posterInput.value : '');
                pathInput.addEventListener('input', function() {
                    updatePreview(this.value, posterInput ? posterInput.value : '');
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

                    var maxBytes = 40 * 1024 * 1024;
                    if (file.size > maxBytes) {
                        if (statusBadge) {
                            statusBadge.className = 'video-status-badge error';
                            statusBadge.textContent = '⚠ El video supera los 40 MB permitidos (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB).';
                        }
                        this.value = '';
                        return;
                    }

                    var tempVideo = document.createElement('video');
                    tempVideo.preload = 'metadata';
                    var objectUrl = URL.createObjectURL(file);
                    tempVideo.src = objectUrl;

                    if (statusBadge) {
                        statusBadge.className = 'video-status-badge loading';
                        statusBadge.textContent = 'Analizando video...';
                    }

                    tempVideo.onloadedmetadata = function() {
                        URL.revokeObjectURL(tempVideo.src);
                        var dur = tempVideo.duration;
                        if (dur > 62) {
                            if (statusBadge) {
                                statusBadge.className = 'video-status-badge error';
                                statusBadge.textContent = '⚠ El video dura ' + Math.round(dur) + ' seg. El límite máximo para reels es de 60 segundos.';
                            }
                            fileInput.value = '';
                            return;
                        }

                        if (statusBadge) {
                            statusBadge.className = 'video-status-badge loading';
                            statusBadge.textContent = 'Subiendo y optimizando con FFmpeg (720p · faststart)... Puede demorar unos segundos.';
                        }

                        var csrfToken = document.querySelector('input[name="csrf_token"]');
                        var formData = new FormData();
                        formData.append('video', file);
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
                                if (pathInput) pathInput.value = result.data.path;
                                if (posterInput) posterInput.value = result.data.poster || '';
                                updatePreview(result.data.path, result.data.poster);
                                triggerChange(pathInput);
                                if (statusBadge) {
                                    statusBadge.className = 'video-status-badge';
                                    var sizeMB = (result.data.size / (1024 * 1024)).toFixed(1);
                                    statusBadge.textContent = '✓ Reel optimizado (' + result.data.duration + ' seg · ' + sizeMB + ' MB · H.264)';
                                }
                            } else {
                                var err = (result.data && result.data.error) ? result.data.error : 'Error al procesar el video.';
                                if (statusBadge) {
                                    statusBadge.className = 'video-status-badge error';
                                    statusBadge.textContent = '⚠ ' + err;
                                }
                            }
                        })
                        .catch(function() {
                            if (statusBadge) {
                                statusBadge.className = 'video-status-badge error';
                                statusBadge.textContent = '⚠ Error de red al subir el video.';
                            }
                        });
                    };

                    tempVideo.onerror = function() {
                        URL.revokeObjectURL(tempVideo.src);
                        if (statusBadge) {
                            statusBadge.className = 'video-status-badge error';
                            statusBadge.textContent = '⚠ No se pudo leer el archivo de video. Verificá que sea MP4 o WebM.';
                        }
                    };
                });
            }
        });
    }

    function initRepeaters() {
        document.querySelectorAll('.repeater-list').forEach(function(container) {
            var arrayName = container.getAttribute('data-array-name') || 'items';
            reindexContainer(container, arrayName);
        });

        document.addEventListener('click', function(e) {
            var target = e.target;
            if (!target) return;

            // Move Up button
            if (target.matches('.btn-move-up') || target.closest('.btn-move-up')) {
                var upBtn = target.matches('.btn-move-up') ? target : target.closest('.btn-move-up');
                var item = upBtn.closest('.repeater-item');
                var container = item ? item.closest('.repeater-list') : null;
                if (container && item && item.previousElementSibling && item.previousElementSibling.classList.contains('repeater-item')) {
                    var prev = item.previousElementSibling;
                    container.insertBefore(item, prev);
                    var arrayName = container.getAttribute('data-array-name') || 'items';
                    reindexContainer(container, arrayName);
                    triggerChange(container);
                    try {
                        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } catch(err) {}
                }
                return;
            }

            // Move Down button
            if (target.matches('.btn-move-down') || target.closest('.btn-move-down')) {
                var downBtn = target.matches('.btn-move-down') ? target : target.closest('.btn-move-down');
                var item = downBtn.closest('.repeater-item');
                var container = item ? item.closest('.repeater-list') : null;
                if (container && item && item.nextElementSibling && item.nextElementSibling.classList.contains('repeater-item')) {
                    var next = item.nextElementSibling;
                    container.insertBefore(item, next.nextElementSibling);
                    var arrayName = container.getAttribute('data-array-name') || 'items';
                    reindexContainer(container, arrayName);
                    triggerChange(container);
                    try {
                        item.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } catch(err) {}
                }
                return;
            }

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
                    triggerChange(container);
                }
                return;
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
                        initVideoPickers();
                        triggerChange(list);
                    }
                }
                return;
            }
        });

        // Toggle active/inactive checkbox change event
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('item-active-checkbox')) {
                var item = e.target.closest('.repeater-item');
                if (item) {
                    var isActive = e.target.checked;
                    item.classList.toggle('is-inactive', !isActive);
                    var badge = item.querySelector('.item-status-badge');
                    if (badge) {
                        if (isActive) {
                            badge.className = 'item-status-badge item-status-badge--active';
                            badge.textContent = 'Activo';
                        } else {
                            badge.className = 'item-status-badge item-status-badge--inactive';
                            badge.textContent = 'Inactivo / Pausado';
                        }
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

            if (e.target && e.target.classList.contains('archive-url-input')) {
                var field = e.target.closest('.admin-field');
                if (field) {
                    var previewLink = field.querySelector('.archive-url-preview');
                    var val = (e.target.value || '').trim();
                    if (previewLink) {
                        if (val) {
                            var href = (val.indexOf('http://') === 0 || val.indexOf('https://') === 0) ? val : ('../' + val.replace(/^\/+/, ''));
                            previewLink.href = href;
                            previewLink.style.display = 'inline-block';
                        } else {
                            previewLink.style.display = 'none';
                        }
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
                    triggerChange(input);
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

    function initAudioPickers() {
        document.querySelectorAll('.audio-picker-field').forEach(function(field) {
            if (field.dataset.initialized) return;
            field.dataset.initialized = 'true';

            var pathInput = field.querySelector('.audio-path-input');
            var fileInput = field.querySelector('.audio-file-input');
            var uploadBtn = field.querySelector('.audio-upload-btn');
            var clearBtn = field.querySelector('.btn-clear-audio');
            var previewPlayer = field.querySelector('.audio-preview-player');
            var statusBadge = field.querySelector('.audio-status-badge');

            function updatePreview(audioSrc) {
                if (!previewPlayer) return;
                var cleanAudio = (audioSrc || '').trim();
                if (!cleanAudio) {
                    previewPlayer.style.display = 'none';
                    previewPlayer.pause();
                    previewPlayer.src = '';
                    return;
                }
                var resolvedAudio = cleanAudio.indexOf('http') === 0 ? cleanAudio : '../' + cleanAudio.replace(/^\/+/, '');
                previewPlayer.src = resolvedAudio;
                previewPlayer.style.display = 'block';
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    if (pathInput) {
                        pathInput.value = '';
                        updatePreview('');
                        triggerChange(pathInput);
                    }
                    if (fileInput) fileInput.value = '';
                    if (statusBadge) {
                        statusBadge.className = 'audio-status-badge';
                        statusBadge.textContent = 'Audio quitado';
                    }
                });
            }

            if (pathInput) {
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

                    var maxBytes = 25 * 1024 * 1024;
                    if (file.size > maxBytes) {
                        if (statusBadge) {
                            statusBadge.className = 'audio-status-badge error';
                            statusBadge.textContent = '⚠ El audio supera los 25 MB permitidos.';
                        }
                        this.value = '';
                        return;
                    }

                    var csrfInput = document.querySelector('input[name="csrf_token"]');
                    var csrfToken = csrfInput ? csrfInput.value : '';

                    var formData = new FormData();
                    formData.append('audio', file);
                    formData.append('csrf_token', csrfToken);

                    if (statusBadge) {
                        statusBadge.className = 'audio-status-badge loading';
                        statusBadge.textContent = '⏳ Subiendo audio...';
                    }
                    if (uploadBtn) uploadBtn.disabled = true;

                    fetch('upload.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (uploadBtn) uploadBtn.disabled = false;
                        if (data.success) {
                            if (pathInput) {
                                pathInput.value = data.path;
                                updatePreview(data.path);
                                triggerChange(pathInput);
                            }
                            if (statusBadge) {
                                statusBadge.className = 'audio-status-badge';
                                statusBadge.textContent = '✓ Audio subido correctamente (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB)';
                            }
                        } else {
                            if (statusBadge) {
                                statusBadge.className = 'audio-status-badge error';
                                statusBadge.textContent = '⚠ ' + (data.error || 'Error al subir el audio.');
                            }
                        }
                    })
                    .catch(function(err) {
                        if (uploadBtn) uploadBtn.disabled = false;
                        if (statusBadge) {
                            statusBadge.className = 'audio-status-badge error';
                            statusBadge.textContent = '⚠ Error de red al subir el archivo.';
                        }
                    });
                });
            }
        });
    }

    function initFormDirtyTracker() {
        var form = document.querySelector('.editor-form');
        if (!form) return;

        var actions = form.querySelector('.editor-actions');
        if (!actions) return;

        document.body.classList.add('has-editor-actions');

        var submitBtn = actions.querySelector('button[type="submit"]');
        if (!submitBtn) return;

        submitBtn.classList.add('btn-save');

        // Set up the status badge container
        var infoWrap = actions.querySelector('.editor-actions__info');
        if (!infoWrap) {
            infoWrap = document.createElement('div');
            infoWrap.className = 'editor-actions__info';
            var existingP = actions.querySelector('p');
            if (existingP) {
                actions.insertBefore(infoWrap, existingP);
                infoWrap.appendChild(existingP);
            } else {
                actions.insertBefore(infoWrap, submitBtn);
            }
        }

        var statusBadge = infoWrap.querySelector('.save-status-badge');
        var statusIcon = null;
        var statusText = null;

        if (!statusBadge) {
            statusBadge = document.createElement('span');
            statusBadge.className = 'save-status-badge save-status-badge--clean';
            statusIcon = document.createElement('i');
            statusIcon.className = 'fa fa-check-circle';
            statusIcon.setAttribute('aria-hidden', 'true');
            statusText = document.createElement('span');
            statusText.textContent = 'Sin cambios pendientes';
            statusBadge.appendChild(statusIcon);
            statusBadge.appendChild(statusText);
            infoWrap.insertBefore(statusBadge, infoWrap.firstChild);
        } else {
            statusIcon = statusBadge.querySelector('i');
            statusText = statusBadge.querySelector('span');
        }

        function serializeFormState() {
            var formData = new FormData(form);
            var parts = [];
            formData.forEach(function(val, key) {
                if (key === 'csrf_token') return;
                if (val instanceof File) return;
                parts.push(key + '===' + val);
            });
            // Include repeater counts
            form.querySelectorAll('.repeater-list').forEach(function(list, idx) {
                parts.push('__rep_' + (list.id || idx) + '_len===' + list.querySelectorAll('.repeater-item').length);
            });
            return parts.join('|||');
        }

        var initialState = serializeFormState();
        var isDirty = null; // null initially so updateUI(false) always sets initial clean state
        var isSubmitting = false;
        var checkTimer = null;

        function updateUI(dirty) {
            if (isDirty === dirty) return; // Prevent unnecessary DOM work
            isDirty = dirty;
            submitBtn.disabled = !dirty;
            if (dirty) {
                submitBtn.classList.remove('is-disabled');
                submitBtn.classList.add('is-active');
                actions.classList.add('is-dirty');
                statusBadge.className = 'save-status-badge save-status-badge--dirty';
                if (statusIcon) statusIcon.className = 'fa fa-dot-circle-o';
                if (statusText) statusText.textContent = 'Tenés cambios sin guardar';
            } else {
                submitBtn.classList.add('is-disabled');
                submitBtn.classList.remove('is-active');
                actions.classList.remove('is-dirty');
                statusBadge.className = 'save-status-badge save-status-badge--clean';
                if (statusIcon) statusIcon.className = 'fa fa-check-circle';
                if (statusText) statusText.textContent = 'Sin cambios pendientes';
            }
        }

        function checkChanges() {
            if (isSubmitting) return;
            var current = serializeFormState();
            updateUI(current !== initialState);
        }

        function scheduleCheck() {
            if (isSubmitting) return;
            if (checkTimer) clearTimeout(checkTimer);
            checkTimer = setTimeout(checkChanges, 40);
        }

        // Initialize state (clean by default)
        updateUI(false);

        form.addEventListener('input', scheduleCheck);
        form.addEventListener('change', scheduleCheck);

        form.addEventListener('submit', function() {
            isSubmitting = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Guardando... <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';
        });

        window.addEventListener('beforeunload', function(e) {
            if (isDirty && !isSubmitting) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    function initBackToTop() {
        var btn = document.getElementById('admin-back-to-top');
        if (!btn) return;

        function updateVisibility() {
            var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
            if (scrollY > 160) {
                btn.classList.add('is-visible');
            } else {
                btn.classList.remove('is-visible');
            }
        }

        window.addEventListener('scroll', updateVisibility, { passive: true });
        updateVisibility();

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    function init() {
        initRepeaters();
        initImagePickers();
        initVideoPickers();
        initIconPickers();
        initAudioPickers();
        initFormDirtyTracker();
        initBackToTop();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
