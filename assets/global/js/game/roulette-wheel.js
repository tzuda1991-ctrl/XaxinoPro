var investUrl = window.investUrl || '';
var gameEndUrl = window.gameEndUrl || '';
var color = window.color || null;
var $thisResult = window.$thisResult || '';
var timer = 9000;
var red = [32, 19, 21, 25, 34, 27, 36, 30, 23, 5, 16, 1, 14, 9, 18, 7, 12, 3];

$(function () {
    var $inner = $(".inner"),
        $spin = $("#spin"),
        $reset = $("#reset"),
        $data = $(".data"),
        $mask = $(".mask"),
        maskDefault = "";

    if ($reset.length) {
        $reset.hide();
    }
    if ($mask.length) {
        $mask.text(maskDefault);
    }

    if ($spin.length) {
        $spin.on("click", function () {
            var randomNumber = Math.floor(Math.random() * 37),
                color = null;
            $inner.attr("data-spinto", randomNumber);
            $inner.find('input[value="' + randomNumber + '"]').prop("checked", true);
            $(this).hide();
            $reset.addClass("disabled").prop("disabled", true).show();
            $(".placeholder").remove();

            setTimeout(function () {
                $mask.text("");
            }, timer / 2);

            setTimeout(function () {
                $mask.text(maskDefault);
            }, timer + 500);

            setTimeout(function () {
                $reset.removeClass("disabled").prop("disabled", false);
                if ($.inArray(randomNumber, red) !== -1) {
                    color = "red";
                } else if (randomNumber === 0) {
                    color = "green";
                } else {
                    color = "black";
                }

                $(".result-number").text(randomNumber);
                $(".result-color").text(color);
                $(".result").css({ "background-color": "" + color + "" });
                $data.addClass("reveal");
                $inner.addClass("rest");

                $thisResult = '<li class="previous-result color-' + color + '"><span class="previous-number">' + randomNumber + '</span><span class="previous-color">' + color + "</span></li>";
                $(".previous-list").prepend($thisResult);
            }, timer);
        });
    }

    if ($reset.length) {
        $reset.on("click", function () {
            $inner.attr("data-spinto", "").removeClass("rest");
            $(this).hide();
            $spin.show();
            $data.removeClass("reveal");
        });
    }

    var myElement = document.getElementById("plate");
    if (myElement && typeof Hammer !== 'undefined') {
        try {
            var mc = new Hammer(myElement);
            mc.on("swipe", function (ev) {
                if ($reset.length && !$reset.hasClass("disabled")) {
                    if ($spin.is(":visible")) {
                        $spin.click();
                    } else {
                        $reset.click();
                    }
                }
            });
        } catch (e) {}
    }
});
