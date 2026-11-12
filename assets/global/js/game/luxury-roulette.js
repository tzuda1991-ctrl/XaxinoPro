/**
 * Luxury Canvas Roulette Wheel
 * Authentic European 37-Pocket Casino Roulette Wheel with 3D Turret & Precision Ball Physics
 */
(function(window) {
    'use strict';

    var POCKETS = [0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5, 24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26];
    var RED_NUMBERS = [32, 19, 21, 25, 34, 27, 36, 30, 23, 5, 16, 1, 14, 9, 18, 7, 12, 3];

    function LuxuryRouletteWheel(canvasId, options) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.options = options || {};
        this.onComplete = this.options.onComplete || null;

        this.wheelAngle = 0;
        this.ballAngle = 0;
        this.ballRadius = 0;
        this.state = 'IDLE'; // 'IDLE', 'SPINNING', 'SETTLED'

        this.winningNumber = null;
        this.winningPocketIndex = -1;
        this.spinStartTime = 0;
        this.spinDuration = 7500; // 7.5 seconds authentic casino spin
        this.spinCallback = null;

        this.targetWheelAngle = 0;
        this.startWheelAngle = 0;
        this.startBallAngle = 0;
        this.totalBallRevolutions = 0;

        this.animFrameId = null;
        this.glowPhase = 0;

        this.init();
    }

    LuxuryRouletteWheel.prototype.init = function() {
        var self = this;
        this.resize();
        window.addEventListener('resize', function() {
            self.resize();
        });

        // Start render loop
        this.lastTime = performance.now();
        function loop(now) {
            var dt = (now - self.lastTime) / 1000;
            self.lastTime = now;
            self.update(dt, now);
            self.draw();
            self.animFrameId = requestAnimationFrame(loop);
        }
        this.animFrameId = requestAnimationFrame(loop);
    };

    LuxuryRouletteWheel.prototype.resize = function() {
        var container = this.canvas.parentElement;
        var winW = window.innerWidth;
        // On mobile (< 576px), make wheel compact (270px-285px) so all controls and board fit on screen without excessive scrolling
        var maxSize = winW < 576 ? Math.min(winW - 24, 280) : (winW < 992 ? 310 : 340);
        var rect = container ? container.getBoundingClientRect() : { width: maxSize, height: maxSize };
        var size = Math.min(rect.width || maxSize, maxSize);
        if (size < 240) size = 240;

        var dpr = window.devicePixelRatio || 1;
        this.canvas.width = size * dpr;
        this.canvas.height = size * dpr;
        this.canvas.style.width = size + 'px';
        this.canvas.style.height = size + 'px';

        this.size = size;
        this.dpr = dpr;
        this.centerX = (size * dpr) / 2;
        this.centerY = (size * dpr) / 2;
        this.radius = (size * dpr) / 2 - (5 * dpr);
    };

    LuxuryRouletteWheel.prototype.spinTo = function(winningNumber, callback) {
        this.winningNumber = parseInt(winningNumber, 10);
        this.winningPocketIndex = POCKETS.indexOf(this.winningNumber);
        if (this.winningPocketIndex === -1) {
            this.winningPocketIndex = 0;
            this.winningNumber = 0;
        }

        this.spinCallback = callback;
        this.state = 'SPINNING';
        this.spinStartTime = performance.now();
        this.startWheelAngle = this.wheelAngle;

        // Number of wheel spins: 2 to 3 full rotations
        var wheelSpins = 2.5 * Math.PI * 2;
        this.targetWheelAngle = this.startWheelAngle + wheelSpins;

        // Pocket angle on wheel
        var segmentArc = (2 * Math.PI) / 37;
        var pocketCenterOffset = (this.winningPocketIndex + 0.5) * segmentArc;

        // Ball starts on outer track, spins counter-clockwise at high speed
        this.startBallAngle = this.ballAngle;
        // Ball makes 6 to 8 full reverse spins
        var ballRevolutions = 7.0 * (2 * Math.PI);
        // Final ball angle must match the pocket center exactly:
        // finalBallAngle = targetWheelAngle + pocketCenterOffset
        var finalAngle = this.targetWheelAngle + pocketCenterOffset;
        // Ensure ball moves counter-clockwise (decreasing angle or increasing reverse)
        while (finalAngle < this.startBallAngle + ballRevolutions) {
            finalAngle += 2 * Math.PI;
        }
        this.targetBallAngle = finalAngle;
    };

    LuxuryRouletteWheel.prototype.reset = function() {
        this.state = 'IDLE';
        this.winningNumber = null;
        this.winningPocketIndex = -1;
    };

    LuxuryRouletteWheel.prototype.update = function(dt, now) {
        this.glowPhase += dt * 5;

        if (this.state === 'IDLE') {
            // Gentle ambient glide
            this.wheelAngle += 0.2 * dt;
            // Ball rests in center or gently rolls
            this.ballRadius = this.radius * 0.53;
            var segmentArc = (2 * Math.PI) / 37;
            var targetIdx = (this.winningPocketIndex >= 0) ? this.winningPocketIndex : 0;
            this.ballAngle = this.wheelAngle + (targetIdx + 0.5) * segmentArc;
        } else if (this.state === 'SPINNING') {
            var elapsed = now - this.spinStartTime;
            var progress = Math.min(elapsed / this.spinDuration, 1.0);

            // Easing functions
            // Wheel deceleration
            var wheelEase = 1 - Math.pow(1 - progress, 2.5);
            this.wheelAngle = this.startWheelAngle + (this.targetWheelAngle - this.startWheelAngle) * wheelEase;

            // Ball deceleration
            var ballEase = 1 - Math.pow(1 - progress, 3.0);
            this.ballAngle = this.startBallAngle + (this.targetBallAngle - this.startBallAngle) * ballEase;

            // Ball radial descent:
            // 0 -> 0.65: Outer ball track (0.83 * R)
            // 0.65 -> 0.90: Spirals down into pocket ring (0.83 -> 0.53)
            // 0.90 -> 1.00: Miniature hops/bounces across frets into target pocket
            var outerTrackR = this.radius * 0.83;
            var pocketR = this.radius * 0.53;

            if (progress < 0.65) {
                this.ballRadius = outerTrackR;
            } else if (progress < 0.90) {
                var pSub = (progress - 0.65) / 0.25;
                var rEase = Math.sin(pSub * Math.PI / 2);
                this.ballRadius = outerTrackR - (outerTrackR - pocketR) * rEase;
            } else {
                var pSub = (progress - 0.90) / 0.10;
                // Realistic dampening bounces over frets
                var bounce = Math.sin(pSub * Math.PI * 3) * (1 - pSub) * (8 * this.dpr);
                this.ballRadius = pocketR + bounce;
            }

            if (progress >= 1.0) {
                this.state = 'SETTLED';
                // Lock ball to exact pocket center
                var segmentArc = (2 * Math.PI) / 37;
                this.ballAngle = this.wheelAngle + (this.winningPocketIndex + 0.5) * segmentArc;
                this.ballRadius = pocketR;

                if (this.spinCallback) {
                    var cb = this.spinCallback;
                    this.spinCallback = null;
                    cb(this.winningNumber);
                }
            }
        } else if (this.state === 'SETTLED') {
            // Very slow final glide while ball remains 100% locked in the pocket
            this.wheelAngle += 0.05 * dt;
            var segmentArc = (2 * Math.PI) / 37;
            this.ballAngle = this.wheelAngle + (this.winningPocketIndex + 0.5) * segmentArc;
            this.ballRadius = this.radius * 0.53;
        }
    };

    LuxuryRouletteWheel.prototype.draw = function() {
        var ctx = this.ctx;
        var cx = this.centerX;
        var cy = this.centerY;
        var R = this.radius;
        var dpr = this.dpr;

        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // 1. OUTER MAHOGANY CASINO RIM
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, R, 0, Math.PI * 2);
        var woodGrad = ctx.createRadialGradient(cx, cy, R * 0.85, cx, cy, R);
        woodGrad.addColorStop(0, '#2e1207');
        woodGrad.addColorStop(0.5, '#421a0a');
        woodGrad.addColorStop(0.85, '#2e1207');
        woodGrad.addColorStop(1, '#150803');
        ctx.fillStyle = woodGrad;
        ctx.shadowColor = 'rgba(0,0,0,0.7)';
        ctx.shadowBlur = 12 * dpr;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 4 * dpr;
        ctx.fill();
        ctx.restore();

        // Brass rim inner and outer rings
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, R, 0, Math.PI * 2);
        ctx.strokeStyle = '#c5a059';
        ctx.lineWidth = 2.5 * dpr;
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(cx, cy, R * 0.89, 0, Math.PI * 2);
        var brassEdge = ctx.createRadialGradient(cx, cy, R * 0.88, cx, cy, R * 0.90);
        brassEdge.addColorStop(0, '#7a5a1f');
        brassEdge.addColorStop(0.5, '#ffd700');
        brassEdge.addColorStop(1, '#8a6421');
        ctx.strokeStyle = brassEdge;
        ctx.lineWidth = 2 * dpr;
        ctx.stroke();

        // 16 Decorative brass rivets on mahogany rim
        for (var riv = 0; riv < 16; riv++) {
            var rivAngle = (riv * Math.PI * 2) / 16;
            var rivR = R * 0.94;
            var rx = cx + rivR * Math.cos(rivAngle);
            var ry = cy + rivR * Math.sin(rivAngle);

            ctx.beginPath();
            ctx.arc(rx, ry, 2.5 * dpr, 0, Math.PI * 2);
            ctx.fillStyle = '#ffd700';
            ctx.shadowColor = 'rgba(0,0,0,0.5)';
            ctx.shadowBlur = 2 * dpr;
            ctx.fill();
        }
        ctx.restore();

        // 2. BALL TRACK (BRUSHED METALLIC GUTTER)
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, R * 0.89, 0, Math.PI * 2);
        ctx.arc(cx, cy, R * 0.77, 0, Math.PI * 2, true);
        var trackGrad = ctx.createRadialGradient(cx, cy, R * 0.77, cx, cy, R * 0.89);
        trackGrad.addColorStop(0, '#15171c');
        trackGrad.addColorStop(0.5, '#242730');
        trackGrad.addColorStop(0.9, '#181a20');
        trackGrad.addColorStop(1, '#0e0f12');
        ctx.fillStyle = trackGrad;
        ctx.fill();

        // 8 Silver Diamond Deflectors on Ball Track
        for (var def = 0; def < 8; def++) {
            var defAngle = (def * Math.PI * 2) / 8 + 0.2;
            var defR = R * 0.83;
            var dx = cx + defR * Math.cos(defAngle);
            var dy = cy + defR * Math.sin(defAngle);

            ctx.save();
            ctx.translate(dx, dy);
            ctx.rotate(defAngle);
            ctx.beginPath();
            ctx.moveTo(-2 * dpr, -4 * dpr);
            ctx.lineTo(2 * dpr, 0);
            ctx.lineTo(-2 * dpr, 4 * dpr);
            ctx.lineTo(-4 * dpr, 0);
            ctx.closePath();
            ctx.fillStyle = '#e0e0e0';
            ctx.shadowColor = '#000';
            ctx.shadowBlur = 2 * dpr;
            ctx.fill();
            ctx.restore();
        }
        ctx.restore();

        // 3. POCKETS RING (37 EUROPEAN NUMBERS)
        var arc = (2 * Math.PI) / 37;
        var rOuterPocket = R * 0.77;
        var rInnerPocket = R * 0.44;

        ctx.save();
        for (var i = 0; i < 37; i++) {
            var num = POCKETS[i];
            var startA = this.wheelAngle + i * arc;
            var endA = startA + arc;
            var midA = startA + arc / 2;

            // Pocket color
            var isRed = RED_NUMBERS.indexOf(num) !== -1;
            var isZero = (num === 0);

            ctx.beginPath();
            ctx.arc(cx, cy, rOuterPocket, startA, endA, false);
            ctx.arc(cx, cy, rInnerPocket, endA, startA, true);
            ctx.closePath();

            if (isZero) {
                var gGrad = ctx.createRadialGradient(cx, cy, rInnerPocket, cx, cy, rOuterPocket);
                gGrad.addColorStop(0, '#044c1d');
                gGrad.addColorStop(0.7, '#088034');
                gGrad.addColorStop(1, '#055c23');
                ctx.fillStyle = gGrad;
            } else if (isRed) {
                var rGrad = ctx.createRadialGradient(cx, cy, rInnerPocket, cx, cy, rOuterPocket);
                rGrad.addColorStop(0, '#821313');
                rGrad.addColorStop(0.7, '#c62828');
                rGrad.addColorStop(1, '#961919');
                ctx.fillStyle = rGrad;
            } else {
                var bGrad = ctx.createRadialGradient(cx, cy, rInnerPocket, cx, cy, rOuterPocket);
                bGrad.addColorStop(0, '#111111');
                bGrad.addColorStop(0.7, '#242424');
                bGrad.addColorStop(1, '#151515');
                ctx.fillStyle = bGrad;
            }
            ctx.fill();

            // Highlight Winning Pocket with Golden Aura
            if (this.state === 'SETTLED' && i === this.winningPocketIndex) {
                ctx.save();
                var pulse = 0.5 + 0.5 * Math.sin(this.glowPhase);
                ctx.fillStyle = 'rgba(255, 215, 0, ' + (0.35 + 0.35 * pulse) + ')';
                ctx.fill();
                ctx.strokeStyle = '#ffd700';
                ctx.lineWidth = 3 * dpr;
                ctx.stroke();
                ctx.restore();
            }

            // Metallic Brass/Silver Fret Separator
            ctx.beginPath();
            ctx.moveTo(cx + rInnerPocket * Math.cos(startA), cy + rInnerPocket * Math.sin(startA));
            ctx.lineTo(cx + rOuterPocket * Math.cos(startA), cy + rOuterPocket * Math.sin(startA));
            ctx.strokeStyle = '#d4af37';
            ctx.lineWidth = 1.2 * dpr;
            ctx.stroke();

            // Draw Number Label
            ctx.save();
            var textR = R * 0.64;
            var tx = cx + textR * Math.cos(midA);
            var ty = cy + textR * Math.sin(midA);
            ctx.translate(tx, ty);
            // Rotate text to face inward towards the center spindle
            ctx.rotate(midA + Math.PI / 2);

            ctx.font = 'bold ' + Math.round(11.5 * dpr) + 'px "Montserrat", "Segoe UI", Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#ffffff';
            ctx.shadowColor = 'rgba(0,0,0,0.8)';
            ctx.shadowBlur = 3 * dpr;
            ctx.fillText(num.toString(), 0, 0);
            ctx.restore();
        }
        ctx.restore();

        // 4. INNER SLOPED BOWL CONE (3D BRONZE/GOLD GRADIENT)
        var rTurretOuter = R * 0.44;
        var rTurretHub = R * 0.28;

        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, rTurretOuter, 0, Math.PI * 2);
        var bowlGrad = ctx.createRadialGradient(cx, cy, rTurretHub, cx, cy, rTurretOuter);
        bowlGrad.addColorStop(0, '#5e4315');
        bowlGrad.addColorStop(0.3, '#8f6826');
        bowlGrad.addColorStop(0.7, '#d4a84d');
        bowlGrad.addColorStop(1, '#4a330e');
        ctx.fillStyle = bowlGrad;
        ctx.fill();

        ctx.strokeStyle = '#d4af37';
        ctx.lineWidth = 2 * dpr;
        ctx.stroke();
        ctx.restore();

        // 5. THE WHITE 3D PEARL BALL
        if (this.ballRadius > 0) {
            var bx = cx + this.ballRadius * Math.cos(this.ballAngle);
            var by = cy + this.ballRadius * Math.sin(this.ballAngle);
            var ballSize = 6.8 * dpr;

            ctx.save();
            // Drop shadow
            ctx.beginPath();
            ctx.arc(bx + 1.5 * dpr, by + 2 * dpr, ballSize * 0.95, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(0, 0, 0, 0.55)';
            ctx.fill();

            // Ball 3D pearl sphere
            ctx.beginPath();
            ctx.arc(bx, by, ballSize, 0, Math.PI * 2);
            var bGrad = ctx.createRadialGradient(
                bx - ballSize * 0.35, by - ballSize * 0.35, ballSize * 0.1,
                bx, by, ballSize
            );
            bGrad.addColorStop(0, '#ffffff');
            bGrad.addColorStop(0.3, '#f5f5f5');
            bGrad.addColorStop(0.7, '#d9d9d9');
            bGrad.addColorStop(1, '#999999');
            ctx.fillStyle = bGrad;
            ctx.shadowColor = 'rgba(255, 255, 255, 0.4)';
            ctx.shadowBlur = 4 * dpr;
            ctx.fill();
            ctx.restore();
        }

        // 6. CENTER BRASS TURRET & 4-SPOKE CROSS SPINNER
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(this.wheelAngle); // Rotates with the wheel

        // Outer Turret Rim
        ctx.beginPath();
        ctx.arc(0, 0, rTurretHub, 0, Math.PI * 2);
        var hubGrad = ctx.createRadialGradient(0, 0, 0, 0, 0, rTurretHub);
        hubGrad.addColorStop(0, '#ffd700');
        hubGrad.addColorStop(0.4, '#c59a35');
        hubGrad.addColorStop(0.8, '#825f17');
        hubGrad.addColorStop(1, '#ffd700');
        ctx.fillStyle = hubGrad;
        ctx.shadowColor = 'rgba(0,0,0,0.6)';
        ctx.shadowBlur = 8 * dpr;
        ctx.fill();
        ctx.strokeStyle = '#ffd700';
        ctx.lineWidth = 2 * dpr;
        ctx.stroke();

        // 4 Chrome/Brass Spinner Handles (Monte Carlo Turret)
        for (var h = 0; h < 4; h++) {
            var hAngle = (h * Math.PI) / 2;
            ctx.save();
            ctx.rotate(hAngle);

            // Handle shaft
            ctx.beginPath();
            ctx.moveTo(-2 * dpr, 0);
            ctx.lineTo(-1.2 * dpr, rTurretHub * 0.88);
            ctx.lineTo(1.2 * dpr, rTurretHub * 0.88);
            ctx.lineTo(2 * dpr, 0);
            ctx.closePath();
            var handleGrad = ctx.createLinearGradient(-2 * dpr, 0, 2 * dpr, 0);
            handleGrad.addColorStop(0, '#8c7028');
            handleGrad.addColorStop(0.5, '#fff4cc');
            handleGrad.addColorStop(1, '#8c7028');
            ctx.fillStyle = handleGrad;
            ctx.fill();

            // Handle knob ball
            ctx.beginPath();
            ctx.arc(0, rTurretHub * 0.88, 3.8 * dpr, 0, Math.PI * 2);
            var knobGrad = ctx.createRadialGradient(-1 * dpr, rTurretHub * 0.88 - 1 * dpr, 0.5 * dpr, 0, rTurretHub * 0.88, 3.8 * dpr);
            knobGrad.addColorStop(0, '#ffffff');
            knobGrad.addColorStop(0.4, '#ffd700');
            knobGrad.addColorStop(1, '#825f17');
            ctx.fillStyle = knobGrad;
            ctx.shadowColor = 'rgba(0,0,0,0.4)';
            ctx.shadowBlur = 2 * dpr;
            ctx.fill();
            ctx.restore();
        }

        // Center Dome & Gold/Ruby Cap
        ctx.beginPath();
        ctx.arc(0, 0, rTurretHub * 0.45, 0, Math.PI * 2);
        var domeGrad = ctx.createRadialGradient(-3 * dpr, -3 * dpr, 1 * dpr, 0, 0, rTurretHub * 0.45);
        domeGrad.addColorStop(0, '#ffffff');
        domeGrad.addColorStop(0.2, '#ffd700');
        domeGrad.addColorStop(0.7, '#966d1b');
        domeGrad.addColorStop(1, '#4a330a');
        ctx.fillStyle = domeGrad;
        ctx.fill();
        ctx.strokeStyle = '#ffd700';
        ctx.lineWidth = 1.5 * dpr;
        ctx.stroke();

        // Center Jewel Pin
        ctx.beginPath();
        ctx.arc(0, 0, rTurretHub * 0.18, 0, Math.PI * 2);
        var jewelGrad = ctx.createRadialGradient(-1 * dpr, -1 * dpr, 0.5 * dpr, 0, 0, rTurretHub * 0.18);
        jewelGrad.addColorStop(0, '#ff8080');
        jewelGrad.addColorStop(0.5, '#c62828');
        jewelGrad.addColorStop(1, '#520606');
        ctx.fillStyle = jewelGrad;
        ctx.fill();
        ctx.restore();

        // 7. CENTER RESULT BADGE (WHEN RESULT IS REVEALED)
        if (this.state === 'SETTLED' && this.winningNumber !== null) {
            var isRed = RED_NUMBERS.indexOf(this.winningNumber) !== -1;
            var isZero = (this.winningNumber === 0);
            var badgeColor = isZero ? '#088034' : (isRed ? '#c62828' : '#111111');
            var badgeText = this.winningNumber.toString();

            ctx.save();
            ctx.translate(cx, cy);

            // Circular badge
            ctx.beginPath();
            ctx.arc(0, 0, rTurretHub * 0.65, 0, Math.PI * 2);
            ctx.fillStyle = badgeColor;
            ctx.shadowColor = '#ffd700';
            ctx.shadowBlur = 12 * dpr;
            ctx.fill();
            ctx.strokeStyle = '#ffd700';
            ctx.lineWidth = 3 * dpr;
            ctx.stroke();

            // Winning Number Text
            ctx.font = 'bold ' + Math.round(20 * dpr) + 'px "Montserrat", Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#ffffff';
            ctx.shadowColor = 'rgba(0,0,0,0.8)';
            ctx.shadowBlur = 4 * dpr;
            ctx.fillText(badgeText, 0, 0);
            ctx.restore();
        }
    };

    window.LuxuryRouletteWheel = LuxuryRouletteWheel;
})(window);
