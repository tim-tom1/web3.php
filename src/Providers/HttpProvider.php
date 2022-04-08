<?php

/**
 * This file is part of web3.php package.
 * 
 * (c) Kuan-Cheng,Lai <alk03073135@gmail.com>
 * 
 * @author Peter Lai <alk03073135@gmail.com>
 * @license MIT
 */

namespace Web3\Providers;

use Web3\Methods\EthMethod;
use Web3\RequestManagers\RequestManager;

class HttpProvider extends Provider implements IProvider
{
    /**
     * methods
     * 
     * @var array
     */
    protected $methods = [];

    /**
     * construct
     * 
     * @param \Web3\RequestManagers\RequestManager $requestManager
     * @return void
     */
    public function __construct(RequestManager $requestManager)
    {
        parent::__construct($requestManager);
    }

    /**
     * send
     * 
     * @param EthMethod $method
     * @return mixed
     */
    public function send($method)
    {
        $payload = $method->toPayloadString();

        if (!$this->isBatch) {
            $proxy = function ($err, $res) use ($method) {
                if ($err !== null) {
                	throw new \RuntimeException($err);
                }
                if (!is_array($res)) {
                    $res = $method->transform([$res], $method->outputFormatters);
                    return $res[0];
                }
                return $method->transform($res, $method->outputFormatters);
            };
            return $this->requestManager->sendPayload($payload, $proxy);
        } else {
            $this->methods[] = $method;
            $this->batch[] = $payload;
        }
    }

    /**
     * batch
     * 
     * @param bool $status
     * @return void
     */
    public function batch($status)
    {
	    assert(is_bool($status));

        $this->isBatch = $status;
    }

    /**
     * execute
     *
     * @return mixed
     */
    public function execute()
    {
        if (!$this->isBatch) {
            throw new \RuntimeException('Please batch json rpc first.');
        }
        $methods = $this->methods;
        $proxy = function ($err, $res) use ($methods) {
            if ($err !== null) {
	            throw new \RuntimeException($err);
            }
            foreach ($methods as $key => $method) {
                if (isset($res[$key])) {
                    if (!is_array($res[$key])) {
                        $transformed = $method->transform([$res[$key]], $method->outputFormatters);
                        $res[$key] = $transformed[0];
                    } else {
                        $transformed = $method->transform($res[$key], $method->outputFormatters);
                        $res[$key] = $transformed;
                    }
                }
            }
            return $res;
        };
        $response = $this->requestManager->sendPayload('[' . implode(',', $this->batch) . ']', $proxy);
        $this->methods[] = [];
        $this->batch = [];
        return $response;
    }
}