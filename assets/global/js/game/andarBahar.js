var timerA = null;

function beforeProcess() {
    $('.result-card').empty();
    $('.showAndarCard').empty();
    $('.showBaharCard').empty();
    $("#playBtn").html('<i class="la la-gear fa-spin"></i> Processing...');
    $("#playBtn").attr("disabled", true);
}

function success(data) {
    setPopup(data);
    if (data.bal !== undefined) {
        $('.bal, .user-balance, .balance').html(data.bal);
    }
    $('#playBtn').html('Play Now');
    $('#playBtn').attr('disabled', false);
    $('.single-select').removeClass('active op');
    $('.single-select').find('img').removeClass('op');
    $('img').removeClass('op');
}

function successOrError() {
    $(".gmimg").removeClass("op");
    $("button[type=submit], #playBtn").html("Play Now");
    $("button[type=submit], #playBtn").removeAttr("disabled");
    $(".single-select").removeClass("active op");
    $(".single-select").find("img").removeClass("op");
}

function startGame(data) {
    $('#playBtn').html('<i class="la la-gear fa-spin"></i> Dealing Cards...');
    $('.result-card').html(`<img src="${imagePath}/${data.randomCard}.png" style="width:100%;border-radius:6px;">`);
    $('.ab-card').removeClass('d-none');

    var andarList = data.andarCards || [];
    var baharList = data.baharCards || [];
    var stepMs = 450;

    setTimeout(function () {
        $.each(andarList, function (index, value) {
            if (value !== undefined) {
                setTimeout(function () {
                    playAudio(audioAssetPath, "card.mp3");
                    $('.showAndarCard').append(`<img class="andar-image" src="${imagePath}/${value}.png">`);
                }, index * stepMs);
            }
            if (baharList[index] !== undefined) {
                setTimeout(function () {
                    playAudio(audioAssetPath, "card.mp3");
                    $('.showBaharCard').append(`<img class="bahar-image" src="${imagePath}/${baharList[index]}.png">`);
                }, index * stepMs + (stepMs / 2));
            }
        });

        var totalTime = Math.max(andarList.length, baharList.length) * stepMs + 500;
        setTimeout(function () {
            endGame(data);
            successOrError();
        }, totalTime);
    }, 350);
}

$(".andar").off("click").on("click", function () {
    $("input[name=choose]").val("andar");
    $(this).addClass("op active");
    $(".bahar").removeClass("op active");
    playAudio(audioAssetPath, "click.mp3");
});

$(".bahar").off("click").on("click", function () {
    $("input[name=choose]").val("bahar");
    $(this).addClass("op active");
    $(".andar").removeClass("op active");
    playAudio(audioAssetPath, "click.mp3");
});

$('#game').off('submit').on('submit', function (e) {
    e.preventDefault();
    var chosen = $("input[name=choose]").val();
    var inv = $("input[name=invest]").val();
    if (!chosen) {
        notify('error', 'Please select Andar or Bahar first!');
        return false;
    }
    if (!inv || parseFloat(inv) <= 0) {
        notify('error', 'Please enter or select a chip bet amount!');
        return false;
    }
    playGame($(this).serialize(), "card.mp3");
});
