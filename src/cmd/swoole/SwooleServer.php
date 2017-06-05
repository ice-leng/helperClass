<?php

/**
 * Created by PhpStorm.
 * User: lengbin
 * Date: 2017/5/8
 * Time: 上午11:23
 */

namespace lengbin\helper\cmd\swoole;

class SwooleServer
{
    private $_webSocket;

    public function __construct()
    {
        $this->_webSocket = new \swoole_websocket_server("192.168.1.112", 9502);
        $this->_webSocket->set([
            'worker_num'    => 8,
            'daemonize'     => false,
            'max_request'   => 10000,
            'dispatch_mode' => 2,
            'debug_mode'    => 1,
        ]);
        $this->_webSocket->on('Open', [$this, 'onOpen']);
        $this->_webSocket->on('Message', [$this, 'onMessage']);
        $this->_webSocket->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->_webSocket->on('Close', [$this, 'onClose']);
        $this->_webSocket->start();
    }

    /**
     * 连接， 长连接
     *
     * @param $ws
     * @param $request
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function onOpen($ws, $request)
    {
        $ws->push($request->fd, json_encode(['status' => 1]));
    }

    /**
     * 接收后， 推送消息
     * @param $ws
     * @param $frame
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function onMessage($ws, $frame)
    {
        //获得接收数据
        $request = $frame->data;
        $fd = '1';
        $string = '1'; // string / json
        $ws->push($fd, $string);
    }

    /**
     * crobb 支持 微秒运行
     * @param $ws
     * @param $worker_id
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function onWorkerStart($ws, $worker_id)
    {
        $ws->tick(1000, function () use ($ws) {
            $fd = '1';
            $string = '1'; // string / json
            $ws->push($fd, $string);
        });
    }

    /**
     * 长连接断开
     * @param $ws
     * @param $fd
     *
     * @author lengbin(lengbin0@gmail.com)
     */
    public function onClose($ws, $fd)
    {
        echo "Client {$fd} close connection\n";
    }
}
