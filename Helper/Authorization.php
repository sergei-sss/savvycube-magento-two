<?php

namespace SavvyCube\Connector\Helper;

use Magento\Framework;


class Authorization extends \Magento\Framework\App\Helper\AbstractHelper
{
    const TIMESTAMP_GAP = 600; # 10 min

    const NONCE_TTL = 900; # 15 min

    const SESSION_TTL = 600; # 10 min

    protected $encryptor;

    protected $date;

    protected $cache;

    protected $scRsa;

    protected $rsa;

    protected $cRsa;

    protected $storeManager;

    protected $helper;

    protected $backendUrl;

    protected $configWriter;


    public function __construct(
        Framework\App\Helper\Context $context,
        Framework\App\Config\Storage\WriterInterface $configWriter,
        Framework\Encryption\EncryptorInterface $encryptor,
        Framework\Stdlib\DateTime\DateTime $date,
        Framework\App\Cache $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SavvyCube\Connector\Helper\Data $helper,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
        $this->date = $date;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->backendUrl = $backendUrl;
        parent::__construct($context);
    }

    public function getActivationUrl()
    {
        $savvyUrl = $this->getConfig('savvycube/settings/savvy_url');
        $params = [
            'type' => 'm1',
            'admin_url' => $this->backendUrl->getUrl('savvyadmin/index/activate'),
            'url' => $this->getConfig('savvycube/settings/base_url'),
            'session' => $this->getConfig('savvycube/settings/candidate_ts'),
            'pub' => base64_encode($this->getCandidatePublicKey())
        ];
        $url = $savvyUrl
            . "account/connect-login?"
            . http_build_query($params);
        return $url;
    }

    public function getAppSourcesUrl()
    {
        return $this->getConfig('savvycube/settings/savvy_url')
          . "sources";
    }

    public function cleanCache()
    {
        $this->cache->clean([\Magento\Framework\App\Cache\Type\Config::CACHE_TAG]);
        $this->storeManager->reinitStores();
    }

    public function promoteCandidateKeys($session)
    {
        $currentTs = (int)$this->date->gmtTimestamp();
        if ($session == $this->getConfig('savvycube/settings/candidate_ts')
            && $currentTs - $this->getConfig('savvycube/settings/candidate_ts') < 120
        ) {
            $this->setPublicKey($this->getCandidatePublicKey());
            $this->setPrivateKey($this->getCandidatePrivateKey());
            $this->setCandidatePublicKey('');
            $this->setCandidatePrivateKey('');
            $this->setConfig('savvycube/settings/candidate_ts', 0);
            $this->rsa = null;
            $this->cRsa = null;
            return True;
        }

        return False;
    }

    public function generateKeys()
    {
        if (class_exists("\phpseclib3\Crypt\RSA")) {
            $privateKey = \phpseclib3\Crypt\RSA::createKey(2048);
            $pub = $privateKey->getPublicKey()->toString('PKCS1');
            $priv = $privateKey->toString('PKCS1');
        } else {
            $keys = $this->getRsa()->createKey(2048);
            $pub = $keys['publickey'];
            $priv = $keys["privatekey"];
            $this->cRsa = null;
        }

        $this->setCandidatePublicKey($pub);
        $this->setCandidatePrivateKey($priv);
    }

    public function getScRsa()
    {
        if (!isset($this->scRsa)) {
            if (class_exists("\phpseclib3\Crypt\RSA")) {
                $this->scRsa = \phpseclib3\Crypt\RSA::loadPublicKey($this->getToken())
                    ->withSaltLength(128)
                    ->withHash("sha1")
                    ->withMGFHash("sha1");
            } else {
                $this->scRsa = new \phpseclib\Crypt\RSA();
                $this->scRsa->loadKey($this->getToken());
                $this->scRsa->setSaltLength(128);
            }
        }

        return $this->scRsa;
    }

    public function getCandidateRsa()
    {
        if (!isset($this->cRsa)) {
            if (class_exists("\phpseclib3\Crypt\RSA")) {
                $this->cRsa = \phpseclib3\Crypt\RSA::loadPrivateKey($this->getCandidatePrivateKey())
                    ->withSaltLength(128)
                    ->withHash("sha1")
                    ->withMGFHash("sha1");
            } else {
                $this->cRsa = new \phpseclib\Crypt\RSA();
                $this->cRsa->loadKey($this->getCandidatePrivateKey());
                $this->cRsa->setSaltLength(128);
            }

        }

        return $this->cRsa;
    }

    public function getRsa()
    {
        if (!isset($this->rsa)) {
            if (class_exists("\phpseclib3\Crypt\RSA")) {
                $this->rsa = \phpseclib3\Crypt\RSA::loadPrivateKey($this->getPrivateKey())
                    ->withSaltLength(128)
                    ->withHash("sha1")
                    ->withMGFHash("sha1");
            } else {
                $this->rsa = new \phpseclib\Crypt\RSA();
                $this->rsa->loadKey($this->getPrivateKey());
                $this->rsa->setSaltLength(128);
            }

        }

        return $this->rsa;
    }

    protected function getConfig($path)
    {
        return $this->scopeConfig->getValue($path);
    }

    protected function setConfig($path, $value)
    {
        $this->configWriter->save($path, $value);
    }

    public function getToken()
    {
        return $this->getConfig('savvycube/settings/token');
    }

    public function setToken($token)
    {
        $this->setConfig('savvycube/settings/token', $token);
    }

    public function getCandidatePublicKey()
    {
        return $this->getConfig('savvycube/settings/candidate_pub');
    }

    public function setCandidatePublicKey($val)
    {
        $this->setConfig('savvycube/settings/candidate_pub', $val);
    }

    public function getCandidatePrivateKey()
    {
        return $this->encryptor->decrypt(
            $this->getConfig('savvycube/settings/candidate_priv')
        );
    }

    public function setCandidatePrivateKey($val)
    {
        $currentTs = (int)$this->date->gmtTimestamp();
        $val = $this->encryptor->encrypt($val);
        $this->setConfig('savvycube/settings/candidate_priv', $val);
        $this->setConfig('savvycube/settings/candidate_ts', $currentTs);
    }

    public function getPublicKey()
    {
        return $this->getConfig('savvycube/settings/pub');
    }

    public function setPublicKey($val)
    {
        $this->setConfig('savvycube/settings/pub', $val);
    }

    public function getPrivateKey()
    {
        return $this->encryptor->decrypt($this->getConfig('savvycube/settings/priv'));
    }

    public function setPrivateKey($val)
    {
        $val = $this->encryptor->encrypt($val);
        $this->setConfig('savvycube/settings/priv', $val);
    }

    public function nonceTable()
    {
        return $this->helper->getTableName('savvycube_nonce');
    }

    public function sessionTable()
    {
        return $this->helper->getTableName('savvycube_session');
    }

    public function checkNonce($nonce)
    {
        $nonce = (int)$nonce;
        $nonceTable = $this->nonceTable();
        $select = $this->helper->getConnection()->select();
        $select->from($nonceTable, 'nonce')
            ->where('nonce = ?', $nonce)
            ->where('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) < ?', self::NONCE_TTL);
        $duplicate = $this->helper->getConnection()->fetchOne($select);
        if (!$duplicate) {
            $this->helper->getConnection('core_write')
                ->insert($nonceTable, array('nonce' => $nonce));
            return true;
        }

        return false;
    }

    public function cleanNonce()
    {
        $this->helper->getConnection()->delete($this->nonceTable(), array(
            'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) > ?' => self::NONCE_TTL));
    }

    public function cleanSession()
    {
        $this->helper->getConnection()->delete($this->sessionTable(), array(
            'UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) > ?' => self::SESSION_TTL));
    }

    public function createSession($key)
    {
        $session = uniqid('session_');
        $this->helper->getConnection()->insert(
            $this->sessionTable(), array('session' => $session, 'key' => $key));
        return $session;
    }

    public function getKeyBySession($session)
    {
        $select = $this->helper->getConnection()->select()
            ->from($this->sessionTable(), 'key')
            ->where('session = ?', $session)
            ->where('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at) < ?', self::SESSION_TTL);

        $key = $this->helper->getConnection()->fetchOne($select);
        if ($key)
            return $this->cleanKey($key);
        return False;
    }

    private function getRandomString($len) {
        if (class_exists("\phpseclib3\Crypt\Random")) {
            return \phpseclib3\Crypt\Random::string($len);
        }
        return \phpseclib\Crypt\Random::string($len);
    }

    public function encrypt($key, $data)
    {
        if (class_exists("\phpseclib3\Crypt\AES")) {
            $cipher = new \phpseclib3\Crypt\AES('cbc');
        } else {
            $cipher = new \phpseclib\Crypt\AES();
        }

        $cipher->setKey($key);
        $iv = $this->getRandomString($cipher->getBlockLength() >> 3);
        $cipher->setIV($iv);
        return array($iv, base64_encode($cipher->encrypt($data)));
    }

    public function verifySignature($baseStr, $sig)
    {
        return $this->getScRsa()->verify($baseStr, base64_decode($sig));
    }

    public function auth($request)
    {
        $baseUrl = $this->getConfig('savvycube/settings/base_url');
        $method = strtoupper($request->getMethod());
        $url = strtolower(
            implode('/',
                [trim($baseUrl, '/'),
                trim($request->getOriginalPathInfo(), '/')]
            )
        );
        $paramsBase = array();
        $params = $request->getParams();
        ksort($params, SORT_STRING);
        foreach ($params as $key=>$value) {
            $paramsBase[] = $key . "=" . $value;
        }

        $paramsBase = implode('&', $paramsBase);
        $nonce = $request->getHeader('SC-NONCE');
        $timestamp = $request->getHeader('SC-TIMESTAMP');
        $sig = $request->getHeader('SC-AUTHORIZATION');
        if ($nonce && $timestamp && $sig) {
            $baseStr = implode('&', array($method, $url, $paramsBase, $nonce, $timestamp));
            return $this->checkTimestamp($timestamp)
                && $this->checkNonce($nonce)
                && $this->verifySignature($baseStr, $sig);
        }

        return False;
    }

    public function checkTimestamp($timestamp)
    {
        $currentTs = (int)$this->date->gmtTimestamp();
        return abs($currentTs - (int)$timestamp) < self::TIMESTAMP_GAP;
    }

    public function cleanKey($key)
    {
        return $this->getRsa()->decrypt(base64_decode($key));
    }

    public function candidateSignature($session)
    {
        $currentTs = (int)$this->date->gmtTimestamp();
        if ($session == $this->getConfig('savvycube/settings/candidate_ts')
            && $currentTs - $this->getConfig('savvycube/settings/candidate_ts') < 120
        ) {
            $rsa = $this->getCandidateRsa();
            $iv = $this->getRandomString(10);
            return array(base64_encode($iv),
                base64_encode($rsa->sign($iv)));
        }

        return False;
    }
}
