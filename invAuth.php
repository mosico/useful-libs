<?php
$auth = new Authentication();


//Create Token
$user = new \stdClass();
$user->user_id = (int) $_GET['test_uid'] ?: 4333139;  //4333139;//1121990
$user->first_name = 'fName';
$user->nickname = 'nickName';
$user->ip_address = '192.168.0.1';
$auth->setUser($user);
$token = $auth->token();
echo "UserId: ". $user->user_id . "<br>";
echo "Token: $token <br><br><br>";


//Decrypt Token
// $token = '1c4351b28b1f500676abf65c8fd0fc518ad31a048e6a3dbf00c0b773719ce7efa4d36362729e0cf4f1a3778d1dabc884cbd5dd563d18767aa26f4beb7390125bdabf324fcc1870f8c190c136c85acd7e41c346b2c7452370b70219ae217997a5f7b777df9f53cbcb';
$token = '1c4351b28b1f500676abf65c8fd0fc518ad31a048e6a3dbf00c0b773719ce7efa4d36362729e0cf4f1a3778d1dabc884cbd5dd563d18767aa26f4beb7390125bdabf324fcc1870f84dcefcef19b26f10a2f10203da5de64020dcf629348890f0d7281a347d09cd5b';
// Lemon token
$token = '1c4351b28b1f5006e7ad2d17efbad9daf634e35359ea9208b3da568c3bf09e18d4dce4ad17bf516c98fe3b1868fe6c68563aec157ff02a5668c451df5391e74164abe8c51f4f46ef02265b64a379d05c8f4a68c36111ee686e43dc07cb5b090db8a7ec702405580e78e5e374feac7f8a';
$user = $auth->decrypt($token);
$user = json_decode($user);
var_dump($user);


class Authentication {
    private $user;
    private $key;
    private $iv;

    function __construct() {
        $this->iv = "51d242a5";
        $this->key = 'D9B1CF106F3A4889575B6802';
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function setIV($iv) {
        $this->iv = $iv;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function token(){
        return $this->encrypt(json_encode($this->user));
    }

    public function validate($buffer){
        return ($this->decrypt($buffer));
    }

    public function encrypt($buffer) {
        $extra = 8 - (strlen($buffer) % 8);
        if($extra > 0) {
            for($i = 0; $i < $extra; $i++) {
                $buffer .= "\0";
            }
        }

        return bin2hex(mcrypt_cbc(MCRYPT_3DES, $this->key, $buffer, MCRYPT_ENCRYPT, $this->iv));
    }

    public function decrypt($buffer) {
        return rtrim(mcrypt_cbc(MCRYPT_3DES, $this->key, $this->hex2bin($buffer), MCRYPT_DECRYPT, $this->iv), "\0");
    }

    function hex2bin($data)
    {
        $len = strlen($data);
        return pack("H" . $len, $data);
    }
}