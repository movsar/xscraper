<?php

/*
    THIS FILE SHALL OUTPUT WITH JSON AND ONLY JSON !
*/


//header('Content-Type: application/json');
date_default_timezone_set('Europe/Moscow');
require_once('mysqlidb.php');
require_once('config.php');

$tbl_users = "users";

// Get input params
$input = json_decode(file_get_contents("php://input"), true); // This is an obgroup object

$REQ_DELAY = 2 * 100000;
//request(array('url' => 'https://www.autotrader.co.uk/car-search?sort=sponsored&radius=1500&postcode=e161xl&onesearchad=Used&onesearchad=Nearly%20New&onesearchad=New&page=100'));

$DEBUG_MODE = false;

if (isset($_GET['DEBUG']) || isset($input['DEBUG'])) {
    $DEBUG_MODE = true;
    $res = request(
        array(
            'url' => 'https://www.autotrader.co.uk/results-car-search?sort=recommended&radius=1500&postcode=e161xl&onesearchad=Used&onesearchad=Nearly+New&onesearchad=New&make=BMW&writeoff-categories=on',
            'cookieHeaders' =>
            [
                'Host: www.autotrader.co.uk',
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Referer: https://www.autotrader.co.uk/',
                'Upgrade-Insecure-Requests: 0'
            ],
            'follow' => 1
        )
    );
    $res = json_decode($res);
    $html = $res->html;
    print_r(json_encode($html, 'asdasd'));
    return;
}

if ($input != null) {
    switch ($input['action']) {
        case "GET_CAR_INFO":
            $data = scrapeCarInfo($input['id'], (isset($input['proxy']) ? $input['proxy'] : null));
            print_r($data);
            break;

        case "GET_IDS":
            $data = getIds($input['url'], $input['proxy']);
            print_r($data);
            break;
        case "GET_MODELS":
            $selectMake = request(array('url' => $input['url'], 'proxy' => $input['proxy']));
            preg_match_all('#(?=<div class="sf-flyout__options js-flyout-options">).*?(?=Clear)#s', $selectMake, $models);
            $modelsHTML = $models[0][1];

            // Get models
            preg_match_all('#(?<=<span class="term">).*?(?=</span>)#s', $modelsHTML, $models);
            // Get models count
            preg_match_all('#(?<=count">\().*?(?=\))#s', $modelsHTML, $numbers);

            $sum = 0;
            $res = array('title' => $input['make']);

            for ($i = 0; $i < sizeof($models[0]); $i++) {
                $sum +=  $numbers[0][$i];
                $res['models'][] = array('title' => $models[0][$i], 'count' => $numbers[0][$i]);
            }
            $res['total'] = $sum;
            print_r(json_encode($res));
            break;

        case "JSON_TO_FILE":
            $fileName = $input['fileName'];
            $append =  isset($input['append']) ? $input['append'] : false;
            $contents = json_encode($input['contents']);

            $path = getcwd() . DIRECTORY_SEPARATOR . "appdata" . DIRECTORY_SEPARATOR . $fileName;

            if ($append == true)
                file_put_contents($path, $contents, FILE_APPEND);
            else
                file_put_contents($path, $contents);

            echo json_encode(array("JSON_TO_FILE" => "OK"));
            break;

        case "JSON_FROM_FILE":
            $fileName = $input['fileName'];
            print_r(file_get_contents_utf8($fileName));

            break;
    }
}

function file_get_contents_utf8($path)
{
    $content = file_get_contents($path);
    return mb_convert_encoding(
        $content,
        'UTF-8',
        mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)
    );
}

function mb_trim($str)
{
    // внутренний прег мач триммит, внешьний удаляет повторяющиеся пробелы, ключ /u - преобразует в utf-8
    return preg_replace("/\s+/us", " ", preg_replace("/(^\s+)|(\s+$)/us", "", $str));
}


function request($settings)
{
    //if ($GLOBALS['DEBUG_MODE'] === true) var_dump($settings);
    $proxy = isset($settings['proxy']) ? $settings['proxy'] : NULL;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.89 Safari/537.36');
    curl_setopt($ch, CURLOPT_URL, $settings['url']); // отправляем на
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_HEADER, (isset($settings['header'])) ? $settings['header'] : 0); // пустые заголовки
    curl_setopt($ch, CURLOPT_NOBODY, (isset($settings['nobody'])) ? 1 : 0); // без тела
    curl_setopt($ch, CURLOPT_COOKIESESSION, (isset($settings['session'])) ? 1 : 0); // новая сессия
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, (isset($settings['follow'])) ? 1 : 0); // следовать за редиректами
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // возвратить то что вернул сервер
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // таймаут4
    curl_setopt($ch, CURLOPT_REFERER, (isset($settings['referer'])) ? $settings['referer'] : "");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if (isset($settings['cookieHeaders'])) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $settings['cookieHeaders']);
    }
    if (isset($settings['cookieFile'])) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/' . $settings['cookieFile']); // сохранять куки в файл
        curl_setopt($ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/' . $settings['cookieFile']);
    }
    if (isset($settings['post'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $settings['post']);
        curl_setopt($ch, CURLOPT_POST, 1); // использовать данные в post
    }
    if (isset($settings['cookieStr'])) {
        curl_setopt($ch, CURLOPT_COOKIE, $settings['cookieStr']);
    }
    $data = "";

    try {
        $data = curl_exec($ch);
    } catch (Exception $ex) { }

    if ($data === false || $data === "") {
        $err = curl_error($ch);
        curl_close($ch);
        return json_encode(array('ERR_CODE' => 'NO_RESPONSE', 'ERR_MSG' => $err, 'URL' => $settings['url'], 'PROXY' => $proxy));
    } else {
        curl_close($ch);
        return $data;
    }
}

function scrapeCarInfo($id, $proxy)
{
    $GLOBALS['db']->where("carId", $id);
    $res = $GLOBALS['db']->getOne("autoradar_cars");

    if ($res !== null) {
        return (json_encode(["id" => $id,  "index" => $res['id'], "notes" => 'already there']));
    }

    if (strlen($id) > 20) {
        // new car

        $response = json_decode(request(array('url' => 'https://www.autotrader.co.uk/json/new-cars/derivative/get?id=f0d8e2aea02747a998f94f28c981a0eb', 'proxy' => $proxy)));
        if (property_exists($response, 'ERR_CODE')) return json_encode($response);
        $ci = ($response);

        usleep(250000);

        $response = json_decode(request(array('url' => 'https://www.autotrader.co.uk/json/dealers/search/by-derivative?derivativeId=f0d8e2aea02747a998f94f28c981a0eb&postcode=e161xl', 'proxy' => $proxy)));
        if (!is_array($response)) return $response;
        $di = ($response);

        $title = $ci->make . " " . $ci->name;
        $phone = $di[0]->review->dealer->phoneNo1;
        $price = $ci->price;
        $href = 'https://www.autotrader.co.uk' . $ci->uri;
    } else {
        //  used car
        $response = json_decode(request(array('url' => ('https://www.autotrader.co.uk/json/fpa/initial/' . $id), 'proxy' => $proxy)));
        if (property_exists($response, 'ERR_CODE')) return json_encode($response);

        $carInfo = ($response);

        $title = $carInfo->advert->title;
        $phone =  $carInfo->seller->primaryContactNumber;
        $price = $carInfo->advert->price;
        $href = 'https://www.autotrader.co.uk/classified/advert/' . $id;
    }

    // Save data
    $data = array(
        'carId' => $id,
        'title' => $title,
        'href' => $href,
        'phone' => $phone,
        'price' => $price
    );


    $insertedId = $GLOBALS['db']->insert('autoradar_cars', $data);
    $response = array("id" => $id,  "index" => $insertedId);

    return json_encode($response);
}

function getIds($url, $proxy)
{
    $response = json_decode(
        request(
            array(
                'url' => $url,
                'cookieHeaders' => [
                    'Host: www.autotrader.co.uk',
                    'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0',
                    'Cookie: bucket=desktop; _ATD_SESSION_=1554721507|286b92f7-bc24-4a3d-8a8c-7cb2e0c7b566; _ATD_DIGEST_=bb2193cb640eb14046bc788899f1a273; sessVar=2df02ac3-1ae3-41c9-a77e-5068bfe5c4fd; userid=ID=d74781c2-50d6-450b-8f86-b443d075f792; user=STATUS=0&HASH=c7756a3b861472a854aa311b0a25874c&PR=&ID=d74781c2-50d6-450b-8f86-b443d075f792; abtcid=7ba287fb-d127-4384-99a1-ad051c91c563; GeoLocation=Town=LONDON&Northing=180700&Latitude=51.5077215516&Easting=54070&ACN=62&Postcode=E161XL&Longitude=0.0260180243; SearchData=postcode=E161XL; postcode=postcode=E161XL; searches=CAR_MAKE=bmw|30%2CNEW_CAR_MAKE=bentley|30%2CCAR_MODEL=5_series|30; TS0182e6e2=018d4aa6903aa82ca8c5824d93413fa5519be63874dfb7985e39e64e631251b924fd9e95edea63a4a2e5b2912bfd5d53329b391480f6de1cc961f7e30a8b65134282f4e1c6dfc62d94960b70031691e2461462a52a875c7f9a6682fb5471422de2edc1c215b9c1036d9749b860674222bab086828be3101e1e096345563690ec4ec1041410; cookiePolicy=seen.; surveySplitSegmentation=hotjar; utag_main=v_id:0169fc9f96c50024e0f35220af440004d009500d00bd0$_sn:3$_ss:0$_st:1554748644978$ses_id:1554744344119%3Bexp-session$_pn:21%3Bexp-session$_prevpage:undefined%3Bexp-1554750444984; AMCV_E4EF2A3F555B7FEA7F000101%40AdobeOrg=-1891778711%7CMCIDTS%7C17995%7CMCMID%7C56339806417381611452019739405713808971%7CMCAAMLH-1555326312%7C6%7CMCAAMB-1555345222%7CRKhpRz8krg2tLO6pguXWp5olkAcUniQYPHaMWWgdJ3xzPWQmdj0y%7CMCOPTOUT-1554747622s%7CNONE%7CvVersion%7C2.4.0; short_date=4/8/2019; ck_time=Mon Apr 08 2019 14:05:11 GMT+0300 (Moscow Standard Time); _sp_id.0536=6c3b899b-fcd5-4062-bf99-d1f9f8814698.1554721512.3.1554747211.1554740441.153853d6-9e7a-42d6-a370-2743e46bf89e; _cs_ex=1535460044; _cs_c=0; m_ses=20190408140512; m_cnt=168; __adal_id=986a221e-d89b-48cf-857b-e3ecff24cb13.1554721512.6.1554746840.1554740435.6c806042-f62a-48d1-a4b2-bd4752c463bf; __adal_ca=so%3Ddirect%26me%3Dnone%26ca%3Ddirect%26co%3D%28not%2520set%29%26ke%3D%28not%2520set%29; __adal_cw=1554721512258; AMCVS_E4EF2A3F555B7FEA7F000101%40AdobeOrg=1; LPCKEY-p-245=2f0c5e24-80bc-44e0-889b-c71ee54dd2abf-20332%7Cnull%7CindexedDB%7C120; CAOCID=368e0b3d-66f9-4c6c-ad64-1748697f3bd00-8801; AAMC_autouk_0=REGION%7C6; osp_aam=sg%3D2720351%2Csg%3D4683140%2Csg%3D8065423%2Csg%3D8065424%2Csg%3D4683140%2Csg%3D5835602%2Csg%3D8508409%2Csg%3D2824386%2Csg%3D11726816%2Csg%3D12243148%2Csg%3D13486431%2Csg%3D4047261; aam_uuid=56300553188481617562020318227576037998; _ga=GA1.3.1417908522.1554721514; _gid=GA1.3.66202524.1554721514; _gcl_au=1.1.484903221.1554721515; _fbp=fb.2.1554721515098.1695574163; _scid=a723ee1b-e0ca-4a0f-bd65-4d16b8b259a0; _sctr=1|1554670800000; __gads=ID=bc53726a41e70ec6:T=1554721516:S=ALNI_MZHMgT1yEzHxZtYNaW4ge05b4KkBA; ga_cid_cookie_val=1417908522.1554721514; ki_r=; ki_t=1554721525594%3B1554721525594%3B1554746840297%3B1%3B166; ki_s=188290%3A0.0.0.0.2%3B194146%3A0.0.0.0.0; JSESSIONID=node09zsqghpp27su1roloaujylgej10101.node0; latestSecurityAlert=2019-04-08T07:38:29; securityAlert=2019-04-08T07%3A38%3A29; _sp_ses.0536=*; __adal_ses=*; model_5mintimestamp=Mon Apr 08 2019 20:52:00 GMT+0300 (Moscow Standard Time); prev_5minmodel_run=1; y6=4; y39=2; mk_search_string_cookie=bmw; md_search_string_cookie=5series; y51=2; s3_AEsess=pageCntVx%3A-1%26time%3A5000; _s3_id.75.9ea4=8f419035fbe7839f.1554745950.1.1554746839.1554745950.; _s3_ses.75.9ea4=*; abTestGroups=cape-mdi-dzI-dqI-dmI-nrI-pmS-pbI-pfI-dsC-dnT-cfpat-dwI-svI-rdI-dpv2-drR-dcT-invic'
                ],
                'proxy' => $proxy
            )
        )
    );

    if (property_exists($response, 'ERR_CODE')) return json_encode($response);
    $carsHTML = $response->html;

    preg_match_all('#listing-title.*?page=\d+">.*?</a>#s', $carsHTML, $matches);
    foreach ($matches[0] as &$value) {
        $strpos = strpos($value, 'href');
        $value = substr($value, $strpos);
        preg_match('#\d{14,}#s', $value, $match);
        if ($match == null) {
            preg_match('#(?<=id=).*?(?=&)#s', $value, $match);
            if ($match != null) {
                $id = $match[0];
            } else {
                return (json_encode(array('ERR' => 'Couldn\'t retrieve the id', 'href' => $value)));
            }
        } else {
            $id = $match[0];
        }
        $value = $id;
    }

    $response = $matches[0];
    if (sizeof($matches[0]) == 0) {
        $response = (array('ERR_CODE' => 'EMPTY_IDS', 'html' => $carsHTML));
    }
    return json_encode($response);
}
