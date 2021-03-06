<?php

namespace App\Service\Wm\Jd;

class Curl
{

    public $config;

    public function call($api_type = '/api/v1', $args_data)
    {

        //请求接口参数数组
        $http_data = [
            "token" => $this->config['token'],
            "app_key" => $this->config['appKey'],
            "timestamp" => time(),
            "format" => 'json',
            "v" => '1.0',
            "jd_param_json" => json_encode($args_data)
        ];
        $http_data['sign'] = $this->signature($http_data);

        //如果没有参数，赋值为一个空对象
        if (count($args_data) == 0) {
            $http_data["jd_param_json"] = (object)[];
        }

        $result = $this->post($this->config['getWay'] . $api_type, $http_data);
        return $result;

    }

    /**
     * PRC调用请求
     * @param $url
     * @param $data
     * @return array
     */
    private function post($url, $data) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['content-type: application/x-www-form-urlencoded; charset=UTF-8']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_USERAGENT, 'jd-openapi-php-sdk');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        $request_response = curl_exec($ch);
        $curl_http_status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $curl_http_info = curl_getinfo($ch);
        if ($request_response == false || curl_errno($ch)) {
            $error = curl_error($ch);
            return array('code' => $curl_http_status, 'message' => $error,'http_info' => $curl_http_info);
        }
        $result = json_decode($request_response, true);
        if (is_null($result)) {
            $result = $request_response;
        }
        if (!empty($result['error'])) {
            return ['code'=>$result['code'], 'message'=>$result['参数校验失败']];
        }

        return [
            'code' => $curl_http_status,
            'message' => 'ok',
            'data' => $result,
            'http_info' => $curl_http_info
        ];

    }

    /**
     * API接口签名验签
     * @param $params
     * @return string
     */
    private function signature($params) {

        ksort($params);

        $sign_str = "";
        foreach ($params as $key => $value) {
            if ($value == '' || $key == 'sign')
                continue;
            $sign_str .= $key . $value;
        }

        $md5 = md5($this->config['appSecret'] . $sign_str . $this->config['appSecret']);
        return strtoupper($md5);

    }

    /**
     * 消息推送签名方法
     * @param $params
     * @return string
     */
    public function push_signature($params)
    {

        ksort($params);
        $sign_str = "";

        foreach ($params as $key => $value) {
            if ($key == 'signature')
                continue;
            $sign_str .= $key . "=" . $value;
        }

        $md5 = md5($this->config['appSecret'].$sign_str . $this->config['appSecret']);
        return strtoupper($md5);

    }

    /**
     * 信息返回
     * @param $message
     * @param int $code
     * @param array $data
     * @return array
     */
    public function response($message, $code = 400, $data = [])
    {
        return ['code'=>$code, 'message'=>$message, 'data'=>$data];
    }


}