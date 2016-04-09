
<div class="row1">
	<div class="span1 material">
		<h1>Token debugging</h1>
		<p>
			You just created a token, this process is usually handled by an automated
			application that authenticates you somewhere else.
		</p>
		
		<p>
			Normally, you would now be redirected to the URL:
		</p>
		
		<pre><code><?= new absoluteURL('auth', 'oauth', $token->token) ?></code></pre>
		
		<p>
			Which would allow you to decide whether you wish to share your data with
			the application requesting it. This URL can take several additional
			parameters to indicate where the request should be directed after login.
		</p>
	</div>
</div>
