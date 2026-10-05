(function() {
    'use strict';

    var lightboxEl = null;
    var backdropEl = null;
    var closeBtn = null;
    var prevBtn = null;
    var nextBtn = null;
    var imgEl = null;
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
            '    <button type="button" class="site-lightbox__nav site-lightbox__prev" aria-label="Imagen anterior">&#8249;</button>',
            '    <div class="site-lightbox__figure">',
            '      <img class="site-lightbox__img" src="" alt="">',
            '    </div>',
            '    <button type="button" class="site-lightbox__nav site-lightbox__next" aria-label="Imagen siguiente">&#8250;</button>',
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
        
        imgEl.classList.add('is-loading');
        imgEl.src = item.src;
        imgEl.alt = item.caption || '';
        imgEl.onload = function() {
            imgEl.classList.remove('is-loading');
        };

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
        // Collect all containers that have gallery items
        var grids = document.querySelectorAll('.facility-grid, [data-gallery-group]');
        grids.forEach(function(grid) {
            var links = grid.querySelectorAll('a.facility-item, a[data-lightbox]');
            var galleryData = [];

            links.forEach(function(link, idx) {
                var imgSrc = link.getAttribute('href');
                var imgThumb = link.querySelector('img');
                var caption = link.getAttribute('data-caption') || (imgThumb ? imgThumb.getAttribute('alt') : '') || link.textContent.trim();
                // Clean up "Ver imagen 01" from caption if present
                caption = caption.replace(/\s*·?\s*Ver imagen\s*\d+/i, '').replace(/\s*·?\s*Imagen\s*\d+/i, '').trim();

                galleryData.push({
                    src: imgSrc,
                    caption: caption
                });

                link.removeAttribute('target');
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    openLightbox(galleryData, idx);
                });
            });
        });

        // Individual lightbox links not inside a grid
        document.querySelectorAll('a[data-lightbox]:not(.facility-item)').forEach(function(link) {
            link.removeAttribute('target');
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var imgSrc = this.getAttribute('href');
                var caption = this.getAttribute('data-caption') || this.getAttribute('title') || '';
                openLightbox([{ src: imgSrc, caption: caption }], 0);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGalleries);
    } else {
        initGalleries();
    }
})();