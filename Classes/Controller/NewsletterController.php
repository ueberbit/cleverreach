<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Controller;

use Supseven\Cleverreach\DTO\RegistrationRequest;
use Supseven\Cleverreach\DTO\Subscriber;
use Supseven\Cleverreach\Service\SubscriptionService;
use TYPO3\CMS\Core\Exception;
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

    /** @var int RootPageUid */
    protected int $rootUid = 0;

    /** @var array Newsletter TypoScript Settings */
    protected array $newsletterSettings = [];

    /**
     * @param SubscriptionService $subscriptionService
     */
    public function injectSubscriptionService(SubscriptionService $subscriptionService): void
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function initializeAction(): void
    {
        $this->rootUid = (int)$GLOBALS['TSFE']->rootLine[0]['uid'];

        if (!isset($this->settings['newsletter'][$this->rootUid])) {
            throw new Exception('No Newsletter Configuration found. Please check TypoScript Settings', 1594110443);
        }
        $this->newsletterSettings = $this->settings['newsletter'][$this->rootUid];

        parent::initializeAction();
    }

    /**
     * @IgnoreValidation("receiver")
     * @param RegistrationRequest|null $receiver
     */
    public function optinFormAction(?RegistrationRequest $receiver = null): void
    {
        $newsletter = [];

        foreach ($this->newsletterSettings ?? [] as $groupId => $item) {
            $newsletter[$groupId] = $item['label'];
        }

        $this->view->assign('receiver', $receiver ?? new RegistrationRequest());
        $this->view->assign('newsletter', $newsletter);
    }

    /**
     * @Validate(validator="\Supseven\Cleverreach\Validation\Validator\OptinValidator", param="receiver")
     * @param RegistrationRequest $receiver
     */
    public function optinSubmitAction(RegistrationRequest $receiver): void
    {
        $groupId = $receiver->groupId;
        $formId = (int)$this->newsletterSettings[$groupId]['formId'];
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

        foreach ($this->newsletterSettings ?? [] as $groupId => $item) {
            $newsletter[$groupId] = $item['label'];
        }

        $this->view->assign('receiver', $receiver ?? new RegistrationRequest());
        $this->view->assign('newsletter', $newsletter);
    }

    /**
     * @Validate(validator="\Supseven\Cleverreach\Validation\Validator\OptoutValidator", param="receiver")
     * @param RegistrationRequest $receiver
     */
    public function optoutSubmitAction(RegistrationRequest $receiver): void
    {
        $groupId = $receiver->groupId;
        $formId = (int)$this->newsletterSettings[$groupId]['formId'];
        $subscription = new Subscriber($receiver->email, $groupId, $formId);

        $this->subscriptionService->unsubscribe($subscription);

        $uri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)->setTargetPageUid((int)$this->settings['redirect']['optout'])->build();
        $this->redirectToUri($uri);
    }
}
