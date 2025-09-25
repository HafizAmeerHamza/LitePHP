<?php

use App\Core\Route;

function sendSuccess($message, $data = null, $statusCode = 200)
{
    http_response_code(200);

    return [
        'status' => true,
        'statusCode' => $statusCode,
        'message' => $message,
        'data' => $data
    ];
}

/**
* Used to return error response in json
*/
function sendError($message, $data = null, $statusCode = 400)
{
    // Set the actual HTTP response code
    http_response_code(200);

    return [
        'status' => false,
        'statusCode' => $statusCode,
        'message' => $message,
        'data' => $data
    ];
}

function asset($path)
{
    if(!empty(env('ASSET_URL'))) {
        $baseUrl = rtrim(env('ASSET_URL', ''), '/');
        $path = ltrim($path, '/');
    
        return $baseUrl . '/' . $path;
    }

    $path = ltrim($path, '/');
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    return $protocol . $host . '/assets/' . $path;
}

function baseUrl()
{
    return rtrim(env('APP_URL'), '/') . '/';
}

function route($name, $params = [])
{
    if (!method_exists(Route::class, 'getNamedRoutes')) {
        throw new \Exception("Route class must implement getNamedRoutes method.");
    }

    $namedRoutes = Route::getNamedRoutes();

    if (!isset($namedRoutes[$name])) {
        throw new \Exception("Route name '{$name}' not defined.");
    }

    $path = trim($namedRoutes[$name], '/');

    // Replace placeholders like {id} with actual parameters
    foreach ($params as $key => $value) {
        $path = str_replace("{{$key}}", $value, $path);
    }

    return rtrim(baseUrl() . $path, '/') . '/';
}

function redirect($path) {
    header("Location: " . route($path));
    exit;
}

function downloadPost($postURL)
{
    return $page=@file_get_contents($postURL);
    if($page){
        $data=array();

        preg_match_all("/<p class=\"share-update-card__update-text public-post__update-text\">([^<]+)<\/p>/",$page,$title_matches);
        if(isset($title_matches[1][0])){
            $data["title"]=$title_matches[1][0];
        }else{
            $data["title"]=null;
        }

        preg_match_all("/<video class=\"share-native-video__node video-js\"data-sources=\"(\[[^\]]*\])\"data-poster-url=\"([^\"]*)\".*><\/video>/",$page,$video_matches);
        if(isset($video_matches[1][0])){
            $data["videos"]=json_decode(html_entity_decode($video_matches[1][0]),true);
            var_dump($data["videos"]); 
            exit;
        }else{
            $data["videos"]=null;
        }

        var_dump($data);
        exit;
    }else{
        echo "failed to load page";
    }
}


