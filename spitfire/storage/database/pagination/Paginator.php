<?php namespace spitfire\storage\database\pagination;

use spitfire\core\http\URL;
use spitfire\storage\database\Query;
use function collect;
use function within;

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

/**
 * This class is the base for the database query pagination inside of Spitfire.
 * It provides the necessary tools to generate a list of pages inside your 
 * applications so queries aren't able to collapse your system / clients.
 * 
 * By default this class includes a getEmpty method that returns a message when 
 * no results are available. Although it is not a good practice to allow classes
 * perform actions that aren't strictly related to their task. But the improvement
 * on readability gained in Views is worth the change.
 * 
 * @link http://www.spitfirephp.com/wiki/index.php/Database/pagination Related data and tutorials
 * 
 * @todo Somehow this class should cache the counts, so the database doesn't need to read the data every time.
 * @todo This class should help paginating without the use of LIMIT
 * @todo Update the pertinent documentation on the wiki
 */
class Paginator
{
	/**
	 * The query to paginate. The offset and limit properties of the query may be
	 * overwritten by the paginator.
	 *
	 * @var Query
	 */
	private $query;
	
	/**
	 * This property allows the paginator to communicate with the user to retrieve
	 * the current page and present a set of options to jump to another page.
	 *
	 * @var PaginationInterface
	 */
	private $io;
	
	/**
	 * The number of records to be fit in a single page. Once the number of records
	 * is exceeded, another page is added.
	 *
	 * @var int
	 */
	private $pageSize;
	
	/**
	 * The distance suggests spitfire the amount of pages a user should be able
	 * to jump from the current page.
	 * 
	 * When there's enough pages, the user should be able to jump at least (distance)
	 * and at most (distance * 2).
	 *
	 * @var int 
	 */
	private $distance = 3;
	
	/**
	 * Paginates a query. Paginating is the process in which a query that delivers
	 * a resultset too large to be properly transferred over a network or fit into
	 * a window is broken into individual pages.
	 * 
	 * @param Query $query
	 * @param int   $pageSize
	 */
	public function __construct(Query$query, PaginationInterface$io = null, $pageSize = 20) {
		$this->query = $query;
		$this->io    = $io? : new SimplePaginator(URL::current(), 'page');
		$this->pageSize = $pageSize;
	}
	
	/**
	 * Estimates the number of pages needed to fit all the results for this query.
	 * 
	 * @return int
	 */
	public function getPageCount() {
		$results = $this->query->count();
		return (int)ceil($results/$this->pageSize);
	}
	
	/**
	 * This function calculates the pages to be displayed in the pagination. It 
	 * calculates the ideal amount of pages to be displayed (based on the max you want)
	 * and generates an array with the numbers for those pages.
	 * 
	 * If you use the default distance of 3 you will always receive up to 9 pages.
	 * Those include the first, the last, the current and the three higher and lower
	 * pages. For page 7/20 you will receive (1,4,5,6,7,8,9,10,20).
	 * 
	 * In case the pagination doesn't find enough elements whether on the right or
	 * left it will try to extend this with results on the other one. This avoids
	 * broken looking paginations when reaching the final results of a set.
	 * 
	 * @return \spitfire\collection\Collection <int>
	 */
	public function pages() {
		$count = $this->getPageCount();
		
		/*
		 * Determine the range within the user can jump. The system will try to find
		 * a value that allows it to render as many pages as possible with the given
		 * distance.
		 * 
		 * Optimizing for drawing the most possible pages provides the user with a
		 * consistent interface and with the biggest amount of options.
		 */
		$start = within(2, $this->io->current() - $this->distance, $count - $this->distance * 2 - 2); 
		
		/*
		 * Generate the page numbers from start to start + 2(distance). This ensures
		 * that we always receive either all the pages or distance * 2 + 3 pages.
		 */
		$pages = collect(range(
			$start, 
			$start + $this->distance * 2 + 1
		));
		
		/*
		 * We finally add the first and last page to the result set. Please note that
		 * we do not check if the pages have already been added at this stage since
		 * we simply remove the duplicates once all of them have been added.
		 */
		$pages->push(1);
		$pages->push($count);
		
		return $pages->unique()->sort()->filter(function($e) { return $e > 0 && $e <= $this->getPageCount(); });
	}
	
	/**
	 * Gets the records that populate the current page.
	 */
	public function records() {
		return $this->query->range( ($this->io->current() - 1) * $this->pageSize, $this->pageSize);
	}
	
	/**
	 * This method makes it quick and comfortable to present the pagination, just
	 * by running <code>echo $pagination;</code> wherever your application whishes
	 * to present the pages.
	 * 
	 * Please note, the pagination will also render HTML whenever your query returns
	 * an empty result. Therefore, you don't need to check whether the result set
	 * is empty or not.
	 * 
	 * @return string
	 */
	public function __toString() {
		$pages      = $this->pages();
		$previous   = 0;
		
		if ($this->getPageCount() === 0) {
			return $this->io->emptyResultMessage();
		}
		
		/*
		 * It's common for HTML forms to have some kind of boilerplate to prepare 
		 * for the output. Since HTML is the most common form of pagination, it's 
		 * easy to make adjustments to ensure it's easy to generate HTML.
		 */
		$_ret = $this->io->before();
		
		/*
		 * Render a "Jump to first page button". These are usually the first element
		 * in a pagination. While it functions exactly like the page 1 link, it allows
		 * for the application to customize the experience.
		 */
		$_ret.= $this->io->first();
		
		/*
		 * Print a "Jump to previous page". Again, these can have special layouts
		 * or styling. The previous page is always included in the pages.
		 */
		$_ret.= $this->io->previous($this->io->current() <= 1);
		
		/*
		 * Render the pages. All the pages sent to the io component are valid, but
		 * can be pages the user should be unable to jump to. For example, the 
		 * current page is renderer, but many developers prefer to have a faux
		 * link in place, if this is the case, your renderer needs to gray it out.
		 */
		foreach ($pages as $page) { 
			if ($page > $previous + 1) { $_ret.= $this->io->gap(); }
			$_ret.= $this->io->page($page); 
			$previous = $page;
		}
		
		/*
		 * Just like the before and first jump buttons, the system terminates by
		 * adding the next and last pages.
		 */
		$_ret.= $this->io->next($this->io->current() >= $this->getPageCount());
		$_ret.= $this->io->last($this->getPageCount());
		
		/*
		 * In case the boilerplate requires it, your renderer can use this to present
		 * any boilerplate it requires to end the pagination.
		 */
		$_ret.= $this->io->after();
		
		return $_ret;
	}
	
}
