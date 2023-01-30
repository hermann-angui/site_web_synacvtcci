<?php
namespace App\Traits;

use App\Repository\CompanyRepository;
use App\Repository\EmployeeRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Service\Wave\WaveCheckoutRequest;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use App\Entity\User;

trait UserTrait
{

    /**
     * @param string $amount
     * @param UserInterface|null $user
     * @return string|void
     */
    public function payForSubscription(string $amount, ?UserInterface $user) : ?string
    {
        try{
            $waveCheckoutRequest = new WaveCheckoutRequest();
            $waveCheckoutRequest->setCurrency("XOF")
                ->setAmount($amount)
                ->setClientReference(Uuid::v4()->toRfc4122())
                ->setErrorUrl($this->getParameter("wave_error_url"))
                ->setSuccessUrl($this->getParameter("wave_success_url"));
            // ->setErrorUrl($this->generateUrl('wave_payment_callback', ["status" => "error"], UrlGenerator::ABSOLUTE_URL))
            // ->setSuccessUrl($this->generateUrl('wave_payment_callback', ["status" => "success"], UrlGenerator::ABSOLUTE_URL));

            $waveResponse = $this->waveService->checkOutRequest($waveCheckoutRequest);

            if ($waveResponse) {
                $subscription = new Subscription();

                $now = new \DateTime();
                $endDate = $now->add(new \DateInterval('P1Y'));
                $subscription->setAmount($waveResponse->getAmount())
                    ->setCurrency($waveResponse->getCurrency())
                    ->setPaymentReference($waveResponse->getClientReference())
                    ->setCheckoutSessionId($waveResponse->getCheckoutSessionId())
                    ->setSubscriber($user)
                    ->setOperator("WAVE")
                    ->setPaymentMode("WEBSITE")
                    ->setPaymentType("MOBILE_MONEY")
                    ->setPaymentDate($waveResponse->getWhenCreated())
                    ->setSubscriptionStartDate($now)
                    ->setSubscriptionExpireDate($endDate)
                    ->setCreatedAt(new \DateTime())
                    ->setModifiedAt(new \DateTime())
                    ->setPaymentStatus(strtoupper($waveResponse->getPaymentStatus()));

                $this->subscriptionRepository->add($subscription, true);


                return $waveResponse->getWaveLaunchUrl();
            }
        }catch(\Exception $e){
            dump($e);
            die;
            return null;
        }
    }

    /**
     * @param array $payload
     * @param SubscriptionRepository $subscriptionRepository
     * @param UserRepository $userRepository
     * @return void
     */
    private function saveSubscriptionPaymentStatus(array $payload): void
    {
        if (!empty($payload) && array_key_exists("client_reference", $payload)) {
            $subscription = $this->subscriptionRepository->findOneBy(["payment_reference" => $payload["client_reference"], "checkout_session_id" => $payload["id"]]);
            if ($subscription) {
                $now = new \DateTime();
                $user = $subscription->getSubscriber();
                $this->updateUserStatus($user, $now, $this->userRepository);
                $subscription->setPaymentStatus(strtoupper($payload["payment_status"]));
                $subscription->setOperatorTransactionId($payload["transaction_id"]);
                $subscription->setSubscriptionStartDate($now);
                $subscription->setSubscriptionExpireDate($now->add(new \DateInterval('P1Y')));
                $subscription->setModifiedAt(new \DateTime());
                $this->subscriptionRepository->add($subscription, true);
            }
        }
    }

    /**
     * @param User|null $user
     * @param \DateTime $now
     * @param UserRepository $userRepository
     * @return void
     */
    private function updateUserStatus(?User $user, \DateTime $now, UserRepository $userRepository): void
    {
        $user->setStatus("SUBSCRIPTION_VALID");

        $userRepository->add($user, true);
    }

    /**
     * @param CompanyRepository $companyRepository
     * @param int|null $beneficiaryId
     * @param EmployeeRepository $employeeRepository
     * @param \DateTime $now
     * @return void
     */
    private function updateCompanyStatus(CompanyRepository $companyRepository, ?int $beneficiaryId, EmployeeRepository $employeeRepository, \DateTime $now): void
    {
        $company = $companyRepository->find($beneficiaryId);
        $employeeList = $employeeRepository->findBy(['company' => $company, 'status' => "WAITING_FOR_PAYMENT"]);
        foreach ($employeeList as $employee) {
            $employee->setStatus("VALID_MEMBER");
            $employee->setSubscriptionStartDate($now);
            $employee->setSubscriptionExpireDate($now->add(new \DateInterval('P1Y')));
            $employeeRepository->add($employee);
        }
        $company->setStatus("VALID_MEMBER");
        $company->setSubscriptionStartDate($now);
        $company->setSubscriptionExpireDate($now->add(new \DateInterval('P1Y')));
        $companyRepository->add($company);
    }

    /**
     * @param EmployeeRepository $employeeRepository
     * @param int|null $beneficiaryId
     * @param \DateTime $now
     * @return void
     */
    private function updateemployeeStatus(EmployeeRepository $employeeRepository, ?int $beneficiaryId, \DateTime $now): void
    {
        $employee = $employeeRepository->find($beneficiaryId);
        $employee->setStatus("SUBSCRIPTION_VALID");
        $employee->setSubscriptionStartDate($now);
        $employee->setSubscriptionExpireDate($now->add(new \DateInterval('P1Y')));
        $employeeRepository->add($employee);
    }

}
