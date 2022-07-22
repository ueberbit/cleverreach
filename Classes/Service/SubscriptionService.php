<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Service;

use Supseven\Cleverreach\DTO\Receiver;
use Supseven\Cleverreach\DTO\Subscriber;

/**
 * Abstract away api calls
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class SubscriptionService
{
    public function __construct(
        private readonly ApiService $apiService,
        private readonly ConfigurationService $configurationService
    ) {
    }

    public function subscribe(Subscriber $subscriber): void
    {
        if ($this->configurationService->isTestEmail($subscriber->email)) {
            return;
        }

        $receiver = Receiver::create($subscriber->email);
        $this->apiService->addReceiversToGroup($receiver, $subscriber->groupId);
        $this->apiService->sendSubscribeMail($subscriber->email, $subscriber->formId, $subscriber->groupId);
    }

    public function unsubscribe(Subscriber $subscriber): void
    {
        if ($this->configurationService->isTestEmail($subscriber->email)) {
            return;
        }

        $this->apiService->sendUnsubscribeMail($subscriber->email, $subscriber->formId, $subscriber->groupId);
    }
}
