<?php

namespace core\utils;

use DateTimeImmutable;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;
use core\exceptions\AuthException;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

class JwtAuth
{
    /**
     * Claim
     * @var array
     */
    private array $claim;

    /**
     * 签发时间
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $issuedAt;

    /**
     * 失效时间
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $expiresAt;

    /**
     * jwt编号
     * @var string
     */
    private string $identified = 'yEbgoir0QOwf13VrSJ';

    /**
     * jwt签发者
     * @var string
     */
    private string $issuedBy = 'https://www.brandsz.cn';

    /**
     * jwt密钥串
     * @var string
     */
    private string $jwtSercet = 'YvZubJZqZK0QI5YhWp7EP1Ytm9X3hJzL';

    public function __construct()
    {
        $this->claim = [
            'ipaddress' => ip2long(app()->request->ip()),
            'userAgent' => md5(app()->request->header('USER_AGENT'))
        ];
        $this->issuedAt = new DateTimeImmutable();
        $this->expiresAt = $this->issuedAt->modify(config('index.token_expire_time'));
    }

    /**
     * 生成配置项
     * @return Configuration
     */
    public function createJwtObject(): Configuration
    {
        return Configuration::forSymmetricSigner(new Sha384(), InMemory::base64Encoded($this->jwtSercet));
    }

    /**
     * 创建Token字符串
     * @return string
     * @param int $uid 用户id
     * @param int $gid 用户组id
     * @param  string $audience 当前用户
     */
    public function createToken(int $uid = 0, int $gid = 0, string $audience = 'szbrand'): string
    {
        $config = $this->createJwtObject();
        $builder = $config->builder();

        foreach($this->claim as $k => $v){
            $builder->withClaim($k, $v);
        }

        $token = $builder
            ->permittedFor($audience)
            ->issuedBy($this->issuedBy)
            ->issuedAt($this->issuedAt)
            ->expiresAt($this->expiresAt)
            ->withClaim('uid', $uid)
            ->withClaim('gid', $gid)
            ->identifiedBy($this->identified)
            ->canOnlyBeUsedAfter($this->issuedAt)
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }

    /**
     * 解析token
     * @return mixed
     * @param string $token
     */
    public function parseToken(string $token): mixed
    {
        try {
            $config = $this->createJwtObject();
            $decodeToken = $config->parser()->parse($token);
            return json_decode(base64_decode($decodeToken->claims()->toString()), true);
        } catch (\Exception $e) {
            throw new AuthException($e->getMessage());
        }
    }

    /**
     * 验证Token
     * @return void
     * @param string $token
     */
    public function verifyToken(string $token): void
    {
        $config = $this->createJwtObject();

        try {
            $token = $config->parser()->parse($token);
            assert($token instanceof UnencryptedToken);
        } catch (\Exception $e) {
            throw new AuthException($e->getMessage());
        }

        /* validateExp */
        $timezone = new \DateTimeZone('Asia/Shanghai');
        $time = new SystemClock($timezone);
        $validateExp = new StrictValidAt($time);
        /* validateJti */
        $validateJti = new IdentifiedBy($this->identified);
        /* validateAud */
        $audience = $token->claims()->get('aud');
        $validateAud = new PermittedFor($audience[0] ?? 'brand');
        /* validateIssued */
        $validateIssued = new IssuedBy($this->issuedBy);
        /* validatorSigned */
        $validatorSigned = new SignedWith(new Sha384(),InMemory::base64Encoded($this->jwtSercet));
        $config->setValidationConstraints($validateJti, $validateExp, $validateAud, $validateIssued, $validatorSigned);
        $constraints = $config->validationConstraints();
        try {
            $config->validator()->assert($token, ...$constraints);
        } catch(RequiredConstraintsViolated $e) {
            throw new AuthException(substr($e->getMessage(), 58) . ', Please try login again');
        }

        /* Gets the userAgent from current token */
        $userAgent = $token->claims()->get('userAgent');
        /* Gets the ipaddress from current token */
        $ipaddress = $token->claims()->get('ipaddress');
        /* Validate the userAgent from current token and now userAgent */
        $userAgent !== $this->claim['userAgent'] && throw new AuthException('Unauthorized operation, UserAgent have been changed');
        /* Validate the ipaddress from current token and now ipaddress */
        $ipaddress !== $this->claim['ipaddress'] && throw new AuthException('Unauthorized operation, Ipaddress have been changed');
    }
}
