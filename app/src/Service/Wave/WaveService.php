<?php

namespace App\Service\Wave;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Uid\Uuid;

class WaveService
{
    public function __construct(private ContainerInterface $container){}

    public function checkOutRequest(?WaveCheckoutRequest $request) : ?WaveCheckoutResponse
    {
        if($this->container->get('kernel')->getEnvironment() === 'dev'){
            $waveResponse = new WaveCheckoutResponse();
                $waveResponse->setAmount($request->getAmount())
                ->setPaymentStatus("SUCCESS")
                ->setCurrency($request->getCurrency())
                ->setClientReference($request->getClientReference())
                ->setCheckoutSessionId('cos-18qq25rgr100a')
                ->setCheckoutStatus('complete')
                ->setWhenCreated(new \DateTime())
                ->setWhenCompleted(new \DateTime())
                ->setWhenExpires(new \DateTime())
                ->setWaveLaunchUrl( 'http://synacvtcci.develop/payment/wave/checkout/success?ref=' . $request->getClientReference());
            return $waveResponse;
        }
        else{
            try {
                $encodedPayload = json_encode([
                    'amount' => $request->getAmount(),
                    'currency' => $request->getCurrency(),
                    'client_reference' => $request->getClientReference(),
                    'success_url' => $this->container->getParameter('app.wave_success_url') . $request->getClientReference(),
                    'error_url' => $this->container->getParameter('app.wave_success_url') . $request->getClientReference()
                ]);

                $curlOptions = [
                    CURLOPT_URL => $this->container->getParameter('app.wave_checkout_url'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $encodedPayload,
                    CURLOPT_HTTPHEADER => [
                        "Authorization: Bearer " . $this->container->getParameter('app.wave_api_key'),
                        "Content-Type: application/json"
                    ],
                ];

                # Execute the request and get a response
                $curl = curl_init();
                curl_setopt_array($curl, $curlOptions);
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    return null;
                } else {
                    # You can now decode the response and use the checkout session. Happy coding ;)
                    $checkout_session = json_decode($response, true);
                    $waveResponse = new WaveCheckoutResponse();

                    $waveResponse->setAmount($checkout_session["amount"])
                        ->setPaymentStatus($checkout_session["payment_status"])
                        ->setCurrency($checkout_session["currency"])
                        ->setClientReference($checkout_session["client_reference"])
                        ->setCheckoutSessionId($checkout_session["id"])
                        ->setCheckoutStatus($checkout_session["checkout_status"])
                        ->setWhenCreated(new \DateTime($checkout_session["when_created"]))
                        ->setWhenCompleted(new \DateTime($checkout_session["when_completed"]))
                        ->setWhenExpires(new \DateTime($checkout_session["when_expires"]))
                        ->setWaveLaunchUrl($checkout_session["wave_launch_url"]);

                    return $waveResponse;
                }
            }catch(\Exception $e){
                return null;
            }
        }
    }


    public function requestPayment($montant) : ?WaveCheckoutResponse
    {
        try{
            $waveCheckoutRequest = new WaveCheckoutRequest();
            $waveCheckoutRequest->setCurrency("XOF")
                ->setAmount($montant)
                ->setClientReference(Uuid::v4()->toRfc4122())
                ->setSuccessUrl($this->container->getParameter('app.wave_success_url'));

            $waveResponse = $this->checkOutRequest($waveCheckoutRequest);
            if($waveResponse) return $waveResponse;
            else return null;

        }catch(\Exception $e){
            return null;
        }
    }

}
