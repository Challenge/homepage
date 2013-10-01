window.addEvent('domready',function() {
			//settings
			var duration = 40000;
			var length = 800;
			var count = 0;
			
			var tweener;
			
			// Executes the standard tween on the background position
			var run = function() {
				tweener.tween('background-position','-' + (++count * length) + 'px 0px');
			};
			
			// Defines the tween
			tweener = $('page_bg').setStyle("background-position","0px 0px").set('tween',{ 
				duration: duration, 
				transition: Fx.Transitions.linear,
				onComplete: run,
				wait: false
			});
			
			// Starts the initial run of the transition
			run();
			
		});