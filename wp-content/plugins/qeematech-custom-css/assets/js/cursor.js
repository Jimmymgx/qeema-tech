(function () {
	if (!window.matchMedia('(pointer: fine)').matches) {
		return;
	}
	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
		return;
	}

	var label = document.createElement('div');
	label.className = 'qeema-cursor-label';

	function mount() {
		document.body.appendChild(label);
	}
	if (document.body) {
		mount();
	} else {
		document.addEventListener('DOMContentLoaded', mount);
	}

	window.addEventListener('mousemove', function (e) {
		label.style.left = e.clientX + 'px';
		label.style.top = e.clientY + 'px';
	});

	document.addEventListener('mouseover', function (e) {
		if (!e.target.closest) {
			return;
		}
		var labelTarget = e.target.closest('[data-cursor]');
		if (labelTarget) {
			label.textContent = labelTarget.getAttribute('data-cursor');
			label.classList.add('is-active');
		}
	});
	document.addEventListener('mouseout', function (e) {
		if (!e.target.closest) {
			return;
		}
		if (e.target.closest('[data-cursor]')) {
			label.classList.remove('is-active');
		}
	});
	document.addEventListener('mouseleave', function () {
		label.classList.remove('is-active');
	});
})();
