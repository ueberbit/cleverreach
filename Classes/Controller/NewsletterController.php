<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Controller;

use Psr\Http\Message\ResponseInterface;
use Supseven\Cleverreach\DTO\RegistrationRequest;
use Supseven\Cleverreach\DTO\Subscriber;
use Supseven\Cleverreach\Service\ConfigurationService;
use Supseven\Cleverreach\Service\SubscriptionService;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Actions for newsletter optin/optout
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class NewsletterController extends ActionController
{
    /**
     * @var SubscriptionService
     */
    protected SubscriptionService $subscriptionService;

    protected ConfigurationService $configurationService;

    /**
     * @param SubscriptionService $subscriptionService
     */
    public function injectSubscriptionService(SubscriptionService $subscriptionService): void
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @param ConfigurationService $configurationService
     */
    public function injectConfigurationService(ConfigurationService $configurationService): void
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @IgnoreValidation("receiver")
     * @param RegistrationRequest|null $receiver
     * @return ResponseInterface
     */
    public function optinFormAction(?RegistrationRequest $receiver = null): ResponseInterface
    {
        $newsletter = [];

        foreach ($this->configurationService->getCurrentNewsletters() as $groupId => $item) {
            $newsletter[$groupId] = $item['label'];
        }

        $this->view->assign('receiver', $receiver ?? new RegistrationRequest());
        $this->view->assign('newsletter', $newsletter);

        return $this->htmlResponse();
    }

    /**
     * @Validate(validator="\Supseven\Cleverreach\Validation\Validator\OptinValidator", param="receiver")
     * @param RegistrationRequest|null $receiver
     */
    public function optinSubmitAction(?RegistrationRequest $receiver = null): void
    {
        if (!$receiver) {
            $this->redirect('optinForm');
        }

        $groupId = $receiver->groupId;
        $formId = (int)$this->configurationService->getCurrentNewsletters()[$groupId]['formId'];
        $subscription = new Subscriber($receiver->email, $groupId, $formId);

        $this->subscriptionService->subscribe($subscription);

        $uri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)->setTargetPageUid((int)$this->settings['redirect']['optin'])->build();
        $this->redirectToUri($uri);
    }

    /**
     * @IgnoreValidation("receiver")
     * @param RegistrationRequest|null $receiver
     */
    public function optoutFormAction(?RegistrationRequest $receiver = null): void
    {
        $newsletter = [];

        foreach ($this->configurationService->getCurrentNewsletters() as $groupId => $item) {
            $newsletter[$groupId] = $item['label'];
        }

        $this->view->assign('receiver', $receiver ?? new RegistrationRequest());
        $this->view->assign('newsletter', $newsletter);
    }

    /**
     * @Validate(validator="\Supseven\Cleverreach\Validation\Validator\OptoutValidator", param="receiver")
     * @param RegistrationRequest|null $receiver
     */
    public function optoutSubmitAction(?RegistrationRequest $receiver = null): void
    {
        if (!$receiver) {
            $this->redirect('optinForm');
        }

        $groupId = $receiver->groupId;
        $formId = (int)$this->configurationService->getCurrentNewsletters()[$groupId]['formId'];
        $subscription = new Subscriber($receiver->email, $groupId, $formId);

        $this->subscriptionService->unsubscribe($subscription);

        $uri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)->setTargetPageUid((int)$this->settings['redirect']['optout'])->build();
        $this->redirectToUri($uri);
    }
}
