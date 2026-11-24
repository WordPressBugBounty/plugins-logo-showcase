/*! Logo Showcase 5.0 front end: dependency-free slider. */
(function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var ICON_PREV = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>';
	var ICON_NEXT = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>';
	var ICON_PAUSE = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>';
	var ICON_PLAY = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>';

	function el(tag, cls, html) {
		var n = document.createElement(tag);
		if (cls) { n.className = cls; }
		if (html) { n.innerHTML = html; }
		return n;
	}

	function Slider(root) {
		this.root = root;
		this.cfg = {};
		try { this.cfg = JSON.parse(root.getAttribute('data-lsw')) || {}; } catch (e) { return; }
		this.viewport = root.querySelector('.lsw-viewport');
		this.track = root.querySelector('.lsw-track');
		if (!this.viewport || !this.track) { return; }
		this.n = this.track.children.length;
		this.pos = 0;           // logical index of the first visible logo
		this.userPaused = false;
		this.hoverPaused = false;
		this.offscreen = false;
		this.busy = false;
		this.timer = null;
		this.rtl = getComputedStyle(root).direction === 'rtl';
		this.autoplay = !!this.cfg.autoplay && !reduceMotion;
		this.build();
		this.bind();
		this.layout();
		root.classList.add('lsw-ready');
		this.schedule();
	}

	Slider.prototype.perView = function () {
		var w = window.innerWidth, pv = this.cfg.perView || {};
		var v = w >= 1024 ? pv.desktop : (w >= 640 ? pv.tablet : pv.mobile);
		return Math.max(1, parseInt(v, 10) || 1);
	};

	Slider.prototype.build = function () {
		var c = this.cfg, r = this.root, i18n = c.i18n || {}, self = this;
		var navTop = c.navPos !== 'center';
		this.nav = null; this.dotsBox = null; this.pauseBtn = null; this.dots = [];

		if (c.nav) {
			this.nav = el('div', 'lsw-nav lsw-nav-pos-' + c.navPos + ' lsw-nav-shape-' + c.navShape);
			this.prevBtn = el('button', 'lsw-btn lsw-prev', ICON_PREV);
			this.nextBtn = el('button', 'lsw-btn lsw-next', ICON_NEXT);
			this.prevBtn.type = this.nextBtn.type = 'button';
			this.prevBtn.setAttribute('aria-label', i18n.prev || 'Previous');
			this.nextBtn.setAttribute('aria-label', i18n.next || 'Next');
			this.nav.appendChild(this.prevBtn);
			this.nav.appendChild(this.nextBtn);
			r.appendChild(this.nav);
			if (navTop) { r.classList.add('lsw-nav-top'); }
			this.prevBtn.addEventListener('click', function () { self.userInteract(); self.prev(); });
			this.nextBtn.addEventListener('click', function () { self.userInteract(); self.next(); });
		}
		if (c.dots || this.autoplay) {
			this.dotsBox = el('div', 'lsw-dots lsw-dots-style-' + c.dotsStyle + ' lsw-dots-align-' + c.dotsAlign);
			this.dotsHolder = el('span', '');
			this.dotsHolder.style.display = 'contents';
			this.dotsBox.appendChild(this.dotsHolder);
			if (this.autoplay) {
				this.pauseBtn = el('button', 'lsw-pause', ICON_PAUSE);
				this.pauseBtn.type = 'button';
				this.pauseBtn.setAttribute('aria-label', i18n.pause || 'Pause');
				this.pauseBtn.addEventListener('click', function () {
					self.userPaused = !self.userPaused;
					self.syncPause();
					self.schedule();
				});
				this.dotsBox.appendChild(this.pauseBtn);
			}
			r.appendChild(this.dotsBox);
		}
	};

	Slider.prototype.syncPause = function () {
		if (!this.pauseBtn) { return; }
		var i18n = this.cfg.i18n || {};
		this.pauseBtn.innerHTML = this.userPaused ? ICON_PLAY : ICON_PAUSE;
		this.pauseBtn.setAttribute('aria-label', this.userPaused ? (i18n.play || 'Play') : (i18n.pause || 'Pause'));
		this.track.setAttribute('aria-live', this.userPaused ? 'polite' : 'off');
	};

	Slider.prototype.bind = function () {
		var self = this, root = this.root, vp = this.viewport;
		if (this.cfg.pauseHover) {
			root.addEventListener('mouseenter', function () { self.hoverPaused = true; });
			root.addEventListener('mouseleave', function () { self.hoverPaused = false; self.schedule(); });
		}
		root.addEventListener('focusin', function () { self.hoverPaused = true; });
		root.addEventListener('focusout', function () { self.hoverPaused = false; self.schedule(); });
		vp.addEventListener('scroll', function () { vp.scrollLeft = 0; });
		document.addEventListener('visibilitychange', function () { self.schedule(); });

		if ('IntersectionObserver' in window) {
			new IntersectionObserver(function (e) { self.offscreen = !e[0].isIntersecting; self.schedule(); }).observe(root);
		}
		if ('ResizeObserver' in window) {
			var raf = 0, last = root.clientWidth;
			new ResizeObserver(function () {
				if (root.clientWidth === last) { return; }
				last = root.clientWidth;
				cancelAnimationFrame(raf);
				raf = requestAnimationFrame(function () { self.layout(); });
			}).observe(root);
		} else {
			window.addEventListener('resize', function () { self.layout(); });
		}

		// Drag / swipe: the track follows the pointer, then snaps to the nearest slide.
		var d = null, moved = false;
		var sgn = function () { return self.rtl ? -1 : 1; };
		vp.addEventListener('dragstart', function (e) { e.preventDefault(); });
		vp.addEventListener('pointerdown', function (e) {
			if (self.static || self.busy || d) { return; }
			if (e.pointerType === 'mouse' && e.button !== 0) { return; }
			if (e.target.closest && e.target.closest('.lsw-nav,.lsw-dots')) { return; }
			d = { id: e.pointerId, x: e.clientX, y: e.clientY, on: false, base: self.loop ? 0 : -self.pos * self.dist, rotated: false, u: 0 };
			moved = false;
		});
		var setU = function (u) {
			d.u = u;
			self.track.style.transform = 'translate3d(' + (u * sgn()) + 'px,0,0)';
		};
		vp.addEventListener('pointermove', function (e) {
			if (!d || e.pointerId !== d.id) { return; }
			var dx = e.clientX - d.x, dy = e.clientY - d.y;
			if (!d.on) {
				if (Math.abs(dx) < 6 || Math.abs(dx) < Math.abs(dy)) { return; }
				d.on = true; moved = true;
				clearTimeout(self.timer);
				try { vp.setPointerCapture(e.pointerId); } catch (x) { /* ignore */ }
				self.root.classList.add('lsw-dragging');
				self.track.style.transition = 'none';
			}
			var u = d.base + dx * sgn();
			if (self.loop) {
				if (u > 0 && !d.rotated) {
					self.track.insertBefore(self.track.lastElementChild, self.track.firstElementChild);
					d.rotated = true; d.base -= self.dist; u -= self.dist;
				}
			} else {
				var lo = -Math.max(0, self.n - self.pv) * self.dist;
				if (u > 0) { u *= 0.35; } else if (u < lo) { u = lo + (u - lo) * 0.35; }
			}
			setU(u);
		});
		var finish = function (e, cancelled) {
			if (!d || (e && e.pointerId !== d.id)) { return; }
			var st = d; d = null;
			if (!st.on) { return; }
			try { vp.releasePointerCapture(st.id); } catch (x) { /* ignore */ }
			self.root.classList.remove('lsw-dragging');
			var du = st.u - st.base, thr = Math.min(80, self.dist * 0.2);
			var fwd = du < -thr, back = du > thr;
			if (cancelled) { fwd = back = false; }
			var sp = reduceMotion ? 0 : (self.cfg.speed || 450);
			var ease = sp ? 'transform ' + sp + 'ms ease' : 'none';
			if (!self.loop) {
				var max = Math.max(0, self.n - self.pv);
				self.pos = Math.max(0, Math.min(max, self.pos + (fwd ? 1 : back ? -1 : 0)));
				self.applyOffset(true);
				self.updateState(); self.schedule();
				return;
			}
			self.busy = true;
			var settle = function () { self.busy = false; self.updateState(); self.schedule(); };
			self.track.style.transition = ease;
			if (st.rotated) {
				if (back && !cancelled) {            // previous logo: finish the move to the left edge
					self.track.style.transform = 'translate3d(0,0,0)';
					self.pos = (self.pos - 1 + self.n) % self.n;
					setTimeout(settle, sp ? sp + 20 : 0);
				} else {                              // cancel: slide back, then undo the rotation
					self.track.style.transform = 'translate3d(' + (-self.dist * sgn()) + 'px,0,0)';
					setTimeout(function () {
						self.track.style.transition = 'none';
						self.track.appendChild(self.track.firstElementChild);
						self.track.style.transform = 'translate3d(0,0,0)';
						settle();
					}, sp ? sp + 20 : 0);
				}
			} else if (fwd) {                         // next logo
				self.track.style.transform = 'translate3d(' + (-self.dist * sgn()) + 'px,0,0)';
				var done = function () {
					self.track.style.transition = 'none';
					self.track.appendChild(self.track.firstElementChild);
					self.track.style.transform = 'translate3d(0,0,0)';
					self.pos = (self.pos + 1) % self.n;
					settle();
				};
				if (sp) { setTimeout(done, sp + 20); } else { done(); }
			} else {                                  // not far enough: snap back
				self.track.style.transform = 'translate3d(0,0,0)';
				setTimeout(settle, sp ? sp + 20 : 0);
			}
		};
		vp.addEventListener('pointerup', function (e) { finish(e, false); });
		vp.addEventListener('pointercancel', function (e) { finish(e, true); });
		vp.addEventListener('click', function (e) {
			if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
		}, true);
	};

	Slider.prototype.userInteract = function () { /* arrows keep autoplay running */ };

	Slider.prototype.layout = function () {
		var pv = this.perView(), c = this.cfg, items = this.track.children, m = parseInt(c.margin, 10) || 0;
		this.pv = pv;
		this.static = this.n <= pv;
		var w = this.viewport.clientWidth;
		var iw = (w - m * (pv - 1)) / pv;
		this.dist = iw + m;
		this.track.style.gap = m + 'px';
		this.track.style.transition = 'none';
		this.track.style.transform = 'translate3d(0,0,0)';
		for (var i = 0; i < items.length; i++) { items[i].style.width = iw + 'px'; }
		this.loop = !!c.loop && !this.static;
		if (!this.loop) { this.pos = Math.max(0, Math.min(this.pos, Math.max(0, this.n - pv))); this.applyOffset(false); }
		this.pages = Math.max(1, Math.ceil(this.n / pv));
		this.renderDots();
		if (this.nav) { this.nav.style.display = this.static ? 'none' : ''; }
		if (this.dotsBox) { this.dotsBox.style.display = (this.static && !this.autoplay) ? 'none' : ''; }
		this.updateState();
	};

	Slider.prototype.renderDots = function () {
		var self = this, i18n = this.cfg.i18n || {};
		if (!this.dotsHolder) { return; }
		this.dotsHolder.innerHTML = '';
		this.dots = [];
		if (!this.cfg.dots || this.static) { return; }
		for (var i = 0; i < this.pages; i++) {
			(function (i) {
				var b = el('button', 'lsw-dot');
				b.type = 'button';
				b.setAttribute('aria-label', (i18n.go || 'Go to slide %d').replace('%d', i + 1));
				b.addEventListener('click', function () { self.goPage(i); });
				self.dotsHolder.appendChild(b);
				self.dots.push(b);
			})(i);
		}
	};

	// Non-loop mode keeps this.pos as the track offset; loop mode rotates the DOM and keeps pos logical.
	Slider.prototype.applyOffset = function (animate) {
		var sp = this.cfg.speed || 450;
		this.track.style.transition = animate && !reduceMotion ? 'transform ' + sp + 'ms ease' : 'none';
		var x = -this.pos * this.dist * (this.rtl ? -1 : 1);
		this.track.style.transform = 'translate3d(' + x + 'px,0,0)';
	};

	Slider.prototype.next = function () {
		if (this.static || this.busy) { return; }
		var self = this;
		if (!this.loop) {
			this.pos = this.pos >= this.n - this.pv ? 0 : this.pos + 1;
			this.applyOffset(true);
			this.updateState();
			return;
		}
		this.busy = true;
		var sp = reduceMotion ? 0 : (this.cfg.speed || 450);
		this.track.style.transition = sp ? 'transform ' + sp + 'ms ease' : 'none';
		this.track.style.transform = 'translate3d(' + (-this.dist * (this.rtl ? -1 : 1)) + 'px,0,0)';
		var done = function () {
			self.track.style.transition = 'none';
			self.track.appendChild(self.track.firstElementChild);
			self.track.style.transform = 'translate3d(0,0,0)';
			self.pos = (self.pos + 1) % self.n;
			self.busy = false;
			self.updateState();
		};
		if (sp) { setTimeout(done, sp + 20); } else { done(); }
	};

	Slider.prototype.prev = function () {
		if (this.static || this.busy) { return; }
		var self = this;
		if (!this.loop) {
			this.pos = this.pos <= 0 ? this.n - this.pv : this.pos - 1;
			this.applyOffset(true);
			this.updateState();
			return;
		}
		this.busy = true;
		var sp = reduceMotion ? 0 : (this.cfg.speed || 450);
		this.track.style.transition = 'none';
		this.track.insertBefore(this.track.lastElementChild, this.track.firstElementChild);
		this.track.style.transform = 'translate3d(' + (-this.dist * (this.rtl ? -1 : 1)) + 'px,0,0)';
		void this.track.offsetWidth;
		this.track.style.transition = sp ? 'transform ' + sp + 'ms ease' : 'none';
		this.track.style.transform = 'translate3d(0,0,0)';
		this.pos = (this.pos - 1 + this.n) % this.n;
		setTimeout(function () { self.busy = false; self.updateState(); }, sp ? sp + 20 : 0);
	};

	Slider.prototype.goPage = function (page) {
		if (this.static) { return; }
		var target = Math.min(page * this.pv, this.n - 1);
		if (!this.loop) {
			this.pos = Math.min(target, Math.max(0, this.n - this.pv));
			this.applyOffset(true);
		} else {
			var guard = this.n;
			this.track.style.transition = 'none';
			while (this.pos !== target && guard-- > 0) {
				this.track.appendChild(this.track.firstElementChild);
				this.pos = (this.pos + 1) % this.n;
			}
		}
		this.updateState();
		this.schedule();
	};

	Slider.prototype.updateState = function () {
		var items = this.track.children, i, vis;
		var first = this.loop ? 0 : this.pos;
		for (i = 0; i < items.length; i++) {
			vis = i >= first && i < first + this.pv;
			if (vis) { items[i].removeAttribute('inert'); items[i].removeAttribute('aria-hidden'); }
			else { items[i].setAttribute('inert', ''); items[i].setAttribute('aria-hidden', 'true'); }
		}
		if (this.cfg.center && !this.static) {
			var mid = first + Math.floor((this.pv - 1) / 2);
			this.root.classList.add('lsw-center-on');
			for (i = 0; i < items.length; i++) { items[i].classList.toggle('is-center', i === mid); }
		}
		this.root.dispatchEvent(new CustomEvent('lsw:update', { bubbles: true, detail: { first: first, pv: this.pv, loop: this.loop } }));
		if (this.dots.length) {
			var active = Math.min(this.pages - 1, Math.ceil(this.pos / this.pv));
			for (i = 0; i < this.dots.length; i++) {
				if (i === active) { this.dots[i].setAttribute('aria-current', 'true'); }
				else { this.dots[i].removeAttribute('aria-current'); }
			}
		}
	};

	Slider.prototype.schedule = function () {
		var self = this;
		clearTimeout(this.timer);
		if (!this.autoplay || this.static || this.userPaused || this.hoverPaused || this.offscreen || document.hidden) { return; }
		this.timer = setTimeout(function () {
			self.next();
			self.schedule();
		}, Math.max(1000, this.cfg.delay || 3000));
	};

	function init(scope) {
		var list = (scope || document).querySelectorAll('.lsw-layout-slider[data-lsw]');
		for (var i = 0; i < list.length; i++) {
			if (!list[i].__lsw) { list[i].__lsw = new Slider(list[i]); }
		}
		document.dispatchEvent(new CustomEvent('lsw:init', { detail: { scope: scope || document } }));
	}

	window.LSW = window.LSW || {};
	window.LSW.init = init;
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', function () { init(); }); }
	else { init(); }
})();
