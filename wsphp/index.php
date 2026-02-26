<?php
/*header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

foreach($_SERVER as $chiave=>$valore){
    echo $chiave."-->".$valore."\n<br>";
}
*/

//elabora header
$metodo=$_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

//legge il tipo di contenuto inviato dal client
$ct=$_SERVER["CONTENT_TYPE"];
$type=explode("/",$ct);

//legge il tipo di contenuto di ritorno richiesto dal client
$retct=$_SERVER["HTTP_ACCEPT"];
$ret=explode("/",$retct);
echo $type[1];
//print_r($uri);
//echo "metodo-->".$metodo;

if ($metodo=="GET"){
    echo "get";       
}
if ($metodo=="POST"){
    echo "post\n";
    //recupera i dati dall'header
   $body=file_get_contents('php://input');
   // echo $body
   
   //converte in array associativo
    if ($type[1]=="json"){
        $data = json_decode($body,true);
    }
    if ($type[1]=="xml"){
        $xml = simplexml_load_string($body);
        $json = json_encode($xml);
        $data = json_decode($json, true);
    }
    
    //elabora i dati o interagisce con il database
    $data["valore"]+=2000;
    
    //settaggio dei campi dell'header
    header("Content-Type: ".$retct);    
    //restituisce i dati convertiti nel formato richiesto
    if ($ret[1]=="json"){
        echo json_encode($data);
    }
    if ($ret[1]=="xml"){
        $xml = new SimpleXMLElement('<root/>');
        array_walk_recursive($data, array ($xml, 'addChild'));    
        echo $xml->asXML();
        //alternativa
        $r='<?xml version="1.0"?><rec><nome>'.$data["nome"].'</nome><valore>'.$data["valore"].'</valore></rec>';
    }
   
}
if ($metodo=="PUT"){
    echo "put";
    //codice di risposta
    http_response_code(404);
}
if ($metodo=="DELETE"){
    echo "delete";
    http_response_code(404);
}



?>