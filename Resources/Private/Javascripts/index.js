
let mfLIB;

if (typeof $$ === 'function') {
	mfLIB = $$;
} else if (typeof Gator === 'function') {
	mfLIB = Gator;
} else if (typeof jQuery === 'function') {
	mfLIB = jQuery;
}

if (Element && !Element.prototype.matches) {
	let proto = Element.prototype;
	proto.matches = proto.matchesSelector || proto.mozMatchesSelector || proto.msMatchesSelector || proto.oMatchesSelector || proto.webkitMatchesSelector;
}

class MF {
	constructor(parameter, context) {
		if (parameter instanceof MF) {
			return parameter;
		}

		if (typeof parameter === 'string') {
			parameter = (context || document).querySelectorAll(parameter);
		}

		if (parameter && parameter.nodeName) {
			parameter = [parameter];
		}
		this.nodes = [];
		this.nodes = this.slice(parameter);
	}

	get length () {
		return this.nodes.length;
	}

	slice (pseudo) {
		if (!pseudo || pseudo.length === 0 || typeof pseudo === 'string' || pseudo.toString() === '[object Function]') {
			return [];
		}
		return pseudo.length ? [].slice.call(pseudo.nodes || pseudo) : [pseudo];
	}

	each (callback) {
		this.nodes.forEach(callback.bind(this));
		return this;
	}

	trigger (eventName = 'change') {
		return this.each(node => {
			let eventTrigger;
			try {
				eventTrigger = new window.CustomEvent(eventName);
			} catch (error) {
				if (document.createEvent) {
					eventTrigger = document.createEvent('HTMLEvents');
					// eventTrigger = document.createEvent('CustomEvent');
					eventTrigger.initEvent(eventName, true, true);
				} else {
					eventTrigger = document.createEventObject();
					eventTrigger.eventType = eventName;
				}
				eventTrigger.eventName = eventName;
			}

			if (document.createEvent) {
				node.dispatchEvent(eventTrigger);
			} else {
				node.fireEvent('on' + eventTrigger.eventType, eventTrigger);
			}
		});
	}

	val (value) {
		if (typeof value === 'string' || typeof value === 'number') {
			return this.each(element => {
				element.value = value;
				new MF(element).trigger('change');
				new MF(element).trigger('input');
			});
		} else {
			return this.nodes[0].value;
		}
	}

	toggle (element) {
		element.parentNode.classList[element.value ? 'add' : 'remove']('mf-has-value');
	}

	spinner (callback = () => {}, time = 2000) {
		let className = 'mf-spinner';
		return this.each(element => {
			let spinner = document.createElement('div');
			let up = document.createElement('div');
			let down = document.createElement('div');
			let min = parseInt(element.min);
			let max = parseInt(element.max);
			let step = parseInt(element.step) || 1;

			if (isNaN(min)) {
				min = false;
			}
			if (isNaN(max)) {
				max = false;
			}

			function noValue() {
				if (!element.value && element.value != 0) {
					element.value = 0;
				}
			}
			spinner.className = className;
			up.className = className + '-up';
			down.className = className + '-down';
			spinner.appendChild(up);
			spinner.appendChild(down);
			up.addEventListener('click', () => {
				noValue();
				try {
					element.stepUp();
				} catch (error) {
					let value = parseInt(element.value) || 0;
					if (max !== false && value >= max) {
						value = max;
					} else {
						value += step;
					}
					element.value = value;
				} finally {
					new MF(element).trigger('input');
				}
			});
			down.addEventListener('click', () => {
				noValue();
				try {
					element.stepDown();
				} catch (error) {
					let value = parseInt(element.value) || 0;
					if (min !== false && value <= min) {
						value = min;
					} else {
						value -= step;
					}
					element.value = value;
				} finally {
					new MF(element).trigger('input');
				}
			});
			new MF(element).handle(callback, time);
			element.parentNode.appendChild(spinner);
		});
	}

	handle (callback = () => {}, time = 2000) {
		let timer;
		let _this = this;
		return this.each(element => {
			this.toggle(element);
			element.addEventListener('input', () => {
				this.toggle(element);
				clearTimeout(timer);
				timer = setTimeout(() => {callback(element)}, time);
			});
		});
	}

}
{
	const _MF = MF;
	MF = function(parameter, context) {
		return new _MF(parameter, context);
	};
	MF.prototype = _MF.prototype;
}

// Dropdown Extension
MF.prototype.dropdown = function (callback = () => {}, time = 500) {
	let timer;
	let prefix = 'mf-select-';
	let properties = {
		active: 'mf-has-value',
		open: 'mf-select-open',
		dropdown: prefix + 'dropdown',
		list: prefix + 'dropdown-menu',
		right: prefix + 'dropdown-right',
		button: prefix + 'btn',
		open: prefix + 'open',
		caret: '<i class="ico"></i>'
	};

	function getText(element) {
		let text = element.textContent || element.innerText;
		return text.trim();
	}

	return this.each(element => {
		let parent = element.parentNode;
		let children = new MF(element.children);
		let options = children.nodes;
		let active = options[0];
		let list = '';
		let button = '';
		let dropdown = document.createElement('div');
		let allDropdowns = new MF('.mf-select');
		let dropdownText;

		if (element.getAttribute('data-sort')) {
			options.sort((a, b) => getText(a) > getText(b));
		}

		createList();
		createButton();
		writeDropdown();
		addHandler();

		function createList() {
			let markup ='<div class="' + properties.list + '"><ul>';
			children.each(option => {
				let value = option.getAttribute('value');
				if (option.matches('[selected]')) {
					active = option;
				}
				if (value) {
					markup += '<li data-value="' + value + '">' + getText(option) + '</li>';
				}
			});

			markup += '</ul></div>';
			list = markup;
		}

		function createButton() {
			let text = getText(active) || '&nbsp;';
			let markup = '<button class="' + properties.button + '" type="button" aria-haspopup="true" aria-expanded="false"><span class="' +  properties.button + '-text">' + text + '</span> ' + properties.caret + '</button>';
			button = markup;

			if (text !== '&nbsp;') {
				parent.classList.add(properties.active);
			}
		}

		function writeDropdown() {
			let markup = '';
			dropdown.className = properties.dropdown;
			dropdown.innerHTML = button + list;
			element.parentNode.insertBefore(dropdown, element);
			element.style.display = 'none';
			dropdownText = dropdown.querySelector('.' + properties.button + '-text');
		}

		function closeAllDropdowns() {
			allDropdowns.each(element => {
				element.classList.remove(properties.open);
			});
		}

		function addHandler() {
			mfLIB(dropdown).on('click', 'button', event => {
				let isOpen = parent.classList.contains(properties.open);
				event.stopPropagation();
				closeAllDropdowns();
				if (!isOpen) {
					setTimeout(() => {
						parent.classList.add(properties.open);
					}, 10);
				}
			});
			mfLIB(element).on('change', function() {
				let value = this.value;
				dropdown.querySelector('li[data-value="' + value + '"]').click();
			});
			mfLIB(dropdown).on('click', 'li[data-value]', function(event) {
				event.stopPropagation();
				let value = this.getAttribute('data-value');
				let text = getText(this);
				element.value = value;
				dropdownText.innerHTML = text;
				parent.classList[text ? 'add' : 'remove'](properties.active);
				closeAllDropdowns();
				clearTimeout(timer);
				timer = setTimeout(() => { callback(element) }, time);
			});

			mfLIB(document).on('click', () => {
				parent.classList.remove(properties.open);
			});
		}
	});
};

export default MF;
