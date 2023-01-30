<?php

namespace App\Service\Wave;

final class WaveCheckoutRequest
{
    private string $amount;
    private string $currency;
    private string $client_reference;
    private string $success_url;
    private string $error_url;

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return WaveCheckoutRequest
     */
    public function setAmount(string $amount): WaveCheckoutRequest
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientReference(): string
    {
        return $this->client_reference;
    }

    /**
     * @param string $client_reference
     * @return WaveCheckoutRequest
     */
    public function setClientReference(string $client_reference): WaveCheckoutRequest
    {
        $this->client_reference = $client_reference;
        return $this;
    }

    /**
     * @return string
     */
    public function getSuccessUrl(): string
    {
        return $this->success_url;
    }

    /**
     * @param string $success_url
     * @return WaveCheckoutRequest
     */
    public function setSuccessUrl(string $success_url): WaveCheckoutRequest
    {
        $this->success_url = $success_url;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return WaveCheckoutRequest
     */
    public function setCurrency(string $currency): WaveCheckoutRequest
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorUrl(): string
    {
        return $this->error_url;
    }

    /**
     * @param string $error_url
     * @return WaveCheckoutRequest
     */
    public function setErrorUrl(string $error_url): WaveCheckoutRequest
    {
        $this->error_url = $error_url;
        return $this;
    }

}