/* SoundMarket — player.js */
(function () {
  'use strict';

  var players = [];

  function formatTime(s) {
    s = Math.floor(s || 0);
    return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
  }

  function stopAll(except) {
    players.forEach(function (p) {
      if (p !== except && p.audio && !p.audio.paused) {
        p.audio.pause();
        p.iconPlay.style.display  = '';
        p.iconPause.style.display = 'none';
      }
    });
  }

  document.querySelectorAll('.custom-player').forEach(function (el) {
    var src       = el.dataset.src;
    var btnPlay   = el.querySelector('.cp-play');
    var btnMute   = el.querySelector('.cp-mute');
    var bar       = el.querySelector('.cp-bar');
    var fill      = el.querySelector('.cp-fill');
    var timeEl    = el.querySelector('.cp-time');
    var iconPlay  = el.querySelector('.icon-play');
    var iconPause = el.querySelector('.icon-pause');
    var iconSound = el.querySelector('.icon-sound');
    var iconMute  = el.querySelector('.icon-mute');

    var audio = null;
    var obj   = { audio: null, iconPlay: iconPlay, iconPause: iconPause };
    players.push(obj);

    function ensureAudio() {
      if (audio) return;
      audio     = new Audio(src);
      obj.audio = audio;

      audio.addEventListener('timeupdate', function () {
        if (!audio.duration) return;
        var pct = (audio.currentTime / audio.duration) * 100;
        fill.style.width  = pct + '%';
        timeEl.textContent = formatTime(audio.currentTime) + ' / ' + formatTime(audio.duration);
      });

      audio.addEventListener('ended', function () {
        audio.currentTime = 0;
        fill.style.width  = '0%';
        iconPlay.style.display  = '';
        iconPause.style.display = 'none';
      });

      audio.addEventListener('loadedmetadata', function () {
        timeEl.textContent = '0:00 / ' + formatTime(audio.duration);
      });
    }

    btnPlay.addEventListener('click', function () {
      ensureAudio();
      if (audio.paused) {
        stopAll(obj);
        audio.play();
        iconPlay.style.display  = 'none';
        iconPause.style.display = '';
      } else {
        audio.pause();
        iconPlay.style.display  = '';
        iconPause.style.display = 'none';
      }
    });

    bar.addEventListener('click', function (e) {
      ensureAudio();
      if (!audio.duration) return;
      var rect = bar.getBoundingClientRect();
      var pct  = (e.clientX - rect.left) / rect.width;
      audio.currentTime = Math.max(0, Math.min(1, pct)) * audio.duration;
    });

    bar.addEventListener('touchstart', function (e) {
      ensureAudio();
      if (!audio.duration) return;
      var rect = bar.getBoundingClientRect();
      var pct  = (e.touches[0].clientX - rect.left) / rect.width;
      audio.currentTime = Math.max(0, Math.min(1, pct)) * audio.duration;
    }, { passive: true });

    btnMute.addEventListener('click', function () {
      ensureAudio();
      audio.muted = !audio.muted;
      iconSound.style.display = audio.muted ? 'none' : '';
      iconMute.style.display  = audio.muted ? ''     : 'none';
    });
  });

})();
