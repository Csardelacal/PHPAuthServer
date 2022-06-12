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


depend(['m3/animation/bezier'], function(bezier) {
	
	var Segment = function (a, b) {
		
		this.calculate = function (x) {
			if (Math.min(a.x, b.x) > x || Math.max(a.x, b.x) < x) {
				return undefined;
			}
			
			return a.y + (b.y - a.y) * ((x - a.x) / (b.x - a.x)); 
		};
	};
	
	var Line = function (segments) {
		
		this.calculate = function(x) {
			var t;
			
			for (var i = 0; i < segments.length; i++) {
				t = segments[i].calculate(x);
				if (t !== undefined) { return t; }
			}
			
			return undefined;
		};
	};
	
	var compile = function(b, c, precision) {
		var curve = bezier.bezier(bezier.point(0, 0), b, c, bezier.point(1, 1)),
		    table = [],
		    last  = undefined;
		
		for (var i = 0; i <= precision; i++) {
			var p = bezier.point(curve.x(i / precision), curve.y(i / precision));
			
			if (last !== undefined) {
				table.push(new Segment(last, p));
			}
			
			last = p;
		}
		return new Line(table);
	};
	
	return {
		compile: compile
	};
});