<?php
header('Sccess-Control-Allow-Origin: *');
header('Content-type: application/json');
$header[] = "Content-type: text/json";
$header[] = "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586";
$header_get[] = "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) nuoxian/API";

if (isset($_GET['uuid']) || isset($_GET['id'])) {
    $uuid = @$_GET['uuid'];
    
    $curl = curl_init();
    $curl_id = curl_init();
    
    if (!empty($_GET['id'])) {
        $id = @$_GET['id'];
        
        curl_setopt($curl_id, CURLOPT_URL, "https://api.nxvav.cn/api/mc/?id=".$id);
        curl_setopt($curl_id, CURLOPT_HEADER, false);
        curl_setopt($curl_id, CURLOPT_HTTPHEADER, $header_get);
        curl_setopt($curl_id, CURLOPT_RETURNTRANSFER, true);
        $uuid = json_decode(curl_exec($curl_id), true);
        $uuid = $uuid['uuid'];

        curl_close($curl_id);
    } else {
        curl_setopt($curl, CURLOPT_URL, "https://sessionserver.mojang.com/session/minecraft/profile/".$uuid."?unsigned=false");
        curl_close($curl_id);
    }
    
    curl_setopt($curl, CURLOPT_URL, "https://sessionserver.mojang.com/session/minecraft/profile/".$uuid."?unsigned=false");
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $data = curl_exec($curl);
    $data = json_decode($data, true);
    
    curl_close($curl);
    
    if (isset($data['errorMessage']) || $data == NULL) {
        exit(json_encode(array('code'=>'201','msg'=>'查询失败'),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    } else {
        $skin = $data['properties'][0]['value'];
        $skin = base64_decode($skin);
        $skin = json_decode($skin, true);
        // var_dump($skin);
        
        if ($_GET['type'] === "skin_url") {
            header("Location: ".$skin['textures']['SKIN']['url']);
        } elseif ($_GET['type'] === "skin_cloak") {
            // 判断玩家是否有披风
            if ($skin['textures']['CAPE']['url'] !== NULL) {
                header("Location: ".$skin['textures']['CAPE']['url']);
            } else {
                exit(json_encode(array('code'=>'201','msg'=>'无法获取当前玩家披风'),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            }
        } else {
            $Json = array (
                'code' => 200,
                'name' => $data['name'],
                'uuid' => $data['id'],
                'properties' => $data['properties'][0]['name'],
                'Yggdrasil' => $data['properties'][0]['signature'],
                'skin_' => array (
                    'skin_timestamp' => $skin['timestamp'],
                    'skin_url' => $skin['textures']['SKIN']['url'],
                    'skin_cloak' => $skin['textures']['CAPE']['url'],
                    'skin_model' => $skin['textures']['SKIN']['metadata']['model']
                    )
            );
            
            // var_dump($data);
        
            $Json = json_encode($Json,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            echo stripslashes($Json);
            return $Json;
        }
    }
} else {
    exit(json_encode(array('code'=>'201','msg'=>'主要参数为空'),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
?>