(function() {
	// jshint ignore:start
	var define;
	var exports;
	// jshint -W051
	delete define;
	delete exports;
	// jshint +W051
	//= require ../../../../../../../../../bower_components/microplugin/src/microplugin.js
	//= require ../../../../../../../../../bower_components/sifter/sifter.js
	//= require ../../../../../../../../../bower_components/selectize/dist/js/selectize.js
	// jshint ignore:end

	$.onReady.Selectize = function() {
		$('.styled').each(function() {
			var $select = $(this);
			var $input = null;
			var toggleClass = function(hasValue) {
				$select.toggleClass('has-value', hasValue);
			};
			var toggleFocusClass = function(isFocus) {
				$select.toggleClass('has-focus', isFocus);
			};
			var dataValues = $(this).data('values') || [];

			$select.selectize({
				plugins: isLive ? [] : ['drag_drop'],
				delimiter: ',',
				options: dataValues,
				sortField: $select.hasClass('select-sort') ? 'text' : false,
				onInitialize: function() {
					$input = $select.parent().find('.selectize-input input');
					var hasValue = $select.val() ? true : false;
					toggleClass(hasValue);
				},
				onFocus: function() {
					if ($select.is('input')) {
						toggleClass(true);
						toggleFocusClass(true);
					}
				},
				onBlur: function() {
					var hasValue = $select.val() ? true : ($input.val() ? true : false);
					var isFocus = $input.val() ? true : false;
					toggleClass(hasValue);
					toggleFocusClass(isFocus);
				},
				onChange: function(value) {
					var hasValue = value !== '' ? true : ($input.is(':focus') ? true : false);
					toggleClass(hasValue);

					var validationForm = $select.parents('form.form-validation');
					if (validationForm.length) {
						$select.valid();
					}
				},
				onDropdownOpen: function(dropdown) {
					toggleClass(true);
					toggleFocusClass(true);
				},
				onDropdownClose: function() {
					var hasValue = $select.val() ? true : ($input.val() ? true : false);
					var isFocus = $input.val() ? true : false;
					toggleClass(hasValue);
					toggleFocusClass(isFocus);
				}
			});
		});

		$('select.select-change').on('change', function() {
			window.location.href = this.value;
		});
	};
})();
