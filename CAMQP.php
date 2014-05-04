<?php

/**
 * AMQP extension wrapper to communicate with RabbitMQ server
 * @version 1
 */
class CAMQP extends CApplicationComponent
{

    /** @var string */
    public $host = '';
    /** @var string */
    public $port = '';
    /** @var string */
    public $vhost = '';
    /** @var string */
    public $login = '';
    /** @var string */
    public $password = '';

    /**
     * @var \PhpAmqpLib\Connection\AMQPConnection
     */
    private $_connect = null;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    private $_channel = null;

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getVhost()
    {
        return $this->vhost;
    }

    /**
     * @param string $vhost
     */
    public function setVhost($vhost)
    {
        $this->vhost = $vhost;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin($login)
    {
        $this->login = $login;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /*    name: $exchange
      type: direct
      passive: false
      durable: true // the exchange will survive server restarts
      auto_delete: false //the exchange won't be deleted once the channel is closed.
     */
    public function declareExchange($name, $type = 'fanout', $passive = false, $durable = true, $auto_delete = false)
    {

        return $this->getChanel()->exchange_declare($name, $type, $passive, $durable, $auto_delete);
    }

    /*
      name: $queue
      passive: false
      durable: true // the queue will survive server restarts
      exclusive: false // the queue can be accessed in other channels
      auto_delete: false //the queue won't be deleted once the channel is closed.
     */

    public function declareQueue($name, $passive = false, $durable = true, $exclusive = false, $auto_delete = false)
    {
        return $this->getChanel()->queue_declare($name, $passive, $durable, $exclusive, $auto_delete);
    }

    public function bindQueueExchanger($queueName, $exchangeName, $routingKey = '')
    {
        $this->getChanel()->queue_bind($queueName, $exchangeName, $routingKey);
    }

    public function publish_message(
        $message,
        $exchangeName,
        $routingKey = '',
        $content_type = 'text/plain',
        $app_id = ''
    ) {
        $toSend = new \PhpAmqpLib\Message\AMQPMessage($message, array(
            'content_type' => $content_type,
            'content_encoding' => 'utf-8',
            'app_id' => $app_id,
            'delivery_mode' => 2
        ));

        $this->getChanel()->basic_publish($toSend, $exchangeName, $routingKey);
    }

    public function closeConnection()
    {
        if ($this->_channel) {
            $this->_channel->close();
        }
        if ($this->_connect) {
            $this->_connect->close();
        }
    }

    public function exchangeDelete($name)
    {
        $this->getChanel()->exchange_delete($name);
    }

    /**
     * Connection lazy loading
     * todo: use \PhpAmqpLib\Connection\AMQPLazyConnection instead
     *
     * @return \PhpAmqpLib\Connection\AMQPConnection
     */
    private function getConnection()
    {
        if (!$this->getChanel()) {
            $this->_connect = new \PhpAmqpLib\Connection\AMQPConnection($this->host, $this->port, $this->login, $this->password, $this->vhost);
        }

        return $this->getChanel();
    }

    /**
     * Chanel lazy loading
     *
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    private function getChanel()
    {
        if (!$this->_channel) {

            $this->_channel = $this->getConnection()->channel();
        }

        return $this->_channel;
    }


}
