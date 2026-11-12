let audioSound = 'true';
let isRequest = false;
let audioAssetPath = '';
var audio = {
    pause: function () {},
    play: function () { return Promise.resolve(); }
};

function playAudio(audioAssetPath, filename) {
    try {
        audio = new Audio(`${audioAssetPath}/${filename}`);
        if (audioSound == 'true') {
            audioOn();
        }
    } catch (e) {}
}

if (localStorage.getItem('audioStatus') !== null) {
    audioSound = localStorage.getItem('audioStatus');
    if (audioSound == 'false') {
        $('.audioBtn').html(`<i class="fas fa-volume-mute"></i>`);
    }
} else {
    localStorage.setItem('audioStatus', 'true');
}

function audioPause() {
    if (typeof audio !== 'undefined' && audio && typeof audio.pause === 'function') {
        try {
            audio.pause();
        } catch (e) {}
    }
}

function audioOn() {
    if (typeof audio !== 'undefined' && audio && typeof audio.play === 'function') {
        try {
            var playPromise = audio.play();
            if (playPromise !== undefined && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {});
            }
        } catch (e) {}
    }
}

$('.audioBtn').on('click', function () {
    audioSound = (audioSound == 'true') ? 'false' : 'true';
    localStorage.setItem('audioStatus', audioSound);
    if (audioSound == 'true') {
        $(this).html(`<i class="fas fa-volume-up"></i>`);
        if (isRequest) {
            audioOn();
        }
    } else {
        $(this).html(`<i class="fas fa-volume-mute"></i>`);
        if (isRequest) {
            audioPause();
        }
    }
});
