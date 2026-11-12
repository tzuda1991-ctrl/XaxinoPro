// Multi-bet Roulette Manager
var selectedBets = [];

var rouletteGroups = {
    'oneToTw': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    'thrtToTf': [13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24],
    'twfToTs': [25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36],
    'oneToEt': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18],
    'nineteenTtsix': [19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36],
    'even': [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36],
    'odd': [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35],
    'red': [1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36],
    'black': [2, 4, 6, 8, 10, 11, 13, 15, 17, 20, 22, 24, 26, 28, 29, 31, 33, 35],
    'twByOne1': [3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36],
    'twByOne2': [2, 5, 8, 11, 14, 17, 20, 23, 26, 29, 32, 35],
    'twByOne3': [1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31, 34]
};

function syncRouletteUI() {
    $('table td').removeClass('selected');

    selectedBets.forEach(function (num) {
        $('table td').filter(function () {
            var txt = $(this).find('.number-item').text().trim();
            return txt === num.toString();
        }).addClass('selected');
    });

    for (var groupClass in rouletteGroups) {
        var nums = rouletteGroups[groupClass];
        var allIn = nums.length > 0 && nums.every(function (n) {
            return selectedBets.indexOf(n) > -1;
        });
        if (allIn) {
            $('.' + groupClass).addClass('selected');
        } else {
            $('.' + groupClass).removeClass('selected');
        }
    }

    if (selectedBets.length === 0) {
        $('input[name=choose]').val('');
    } else {
        $('input[name=choose]').val(selectedBets.join(','));
    }

    if (typeof updateBonusDisplay === 'function') {
        updateBonusDisplay();
    }
}

function toggleNumberBet(num) {
    num = parseInt(num, 10);
    if (isNaN(num) || num < 0 || num > 36) return;

    var idx = selectedBets.indexOf(num);
    if (idx > -1) {
        selectedBets.splice(idx, 1);
    } else {
        selectedBets.push(num);
    }
    syncRouletteUI();
}

function toggleGroupBet(groupClass) {
    var nums = rouletteGroups[groupClass];
    if (!nums || !nums.length) return;

    var allIn = nums.every(function (n) {
        return selectedBets.indexOf(n) > -1;
    });

    if (allIn) {
        // Deselect group numbers
        selectedBets = selectedBets.filter(function (n) {
            return nums.indexOf(n) === -1;
        });
    } else {
        // Add all group numbers
        nums.forEach(function (n) {
            if (selectedBets.indexOf(n) === -1) {
                selectedBets.push(n);
            }
        });
    }
    syncRouletteUI();
}

function clearAllBets() {
    selectedBets = [];
    syncRouletteUI();
}

$(function () {
    // Hover effects on desktop
    $(".oneToTw").hover(function () { $(".oneToTwEl").addClass("__select"); }, function () { $(".oneToTwEl").removeClass("__select"); });
    $(".thrtToTf").hover(function () { $(".thrtToTfEl").addClass("__select"); }, function () { $(".thrtToTfEl").removeClass("__select"); });
    $(".twfToTs").hover(function () { $(".twfToTsEl").addClass("__select"); }, function () { $(".twfToTsEl").removeClass("__select"); });
    $(".oneToEt").hover(function () { $(".oneToEtEl").addClass("__select"); }, function () { $(".oneToEtEl").removeClass("__select"); });
    $(".nineteenTtsix").hover(function () { $(".nineteenTtsixEl").addClass("__select"); }, function () { $(".nineteenTtsixEl").removeClass("__select"); });
    $(".even").hover(function () { $(".evenEl").addClass("__select"); }, function () { $(".evenEl").removeClass("__select"); });
    $(".odd").hover(function () { $(".oddEl").addClass("__select"); }, function () { $(".oddEl").removeClass("__select"); });
    $(".red").hover(function () { $(".redEl").addClass("__select"); }, function () { $(".redEl").removeClass("__select"); });
    $(".black").hover(function () { $(".blackEl").addClass("__select"); }, function () { $(".blackEl").removeClass("__select"); });

    // Handle clicks on individual number cells
    $('table td').on('click', function (e) {
        var $td = $(this);
        var $numItem = $td.find('.number-item');

        // Check if clicked cell is an individual number
        if ($numItem.length) {
            var numText = $numItem.text().trim();
            if (numText !== '') {
                toggleNumberBet(numText);
                if (typeof audioAssetPath !== 'undefined' && typeof playAudio === 'function') {
                    playAudio(audioAssetPath, "click.mp3");
                }
                return;
            }
        }

        // Check if clicked cell is a group
        for (var groupClass in rouletteGroups) {
            if ($td.hasClass(groupClass)) {
                toggleGroupBet(groupClass);
                if (typeof audioAssetPath !== 'undefined' && typeof playAudio === 'function') {
                    playAudio(audioAssetPath, "click.mp3");
                }
                return;
            }
        }
    });

    // Clear bets button
    $('.clear-bets-btn, .clear-btn').on('click', function (e) {
        e.preventDefault();
        clearAllBets();
        if (typeof audioAssetPath !== 'undefined' && typeof playAudio === 'function') {
            playAudio(audioAssetPath, "click.mp3");
        }
    });
});
