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

interface IProvider
{
    /**
     * send
     * 
     * @param \Web3\Methods\Method $method
     * @return mixed
     */
    public function send($method);

    /**
     * batch
     * 
     * @param bool $status
     * @return void
     */
    public function batch($status);

    /**
     * execute
     * 
     * @param callable $callback
     * @return void
     */
    public function execute();
}