<?php use mail\spam\domain\implementation\SpamDomainModelReader; ?>

<div class="row">
	<div class="span">
		<div class="material">
			<form method="POST" action="" class="regular">
				
				<div class="field">
					<label for="input_hostname">Hostname / IP</label>
					<input type="text" placeholder="Hostname, IP or IP/CIDR..." name="hostname" value="<?= $domain->type == SpamDomainModelReader::TYPE_IP? inet_ntop(base64_decode($domain->host)) : $domain->host ?>">
				</div>
				
				<div class="spacer" style="height: 20px"></div>
				
				<div class="field">
					<label for="input_reason">Reason for listing</label>
					<input type="text" placeholder="Reason for listing..." name="reason" value="<?= $domain->reason ?>">
				</div>
				
				<div class="spacer" style="height: 20px"></div>
				
				<div>
					<input type="radio" name="type" value="IP" <?= $domain->type == SpamDomainModelReader::TYPE_IP? 'checked' : '' ?>><label for="input_type_ip">IP address</label>
					<input type="radio" name="type" value="domain" <?= $domain->type == SpamDomainModelReader::TYPE_HOSTNAME? 'checked' : '' ?>><label for="input_type_domain">Domain</label>
				</div>
				
				<div class="spacer" style="height: 20px"></div>

				<input type="radio" name="list" value="black" <?= $domain->list === SpamDomainModelReader::LIST_BLACKLIST? 'checked' : '' ?>><label for="input_list_black">Blacklist</label>
				<input type="radio" name="list" value="white" <?= $domain->list === SpamDomainModelReader::LIST_WHITELIST? 'checked' : '' ?>><label for="input_list_white">Whitelist</label>

				<div class="spacer" style="height: 20px"></div>

				<input type="submit">
			</form>
		</div>
	</div>
</div>
