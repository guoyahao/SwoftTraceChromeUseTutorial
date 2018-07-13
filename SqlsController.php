<?php
/**
 * This file is part of Swoft.
 *
 * @link https://swoft.org
 * @document https://doc.swoft.org
 * @contact group@swoft.org
 * @license https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\WebSocket;

use Swoft\Http\Message\Server\Request;
use Swoft\Http\Message\Server\Response;
use Swoft\WebSocket\Server\Bean\Annotation\WebSocket;
use Swoft\WebSocket\Server\HandlerInterface;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Class SqlsController - This is an controller for handle websocket
 * @package App\WebSocket
 * @WebSocket("/sqls/")
 */
class SqlsController implements HandlerInterface
{
    /**
     * 在这里你可以验证握手的请求信息
     * - 必须返回含有两个元素的array
     *  - 第一个元素的值来决定是否进行握手
     *  - 第二个元素是response对象
     * - 可以在response设置一些自定义header,body等信息
     * @param Request $request
     * @param Response $response
     * @return array
     * [
     *  self::HANDSHAKE_OK,
     *  $response
     * ]
     */
    public function checkHandshake(Request $request, Response $response): array
    {
        return [self::HANDSHAKE_OK, $response];
    }

    /**
     * 打开连接
     * @param Server $server
     * @param Request $request
     * @param int $fd
     * @return mixed
     */
    public function onOpen(Server $server, Request $request, int $fd)
    {
        $openMessage = json_encode(['type'=>'command','message'=>'LogServer is opening......']);

        $server->push($fd, $openMessage);
    }

    /**
     * 接收消息
     * @param Server $server
     * @param Frame $frame
     * @return mixed
     */
    public function onMessage(Server $server, Frame $frame)
    {
        ws()->sendToAll($frame->data);

        $client = $server->getClientInfo($frame->fd);

        if ($client["remote_ip"]='127.0.0.1')
        {
            $server->close($frame->fd);
        }

        $server->push($frame->fd, 'FD:'.$frame->fd.'hello, I have received your message: ' . $frame->data);
    }

    /**
     * 关闭链接
     * @param Server $server
     * @param int $fd
     * @return mixed
     */
    public function onClose(Server $server, int $fd)
    {
        $server->push($fd, 'this is client-fd'.$fd.' is close');
    }
}
