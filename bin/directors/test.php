<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TestDirector extends \spitfire\mvc\Director
{
	
	private $html = '
	<div style="font-family: sans-serif; background: #EAEAEA; padding: 30px">
		<div style="margin: 0 auto; border: solid 1px #CCC; box-shadow: 0 0 3px #DDD; border-radius: 2px; max-width: 500px; background: #FFF;">
			<!--Header-->
			<div style="padding: 10px; background: #3191F1; color: #FFF; font-weight: bold;">
				YCH.Commishes - 
				You received your first bid			</div>
			
			<!--Content-->
			<div style="padding: 20px;">
				<p>Follow the link to see your auction</p>
				<p>&nbsp;</p>
								<p style="text-align: center">
					<a href="http://ych.commishes.com/auction/show/1DI/pannel-a-slot-2/?test=123" style="background: #3167f1; color: #FFF; border-radius: 5px; padding: 10px; text-decoration: none; font-weight: bold;">Go to the website</a>
				</p>
							</div>
			
			<div style="height: 30px"></div>
			
			<p style="font-size: 12px; color: #555">
				Reply to this email to give us feedback or report issues.
			</p>
		</div>
	</div>';
	
	public function test () {
		
		$from = str_replace('@', '+' . time() . '@', \SysSettingModel::getValue('smtp.from'));
		$email = new \mail\transport\Email(
			'12', 
			new mail\transport\Contact('Cesar', 'cesar@magic3w.com'), 
			new mail\transport\Contact('PHPAS', $from), 
			'Test', 
			$this->html, 
			''
		);
		
		echo $email->html();
		echo PHP_EOL;
		
		$transport = new \mail\PostalTransport('mail.commishes.com', 'JXyYD3bG2NKYoZ3DdnxucgDh');
		$transport->deliver($email);
	}
	
	public function test2() {
		$msgid = 12;
		
		echo Strings::strToHTML($this->html, function ($url) use ($msgid) {
			$router = sprintf('https://%s/url/redirect/%s/', 'account.commishes.com', $msgid);
			return sprintf('<a href="%s">%s</a>', sprintf('%s%s', $router, rawurlencode(base64_encode($url))), $url);
		});
	}
	
}