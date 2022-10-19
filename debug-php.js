(function () {
	window._debToggle = function (clsname) {
		var wrap = document.getElementsByClassName(clsname);

		for (var i = 0; i < wrap.length; i += 1) {
			wrap[i].style.display = (wrap[i].style.display === 'none' ? 'block' : 'none');
		}
	};

	window._debMark = function (cell) {
		cell.parentElement.classList.toggle('mark');
	};

	window._debToggleVar = function (clsname, full_class, recursive) {
		var elements = false,
			plus = document.getElementById('plus' + clsname),
			plus_alt = document.getElementById('plusalt' + clsname),
			plus_state = (plus.style.display === 'none' ? 'inline' : 'none'),
			el_state = (plus_state === 'none' ? 'table-row' : 'none');

		if (recursive) {
			elements = document.querySelectorAll('[class^="' + full_class + '"]');
		} else {
			elements = document.getElementsByClassName(clsname);
		}

		plus.style.display = plus_state;
		plus_alt.style.display = plus_state;

		for (var i = 0; i < elements.length; i += 1) {
			var sub_plus = elements[i].getElementsByClassName('plus');
			var sub_plus_alt = elements[i].getElementsByClassName('plus-alt');

			if (sub_plus && sub_plus.length) {
				sub_plus[0].style.display = (recursive ? 'none' : 'inline');
			}
			if (sub_plus_alt && sub_plus_alt.length) {
				sub_plus_alt[0].style.display = (recursive ? 'none' : 'inline');
			}

			if (elements[i].className === full_class || recursive) {
				elements[i].style.display = el_state;
			} else {
				elements[i].style.display = 'none';
			}
		}
	};
})();
