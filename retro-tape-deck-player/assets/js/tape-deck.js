(function () {
    "use strict";

    /* ================================================================
       Retro Tape Deck MP3 Player – JavaScript Engine
       ================================================================ */

    // --- Utility -----------------------------------------------------------
    function formatTime(sec) {
        if (!isFinite(sec) || sec < 0) return "0:00";
        var m = Math.floor(sec / 60);
        var s = Math.floor(sec % 60);
        return m + ":" + (s < 10 ? "0" : "") + s;
    }

    // --- Initialise each player instance -----------------------------------
    document.addEventListener("DOMContentLoaded", function () {
        var players = document.querySelectorAll(".rtd-player");
        players.forEach(initPlayer);
    });

    function initPlayer(root) {
        // Parse track list from embedded JSON
        var dataEl = root.querySelector(".rtd-track-data");
        if (!dataEl) return;
        var tracks = [];
        try {
            tracks = JSON.parse(dataEl.textContent);
        } catch (e) {
            return;
        }
        if (!tracks.length) return;

        // State
        var currentIndex = 0;
        var audio = new Audio();
        audio.preload = "metadata";
        audio.volume = 0.75;

        // DOM refs
        var displayTrack = root.querySelector(".rtd-display-track");
        var displayTime = root.querySelector(".rtd-display-time");
        var progressBar = root.querySelector(".rtd-progress-bar");
        var volumeSlider = root.querySelector(".rtd-volume");
        var btnPlay = root.querySelector(".rtd-btn-play");
        var btnPause = root.querySelector(".rtd-btn-pause");
        var btnStop = root.querySelector(".rtd-btn-stop");
        var btnRew = root.querySelector(".rtd-btn-rew");
        var btnFf = root.querySelector(".rtd-btn-ff");
        var btnPrev = root.querySelector(".rtd-btn-prev");
        var btnNext = root.querySelector(".rtd-btn-next");
        var btnEject = root.querySelector(".rtd-btn-eject");
        var reelsA = root.querySelectorAll(".rtd-well-a .rtd-reel");
        var reelsB = root.querySelectorAll(".rtd-well-b .rtd-reel");
        var led = root.querySelector(".rtd-led");
        var playlistToggle = root.querySelector(".rtd-playlist-toggle");
        var playlistEl = root.querySelector(".rtd-playlist");
        var vuBarsL = root.querySelectorAll(".rtd-vu-left .vu-bar");
        var vuBarsR = root.querySelectorAll(".rtd-vu-right .vu-bar");

        // --- Audio context for VU meters ---
        var audioCtx, analyser, splitter, analyserL, analyserR, sourceNode;

        function initAudioContext() {
            if (audioCtx) return;
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                sourceNode = audioCtx.createMediaElementSource(audio);
                splitter = audioCtx.createChannelSplitter(2);
                analyserL = audioCtx.createAnalyser();
                analyserR = audioCtx.createAnalyser();
                analyserL.fftSize = 64;
                analyserR.fftSize = 64;

                sourceNode.connect(splitter);
                splitter.connect(analyserL, 0);
                splitter.connect(analyserR, 1);
                sourceNode.connect(audioCtx.destination);
            } catch (e) {
                audioCtx = null;
            }
        }

        // --- VU meter animation ---
        var vuFrame;
        function animateVU() {
            if (!audioCtx || audio.paused) {
                resetVU();
                return;
            }
            var dataL = new Uint8Array(analyserL.frequencyBinCount);
            var dataR = new Uint8Array(analyserR.frequencyBinCount);
            analyserL.getByteFrequencyData(dataL);
            analyserR.getByteFrequencyData(dataR);

            updateVUBars(vuBarsL, dataL);
            updateVUBars(vuBarsR, dataR);
            vuFrame = requestAnimationFrame(animateVU);
        }

        function updateVUBars(bars, data) {
            var len = bars.length;
            for (var i = 0; i < len; i++) {
                var idx = Math.floor((i / len) * data.length);
                var val = data[idx] / 255;
                var h = Math.max(2, val * 22);
                bars[i].style.height = h + "px";
            }
        }

        function resetVU() {
            [vuBarsL, vuBarsR].forEach(function (bars) {
                for (var i = 0; i < bars.length; i++) {
                    bars[i].style.height = "2px";
                }
            });
        }

        // --- Load track ---
        function loadTrack(index) {
            if (index < 0 || index >= tracks.length) return;
            currentIndex = index;
            audio.src = tracks[index].url;
            displayTrack.textContent = tracks[index].title;
            displayTime.textContent = "0:00";
            if (progressBar) progressBar.value = 0;
            highlightPlaylistItem();
        }

        // --- Transport controls ---
        function play() {
            if (!audio.src) loadTrack(0);
            initAudioContext();
            if (audioCtx && audioCtx.state === "suspended") {
                audioCtx.resume();
            }
            audio.play();
            setReelsSpinning(true);
            if (led) led.classList.add("on");
            if (btnPlay) btnPlay.classList.add("active");
            animateVU();
        }

        function pause() {
            audio.pause();
            setReelsSpinning(false);
            if (btnPlay) btnPlay.classList.remove("active");
        }

        function stop() {
            audio.pause();
            audio.currentTime = 0;
            setReelsSpinning(false);
            if (led) led.classList.remove("on");
            if (btnPlay) btnPlay.classList.remove("active");
            displayTime.textContent = "0:00";
            if (progressBar) progressBar.value = 0;
            resetVU();
        }

        function rewind() {
            audio.currentTime = Math.max(0, audio.currentTime - 10);
        }

        function fastForward() {
            audio.currentTime = Math.min(audio.duration || 0, audio.currentTime + 10);
        }

        function prevTrack() {
            var idx = currentIndex - 1;
            if (idx < 0) idx = tracks.length - 1;
            var wasPlaying = !audio.paused;
            loadTrack(idx);
            if (wasPlaying) play();
        }

        function nextTrack() {
            var idx = currentIndex + 1;
            if (idx >= tracks.length) idx = 0;
            var wasPlaying = !audio.paused;
            loadTrack(idx);
            if (wasPlaying) play();
        }

        function eject() {
            stop();
            displayTrack.textContent = "-- NO TAPE --";
            audio.src = "";
        }

        // --- Reel animation ---
        function setReelsSpinning(spinning) {
            var allReels = [].concat(
                Array.prototype.slice.call(reelsA),
                Array.prototype.slice.call(reelsB)
            );
            allReels.forEach(function (r) {
                if (spinning) {
                    r.classList.add("spinning");
                } else {
                    r.classList.remove("spinning");
                }
            });
        }

        // --- Playlist ---
        function buildPlaylist() {
            if (!playlistEl) return;
            var ol = playlistEl.querySelector("ol");
            if (!ol) return;
            ol.innerHTML = "";
            tracks.forEach(function (t, i) {
                var li = document.createElement("li");
                li.innerHTML =
                    '<span class="track-num">' + (i + 1) + ".</span> " +
                    '<span class="track-title">' + escapeHtml(t.title) + "</span>" +
                    '<span class="track-duration"></span>';
                li.addEventListener("click", function () {
                    loadTrack(i);
                    play();
                });
                ol.appendChild(li);
            });
            highlightPlaylistItem();
        }

        function highlightPlaylistItem() {
            if (!playlistEl) return;
            var items = playlistEl.querySelectorAll("li");
            items.forEach(function (li, i) {
                li.classList.toggle("active", i === currentIndex);
            });
        }

        function escapeHtml(str) {
            var div = document.createElement("div");
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        // --- Event listeners ---
        if (btnPlay) btnPlay.addEventListener("click", play);
        if (btnPause) btnPause.addEventListener("click", pause);
        if (btnStop) btnStop.addEventListener("click", stop);
        if (btnRew) btnRew.addEventListener("click", rewind);
        if (btnFf) btnFf.addEventListener("click", fastForward);
        if (btnPrev) btnPrev.addEventListener("click", prevTrack);
        if (btnNext) btnNext.addEventListener("click", nextTrack);
        if (btnEject) btnEject.addEventListener("click", eject);

        if (volumeSlider) {
            volumeSlider.addEventListener("input", function () {
                audio.volume = this.value / 100;
            });
        }

        if (progressBar) {
            progressBar.addEventListener("input", function () {
                if (audio.duration) {
                    audio.currentTime = (this.value / 100) * audio.duration;
                }
            });
        }

        if (playlistToggle && playlistEl) {
            playlistToggle.addEventListener("click", function () {
                playlistEl.classList.toggle("open");
            });
        }

        // Audio events
        audio.addEventListener("timeupdate", function () {
            displayTime.textContent = formatTime(audio.currentTime);
            if (progressBar && audio.duration) {
                progressBar.value = (audio.currentTime / audio.duration) * 100;
            }
        });

        audio.addEventListener("ended", function () {
            nextTrack();
        });

        audio.addEventListener("loadedmetadata", function () {
            // Update duration in playlist
            if (playlistEl) {
                var items = playlistEl.querySelectorAll("li");
                if (items[currentIndex]) {
                    var dur = items[currentIndex].querySelector(".track-duration");
                    if (dur) dur.textContent = formatTime(audio.duration);
                }
            }
        });

        // --- Bootstrap ---
        loadTrack(0);
        buildPlaylist();
    }
})();
