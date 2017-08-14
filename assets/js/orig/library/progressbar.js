ebindr.library.progressbar = new Class({

	//implements
	Implements: [Options],

	//options
	options: {
   		container: 'preload-progress-bar',
   		startPercentage: 0,
   		speed: 500,
   		boxID: 'preload-progress',
   		percentageID: 'preload-percent',
   		displayID: 'preload-text',
   		displayText: false
	},
	
	//initialization
	initialize: function(options) {
		//set options
		//$extend(this.options,options);
		this.setOptions(options);
		this.options.container = $(this.options.container);
		//create elements
		this.createElements();
	},
	
	//creates the box and percentage elements
	createElements: function() {
		var box = new Element('div', { id:this.options.boxID });
		var perc = new Element('div', { id:this.options.percentageID, 'style':'width:0px;' });
		perc.inject(box);
		box.inject(this.options.container);
		if(this.options.displayText) { 
			var text = new Element('div', { id:this.options.displayID });
			text.inject(this.options.container);
		}
		this.set(this.options.startPercentage);
	},
	
	//calculates width in pixels from percentage
	calculate: function(percentage) {
		return ($(this.options.boxID).getStyle('width').replace('px','')-7 * (percentage / 100)).toInt();
	},
	
	//animates the change in percentage
	animate: function(to) {
		$(this.options.percentageID).set('morph', { duration: this.options.speed, link:'cancel' }).morph({width:this.calculate(to.toInt())});
		if(this.options.displayText) { 
			$(this.options.displayID).set('text', to.toInt() + '%'); 
		}
	},
	
	//sets the percentage from its current state to desired percentage
	set: function(to) {
		this.animate(to);
	}, 
	
	destroy: function(to) {
		if( $(this.options.boxID) ) $(this.options.boxID).destroy();
		if( $(this.options.displayID) ) $(this.options.displayID).destroy();
		if( $(this.options.percentageID) ) $(this.options.percentageID).destroy();
	}
	
});
