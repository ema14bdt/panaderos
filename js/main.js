(function() {
    'use strict';

    var lightboxEl = null;
    var backdropEl = null;
    var closeBtn = null;
    var prevBtn = null;
    var nextBtn = null;
    var imgEl = null;
    var videoEl = null;
    var captionEl = null;
    var counterEl = null;

    var currentGallery = [];
    var currentIndex = 0;
    var touchStartX = 0;
    var touchEndX = 0;

    function createLightbox() {
        if (document.getElementById('site-lightbox')) {
            lightboxEl = document.getElementById('site-lightbox');
            backdropEl = lightboxEl.querySelector('.site-lightbox__backdrop');
            closeBtn = lightboxEl.querySelector('.site-lightbox__close');
            prevBtn = lightboxEl.querySelector('.site-lightbox__prev');
            nextBtn = lightboxEl.querySelector('.site-lightbox__next');
            imgEl = lightboxEl.querySelector('.site-lightbox__img');
            videoEl = lightboxEl.querySelector('.site-lightbox__video');
            captionEl = lightboxEl.querySelector('.site-lightbox__caption');
            counterEl = lightboxEl.querySelector('.site-lightbox__counter');
            return;
        }

        var wrapper = document.createElement('div');
        wrapper.id = 'site-lightbox';
        wrapper.className = 'site-lightbox';
        wrapper.setAttribute('aria-hidden', 'true');
        wrapper.setAttribute('role', 'dialog');
        wrapper.setAttribute('aria-modal', 'true');

        wrapper.innerHTML = [
            '<div class="site-lightbox__backdrop"></div>',
            '<div class="site-lightbox__container">',
            '  <div class="site-lightbox__header">',
            '    <div class="site-lightbox__meta">',
            '      <span class="site-lightbox__caption"></span>',
            '      <span class="site-lightbox__counter"></span>',
            '    </div>',
            '    <button type="button" class="site-lightbox__close" aria-label="Cerrar modal">&times;</button>',
            '  </div>',
            '  <div class="site-lightbox__stage">',
            '    <button type="button" class="site-lightbox__nav site-lightbox__prev" aria-label="Anterior">&#8249;</button>',
            '    <div class="site-lightbox__figure">',
            '      <img class="site-lightbox__img" src="" alt="">',
            '      <video class="site-lightbox__video" controls playsinline preload="auto" style="display:none;"></video>',
            '    </div>',
            '    <button type="button" class="site-lightbox__nav site-lightbox__next" aria-label="Siguiente">&#8250;</button>',
            '  </div>',
            '</div>'
        ].join('');

        document.body.appendChild(wrapper);

        lightboxEl = wrapper;
        backdropEl = wrapper.querySelector('.site-lightbox__backdrop');
        closeBtn = wrapper.querySelector('.site-lightbox__close');
        prevBtn = wrapper.querySelector('.site-lightbox__prev');
        nextBtn = wrapper.querySelector('.site-lightbox__next');
        imgEl = wrapper.querySelector('.site-lightbox__img');
        videoEl = wrapper.querySelector('.site-lightbox__video');
        captionEl = wrapper.querySelector('.site-lightbox__caption');
        counterEl = wrapper.querySelector('.site-lightbox__counter');

        closeBtn.addEventListener('click', closeLightbox);
        backdropEl.addEventListener('click', closeLightbox);
        prevBtn.addEventListener('click', showPrev);
        nextBtn.addEventListener('click', showNext);

        document.addEventListener('keydown', function(e) {
            if (!lightboxEl.classList.contains('is-open')) return;
            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowLeft') {
                showPrev();
            } else if (e.key === 'ArrowRight') {
                showNext();
            }
        });

        // Touch swipe support
        lightboxEl.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        lightboxEl.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 45) {
                if (diff > 0) {
                    showNext();
                } else {
                    showPrev();
                }
            }
        }, { passive: true });
    }

    function updateSlide() {
        if (!currentGallery.length) return;
        var item = currentGallery[currentIndex];
        var isVideo = item.type === 'video' || (typeof item.src === 'string' && (/\.(mp4|webm|mov)(\?.*)?$/i.test(item.src) || item.src.indexOf('video') !== -1));

        if (isVideo) {
            if (imgEl) {
                imgEl.style.display = 'none';
                imgEl.src = '';
            }
            if (videoEl) {
                videoEl.style.display = 'block';
                videoEl.src = item.src;
                if (item.poster) {
                    videoEl.poster = item.poster;
                } else {
                    videoEl.removeAttribute('poster');
                }
                videoEl.currentTime = 0;
                videoEl.play().catch(function() {});
            }
        } else {
            if (videoEl) {
                videoEl.pause();
                videoEl.src = '';
                videoEl.style.display = 'none';
            }
            if (imgEl) {
                imgEl.style.display = 'block';
                imgEl.classList.add('is-loading');
                imgEl.src = item.src;
                imgEl.alt = item.caption || '';
                imgEl.onload = function() {
                    imgEl.classList.remove('is-loading');
                };
            }
        }

        if (captionEl) {
            captionEl.textContent = item.caption || '';
        }
        if (counterEl) {
            counterEl.textContent = (currentIndex + 1) + ' / ' + currentGallery.length;
        }

        if (currentGallery.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        }
    }

    function openLightbox(gallery, index) {
        createLightbox();
        currentGallery = gallery;
        currentIndex = index || 0;
        updateSlide();
        lightboxEl.classList.add('is-open');
        lightboxEl.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');
    }

    function closeLightbox() {
        if (!lightboxEl) return;
        lightboxEl.classList.remove('is-open');
        lightboxEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lightbox-open');
        if (imgEl) {
            imgEl.src = '';
        }
        if (videoEl) {
            videoEl.pause();
            videoEl.src = '';
            videoEl.style.display = 'none';
        }
    }

    function showPrev() {
        if (!currentGallery.length) return;
        currentIndex = (currentIndex - 1 + currentGallery.length) % currentGallery.length;
        updateSlide();
    }

    function showNext() {
        if (!currentGallery.length) return;
        currentIndex = (currentIndex + 1) % currentGallery.length;
        updateSlide();
    }

    function initGalleries() {
        var handledLinks = [];

        // 1. Grid/container-based groups (.facility-grid, [data-gallery-group])
        var grids = document.querySelectorAll('.facility-grid, [data-gallery-group]');
        grids.forEach(function(grid) {
            var links = Array.prototype.slice.call(grid.querySelectorAll('a.facility-item, a[data-lightbox]'));
            if (!links.length) return;
            var galleryData = [];

            links.forEach(function(link) {
                handledLinks.push(link);
                var imgSrc = link.getAttribute('href');
                var imgThumb = link.querySelector('img');
                var caption = link.getAttribute('data-caption') || (imgThumb ? imgThumb.getAttribute('alt') : '') || link.textContent.trim();
                caption = caption.replace(/\s*·?\s*Ver imagen\s*\d+/i, '').replace(/\s*·?\s*Imagen\s*\d+/i, '').trim();

                galleryData.push({
                    src: imgSrc,
                    caption: caption
                });
            });

            links.forEach(function(link, idx) {
                link.removeAttribute('target');
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    openLightbox(galleryData, idx);
                });
            });
        });

        // 2. Named groups by data-lightbox attribute (e.g. data-lightbox="beneficios")
        var namedGroups = {};
        var allLightboxLinks = Array.prototype.slice.call(document.querySelectorAll('a[data-lightbox]'));
        allLightboxLinks.forEach(function(link) {
            if (handledLinks.indexOf(link) !== -1) return;
            var group = link.getAttribute('data-lightbox');
            if (!group) return;
            if (!namedGroups[group]) {
                namedGroups[group] = [];
            }
            namedGroups[group].push(link);
            handledLinks.push(link);
        });

        Object.keys(namedGroups).forEach(function(groupName) {
            var links = namedGroups[groupName];
            var galleryData = [];

            links.forEach(function(link) {
                var imgSrc = link.getAttribute('href');
                var imgThumb = link.querySelector('img');
                var caption = link.getAttribute('data-caption') || (imgThumb ? imgThumb.getAttribute('alt') : '') || link.getAttribute('title') || '';
                caption = caption.replace(/\s*·?\s*Ver imagen\s*\d+/i, '').replace(/\s*·?\s*Imagen\s*\d+/i, '').trim();

                galleryData.push({
                    src: imgSrc,
                    caption: caption
                });
            });

            links.forEach(function(link, idx) {
                link.removeAttribute('target');
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    openLightbox(galleryData, idx);
                });
            });
        });

        // 3. Standalone individual lightbox links
        allLightboxLinks.forEach(function(link) {
            if (handledLinks.indexOf(link) !== -1) return;
            link.removeAttribute('target');
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var imgSrc = this.getAttribute('href');
                var caption = this.getAttribute('data-caption') || this.getAttribute('title') || '';
                openLightbox([{ src: imgSrc, caption: caption }], 0);
            });
        });
    }

    function initReels() {
        var reelCards = document.querySelectorAll('.reel-card');
        if (!reelCards.length) return;

        var videoGallery = [];
        reelCards.forEach(function(card) {
            var wrap = card.querySelector('.reel-card__video-wrap');
            var video = card.querySelector('video');
            var videoSrc = (wrap && wrap.getAttribute('data-video-src')) || (video ? video.currentSrc || video.src : '');
            var posterSrc = (wrap && wrap.getAttribute('data-video-poster')) || (video ? video.poster : '');
            var titleEl = card.querySelector('.reel-card__title span') || card.querySelector('.reel-card__title');
            var title = (wrap && wrap.getAttribute('data-video-title')) || (titleEl ? titleEl.textContent.trim() : 'Video');

            videoGallery.push({
                type: 'video',
                src: videoSrc,
                poster: posterSrc,
                caption: title
            });
        });

        reelCards.forEach(function(card, idx) {
            var video = card.querySelector('video');
            var playBtn = card.querySelector('.reel-card__play-btn');
            var soundBtn = card.querySelector('.reel-card__sound-btn');
            var expandBtn = card.querySelector('.reel-card__expand-btn');
            var videoWrap = card.querySelector('.reel-card__video-wrap');
            if (!video) return;

            function launchFullscreen() {
                document.querySelectorAll('.reel-card video').forEach(function(other) {
                    other.pause();
                });
                openLightbox(videoGallery, idx);
            }

            if (expandBtn) {
                expandBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    launchFullscreen();
                });
            }

            if (videoWrap) {
                videoWrap.addEventListener('click', function(e) {
                    if (e.target.closest('.reel-card__sound-btn')) return;
                    launchFullscreen();
                });

                videoWrap.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        launchFullscreen();
                    }
                });
            }

            if (playBtn) {
                playBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    launchFullscreen();
                });
            }

            if (soundBtn) {
                soundBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    video.muted = !video.muted;
                    if (video.muted) {
                        soundBtn.innerHTML = '<i class="fa fa-volume-off" aria-hidden="true"></i>';
                    } else {
                        soundBtn.innerHTML = '<i class="fa fa-volume-up" aria-hidden="true"></i>';
                    }
                });
            }

            card.addEventListener('mouseenter', function() {
                if (video.paused && !document.body.classList.contains('lightbox-open')) {
                    video.play().catch(function() {});
                }
            });
            card.addEventListener('mouseleave', function() {
                if (!video.paused) {
                    video.pause();
                }
            });
        });

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    var card = entry.target;
                    var video = card.querySelector('video');
                    if (!video) return;
                    if (!entry.isIntersecting && !video.paused) {
                        video.pause();
                    }
                });
            }, { threshold: 0.25 });

            reelCards.forEach(function(card) {
                observer.observe(card);
            });
        }
    }

    function initLyricsModal() {
        var triggerBtn = document.getElementById('btn-open-lyrics');
        var modal = document.getElementById('lyrics-lightbox');
        if (!triggerBtn || !modal) return;

        var closeBtn = document.getElementById('lyrics-lightbox-close');
        var closeBottomBtn = document.getElementById('lyrics-lightbox-close-bottom');
        var backdrop = modal.querySelector('.lyrics-lightbox__backdrop');
        var stage = modal.querySelector('.lyrics-lightbox__stage');

        function openModal() {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('lightbox-open');
            if (closeBtn) {
                closeBtn.focus();
            }
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('lightbox-open');
            if (triggerBtn) {
                triggerBtn.focus();
            }
        }

        triggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (closeBottomBtn) closeBottomBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        if (stage) {
            stage.addEventListener('click', function(e) {
                if (e.target === stage || e.target.classList.contains('lyrics-lightbox__dialog')) {
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (!modal.classList.contains('is-open')) return;
            if (e.key === 'Escape' || e.key === 'Esc') {
                closeModal();
            }
        });
    }

    function initAll() {
        initGalleries();
        initReels();
        initLyricsModal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();