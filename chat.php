<?php
/**
 * Created by PhpStorm.
 * User: libi
 * Date: 2019/5/10
 * Time: 10:22 AM
 */


$socket = socket_create_listen(8889);

//非阻塞模式
socket_set_nonblock($socket);

//所有客户端连接
$conns = [];
//需要监听的
$write_fd = [];
$read_fd = [];
$e = [];

$users = [];

while(1){

    $read_fd = array_merge($conns,array($socket));
    socket_select($read_fd,$write_fd,$e,0);

    //socket 有新的连接
    if(in_array($socket,$read_fd)){
        $conn = socket_accept($socket);
        $id = (int)$conn;
        $conns[$id] = $conn;
        $write_fd[$id] = $conn;
        socket_write($conn,"使用 @昵称[空格] 可以私聊，例如 @libi xxxx".PHP_EOL);
        socket_write($conn,"请输入昵称:");
        $key = array_search($socket, $read_fd);
        unset($read_fd[$key]);
    }
    $messages = [];
    $welcome = [];
    $leaves = [];
    $priv_messages = [];
    foreach ($read_fd as $fd){
        $message = socket_read($fd,1024);
        $id = (int)$fd;
        var_dump($id);var_dump($message);
        if(!$message){
            socket_close($fd);
            unset($conns[$id]);
            unset($write_fd[$id]);
            if(isset($users[$id])){
                $leaves[] = $users[$id];
            }
            continue;
        }

        if(!isset($users[$id])){
            $nickname = str_replace(PHP_EOL,"",$message);
            $users[$id] = $nickname;
            $welcome[] = $nickname;
            $message = null;
        }
        if($message){
            $is_priv = false;
            if(strpos($message,"@") === 0){
                echo $message;
                $strs = explode(" ",$message);
                $nickname = substr($strs[0],1);
                $to_id = array_search($nickname,$users);
                if($to_id){
                    $priv_message['from'] = $id;
                    $priv_message['to'] = $to_id;
                    $priv_message['message'] = substr($message,strlen($nickname)+2);
                    $priv_messages[] = $priv_message;
                    $is_priv = true;
                }

            }
            if(!$is_priv){
                $messages[$id] = $message;
            }

        }

    }

    foreach ($write_fd as $fd){
        if(count($welcome)>0){
            foreach($welcome as $nickname){
                socket_write($fd,"欢迎".$nickname."登录聊天室".PHP_EOL);

                socket_write($fd,"在线用户:");
                foreach ($users as $user){
                    socket_write($fd,$user." ");
                }
                socket_write($fd,PHP_EOL);

            }
        }
        if(count($leaves)>0){
            foreach($leaves as $nickname){
                socket_write($fd,$nickname."离开了聊天室".PHP_EOL);
            }
        }
        if(count($messages)>0){
            foreach($messages as $id=>$message){
                socket_write($fd,'['.$users[$id]."]: ".$message);
            }
        }
        if(count($priv_messages)>0){
            foreach($priv_messages as $priv_message){
                if($priv_message['to'] == (int)$fd){
                    socket_write($fd,'[私聊信息]'.$users[$priv_message['from']]."对你说: ".$priv_message['message']);
                }
            }
        }
    }

}

