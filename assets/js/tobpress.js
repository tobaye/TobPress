/**
 * TobPress front-end behaviour — one vanilla-JS file, zero jQuery.
 *
 * Features: dark-mode toggle, mobile drawer menu, header search bar,
 * featured carousel, TOC scrollspy, reading progress, back-to-top,
 * content image lightbox, comment emoji panel, Ajax comment submit.
 * Every feature checks for its elements first, so pages without them
 * simply skip the code.
 */
(function () {
	'use strict';

	var root = document.documentElement;
	var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function onReady(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	/* ---------------- Dark mode toggle ---------------- */
	var themeToggle = document.querySelector('.tb-theme-toggle');
	if (themeToggle) {
		themeToggle.addEventListener('click', function () {
			var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
			root.setAttribute('data-theme', next);
			try {
				localStorage.setItem('tobpress-theme', next);
			} catch (e) {
				/* Storage unavailable (private mode etc.) — toggle still applies to this page. */
			}
		});
	}

	/* ---------------- Header search bar ---------------- */
	var searchToggle = document.querySelector('.tb-search-toggle');
	var searchBar = document.getElementById('tb-search-bar');
	if (searchToggle && searchBar) {
		searchToggle.addEventListener('click', function () {
			var open = searchBar.hasAttribute('hidden');
			if (open) {
				searchBar.removeAttribute('hidden');
				searchToggle.setAttribute('aria-expanded', 'true');
				var field = searchBar.querySelector('input[type="search"]');
				if (field) {
					field.focus();
				}
			} else {
				searchBar.setAttribute('hidden', '');
				searchToggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	/* ---------------- Mobile drawer menu ---------------- */
	var menuToggle = document.querySelector('.tb-menu-toggle');
	var mobileMenu = document.getElementById('tb-mobile-menu');
	var scrim = document.getElementById('tb-scrim');
	if (menuToggle && mobileMenu && scrim) {
		var menuOpen = false;

		function openMenu() {
			menuOpen = true;
			mobileMenu.removeAttribute('hidden');
			scrim.removeAttribute('hidden');
			// Next frame so the CSS transition can run.
			requestAnimationFrame(function () {
				mobileMenu.classList.add('is-open');
				scrim.classList.add('is-open');
			});
			menuToggle.setAttribute('aria-expanded', 'true');
			var first = mobileMenu.querySelector('a, button, input');
			if (first) {
				first.focus();
			}
		}

		function closeMenu() {
			if (!menuOpen) {
				return;
			}
			menuOpen = false;
			mobileMenu.classList.remove('is-open');
			scrim.classList.remove('is-open');
			menuToggle.setAttribute('aria-expanded', 'false');
			window.setTimeout(function () {
				mobileMenu.setAttribute('hidden', '');
				scrim.setAttribute('hidden', '');
			}, 260);
			menuToggle.focus();
		}

		menuToggle.addEventListener('click', function () {
			if (menuOpen) {
				closeMenu();
			} else {
				openMenu();
			}
		});
		scrim.addEventListener('click', closeMenu);
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') {
				closeMenu();
			}
		});
	}

	/* ---------------- Featured carousel ---------------- */
	var track = document.getElementById('tb-carousel-track');
	if (track) {
		var carousel = track.closest('.tb-carousel');
		var step = function () {
			return track.clientWidth;
		};
		var goTo = function (dir) {
			var max = track.scrollWidth - track.clientWidth - 4;
			var nextPos = track.scrollLeft + dir * step();
			if (nextPos > max) {
				nextPos = 0;
			}
			if (nextPos < 0) {
				nextPos = max;
			}
			track.scrollTo({ left: nextPos, behavior: reduceMotion ? 'auto' : 'smooth' });
		};
		var prevBtn = carousel ? carousel.querySelector('.tb-carousel-prev') : null;
		var nextBtn = carousel ? carousel.querySelector('.tb-carousel-next') : null;
		if (prevBtn) {
			prevBtn.addEventListener('click', function () { goTo(-1); });
		}
		if (nextBtn) {
			nextBtn.addEventListener('click', function () { goTo(1); });
		}
		if (!reduceMotion && track.children.length > 1) {
			var timer = window.setInterval(function () { goTo(1); }, 6000);
			var stop = function () {
				window.clearInterval(timer);
			};
			track.addEventListener('pointerdown', stop, { once: true });
			track.addEventListener('focusin', stop, { once: true });
			if (carousel) {
				carousel.addEventListener('pointerenter', function () {
					window.clearInterval(timer);
				});
				carousel.addEventListener('pointerleave', function () {
					stop();
					timer = window.setInterval(function () { goTo(1); }, 6000);
				});
			}
		}
	}

	/* ---------------- TOC scrollspy ---------------- */
	var tocLinks = document.querySelectorAll('.tb-toc a[href^="#"]');
	if (tocLinks.length && 'IntersectionObserver' in window) {
		var linkById = {};
		tocLinks.forEach(function (link) {
			linkById[link.getAttribute('href').slice(1)] = link;
		});
		var spy = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						tocLinks.forEach(function (l) { l.classList.remove('is-active'); });
						var active = linkById[entry.target.id];
						if (active) {
							active.classList.add('is-active');
						}
					}
				});
			},
			{ rootMargin: '-15% 0px -70% 0px' }
		);
		Object.keys(linkById).forEach(function (id) {
			var el = document.getElementById(id);
			if (el) {
				spy.observe(el);
			}
		});
	}

	/* ---------------- Reading progress + back-to-top (one rAF-throttled scroll handler) ---------------- */
	var progress = document.getElementById('tb-progress');
	var toTop = document.getElementById('tb-to-top');
	if (progress || toTop) {
		var ticking = false;
		var update = function () {
			ticking = false;
			var doc = document.documentElement;
			var total = doc.scrollHeight - window.innerHeight;
			var y = window.scrollY || doc.scrollTop;
			if (progress) {
				var ratio = total > 0 ? Math.min(1, Math.max(0, y / total)) : 0;
				progress.style.width = (ratio * 100).toFixed(2) + '%';
			}
			if (toTop) {
				toTop.classList.toggle('is-visible', y > 400);
			}
		};
		window.addEventListener(
			'scroll',
			function () {
				if (!ticking) {
					ticking = true;
					requestAnimationFrame(update);
				}
			},
			{ passive: true }
		);
		update();
	}
	if (toTop) {
		toTop.addEventListener('click', function () {
			window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
		});
	}

	/* ---------------- Image lightbox ---------------- */
	onReady(function () {
		var content = document.querySelector('.tb-content');
		if (!content) {
			return;
		}
		var dlg = document.createElement('dialog');
		dlg.className = 'tb-lightbox';
		dlg.setAttribute('aria-label', '图片预览');
		var big = document.createElement('img');
		big.alt = '';
		var closeBtn = document.createElement('button');
		closeBtn.type = 'button';
		closeBtn.className = 'tb-icon-btn tb-lightbox-close';
		closeBtn.setAttribute('aria-label', '关闭');
		closeBtn.textContent = '✕';
		dlg.appendChild(big);
		dlg.appendChild(closeBtn);
		document.body.appendChild(dlg);

		closeBtn.addEventListener('click', function () { dlg.close(); });
		dlg.addEventListener('click', function (e) {
			if (e.target === dlg) {
				dlg.close();
			}
		});

		content.addEventListener('click', function (e) {
			var img = e.target.closest('img');
			if (!img || img.closest('a')) {
				return;
			}
			big.src = img.currentSrc || img.src;
			big.alt = img.alt || '';
			if (typeof dlg.showModal === 'function') {
				dlg.showModal();
			}
		});
	});

	/* ---------------- Comment emoji panel ---------------- */
	onReady(function () {
		var textarea = document.getElementById('comment');
		if (!textarea || !textarea.closest('#commentform')) {
			return;
		}
		var emojis = ['😀', '😁', '😂', '🙂', '😉', '😍', '🤔', '😅', '👍', '👏', '🙏', '🎉', '❤️', '🔥', '💯', '✅', '⭐', '🌸', '☕', '📌', '💡', '⚠️', '🐛', '🚀', '🎈', '🍀', '📷'];
		var wrap = document.createElement('div');
		wrap.className = 'tb-emoji-wrap';
		var toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'tb-emoji-toggle';
		toggle.setAttribute('aria-expanded', 'false');
		toggle.textContent = '😀 表情';
		var panel = document.createElement('div');
		panel.className = 'tb-emoji-panel';
		emojis.forEach(function (emo) {
			var b = document.createElement('button');
			b.type = 'button';
			b.textContent = emo;
			b.setAttribute('aria-label', emo);
			b.addEventListener('click', function () {
				var start = textarea.selectionStart || textarea.value.length;
				var end = textarea.selectionEnd || textarea.value.length;
				textarea.value = textarea.value.slice(0, start) + emo + textarea.value.slice(end);
				textarea.focus();
				var pos = start + emo.length;
				textarea.setSelectionRange(pos, pos);
			});
			panel.appendChild(b);
		});
		wrap.appendChild(toggle);
		wrap.appendChild(panel);
		textarea.parentNode.insertBefore(wrap, textarea);

		toggle.addEventListener('click', function () {
			var open = panel.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		document.addEventListener('click', function (e) {
			if (!wrap.contains(e.target)) {
				panel.classList.remove('is-open');
				toggle.setAttribute('aria-expanded', 'false');
			}
		});
	});

	/* ---------------- Ajax comment submit (progressive enhancement) ---------------- */
	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form || form.id !== 'commentform' || form.dataset.apNative === '1') {
			return;
		}
		if (!window.fetch || !window.FormData || !window.DOMParser) {
			return; // Native submit.
		}
		e.preventDefault();

		var section = document.getElementById('comments');
		var submitBtn = form.querySelector('[type="submit"]');
		var notice = form.querySelector('.tb-comment-notice');
		if (!notice) {
			notice = document.createElement('p');
			notice.className = 'tb-comment-notice';
			notice.setAttribute('role', 'status');
			form.insertBefore(notice, form.firstChild);
		}
		notice.textContent = '正在提交…';
		if (submitBtn) {
			submitBtn.disabled = true;
		}

		var fallback = function () {
			notice.textContent = '';
			if (submitBtn) {
				submitBtn.disabled = false;
			}
			form.dataset.apNative = '1';
			form.submit();
		};

		window
			.fetch(form.action, {
				method: 'POST',
				body: new FormData(form),
				credentials: 'same-origin',
			})
			.then(function (res) {
				return res.text();
			})
			.then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var fresh = doc.getElementById('comments');
				if (!fresh || !section) {
					fallback();
					return;
				}
				section.replaceWith(fresh);
				var target = document.getElementById('comments');
				if (target) {
					target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
				}
			})
			.catch(function () {
				fallback();
			});
	});

	/* ---------------- Like button ---------------- */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-like]');
		if (!btn || !window.tobpressData) {
			return;
		}
		if (btn.classList.contains('is-liked')) {
			return;
		}
		var body = new URLSearchParams();
		body.set('action', 'tobpress_like');
		body.set('post_id', btn.getAttribute('data-like'));
		body.set('_ajax_nonce', window.tobpressData.nonce);
		fetch(window.tobpressData.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
			credentials: 'same-origin'
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (data) {
				if (!data || !data.success) {
					return;
				}
				btn.classList.add('is-liked');
				btn.setAttribute('aria-pressed', 'true');
				var svg = btn.querySelector('svg');
				if (svg) {
					svg.setAttribute('fill', 'currentColor');
				}
				var count = btn.querySelector('.tb-like-count');
				if (count && data.data && data.data.formatted) {
					count.textContent = data.data.formatted;
				}
			})
			.catch(function () {});
	});

	/* ---------------- Search shortcut (Ctrl/Cmd + K) ---------------- */
	document.addEventListener('keydown', function (e) {
		if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
			var t = document.querySelector('.tb-search-toggle');
			if (t) {
				e.preventDefault();
				t.click();
			}
		}
	});

	/* ---------------- Code block copy buttons ---------------- */
	document.querySelectorAll('.tb-content pre').forEach(function (pre) {
		if (pre.querySelector('.tb-copy-btn')) {
			return;
		}
		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'tb-copy-btn';
		btn.textContent = '复制';
		btn.addEventListener('click', function () {
			var code = pre.querySelector('code') || pre;
			var text = code.innerText;
			var done = function () {
				btn.textContent = '已复制';
				btn.classList.add('is-done');
				setTimeout(function () {
					btn.textContent = '复制';
					btn.classList.remove('is-done');
				}, 2000);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(done, done);
			} else {
				var ta = document.createElement('textarea');
				ta.value = text;
				document.body.appendChild(ta);
				ta.select();
				try {
					document.execCommand('copy');
				} catch (err) {}
				document.body.removeChild(ta);
				done();
			}
		});
		pre.appendChild(btn);
	});

	/* ---------------- Share row ---------------- */
	document.querySelectorAll('[data-share-copy]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var url = btn.getAttribute('data-share-copy');
			var done = function () {
				var old = btn.textContent;
				btn.textContent = '已复制';
				setTimeout(function () {
					btn.textContent = old;
				}, 2000);
			};
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(done, done);
			} else {
				done();
			}
		});
	});
	document.querySelectorAll('[data-share-wx]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var wrap = btn.closest('.tb-share-wx');
			var pop = wrap ? wrap.querySelector('.tb-share-qr') : null;
			if (!pop) {
				return;
			}
			var img = pop.querySelector('img');
			if (img && !img.getAttribute('src')) {
				img.setAttribute('src', img.getAttribute('data-qr-src'));
			}
			var show = pop.hasAttribute('hidden');
			if (show) {
				pop.removeAttribute('hidden');
			} else {
				pop.setAttribute('hidden', '');
			}
			btn.setAttribute('aria-expanded', show ? 'true' : 'false');
		});
	});

	/* ---------------- Reward dialog ---------------- */
	var rewardDlg = document.getElementById('tb-reward');
	if (rewardDlg && typeof rewardDlg.showModal === 'function') {
		document.querySelectorAll('[data-reward-open]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				rewardDlg.showModal();
			});
		});
		var rewardClose = rewardDlg.querySelector('[data-reward-close]');
		if (rewardClose) {
			rewardClose.addEventListener('click', function () {
				rewardDlg.close();
			});
		}
		rewardDlg.addEventListener('click', function (e) {
			if (e.target === rewardDlg) {
				rewardDlg.close();
			}
		});
	}

	/* ---------------- Comment likes ---------------- */
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.tb-clike-btn');
		if (!btn || btn.classList.contains('is-liked')) {
			return;
		}
		if (!window.tobpressData || !window.tobpressData.ajaxUrl) {
			return;
		}
		btn.classList.add('is-liked');
		btn.setAttribute('aria-pressed', 'true');
		var svg = btn.querySelector('svg');
		if (svg) {
			svg.setAttribute('fill', 'currentColor');
		}
		var body = new URLSearchParams();
		body.set('action', 'tobpress_comment_like');
		body.set('comment_id', btn.getAttribute('data-comment-id'));
		body.set('_ajax_nonce', window.tobpressData.nonce);
		fetch(window.tobpressData.ajaxUrl, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
			credentials: 'same-origin'
		})
			.then(function (res) {
				return res.json();
			})
			.then(function (data) {
				if (data && data.success && data.data) {
					var count = btn.querySelector('.tb-clike-count');
					if (count) {
						count.textContent = data.data.count;
					}
				}
			})
			.catch(function () {});
	});

	/* ---------------- Image fade-in (blur-up style) ---------------- */
	document.querySelectorAll('.tb-card-thumb img, .tb-featured img').forEach(function (img) {
		var mark = function () {
			img.classList.add('is-loaded');
		};
		if (img.complete && img.naturalWidth > 0) {
			mark();
		} else {
			img.addEventListener('load', mark);
		}
	});
})();
