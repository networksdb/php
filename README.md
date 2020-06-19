# NetworksDB.io official PHP library

This is the official PHP client for the NetworksDB API. This allows you to lookup owner details for any IPv4 or IPv6 IP address, find out which networks, IP addresses and domains are operated by organisations, and much more.

**This requires a NetworksDB API token**. You can get one for free at our website, https://networksdb.io/api/docs. Free keys come with limitations, such as a limited monthly quota and truncated output for large result sets.

The details returned by the API include, but aren't limited to, the following:

- **Organisation info**: Name, address, phone, countries, number of IPv4 and IPv6 networks, number of networks by country, assigned ASNs
- **Organisation networks**: Description, name, size, CIDR, start IP, end IP for each network operated by a specific organisation
- **IP info**: Number of domains resolving to the IPv4 or IPv6 address, owner organisation info, name and description of the network containing the address.
- **IP geolocation**: Country, state, city, latitude and longitude. 
- **ASN info**: Information about the autonomous system, including the owner company.
- **ASN networks**: IPv4/6 network prefixes announced by the autonomous system, including the company they are allocated to.
- **Reverse DNS**: List of domain names resolving to the IP address.
- **"Mass" reverse DNS**: List of domain names resolving to addresses in an IP range *(not available to free API keys)*.

## Installation

No installation is needed. Just copy the `networksdb.php` file to your includes directory, and include it in your script.

This library requires the PHP cURL library. On Debian/Ubuntu systems, this can be installed with `sudo apt install php-curl`.

## Quick Start

Start by getting an instance of a NetworksDB API handler, supplying your API key.
```
require_once '../inc/networksdb.php';
$api = new NetworksDB('11111111-2222-3333-4444-555555555555');
```
Get information about an IP address:
```
$ip = $api->ip_info('8.8.8.8');
```
Omitting the parameter will return information about your source IP address.

Return information about the owner, networks and domains:
```
print($ip->organisation->name);
# Google LLC

print($ip->domains_on_ip);
# 7243

print($ip->network->cidr);
# 8.8.8.0/24

print($ip->network->netname);
# LVLT-GOGL-8-8-8
```

Request geolocation information (This works with IPv6 addresses too):
```
$geo = $api->ip_geo('8.8.8.8');
print("$geo->continent, $geo->country, $geo->state, $geo->city, $geo->latitude, $geo->longitude");
# North America, United States, Virginia, Ashburn, 39.0438, -77.4874
```

View the full API response details by printing any response object:
```
print_r($ip)
/*
stdClass Object
(
    [ip] => 8.8.8.8
    [domains_on_ip] => 7473
    [url] => https://networksdb.io/ip/8.8.8.8
    [organisation] => stdClass Object
        (
            [name] => Google, Inc
            [id] => google-inc
            [url] => https://networksdb.io/ip-addresses-of/google-inc
        )

    [network] => stdClass Object
        (
            [netname] => LVLT-GOGL-8-8-8
            [description] => Google LLC
            [cidr] => 8.8.8.0/24
            [first_ip] => 8.8.8.0
            [last_ip] => 8.8.8.255
            [url] => https://networksdb.io/ips-in-network/8.8.8.0/8.8.8.255
        )

)
*/
```

### Organisation search

To request organisation details, you need to supply its NetworksDB `id`. To find organisation IDs, use the *organisation search API*  The results are sorted by the number of IPv4 addresses for each organisation:

```
$search = $api->org_search('Github');

print($search->total);
# 1

print($search->results[0]->organisation);
# GitHub, Inc

print ($search.results[0]->id)
# github-inc
```

### Organisation info
Once you've found the correct ID, pass it to the *organisation info* API call:
```
$github = $api->org_info('github-inc');

print_r($github);
/*
stdClass Object
(
    [organisation] => GitHub, Inc
    [id] => github-inc
    [address] => 
    [phone] => 
    [countries] => Array
        (
            [0] => United States
        )

    [networks] => stdClass Object
        (
            [ipv4] => 8
            [ipv6] => 3
        )

    [networks_by_country] => stdClass Object
        (
            [United States] => 12
        )

    [url] => https://networksdb.io/ip-addresses-of/github-inc
    [asns] => Array
        (
            [0] => 36459
        )

)
*/
```

### Organisation networks

Find out which networks they own or operate:
```
$github_networks = $api->org_networks($github->id);

foreach ($github_networks->results AS $range)
    print("{$range->netname}, {$range->description}, {$range->cidr}\n");
/*
GITHU, GitHub, Inc, 140.82.112.0/20
GITHU, GitHub, Inc, 143.55.64.0/20
US-GITHUB-20170413, GitHub, Inc, 185.199.108.0/22
GITHUB-NET4-1, GitHub, Inc, 192.30.252.0/22
GITHUB-INC-GTT, GitHub Inc, 77.77.189.112/28
GITHUB-GTT, GitHub, 87.119.80.192/28
ZAYO-IDIA-235983-64-124-138-32-28, GitHub, 64.124.138.32/28
RSPC-48B1F3A4-2615-4566-99CD-D126E3C102BB, GitHub, 174.143.3.100/30
*/
```
Or, for IPv6:
```
$github_ipv6_networks = $api->org_networks($github->id, $ipv6=true);

foreach ($github_ipv6_networks->results AS $range)
    print("{$range->netname}, {$range->description}, {$range->cidr}\n");
/*
US-GITHUB-20170419, GitHub, Inc, 2a0a:a440::/29
GITHUB-NET6-1, GitHub, Inc, 2620:112:3000::/44
GITHUB-GTT6, GitHub, 2001:668:1f:d8::/64
*/
```

### Reverse DNS

List the domains names resolving to a given IPv4 or IPv6 address:
```
$reverse_dns = $api->reverse_dns('185.199.108.153');

print(reverse_dns->total);
# 131590

print_r(array_slice($reverse_dns->results, 0, 10));
/*
Array
(
    [0] => 0.how
    [1] => 0--0.net
    [2] => 000.farm
    [3] => 000.ovh
    [4] => 000095.xyz
    [5] => 0000999.xyz
    [6] => 000fff.design
    [7] => 0023.ru
    [8] => 0061.ru
    [9] => 01-partners.com
)
*/
```
Mass reverse DNS is the same thing, but on a full network block:
```
$mass_reverse = $api->mass_reverse_dns('185.199.108.0/22');

print($mass_reverse->total);
# 493492

foreach (array_slice($mass_reverse->results, 0, 4) AS $result)
    print("$result->ip has " . implode(", ", $result->domains) ."\n");
/*
185.199.108.0 has jidanlee.com, tessmichi.com
185.199.108.15 has djuric.se, fabiotripoli.com, gaby.ec, hectormanrique.com, lucamartinelli.it, trustkaro.com
185.199.108.22 has jidanlee.com
185.199.108.53 has kasasalytics.com, zimenglyu.com
*/
```
*Note: Mass reverse DNS is not available to free API keys.*

### Find all domains hosted by a company
It's pretty easy to iterate through the company's networks and request the list of domain names for each network:
```
$all_domains = [];
$org_id = 'paypal-inc';

foreach ($api->org_networks($org_id)->results AS $network)
{
    $mass_reverse = $api->mass_reverse_dns($network->first_ip, $network->last_ip);

    foreach ($mass_reverse->results AS $domains_in_network)
        $all_domains = array_merge($all_domains, $domains_in_network->domains);
}

print(count($all_domains));
# 334

print_r(array_slice($all_domains, -20));
/*
Array
(
    [0] => paypal.lu
    [1] => paypal.nl
    [2] => paypal.no
    [3] => paypal.ph
    [4] => paypal.pl
    [5] => paypal.pt
    [6] => paypal.ru
    [7] => paypal.se
    [8] => paypal.vn
    [9] => paypal.me
    [10] => paypal.me
    [11] => willhavebeen.com
    [12] => paypal-europe.com
    [13] => paypal-nederland.nl
    [14] => paypal-europe.com
    [15] => paypal-nederland.nl
    [16] => test-paypal.com
    [17] => py.pl
    [18] => test-paypal.com
    [19] => international-healthcare.com
)
*/
```

### ASN information
Request information about a particular ASN:
```
$asn = $api->asn_info(19956);

print("{$asn->as_name}, {$asn->description}, {$asn->networks_announced->ipv4}\n");
# TENNESSEE-NET, BellSouth.net Inc., 21
```
Retreive the networks announced by the ASN and find out who they are assigned to (for IPv6, pass the parameter `ipv6=True`):
```
$as_nets = $api->asn_networks(19956);

foreach ($as_nets->results AS $network)
    print("{$network->cidr}, {$network->countrycode}, {$network->organisation->name}\n");
/*
12.204.201.0/24, US, BellSouth.net Inc.
12.204.208.0/24, US, State of Tennessee
12.204.209.0/24, US, BellSouth.net Inc.
12.204.216.0/21, US, State of Tennessee
64.79.176.0/21, US, Southwest Tennessee Community College
64.79.184.0/21, US, Southwest Tennessee Community College
66.4.14.0/23, US, BellSouth.net Inc.
66.4.27.0/24, US, BellSouth.net Inc.
66.4.28.0/22, US, BellSouth.net Inc.
70.150.247.0/24, US, TNII Networks
72.159.76.0/24, US, Tennessee State Govt
167.29.254.0/24, US, BellSouth.net Inc.
170.141.60.0/23, US, BellSouth.net Inc.
170.141.62.0/24, US, BellSouth.net Inc.
170.178.136.0/22, US, Motlow State Community College
170.190.40.0/22, US, BellSouth.net Inc.
192.230.240.0/20, US, Chattanooga State Community College
198.146.0.0/16, US, Tennessee Board of Regents
206.23.0.0/16, US, Tennessee Board of Regents
208.63.129.0/24, US, BellSouth.net Inc.
208.182.101.0/24, US, BellSouth.net Inc.
*/
