(function () {
	'use strict';
	if (typeof wp === 'undefined' || !wp.customize) {
		return;
	}
	var vars = {
		seopro_brand_primary: '--brand-primary',
		seopro_brand_hover: '--brand-primary-hover',
		seopro_brand_secondary: '--brand-secondary',
		seopro_bg_base: '--bg-base',
		seopro_text_primary: '--text-primary'
	};
	Object.keys(vars).forEach(function (setting) {
		wp.customize(setting, function (value) {
			value.bind(function (to) {
				document.documentElement.style.setProperty(vars[setting], to);
			});
		});
	});
})();
