<?php

namespace vladayson\zorratelecom;

use yii\base\Component;
use yii\helpers\Json;
use yii\httpclient\Client;

class ZorraTelecom extends Component
{
    public $email;

    public $password;

    public $baseUrl = 'https://my.zorra.com/api/';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $token;

    public function init()
    {
        parent::init();

        $this->client = new Client(['baseUrl' => $this->baseUrl]);
    }


    public function login()
    {
        $result = $this->client->post('auth/login', [
            'email' => $this->email,
            'password' => $this->password
        ])->send();

        if ($result->getStatusCode() != 200) {
            return false;
        }
        $data = Json::decode($result->getContent());
        $this->token = $data['access_token'];

        return $data;
    }

    public function sendSms($number, $sender, $text)
    {
        $this->login();
        $result = $this->client->post('v2/mailing/single/send', [
            'type' => 'sms',
            'sender' => $sender,
            'body' => $text,
            'recipient' => $number,
        ])->addHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->send();

        var_export($result); die;

        return $result;
    }
}