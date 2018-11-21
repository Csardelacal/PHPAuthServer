/* 
 * The MIT License
 *
 * Copyright 2018 César de la Cal Bretschneider <cesar@magic3w.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/*
 * Provides routing for the depend.js dependency loader. This allows applications
 * to map namespaces or certain suffixes to certain locations.
 * 
 * This router can generally not be loaded through depend itself. Since usually,
 * the router is required to even load components.
 * 
 * Instead, it will register itself. Therefore allowing to load it in a "classic"
 * fashion and then register rules and load the actual dependencies.
 */
depend('depend/router', [], function () {
	
	"use strict";
	
	function Router() {
		this.rules = [];
		this.catchAll = undefined;
		
	}
	
	Router.prototype = {
		rewrite: function (url) {
			for (var i in this.rules) {
				if (this.rules[i].matches(url)) { return this.rules[i].rewrite(url); }
			}
			
			return this.catchAll? this.catchAll.rewrite(url) : null;
		},
		
		startsWith: function(string) {
			var rule = new Rule(new StartsWithCondition(string));
			this.rules.push(rule);
			return rule;
		},
		
		endsWith: function(string) {
			var rule = new Rule(new EndsWithCondition(string));
			this.rules.push(rule);
			return rule;
		},
		
		equals: function(string) {
			var rule = new Rule(new EqualsCondition(string));
			this.rules.push(rule);
			return rule;
		},
		
		all: function() {
			var rule = new Rule(new PermanentCondition());
			this.catchAll = rule;
			return rule;
		}
	};
	
	function Rule(condition) {
		/*
		 * If the condition is satisfied, the router calls the target with the result.
		 */
		this.conditions = [condition];
		this.target = undefined;
		
		
	}
	
	Rule.prototype = {
		to: function (target) {
			this.target = target;
			return this;
		},
		
		and: function (condition) {
			this.conditions.push(condition);
			return this;
		},
		
		matches: function (string) {
			var matches = true;
			
			for (var i in this.conditions) {
				matches = matches && this.conditions[i].satisfiedBy(string);
			}
			
			return matches;
		},
		
		rewrite: function (string) {
			return this.target? this.target(string) : null;
		},
		
		rules: {
			starts: StartsWithCondition,
			ends: EndsWithCondition,
			equals: EqualsCondition
		}
	};
	
	function StartsWithCondition(search) {
		
		this.satisfiedBy = function (string) {
			return string.indexOf(search) === 0;
		};
	}
	
	function EndsWithCondition(search) {
		
		this.satisfiedBy = function (string) {
			return string.lastIndexOf(search) + search.length === string.length;
		};
	}
	
	function EqualsCondition(search) {
		this.satisfiedBy = function (string) {
			return search === string;
		};
	}
	
	function PermanentCondition() {
		this.satisfiedBy = function () {
			return true;
		};
	}
	
	/*
	 * Instance the router and register it with the dependency system.
	 */
	var router = new Router();
	window.depend.setRouter(function (e) { return router.rewrite(e); });
	
	/*
	 * Return it, this way we can depend on the router to configure it.
	 */
	return router;
});