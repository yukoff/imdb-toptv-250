<?php
require 'scraperwiki.php';
/**************************************
 * Basic PHP scraper
 **************************************/

$baseUrl = 'http://www.imdb.com';
$scrapeDate = new DateTime();

$html = scraperwiki::scrape("${baseUrl}/chart/toptv");
$html = oneline($html);
$xml = @DOMDocument::loadHTML($html);
$xpath = new DOMXPath($xml);

/** @var DOMNodeList $titles */
$titles = $xpath->query('//table/tbody/tr');

/** @var DOMElement $title */
foreach ($titles as $title) {
    /** @var DOMNodeList $cols */
    $cols = $title->getElementsByTagName('td');

    /** @var DOMElement $rankTitle */
    $rankTitleYear = $cols->item(1);
    preg_match('|(\d+)\.\s*(.*?)\s*\((.*?)\)|', trim($rankTitleYear->nodeValue), $arr);

    /** @var DOMElement $link */
    $link = $rankTitleYear->getElementsByTagName('a')->item(0);

    /** @var string $href */
    $href = $baseUrl . $link->getAttribute('href');
    $url = parse_url($href);
    $href = sprintf('%s://%s%s', $url['scheme'], $url['host'], $url['path']);
    preg_match('|/title/([^/]+)/?|', $url['path'], $id);
    $id = $id[1];

    /** @var string $rating */
    $rating = $cols->item(2)->nodeValue;

    $data = [
        'id' => ((integer) clean($arr[1])),
        'imdb_id' => $id,
        'title' => clean($arr[2]),
        'year' => clean($arr[3]),
        'rating' => clean($rating),
        'link' => clean($href),
        // 'date' => $scrapeDate,
    ];
    scraperwiki::save([
        'id',
        'imdb_id',
    ], $data);
}

function clean($val)
{
    $val = str_replace('&nbsp;', ' ', $val);
    $val = str_replace('&amp;', '&', $val);
    $val = html_entity_decode($val);
    $val = strip_tags($val);
    $val = trim($val);
    $val = utf8_decode($val);
    return $val;
}

function oneline($code)
{
    $code = str_replace("\n", '', $code);
    $code = str_replace("\r", '', $code);
    return $code;
}
