(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var img = document.querySelector('img.obi-hero__media[src*="hero-poster.jpg"]');
    if (!img) return;

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var baseClasses = img.className;
    var baseSrc = img.src.replace(/hero-poster\.jpg.*$/, '');

    var wrap = document.createElement('div');
    // The wrapper takes over the original element's `.obi-hero__media` slot
    // in the page CSS (components.css: `.obi-hero__media{opacity:0}` /
    // `.is-active{opacity:1}`), so it must always carry is-active - the
    // crossfade between the two videos inside it is handled separately via
    // their own inline opacity, not this class.
    wrap.className = baseClasses.indexOf('is-active') === -1 ? baseClasses + ' is-active' : baseClasses;
    wrap.style.position = 'relative';
    wrap.style.overflow = 'hidden';

    function makeSlide(bgFile, posterFile, active) {
      var video = document.createElement('video');
      video.className = baseClasses + (active ? '' : '');
      video.classList.toggle('is-active', active);
      video.muted = true;
      video.defaultMuted = true;
      video.setAttribute('muted', '');
      video.setAttribute('playsinline', '');
      video.setAttribute('loop', '');
      video.setAttribute('preload', active ? 'auto' : 'metadata');
      video.setAttribute('poster', baseSrc + posterFile);
      video.style.position = 'absolute';
      video.style.inset = '0';
      video.style.width = '100%';
      video.style.height = '100%';
      video.style.objectFit = 'cover';
      video.style.opacity = active ? '1' : '0';
      video.style.transition = 'opacity 1.1s ease';
      var source = document.createElement('source');
      source.src = baseSrc + bgFile;
      source.type = 'video/mp4';
      video.appendChild(source);
      return video;
    }

    var slideA = makeSlide('hero-bg-1.mp4', 'hero-poster.jpg', true);
    var slideB = makeSlide('hero-bg-2.mp4', 'hero-poster-2.jpg', false);
    wrap.appendChild(slideA);
    wrap.appendChild(slideB);
    img.replaceWith(wrap);
    slideA.play().catch(function () {});

    if (reduceMotion) return;

    var slides = [slideA, slideB];
    var current = 0;
    setInterval(function () {
      var next = (current + 1) % slides.length;
      slides[next].currentTime = 0;
      slides[next].play().catch(function () {});
      slides[next].style.opacity = '1';
      slides[current].style.opacity = '0';
      var prev = slides[current];
      setTimeout(function () {
        prev.pause();
      }, 1200);
      current = next;
    }, 6500);
  });
})();
