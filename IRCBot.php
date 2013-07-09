<?php
set_time_limit(0);
ini_set('display_errors', 'on');

$config = array( 
		'server' => 'irc.server.com', 
		'port' => 6667,
		'nick' => 'php_bot',
		'name' => 'php_bot', 
		'pass' => 'nickservpassword', 
                'email' => 'nickserv@register.email'
);

class IRCBot {
	var $socket;
	var $ex = array();
        
        var $channels = array(
            '#channel1', 
            '#channel2',
            '#channel3'
        );

        function __construct($config) {
            $this->socket = fsockopen($config['server'], $config['port']);
            $this->login($config);
            $this->main($config);
	}

	function login($config)	{
            $this->write('NICK', $config['nick']);
            $this->write('USER', $config['nick'].' 0 * :'.$config['name']);
	}

	function main($config)	{
		$data = fgets($this->socket, 128);
		echo nl2br($data);
		flush();
		$this->ex = explode(' ', $data);

		if($this->ex[0] == 'PING'){
                    $this->write('PONG', $this->ex[1]);
		}
                
                $command = str_replace(array(chr(10), chr(13)), '', $this->ex[3]);
                
                    switch($command) {
                        case ':~phpjoin':
                            echo "joining ".$this->ex[4];
                            $this->join($this->ex[4]);
                            break;
                        case ':~phpquit':
                            $this->write('QUIT', 'php is the balls dude :)');
                            break;
                        case ':~phpregister':
                            $this->write('PRIVMSG', 'nickserv register '.$config['pass'].' '.$config['email']);
                            break;
                        case ':~phpidentify':
                            echo "Identifying";
                            $this->write('PRIVMSG', 'nickserv identify '.$config['pass']);
                            break;
#                           command not complete
//                        case ':~phpeval':
//                            $code = implode(" ", array_splice($this->ex, 5));
//                            ob_start();
//                            eval($code);
//                            $ret = ob_get_clean();
//                            $this->
//                            break;
                }
		$this->main($config);
	}

	function write($cmd, $msg = null) {
		if($msg == null) {
			fputs($this->socket, $cmd." \r\n");
			echo $cmd;
		} else {
			fputs($this->socket, $cmd.' '.$msg." \r\n");
			echo $cmd.' '.$msg;
		}
	}

	function join($channel)	{
		if(is_array($channel)) {
			foreach($channel as $chan) {
                            $this->write('JOIN', $chan);
			}
		} else {
			$this->write('JOIN', $channel);
		}
	}

	function mode_protect($user = '') {
		if($user == '') {
                    if(php_version() >= '5.3.0')
                    {
			$user = strstr($this->ex[0], '!', true);
                    } else {
			$length = strstr($this->ex[0], '!');
			$user   = substr($this->ex[0], 0, $length);
                    }
		}
        	$this->write('MODE', $this->ex[2] . ' +a ' . $user);
                    }

	function mode_op($channel = '', $user = '', $op = true)	{
		if($channel == '' || $user == '') {
			if($channel == '') {
                            $channel = $this->ex[2];
			}

			if($user == '') { 
                            if(php_version() >= '5.3.0')
                            {
				$user = strstr($this->ex[0], '!', true);
                            } else {
				$length = strstr($this->ex[0], '!');
				$user   = substr($this->ex[0], 0, $length);
                            }
			}
		}

		if($op) {
			$this->write('MODE', $channel . ' +o ' . $user);
		} else {
			$this->write('MODE', $channel . ' -o ' . $user);
		}
	}
}
$bot = new IRCBot($config);
?>
