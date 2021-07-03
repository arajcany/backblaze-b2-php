<?php

declare(strict_types = 1);

namespace Zaxbux\BackblazeB2\Client;

use function time;
use function json_encode;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Zaxbux\BackblazeB2\Client as B2Client;
use Zaxbux\BackblazeB2\B2Object\B2Object;
use Zaxbux\BackblazeB2\B2Object\ProxyArrayAccessToProperties;

/** @package Zaxbux\BackblazeB2\Client */
class AccountAuthorization implements B2Object
{
	use ProxyArrayAccessToProperties;

	public const ATTRIBUTE_ACCOUNT_ID                 = 'accountId';
	public const ATTRIBUTE_AUTHORIZATION_TOKEN        = 'authorizationToken';
	public const ATTRIBUTE_ALLOWED                    = 'allowed';
	public const ATTRIBUTE_API_URL                    = 'apiUrl';
	public const ATTRIBUTE_DOWNLOAD_URL               = 'downloadUrl';
	public const ATTRIBUTE_RECOMMENDED_PART_SIZE      = 'recommendedPartSize';
	public const ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE = 'absoluteMinimumPartSize';
	public const ATTRIBUTE_S3_API_URL                 = 's3ApiUrl';
	public const ATTRIBUTE_AUTHORIZATION_TIMESTAMP    = 'authorizationTimestamp';

	/**  @deprecated */
	public const ATTRIBUTE_MINIMUM_PART_SIZE          = 'minimumPartSize';

	/** @var string */
	private $accountId;

	/** @var string */
	private $authorizationToken;

	/** @var array */
	private $allowed;

	/** @var string */
	private $apiUrl;

	/** @var string */
	private $downloadUrl;

	/** @var int */
	private $recommendedPartSize;

	/** @var int */
	private $absoluteMinimumPartSize;

	/** @var string */
	private $s3ApiUrl;

	/** @var int */
	private $authorizationTimestamp;

	public function __construct(
		string $accountId,
		string $authorizationToken,
		array $allowed,
		string $apiUrl,
		string $downloadUrl,
		int $recommendedPartSize,
		int $absoluteMinimumPartSize,
		string $s3ApiUrl,
		?int $authorizationTimestamp = null
	) {
		$this->accountId = $accountId;
		$this->authorizationToken = $authorizationToken;
		$this->allowed = $allowed;
		$this->apiUrl = $apiUrl;
		$this->downloadUrl = $downloadUrl;
		$this->recommendedPartSize = $recommendedPartSize;
		$this->absoluteMinimumPartSize = $absoluteMinimumPartSize;
		$this->s3ApiUrl = $s3ApiUrl;
		$this->authorizationTimestamp = $authorizationTimestamp;
	}

	/**
	 * Get the value of accountId
	 */
	public function getAccountId()
	{
		return $this->accountId;
	}

	/**
	 * Set the value of accountId
	 *
	 * @return  self
	 */
	public function setAccountId($accountId)
	{
		$this->accountId = $accountId;

		return $this;
	}

	/**
	 * Get the value of authorizationToken
	 */
	public function getAuthorizationToken()
	{
		return $this->authorizationToken;
	}

	/**
	 * Set the value of authorizationToken
	 *
	 * @return  self
	 */
	public function setAuthorizationToken($authorizationToken)
	{
		$this->authorizationToken = $authorizationToken;

		return $this;
	}

	/**
	 * Get the value of allowed
	 */
	public function getAllowed()
	{
		return $this->allowed;
	}

	/**
	 * Set the value of allowed
	 *
	 * @return  self
	 */
	public function setAllowed($allowed)
	{
		$this->allowed = $allowed;

		return $this;
	}

	/**
	 * Get the value of apiUrl
	 */
	public function getApiUrl()
	{
		return $this->apiUrl;
	}

	/**
	 * Set the value of apiUrl
	 *
	 * @return  self
	 */
	public function setApiUrl($apiUrl)
	{
		$this->apiUrl = $apiUrl;

		return $this;
	}

	/**
	 * Get the value of downloadUrl
	 */
	public function getDownloadUrl()
	{
		return $this->downloadUrl;
	}

	/**
	 * Set the value of downloadUrl
	 *
	 * @return  self
	 */
	public function setDownloadUrl($downloadUrl)
	{
		$this->downloadUrl = $downloadUrl;

		return $this;
	}

	/**
	 * Get the value of recommendedPartSize
	 */
	public function getRecommendedPartSize()
	{
		return $this->recommendedPartSize;
	}

	/**
	 * Set the value of recommendedPartSize
	 *
	 * @return  self
	 */
	public function setRecommendedPartSize($recommendedPartSize)
	{
		$this->recommendedPartSize = $recommendedPartSize;

		return $this;
	}

	/**
	 * Get the value of absoluteMinimumPartSize
	 */
	public function getAbsoluteMinimumPartSize()
	{
		return $this->absoluteMinimumPartSize;
	}

	/**
	 * Set the value of absoluteMinimumPartSize
	 *
	 * @return  self
	 */
	public function setAbsoluteMinimumPartSize($absoluteMinimumPartSize)
	{
		$this->absoluteMinimumPartSize = $absoluteMinimumPartSize;

		return $this;
	}

	/**
	 * Get the value of s3ApiUrl
	 */
	public function getS3ApiUrl()
	{
		return $this->s3ApiUrl;
	}

	/**
	 * Set the value of s3ApiUrl
	 *
	 * @return  self
	 */
	public function setS3ApiUrl($s3ApiUrl)
	{
		$this->s3ApiUrl = $s3ApiUrl;

		return $this;
	}

	/**
	 * Get the value of authorizationTimestamp
	 */ 
	public function getAuthorizationTimestamp(): int
	{
		return $this->authorizationTimestamp;
	}

	/**
	 * Set the value of authorizationTimestamp
	 *
	 * @param int $authorizationTimestamp
	 */ 
	public function setAuthorizationTimestamp(int $authorizationTimestamp): AccountAuthorization
	{
		$this->authorizationTimestamp = $authorizationTimestamp;

		return $this;
	}

	/**
	 * Check if the authorization token has expired, based on the `authorizationTimestamp`.
	 * Will always return `false` if there is no `authorizationTimestamp`.
	 * 
	 * @return bool `true` if `NOW` - `VALIDITY_PERIOD` ≥ `AUTHORIZATION_TIMESTAMP`; `false` otherwise.
	 */
	public function isStale(): bool
	{
		return time() - IAuthorizationCache::EXPIRES >= $this->authorizationTimestamp ?? 0;
	}

	public static function refresh(string $applicationKeyId, string $applicationKey, ClientInterface $client = null): AccountAuthorization {
		$client = $client ?: new Client([
			'base_uri' => B2Client::B2_API_BASE_URL . B2Client::B2_API_V2,
		]);

		$response = $client->request('GET', '/b2_authorize_account', [
			'headers' => [
				'Authorization' => static::getBasicAuthorization($applicationKeyId, $applicationKey)
			]
		]);

		return AccountAuthorization::fromArray(json_decode((string) $response->getBody(), true));
	}

	public static function fromArray(array $data): AccountAuthorization
	{
		return new AccountAuthorization(
			$data[static::ATTRIBUTE_ACCOUNT_ID],
			$data[static::ATTRIBUTE_AUTHORIZATION_TOKEN],
			$data[static::ATTRIBUTE_ALLOWED],
			$data[static::ATTRIBUTE_API_URL],
			$data[static::ATTRIBUTE_DOWNLOAD_URL],
			$data[static::ATTRIBUTE_RECOMMENDED_PART_SIZE],
			$data[static::ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE],
			$data[static::ATTRIBUTE_S3_API_URL],
			$data[static::ATTRIBUTE_AUTHORIZATION_TIMESTAMP]
		);
	}

	public function jsonSerialize(): array
	{
		return [
			static::ATTRIBUTE_ACCOUNT_ID => $this->accountId,
			static::ATTRIBUTE_AUTHORIZATION_TOKEN => $this->authorizationToken,
			static::ATTRIBUTE_ALLOWED => $this->allowed,
			static::ATTRIBUTE_API_URL => $this->apiUrl,
			static::ATTRIBUTE_DOWNLOAD_URL => $this->downloadUrl,
			static::ATTRIBUTE_RECOMMENDED_PART_SIZE => $this->recommendedPartSize,
			static::ATTRIBUTE_ABSOLUTE_MINIMUM_PART_SIZE => $this->absoluteMinimumPartSize,
			static::ATTRIBUTE_S3_API_URL => $this->s3ApiUrl,
			static::ATTRIBUTE_AUTHORIZATION_TIMESTAMP => $this->authorizationTimestamp,
		];
	}

	public function __toString()
	{
		return json_encode($this);
	}

	/**
	 * Create a base64 encoded string comprised of the application key and application key ID.
	 * 
	 * @internal
	 * 
	 * @param string $applicationKeyId 
	 * @param string $applicationKey   
	 * 
	 * @return string base64 encoded string for HTTP `Authorization` header.
	 */
	private static function getBasicAuthorization(string $applicationKeyId, string $applicationKey): string {
		return 'Basic ' . base64_encode($applicationKeyId . ':' . $applicationKey);
	}
}
