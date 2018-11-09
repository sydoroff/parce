<?php
require_once ('./phpQuery/phpQuery.php');
$position = (int) file_get_contents('./page.txt');

if ($position>1) $pos_url="page={$position};";

$db = mysqli_connect('127.0.0.1','root','','php28','3306');

$url = "https://hard.rozetka.com.ua/computers/c80095/filter/{$pos_url}sort=cheap/";
$element = array();
$pg=0;

$fil=file_get_contents($url);
$results = phpQuery::newDocument($fil);
$body = $results->find('body');

foreach($body[0]->find('span.paginator-catalog-l-i-active') as $page)
    $pg=pq($page)->text();

foreach (pq($$body[0])->find('.g-i-tile-i-box') as $item)
{
$el = array();
foreach(pq($item)->find('.g-i-tile-i-title a') as $result){
    $el['name'] = trim(pq($result)->text());
    $el['href'] = pq($result)->attr('href');
}

foreach(pq($item)->find('.g-i-tile-i-image a.responsive-img img') as $result){
    $el['src'] = pq($result)->attr('src');
}

foreach(pq($item)->find('script') as $page) {

        preg_match('/var pricerawjson = \'(.+)\';/', pq($page)->html(), $str);
        if ($str[1]) {
            $pr = json_decode(urldecode($str[1]));
            $el['price']= $pr->price;
        }
    }
    $element[]=$el;
}
foreach ($element as $item)
    mysqli_query($db,"INSERT INTO `product` (`id`, `name`, `price`, `href`, `img`) VALUES (NULL, '".htmlspecialchars($item['name'])."', '".$item['price']."', '".urlencode($item['href'])."', '".urlencode($item['src'])."')");
$position++;
if ($pg<$position)
    echo "All OK!!!";
else {
    file_put_contents('./page.txt', $position);
    sleep(rand(10,25));
    header('Location: http://php28.fuck/down/');
}
?>