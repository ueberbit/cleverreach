<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Service;

use GuzzleHttp\RequestOptions;
use Throwable;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
class RestService implements SingletonInterface
{
    private string $url = '';
    private string $bearerToken = '';
    private array $fetchCache = [];

    public function __construct(private readonly RequestFactory $requestFactory)
    {
    }

    /**
     * @param string $url
     * @return RestService
     */
    public function setUrl(string $url): RestService
    {
        $this->url = rtrim($url, '/');

        return $this;
    }

    /**
     * @param string $bearerToken
     * @return RestService
     */
    public function setBearerToken(string $bearerToken): RestService
    {
        $this->bearerToken = $bearerToken;

        return $this;
    }

    /**
     * makes a GET call
     * @param string $path
     * @param array|null $data
     * @param string $method
     * @return array|string
     */
    public function get(string $path, ?array $data = null, string $method = 'GET'): array | string
    {
        $url = $this->url . $path;

        if ($data !== null) {
            $url .= '?' . http_build_query($data);
        }

        if (!array_key_exists($url, $this->fetchCache)) {
            $this->fetchCache[$url] = $this->request($url, $method);
        }

        return $this->fetchCache[$url];
    }

    /**
     * makes a DELETE call
     * @param string $path
     * @param string|array|null $data
     * @return array|string
     */
    public function delete(string $path, null | string | array $data = null): array | string
    {
        $url = $this->url . $path;

        if ($data !== null) {
            $url .= '?' . http_build_query($data);
        }

        return $this->request($url, 'DELETE');
    }

    /**
     * makes a put call
     * @param string $path
     * @param string|array|null $data
     * @return array|string
     */
    public function put(string $path, null | string | array $data = null): array | string
    {
        return $this->request($this->url . $path, 'PUT', $data);
    }

    /**
     * does POST
     * @param string $path
     * @param string|array|null $data
     * @return array|string
     */
    public function post(string $path, null | string | array $data = null): array | string
    {
        return $this->request($this->url . $path, 'POST', $data);
    }

    /**
     * @param string $url
     * @param string $method
     * @param array|string|null $data
     * @return array|string
     */
    private function request(string $url, string $method, null | array | string $data = null): array | string
    {
        $options = [];

        if ($this->bearerToken) {
            $options[RequestOptions::HEADERS] = [
                'Authorization' => 'Bearer ' . $this->bearerToken,
            ];
        }

        if ($data !== null) {
            if (is_array($data)) {
                $data = json_encode($data, JSON_THROW_ON_ERROR);
            }

            $options[RequestOptions::BODY] = $data;
        }

        $response = $this->requestFactory->request($url, $method, $options)->getBody()->getContents();

        if ($response) {
            try {
                $decodedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

                if ($decodedResponse) {
                    $response = $decodedResponse;
                }
            } catch (Throwable $ex) {
                // no-op, ignore
            }
        }

        return $response;
    }
}
