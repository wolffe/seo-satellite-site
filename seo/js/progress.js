/**
 * Progress constructor.
 */
function Progress(element, options) {
	element = element instanceof HTMLElement ? element : document.querySelector(element)
	this.options = options = Object.assign({
		progress: 0,
		roundStroke: true,
		zeroTick: false,
		label: '<span class="progress__text"></span><span class="progress__percent">% optimised</span>',
		path: 'M48.979 161.998C25.083 147.563 9.093 121.339 9.093 91.409c0-45.481 36.925-82.407 82.407-82.407s82.407 36.926 82.407 82.407c0 29.767-15.817 55.869-39.497 70.353',
		viewBox: '0 0 183 183',
		indicatorOffset: [30, 30],
		decimals: 0
	}, options || {})
	element.dataset.options = JSON.stringify(this.options)

	// Create DOM.
	this.dom = {}
	this.dom.root = element
	this.dom.track = element.querySelector('.progress__track')
	if (!this.dom.track) {
		this.dom.track = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
		this.dom.track.setAttribute('viewBox', this.options.viewBox)
		if (this.options.roundStroke) this.dom.track.setAttribute('stroke-linecap', 'round')
		var path = document.createElementNS(this.dom.track.namespaceURI, 'path')
		path.setAttribute('d', this.options.path)
		this.dom.track.appendChild(path)
		this.dom.root.appendChild(this.dom.track)
	}
	this.dom.progress = element.querySelector('.progress__progress')
	if (!this.dom.progress) {
		this.dom.progress = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
		this.dom.progress.setAttribute('viewBox', this.options.viewBox)
		if (this.options.roundStroke) this.dom.progress.setAttribute('stroke-linecap', 'round')
		this.dom.root.appendChild(this.dom.progress)
	}
	this.dom.progressPath = this.dom.progress.querySelector('path')
	if (!this.dom.progressPath) {
		this.dom.progressPath = document.createElementNS(this.dom.progress.namespaceURI, 'path')
		this.dom.progressPath.setAttribute('d', this.options.path)
		this.dom.progress.appendChild(this.dom.progressPath)
	}
	this.dom.indicator = element.querySelector('.progress__indicator')
	if (!this.dom.indicator) {
		this.dom.indicator = document.createElement('div')
		this.dom.indicator.classList.add('progress__indicator')
		element.appendChild(this.dom.indicator)
	}
	this.dom.indicatorHand = element.querySelector('.progress__indicator-hand')
	if (!this.dom.indicatorHand) {
		this.dom.indicatorHand = document.createElement('div')
		this.dom.indicatorHand.classList.add('progress__indicator-hand')
		this.dom.indicator.appendChild(this.dom.indicatorHand)
	}
	this.dom.label = element.querySelector('.progress__label')
	if (!this.dom.label) {
		this.dom.label = document.createElement('div')
		this.dom.label.innerHTML = this.options.label
		this.dom.root.appendChild(this.dom.label)
	}
	this.dom.labelText = element.querySelector('.progress__text')
	if (!this.dom.labelText) {
		this.dom.labelText = document.createElement('span')
		this.dom.label.appendChild(this.dom.labelText)
	}
	this.dom.root.classList.add('progress')
	this.dom.track.classList.add('progress__track')
	this.dom.progress.classList.add('progress__progress')
	this.dom.label.classList.add('progress__label')
	this.dom.labelText.classList.add('progress__text')

	// Cache stroke length and update to starting progress level.
	this.strokeLength = this.dom.progressPath.getTotalLength()
	this.dom.progress.style.strokeDasharray = this.strokeLength
	this.update(this.options.progress)
}

/**
 * Progress prototype.
 */
Progress.prototype.update = function (progress) {
	if (!progress) progress = 0;
	if (typeof progress === 'string') progress = Number(progress);
	if (typeof progress !== 'number') return
	// Round progress to a whole number.
	this.progress = progress = progress.toFixed(0)
	// Calculate stroke dashoffset value and set in styles.
	this.strokeOffset = this.strokeLength * ((100 - progress) / 100)
	this.dom.progressPath.style.strokeDashoffset = this.options.zeroTick ? Math.min(this.strokeOffset, this.strokeOffset - 1) : this.strokeOffset
	// Rotate the indicator.
	this.dom.indicator.style.transform = 'rotate(' + (this.options.indicatorOffset[0] + ((360 - this.options.indicatorOffset[0] - this.options.indicatorOffset[1]) * (progress / 100))) + 'deg)'
	// Update text label.
	this.dom.labelText.innerHTML = progress
}

/**
 * Initialization.
 */
Array.from(document.querySelectorAll('.progress')).forEach(function(element) {
	var options = {};
	['progress', 'roundStroke', 'zeroTick', 'label'].forEach(function (item) {
		if (element.dataset[item]) {
			options[item] = element.dataset[item]
			if (options[item] === 'true') options[item] = true
			if (options[item] === 'false') options[item] = false
		}
	})
	var progress = new Progress(element, options)
})
