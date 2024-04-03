<?php

namespace App\Service\Wave;

use App\Entity\Member;
use App\Service\ConfigurationService\ConfigurationService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class WaveService
{

    public function __construct(private ContainerInterface $container, private ConfigurationService  $configurationService){

    }
    public function checkOutRequest(?WaveCheckoutRequest $request) : ?WaveCheckoutResponse
    {
        try {
            $env = $this->container->get('kernel')->getEnvironment();

            if($env === 'dev'){
                $waveResponse = new WaveCheckoutResponse();
                $waveResponse->setAmount($request->getAmount())
                            ->setPaymentStatus('PROCESSING')
                            ->setCurrency($request->getCurrency())
                            ->setClientReference( $request->getClientReference())
                            ->setCheckoutSessionId('8899-9999-9999-985599-99999')
                            ->setCheckoutStatus('SUCCESS')
                            ->setWhenCreated(new \DateTime('now'))
                            ->setWhenCompleted(new \DateTime('now'))
                            ->setWhenExpires(new \DateTime('now'))
                            ->setWaveLaunchUrl($this->configurationService->getParameter('app.wave.checkout_success_url') . $request->getClientReference());

                return $waveResponse;
            }

            $encodedPayload = json_encode([
                'amount' => $request->getAmount(),
                'currency' => $request->getCurrency(),
                'client_reference' => $request->getClientReference(),
                'success_url' => $this->configurationService->getParameter('app.wave.checkout_success_url') . $request->getClientReference(),
                'error_url' => $this->configurationService->getParameter('app.wave.checkout_error_url')
            ]);

            $curlOptions = [
                CURLOPT_URL => $this->configurationService->getParameter('app.wave.checkout_url'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encodedPayload,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . $this->configurationService->getParameter('app.wave.api_key'),
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
                dump($err);
                return null;
            } else {
                # You can now decode the response and use the checkout session. Happy coding ;)
                $checkout_session = json_decode($response, true);
                $waveResponse = new WaveCheckoutResponse();
                $waveResponse->setAmount($checkout_session["amount"])
                    ->setPaymentStatus(strtoupper($checkout_session["payment_status"]))
                    ->setCurrency($checkout_session["currency"])
                    ->setClientReference($checkout_session["client_reference"])
                    ->setCheckoutSessionId($checkout_session["id"])
                    ->setCheckoutStatus(strtoupper($checkout_session["checkout_status"]))
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


    public function makePayment($montant) : ?WaveCheckoutResponse
    {
        try{
            $waveCheckoutRequest = new WaveCheckoutRequest();
            $waveCheckoutRequest->setCurrency("XOF")
                ->setAmount($montant)
                ->setClientReference(Uuid::v4()->toRfc4122())
                ->setSuccessUrl($this->configurationService->getParameter('app.wave.checkout_success_url'));

            $waveResponse = $this->checkOutRequest($waveCheckoutRequest);
            if($waveResponse) return $waveResponse;
            else return null;

        }catch(\Exception $e){
            dump($e);
            return null;
        }
    }

}
