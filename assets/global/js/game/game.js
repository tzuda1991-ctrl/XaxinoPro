var investUrl;
var gameEndUrl;
var timerA = null;
var isRequest = false;
var winLossPopupFooterDisplay = true;

function upgradeGameUrl(u) {
    if (!u || typeof u !== 'string') return u;
    if (window.location.protocol === 'https:' && u.indexOf('http://') === 0) {
        return 'https://' + u.substring(7);
    }
    return u;
}

function hasDecimalPlace(value, x) {
    var pointIndex = value.indexOf('.');
    return pointIndex >= 0 && pointIndex < value.length - x;
}

function safeAudioPause() {
    try {
        if (typeof audioPause === 'function') {
            audioPause();
        } else if (typeof audio !== 'undefined' && audio && typeof audio.pause === 'function') {
            audio.pause();
        }
    } catch (e) {}
}

function gameFinish(data, timerRef) {
    if (timerRef) {
        clearInterval(timerRef);
    }
    if (timerA) {
        clearInterval(timerA);
    }
    setTimeout(function () {
        if (typeof success === 'function') {
            success(data);
        }
    }, 600);
}

function complete(data) {
    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        url: upgradeGameUrl(gameEndUrl),
        method: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            game_log_id: data.game_log_id,
            choose: data.choose || $('input[name=choose]').val()
        },
        success: function (res) {
            if (checkErrors(res) == true) {
                safeAudioPause();
                isRequest = false;
                return false;
            }
            if (res.bal !== undefined) {
                $(".bal, .user-balance, .balance").html(res.bal);
            } else if (res.balance !== undefined) {
                $(".bal, .user-balance, .balance").html(res.balance);
            }
            gameFinish(res, timerA);
            isRequest = false;
        },
        error: function (xhr) {
            safeAudioPause();
            isRequest = false;
            var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Connection error, please try again';
            notify("error", msg);
            if (typeof successOrError === 'function') {
                successOrError();
            }
        }
    });
}

function endGame(data) {
    safeAudioPause();
    complete(data);
}

function game(data) {
    isRequest = true;
    var payload = data;
    var csrf = $('meta[name="csrf-token"]').attr("content");
    if (typeof payload === 'string' && csrf && payload.indexOf('_token=') === -1) {
        payload += '&_token=' + encodeURIComponent(csrf);
    } else if (typeof payload === 'object' && payload !== null && csrf) {
        payload._token = csrf;
    }

    $.ajax({
        headers: {
            "X-CSRF-TOKEN": csrf,
        },
        url: upgradeGameUrl(investUrl),
        method: "POST",
        data: payload,
        success: function (res) {
            if (checkErrors(res) == true) {
                safeAudioPause();
                isRequest = false;
                return false;
            }
            if (res.balance !== undefined) {
                $(".bal, .user-balance, .balance").text(res.balance);
            } else if (res.bal !== undefined) {
                $(".bal, .user-balance, .balance").text(res.bal);
            }
            startGame(res);
        },
        error: function (xhr) {
            safeAudioPause();
            isRequest = false;
            var msg = (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) || 'Unable to start game. Please check your bet and balance.';
            notify("error", msg);
            if (typeof successOrError === 'function') {
                successOrError();
            }
        }
    });
}

function playGame(data, music) {
    try {
        if (typeof playAudio === 'function' && typeof audioAssetPath !== 'undefined') {
            playAudio(audioAssetPath, music);
        }
    } catch (e) {}
    if (typeof beforeProcess === 'function') {
        beforeProcess();
    }
    game(data);
}

function setPopup(data) {
    $(".win-loss-popup").addClass("active");
    $(".win-loss-popup__body").find("img").addClass("d-none");
    if (data.win_status == 0) {
        $(".win-loss-popup__body").find(".lose").removeClass("d-none");
        try { playAudio(audioAssetPath, "lose.wav"); } catch (e) {}
    } else {
        $(".win-loss-popup__body").find(".win").removeClass("d-none");
        try { playAudio(audioAssetPath, "win.wav"); } catch (e) {}
    }
    if (data.result && winLossPopupFooterDisplay) {
        $(".win-loss-popup__footer").removeClass("d-none");
        $(".win-loss-popup__footer").find(".data-result").text(data.result);
    } else {
        $(".win-loss-popup__footer").addClass("d-none");
    }
}

function checkErrors(data) {
    if (!data) return false;
    if (data.errors) {
        notify("error", data.errors);
        if (typeof successOrError === 'function') successOrError();
        return true;
    }
    if (data.error) {
        notify("error", data.error);
        if (typeof successOrError === 'function') successOrError();
        return true;
    }
    return false;
}
