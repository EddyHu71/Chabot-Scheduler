<?php
require __DIR__ . '/vendor/autoload.php';
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

// set false for production
$pass_signature = true;
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "MZyGpHKXlgysmyzfbAd9rU5qBwJdKnw/OxzTYDRrsGapIhdszPDNgMI0SdnqDqMCR2o7bAQh8DCzQRFpvyUJsBQq+1u1WoUxatFGzf0VlehWFAEQkBP1F4squ1Kmg2Gf9rAi9+5Yx0PwKcK8Y7mQqAdB04t89/1O/w1cDnyilFU=";
$channel_secret = "5615db743ce531f4a7e024e5e3b59a5c";
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
$configs =  [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);
 
// buat route untuk url homepage
$app->get('/', function($req, $res)
{
  echo "Welcome at Slim Framework";
});
 
// buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature)
{
    // get request body and line signature header
    $body        = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: '.$body);
 
    if($pass_signature === false)
    {
        // is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }
 
        // is this request comes from LINE?
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Invalid signature');
        }
    }
 
    // kode aplikasi nanti disini
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {
                    //cari nim
                    $balas = true;
                    $pengirim = $event['source']['userId'];
                    $replyInput = strtolower($event['message']['text']);
                    $word = explode(' ',trim($replyInput));
                    if ((strlen($event['message']['text']) === 9)&&(is_numeric($event['message']['text'])))
                    {
                        $msg = file_get_contents('https://iklcjadwal.info/ambil.php?nim=' . $event['message']['text']);
                    }
                    else if (strcmp($word[0],"jadwal") == 0)
                    {
                        if ((strlen($word[1]) === 9)&&(is_numeric($word[1])))
                        {
                            $msg = file_get_contents('https://iklcjadwal.info/ambil.php?nim=' . $word[1]);
                        }
                        else
                        {
                            $msg = file_get_contents('https://iklcjadwal.info/ambil.php?kode=' . $word[1]);
                        }
                    }
                    else if (strcmp($word[0],"help") === 0)
                    {
                        $balas = false;
                    }
                    else if (strcmp($pengirim,"U0f83975415512a7f87c1d238c3749842") == 0)
                    {
                        $msg = file_get_contents('https://iklcjadwal.info/ambil.php?kode=' . $word[1]);
                        $msgWord = explode(' ',trim($msg));
                        if ($msgWord[0] === "Maaf") $msg = $msg . "test \n";
                    }
                    else
                    {
                        $msg = "Kueri error, silakan cek kembali kata-kata";
                    }
                    if ($balas) $result = $bot->replyText($event['replyToken'], $msg);
                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
     
                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }
            }
        }
    }
});
$app->run();