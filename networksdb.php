<?php

class NetworksDB
{
	function __construct($apikey=null)
	{
		$this->apikey = $apikey;
		$this->endpoint = 'https://networksdb.io';
		$this->ch = curl_init();

		curl_setopt($this->ch, CURLOPT_USERAGENT, 'NetworksDB/PHPClient 1.0');
		curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, ["X-Api-Key: {$this->apikey}"]);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
	}

	function __destruct()
	{
		curl_close($this->ch);
	}

	function request($path, $params=[])
	{
		curl_setopt($this->ch, CURLOPT_URL, "{$this->endpoint}{$path}"); 
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($params));

		$output = curl_exec($this->ch);

		return json_decode($output);
	}

	function key_info()
	{
		return $this->request("/api/key");
	}

	function ip_info($ip=null)
	{
		if ($ip)
			return $this->request('/api/ip-info', ['ip' => $ip]);
		else
			return $this->request('/api/ip-info'); # Will return info for your own IP
	}

	function ip_geo($ip=null)
	{
		if ($ip)
			return $this->request('/api/ip-geo', ['ip' => $ip]);
		else
			return $this->request('/api/ip-geo'); # Will return info for your own IP
	}

	function org_search($query)
	{
		return $this->request('/api/org-search', ['search' => $query]);
	}

	function org_info($id)
	{
		return $this->request('/api/org-info', ['id' => $id]);
	}

	function org_networks($id, $ipv6=false)
	{
		if ($ipv6)
			return $this->request('/api/org-networks', ['id' => $id, 'ipv6' => true]);
		else
			return $this->request('/api/org-networks', ['id' => $id]);
	}

	function asn_info($asn)
	{
		return $this->request('/api/asn', ['asn' => $asn]);
	}

	function asn_networks($asn, $ipv6=false)
	{
		if ($ipv6)
			return $this->request('/api/asn-networks', ['asn' => $asn, 'ipv6' => true]);
		else
			return $this->request('/api/asn-networks', ['asn' => $asn]);
	}

	function dns($domain)
	{
		return $this->request('/api/dns', ['domain' => $domain]);
	}

	function reverse_dns($ip)
	{
		return $this->request('/api/reverse-dns', ['ip' => $ip]);
	}

	function mass_reverse_dns($start, $end=null)
	{
		if ($end)
			return $this->request('/api/mass-reverse-dns', ['ip_start' => $start, 'ip_end' => $end]);
		else
			return $this->request('/api/mass-reverse-dns', ['cidr' => $start]);
	}
}
