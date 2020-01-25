<?php
require __DIR__ . '/vendor/autoload.php';

use TelegramBot\Api\BotApi;

function callUrl($prefix, $number)
{
    $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.119 Safari/537.36',
    ];
 
    $ch = curl_init("https://moskva.beeline.ru/fancynumber/similar/?defnumber={$prefix}&pattern={$number}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


    $response = curl_exec($ch);        
    curl_close($ch);


    return $response;
}


$file = dirname(__FILE__ ) . '/data/number_list.txt' ;

//Получаем номера из файла
$list_number = [];
$fh = fopen($file,'r');
while ($line = fgets($fh)) {
    $number = [];
    $number['number'] = trim($line);
    $number['prefix'] = substr(trim($line), 0, 3);
    $number['last_number'] = substr(trim($line), 3);
    $list_number [] = $number;
}
fclose($fh);

$all_number = [];
$telegram_message = '';
foreach ($list_number as $item)
{
    $result = callUrl($item['prefix'], $item['last_number']); 
    $json = json_decode($result, true);

    //Перебор статусов номеров
    foreach ($json['Numbers'] as $status)
    {
        //Добавление номеров в одну общую коллекцию
        foreach ($status['Numbers'] as $number)
        {
            $all_number[] = $number['Value'];
        }       
    }

    //В этом месте проверяем есть нужный номер в списке
    if(in_array($item['number'], $all_number))
    {
        $telegram_message .= 'Номер найден ' . $item['number'] . "\n";
    }
    else
    {
        $telegram_message .= 'Номер не найден ' . $item['number'] . "\n";
    }

    $chatId = '';
    $bot = new BotApi('');

    //$bot->setProxy('root:6zd4{k879B8$@195.161.41.150:3128');
    $bot->sendMessage($chatId, $telegram_message, 'HTML'); 


}




?>