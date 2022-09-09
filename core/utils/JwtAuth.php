<?php

namespace core\utils;

use DateTimeImmutable;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;
use core\exceptions\AuthException;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\InvalidTokenStructure;
use Lcobucci\JWT\Encoding\CannotDecodeContent;
use Lcobucci\JWT\Token\UnsupportedHeaderFound;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;

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
     * access_token失效时间
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $expiresAt;

    /**
     * refresh_token失效时间
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $refresh_token_expiresAt;

    /**
     * jwt编号
     * @var string
     */
    private string $identified = 'LmB6MtvrprP@8cMqaT';

    /**
     * jwt签发者
     * @var string
     */
    private string $issuedBy = 'https://www.lcs-led.com';

    /**
     * jwt密钥串
     * @var string
     */
    private string $jwtSercet = 'Rng0UzhBejN6JXcmJmdZd2d0YlJBKkAkRDdzZmpHdXFYcnEhUiMkTmszZEBleEJj';

    public function __construct()
    {
        $this->claim = [
            'ipaddress' => ip2long(app()->request->ip()),
            'userAgent' => md5(app()->request->header('USER_AGENT'))
        ];
        $this->issuedAt = new DateTimeImmutable();
        $this->expiresAt = $this->issuedAt->modify(config('index.token_expire_time'));
        $this->refresh_token_expiresAt = $this->issuedAt->modify(config('index.refresh_token_expire_time'));
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
     * 生成Token
     * @return array
     * @param int|null $uid 用户id
     * @param int|null $gid 用户组id
     * @param string|null $audience 当前用户
     * @param null|bool $isRefreshToken 是否为refreshToken
     */
    public function createToken(?int $uid, ?int $gid, ?string $audience = 'brand', ?bool $isRefreshToken = false): array
    {
        $config = $this->createJwtObject();
        $builder = $config->builder();

        if ($isRefreshToken) {
            $builder
                ->relatedTo('refresh_token')
                ->expiresAt($this->refresh_token_expiresAt);
        } else {
            $builder
                ->relatedTo('access_token')
                ->expiresAt($this->expiresAt);
            $claims = array_merge(
                $this->claim, ['uid' => $uid, 'gid' => $gid]);
            foreach($claims as $k => $v){
                $builder->withClaim($k, $v);
            }
        }

        $token = $builder
            ->permittedFor($audience)
            ->issuedBy($this->issuedBy)
            ->issuedAt($this->issuedAt)
            ->identifiedBy($this->identified)
            ->canOnlyBeUsedAfter($this->issuedAt)
            ->getToken($config->signer(), $config->signingKey());

        return ['token' => $token->toString(), 'expiresAt' => $this->expiresAt->getTimestamp()];
    }

    /**
     * 解析Token
     * @return mixed
     * @param string $token
     */
    public function parseToken(string $token): mixed
    {
        $config = $this->createJwtObject();
        try {
            $parseToken = $config->parser()->parse($token);
            return json_decode(base64_decode($parseToken->claims()->toString()), true);
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $e) {
            throw new AuthException($e->getMessage());
        }
    }

    /**
     * 验证Token
     * @return void
     * @param string $token
     * @param bool|null $isRefreshToken 是否为refreshToken
     */
    public function verifyToken(string $token, ?bool $isRefreshToken = false): void
    {
        $config = $this->createJwtObject();
        $sub = $isRefreshToken ? 'refresh_token' : 'access_token';
        try {
            $token = $config->parser()->parse($token);
            assert($token instanceof UnencryptedToken);
        } catch (CannotDecodeContent | InvalidTokenStructure | UnsupportedHeaderFound $e) {
            throw new AuthException($e->getMessage());
        }

        /* validateExp */
        $timezone = new \DateTimeZone('Asia/Shanghai');
        $time = new SystemClock($timezone);
        $validateExp = new StrictValidAt($time);
        !$config->validator()->validate($token, $validateExp)
        && throw new AuthException('Token has expired, Please try login again');
        /* validateIssued */
        $validateIssued = new IssuedBy($this->issuedBy);
        !$config->validator()->validate($token, $validateIssued)
        && throw new AuthException('Issued verification failed, Please try login again');
        /* validatorSigned */
        $validatorSigned = new SignedWith(new Sha384(), InMemory::base64Encoded($this->jwtSercet));
        !$config->validator()->validate($token, $validatorSigned)
        && throw new AuthException('Signed verification failed, Please try login again');
        /* validateAud */
        $audience = $token->claims()->get('aud');
        $validateAud = new PermittedFor(array_shift($audience));
        !$config->validator()->validate($token, $validateAud)
        && throw new AuthException('Audience verification failed, Please try login again');
        /* validateJti */
        $validateJti = new IdentifiedBy($this->identified);
        !$config->validator()->validate($token, $validateJti)
        && throw new AuthException('Identified verification failed, Please try login again');
        /* validateSub */
        $validateSub = new RelatedTo($sub);
        !$config->validator()->validate($token, $validateSub)
        && throw new AuthException('Subject verification failed, Please check your token or try login again');
        /** Custom validate */
        if (!$isRefreshToken) {
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
}
