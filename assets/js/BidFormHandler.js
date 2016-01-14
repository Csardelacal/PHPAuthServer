(function () {
	
	"use strict";
	
	var messages = {
		'bidtoolow'         : 'Your bid is too low, please increase it.',
		'autobuyachieved'   : 'You reached autobuy, with this amount the piece is yours immediately after you confirm the bid',
		'bidmustbemorethan' : 'Enter at least $'
	};
	
	function BidFormHandler(form, startbid, highestbid, minincrease, autobuy) {
		//The CTX variable allows us to manage the context of the object outside the this scope
		var ctx = this;
		//The dialog manages the messages' HTML.
		var dialog = null;
		
		if (form === null) { throw 'Could not find a form to handle'; }
		
		/**
		 * This function listens to the bid input and allows the application to react
		 * to several input conditions like too low bids, negative bids or bids
		 * that exceed the auto buy limit in case one was defined.
		 * 
		 * @param {Number} bid The bid the user entered into the form
		 * @returns {Boolean}
		 */
		this.validate = function (bid) {
			
			//If the bid is below the starting bid, then it's obviously not enough.
			if (bid < startbid) { this.message(messages.bidtoolow, 'error'); return false; }
			
			//Checks to be performed when the autobuy is enabled
			if (autobuy !== null && parseInt(autobuy) > 0) {
				if (bid >= autobuy) { this.message(messages.autobuyachieved, 'success'); return true; }
			}
			
			if (highestbid) {
				var minbid = parseFloat(highestbid) + parseFloat(minincrease);
				if (bid < minbid) { this.message(messages.bidtoolow + ' ' + messages.bidmustbemorethan + ( minbid ), 'error'); return false; }
			}
			
			this.message(null, '');
			return true;
		};
		
		/**
		 * Sets a message to be displayed when there is something to comment about
		 * the bid that the user should know.
		 * 
		 * @param {string} message
		 * @param {string} status
		 * @returns {undefined}
		 */
		this.message = function (message, status) {
			var input  = form.querySelector('#bid');
			
			if (dialog === null) {
				dialog = document.createElement('div');
				input.parentNode.insertBefore(dialog, input.nextSibling);
			}
			
			dialog.className = 'message ' + status;
			dialog.innerHTML = message;
			
			dialog.style.display = (message !== null)? 'block' : 'none';
		};
		
		this.setHighestBid = function (bid) {
			highestbid = bid;
		};
		
		/*
		 * Link the bidding input to this object so we can inform the user at any
		 * given time when there is something noteworthy about his bid.
		 */
		if (window.addEventListener) { form.querySelector('#bid').addEventListener('keyup', function () { ctx.validate(parseFloat(this.value)); }, false); }
		else { throw new 'Your browser does not support events. Please upgrade it'; }
		
	}
	
	//Export the required variables and constructors
	window.BidFormHandler = BidFormHandler;
})();