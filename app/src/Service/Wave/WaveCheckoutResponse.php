<?php

namespace App\Service\Wave;

final class WaveCheckoutResponse
{
    private string $wave_launch_url;
    private string $checkout_session_id;

    private string $amount;

    private string $currency;
    private ?string $checkout_status;
    private ?string $client_reference;
    private ?string $last_payment_error;
    private ?string $payment_status;
    private ?string $transaction_id ;
    private \DateTime $when_completed;
    private \DateTime $when_created;
    private \DateTime $when_expires;

    /**
     * @return string
     */
    public function getWaveLaunchUrl(): ?string
    {
        return $this->wave_launch_url;
    }

    /**
     * @param string $wave_launch_url
     * @return WaveCheckoutResponse
     */
    public function setWaveLaunchUrl(?string $wave_launch_url): WaveCheckoutResponse
    {
        $this->wave_launch_url = $wave_launch_url;
        return $this;
    }

    /**
     * @return string
     */
    public function getCheckoutSessionId(): ?string
    {
        return $this->checkout_session_id;
    }

    /**
     * @param string $checkout_session_id
     * @return WaveCheckoutResponse
     */
    public function setCheckoutSessionId(?string $checkout_session_id): WaveCheckoutResponse
    {
        $this->checkout_session_id = $checkout_session_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): ?string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return WaveCheckoutResponse
     */
    public function setAmount(?string $amount): WaveCheckoutResponse
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return WaveCheckoutResponse
     */
    public function setCurrency(?string $currency): WaveCheckoutResponse
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCheckoutStatus(): ?string
    {
        return $this->checkout_status;
    }

    /**
     * @param string $checkout_status
     * @return WaveCheckoutResponse
     */
    public function setCheckoutStatus(?string $checkout_status): WaveCheckoutResponse
    {
        $this->checkout_status = $checkout_status;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientReference(): ?string
    {
        return $this->client_reference;
    }

    /**
     * @param string $client_reference
     * @return WaveCheckoutResponse
     */
    public function setClientReference(?string $client_reference): WaveCheckoutResponse
    {
        $this->client_reference = $client_reference;
        return $this;
    }


    /**
     * @return string
     */
    public function getLastPaymentError(): ?string
    {
        return $this->last_payment_error;
    }

    /**
     * @param string $last_payment_error
     * @return WaveCheckoutResponse
     */
    public function setLastPaymentError(?string $last_payment_error): WaveCheckoutResponse
    {
        $this->last_payment_error = $last_payment_error;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentStatus(): ?string
    {
        return $this->payment_status;
    }

    /**
     * @param string $payment_status
     * @return WaveCheckoutResponse
     */
    public function setPaymentStatus(?string $payment_status): WaveCheckoutResponse
    {
        $this->payment_status = $payment_status;
        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getWhenCompleted(): \DateTime
    {
        return $this->when_completed;
    }

    /**
     * @param \DateTime $when_completed
     * @return WaveCheckoutResponse
     */
    public function setWhenCompleted(?\DateTime $when_completed): WaveCheckoutResponse
    {
        $this->when_completed = $when_completed;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getWhenCreated(): ?\DateTime
    {
        return $this->when_created;
    }

    /**
     * @param \DateTime $when_created
     * @return WaveCheckoutResponse
     */
    public function setWhenCreated(?\DateTime $when_created): WaveCheckoutResponse
    {
        $this->when_created = $when_created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getWhenExpires(): ?\DateTime
    {
        return $this->when_expires;
    }

    /**
     * @param \DateTime $when_expires
     * @return WaveCheckoutResponse
     */
    public function setWhenExpires(?\DateTime $when_expires): WaveCheckoutResponse
    {
        $this->when_expires = $when_expires;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId(): ?string
    {
        return $this->transaction_id;
    }

    /**
     * @param string $transaction_id
     */
    public function setTransactionId(?string $transaction_id): void
    {
        $this->transaction_id = $transaction_id;
    }


}
