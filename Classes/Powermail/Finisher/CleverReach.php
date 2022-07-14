<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Powermail\Finisher;

/**
 * This file is part of the "cleverreach" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Mail;
use In2code\Powermail\Finisher\AbstractFinisher;
use Supseven\Cleverreach\DTO\Receiver;
use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Service\ConfigurationService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CleverReach extends AbstractFinisher
{
    /**
     * @var string
     */
    protected string $email = '';

    protected string $name = '';

    public function cleverreachFinisher(): void
    {
        if ($this->email === '') {
            return;
        }

        $formValues = $this->getFormValues($this->getMail());
        $settings = $this->getSettings();
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $formId = (string)($settings['main']['cleverreachFormId'] ?? '') !== '' ? (int)$settings['main']['cleverreachFormId'] : $configurationService->getFormId();
        $groupId = (string)($settings['main']['cleverreachGroupId'] ?? '') !== '' ? (int)$settings['main']['cleverreachGroupId'] : $configurationService->getGroupId();

        if (array_key_exists('newslettercondition', $formValues)) {
            // checkbox field exists -> check if true
            if ((int)$formValues['newslettercondition'] !== 1) {
                return;
            }
        }

        $api = GeneralUtility::makeInstance(ApiService::class);

        if ($this->settings['main']['cleverreach'] === ApiService::MODE_OPTIN) {
            $receiver = Receiver::create($this->email, $formValues);
            $api->addReceiversToGroup($receiver, $groupId);
            $api->sendSubscribeMail($this->email, $formId, $groupId);
        } elseif ($this->settings['main']['cleverreach'] === ApiService::MODE_OPTOUT) {
            switch ($configurationService->getUnsubscribeMethod()) {
                case ConfigurationService::UNSCRIBE_DOUBLEOPTOUT:
                    $api->sendUnsubscribeMail($this->email);
                    break;
                case ConfigurationService::UNSCRIBE_DELETE:
                    $api->removeReceiversFromGroup($this->email);
                    $api->deleteReceiver($this->email);
                    break;
                case ConfigurationService::UNSCRIBE_INACTIVE:
                    $api->disableReceiversInGroup($this->email, $groupId);
                    break;
            }
        }
    }

    /**
     * Initialize
     */
    public function initializeFinisher(): void
    {
        $configuration = GeneralUtility::makeInstance(TypoScriptService::class)->convertPlainArrayToTypoScriptArray($this->settings);

        if (is_array($configuration['dbEntry.'] ?? null)) {
            $this->configuration = $configuration['dbEntry.'];
        }

        $this->email = $this->findSenderEmail($this->mail);
        $this->name = $this->findSenderName($this->mail);
    }

    /**
     * @param Mail $mail
     * @return array
     */
    private function getFormValues(Mail $mail)
    {
        $values = [];

        /** @var Answer $answer */
        foreach ($mail->getAnswers() as $answer) {
            $name = $answer->getField()?->getMarker();

            if ($name) {
                $value = $answer->getValue();

                if (\is_array($value)) {
                    $value = implode(', ', $value);
                }

                $values[$name] = $value;
            }
        }

        return $values;
    }

    /**
     * @param Mail $mail
     * @return string
     */
    private function findSenderEmail(Mail $mail): string
    {
        /** @var Answer $answer */
        foreach ($mail->getAnswers() as $answer) {
            if ($answer->getField()?->isSenderEmail()) {
                $value = $answer->getValue();

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                return $value;
            }
        }

        return '';
    }

    /**
     * @param Mail $mail
     * @return string
     */
    private function findSenderName(Mail $mail): string
    {
        /** @var Answer $answer */
        foreach ($mail->getAnswers() as $answer) {
            if ($answer->getField()?->isSenderName()) {
                $value = $answer->getValue();

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                return $value;
            }
        }

        return '';
    }
}
