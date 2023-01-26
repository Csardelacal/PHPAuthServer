
<div class="spacer" style="height: 30px"></div>

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
		
		<blockquote>
			<pre><code><?= new AbsoluteURL('auth', 'oauth2', $token->token) ?></code></pre>
		</blockquote>
		
		<p>
			Which would allow you to decide whether you wish to share your data with
			the application requesting it. This URL can take several additional
			parameters to indicate where the request should be directed after login.
		</p>
		
		<table>
			<thead>
				<tr>
					<th>Parameter</th>
					<th>Description</th>
				</tr>
			</thead>
			<tr>
				<td><strong>returnurl</strong></td>
				<td>
					The URL where the request should be directed after a successful 
					authentication request
				</td>
			</tr>
			<tr>
				<td><strong>cancelurl (optional)</strong></td>
				<td>
					This is the URL where the user should be directed when he does not 
					authenticate this token. If not provided, the user will be directed 
					to the <code>returnurl</code> and application can then use 
					<code><?= new AbsoluteURL('token', 'status', $token->token); ?></code>
					endpoint to check the state.
				</td>
			</tr>
		</table>
	</div>
</div>
