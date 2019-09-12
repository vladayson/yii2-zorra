<?php

namespace vladayson\zorra;

use yii\base\Component;
use yii\helpers\Json;
use yii\httpclient\Client;

/**
 * Class ZorraTelecom
 * @package vladayson\zorra
 */
class ZorraTelecom extends Component
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $baseUrl = 'https://my.zorra.com/api/';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $token;

    /**
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->client = new Client(['baseUrl' => $this->baseUrl]);
    }

    /**
     * @return bool|string
     * @throws Exception
     */
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

    /**
     * @param $number
     * @param $sender
     * @param $text
     * @return bool
     * @throws Exception
     */
    public function sendSms($number, $sender, $text)
    {
        try {
            $this->login();
            $result = $this->client->post('v2/mailing/single/send', [
                'type' => 'sms',
                'sender' => $sender,
                'body' => $text,
                'recipient' => $number,
            ])->addHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept'        => 'application/json',
            ])
                                   ->send();

            if ($result->statusCode != 200) {
                return false;
            }
            $data = Json::decode($result->getContent());

            return $data['success'] == true;
        } catch (\Exception $e) {
            return false;
        }
    }
}