<?php

declare(strict_types=1);

namespace Supseven\Cleverreach\Tests\Powermail\Finisher;

use In2code\Powermail\Domain\Model\Answer;
use In2code\Powermail\Domain\Model\Field;
use In2code\Powermail\Domain\Model\Mail;
use Supseven\Cleverreach\DTO\Receiver;
use Supseven\Cleverreach\Powermail\Finisher\CleverReach;
use Supseven\Cleverreach\Service\ApiService;
use Supseven\Cleverreach\Service\ConfigurationService;
use Supseven\Cleverreach\Tests\LocalBaseTestCase;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class CleverReachTest extends LocalBaseTestCase
{
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }
    public function testInitializeFinisher(): void
    {
        $nameField = $this->createStub(Field::class);
        $nameField->method('isSenderName')->willReturn(true);
        $nameField->method('isSenderEmail')->willReturn(false);
        $nameAnswer = $this->createStub(Answer::class);
        $nameAnswer->method('getField')->willReturn($nameField);
        $nameAnswer->method('getValue')->willReturn('Some Body');

        $mailField = $this->createStub(Field::class);
        $mailField->method('isSenderName')->willReturn(false);
        $mailField->method('isSenderEmail')->willReturn(true);
        $mailAnswer = $this->createStub(Answer::class);
        $mailAnswer->method('getField')->willReturn($mailField);
        $mailAnswer->method('getValue')->willReturn('somebody@domain.tld');

        $answers = new ObjectStorage();
        $answers->attach($nameAnswer);
        $answers->attach($mailAnswer);

        $dbEntryConfig = ['a' => 'b'];
        $tsSettings = ['dbEntry.' => $dbEntryConfig];
        $ebSettings = ['dbEntry' => $dbEntryConfig];
        $mail = $this->createStub(Mail::class);
        $mail->method('getAnswers')->willReturn($answers);

        $tsService = $this->createMock(TypoScriptService::class);
        $tsService->expects(self::any())
            ->method('convertPlainArrayToTypoScriptArray')
            ->with(self::equalTo($ebSettings))
            ->willReturn($tsSettings);

        GeneralUtility::addInstance(TypoScriptService::class, $tsService);

        $subject = new class (
            $mail,
            [],
            $ebSettings,
            true,
            'cleverreachFinisher',
            $this->createStub(ContentObjectRenderer::class)
        ) extends CleverReach {
            public function getName()
            {
                return $this->name;
            }
            public function getEmail()
            {
                return $this->email;
            }
        };

        $subject->initializeFinisher();

        self::assertEquals($dbEntryConfig, $subject->getConfiguration());
        self::assertEquals($nameAnswer->getValue(), $subject->getName());
        self::assertEquals($mailAnswer->getValue(), $subject->getEmail());
    }

    public function testOptinFinisher(): void
    {
        $nameField = $this->createStub(Field::class);
        $nameField->method('isSenderName')->willReturn(true);
        $nameField->method('isSenderEmail')->willReturn(false);
        $nameAnswer = $this->createStub(Answer::class);
        $nameAnswer->method('getField')->willReturn($nameField);
        $nameAnswer->method('getValue')->willReturn('Some Body');

        $mailAddress = 'somebody@domain.tld';
        $mailField = $this->createStub(Field::class);
        $mailField->method('isSenderName')->willReturn(false);
        $mailField->method('isSenderEmail')->willReturn(true);
        $mailAnswer = $this->createStub(Answer::class);
        $mailAnswer->method('getField')->willReturn($mailField);
        $mailAnswer->method('getValue')->willReturn($mailAddress);

        $answers = new ObjectStorage();
        $answers->attach($nameAnswer);
        $answers->attach($mailAnswer);

        $mail = $this->createStub(Mail::class);
        $mail->method('getAnswers')->willReturn($answers);

        $subject = new class (
            $mail,
            [],
            ['main' => ['cleverreach' => ApiService::MODE_OPTIN]],
            true,
            'cleverreachFinisher',
            $this->createStub(ContentObjectRenderer::class)
        ) extends CleverReach {
            public function setEmail(string $email): self
            {
                $this->email = $email;

                return $this;
            }
        };

        $receiver = Receiver::create($mailAddress);
        $apiService = $this->createMock(ApiService::class);
        $apiService->expects(self::once())->method('addReceiversToGroup')->with(
            self::equalTo($receiver),
            self::equalTo(123)
        );
        $apiService->expects(self::once())->method('sendSubscribeMail')->with(
            self::equalTo($mailAddress),
            self::equalTo(456),
            self::equalTo(123)
        );

        GeneralUtility::setSingletonInstance(ApiService::class, $apiService);
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $this->getConfiguration());

        $subject->setEmail($mailAddress)->cleverreachFinisher();
    }

    public function testOptoutDoubleFinisher(): void
    {
        $nameField = $this->createStub(Field::class);
        $nameField->method('isSenderName')->willReturn(true);
        $nameField->method('isSenderEmail')->willReturn(false);
        $nameAnswer = $this->createStub(Answer::class);
        $nameAnswer->method('getField')->willReturn($nameField);
        $nameAnswer->method('getValue')->willReturn('Some Body');

        $mailAddress = 'somebody@domain.tld';
        $mailField = $this->createStub(Field::class);
        $mailField->method('isSenderName')->willReturn(false);
        $mailField->method('isSenderEmail')->willReturn(true);
        $mailAnswer = $this->createStub(Answer::class);
        $mailAnswer->method('getField')->willReturn($mailField);
        $mailAnswer->method('getValue')->willReturn($mailAddress);

        $answers = new ObjectStorage();
        $answers->attach($nameAnswer);
        $answers->attach($mailAnswer);

        $mail = $this->createStub(Mail::class);
        $mail->method('getAnswers')->willReturn($answers);

        $subject = new class (
            $mail,
            [],
            ['main' => ['cleverreach' => ApiService::MODE_OPTOUT]],
            true,
            'cleverreachFinisher',
            $this->createStub(ContentObjectRenderer::class)
        ) extends CleverReach {
            public function setEmail(string $email): self
            {
                $this->email = $email;

                return $this;
            }
        };

        $apiService = $this->createMock(ApiService::class);
        $apiService->expects(self::once())->method('sendUnsubscribeMail')->with(self::equalTo($mailAddress));

        $config = $this->getConfiguration();
        $config->method('getUnsubscribeMethod')->willReturn(ConfigurationService::UNSCRIBE_DOUBLEOPTOUT);

        GeneralUtility::setSingletonInstance(ApiService::class, $apiService);
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $config);

        $subject->setEmail($mailAddress)->cleverreachFinisher();
    }

    public function testOptoutDeleteFinisher(): void
    {
        $nameField = $this->createStub(Field::class);
        $nameField->method('isSenderName')->willReturn(true);
        $nameField->method('isSenderEmail')->willReturn(false);
        $nameAnswer = $this->createStub(Answer::class);
        $nameAnswer->method('getField')->willReturn($nameField);
        $nameAnswer->method('getValue')->willReturn('Some Body');

        $mailAddress = 'somebody@domain.tld';
        $mailField = $this->createStub(Field::class);
        $mailField->method('isSenderName')->willReturn(false);
        $mailField->method('isSenderEmail')->willReturn(true);
        $mailAnswer = $this->createStub(Answer::class);
        $mailAnswer->method('getField')->willReturn($mailField);
        $mailAnswer->method('getValue')->willReturn($mailAddress);

        $answers = new ObjectStorage();
        $answers->attach($nameAnswer);
        $answers->attach($mailAnswer);

        $mail = $this->createStub(Mail::class);
        $mail->method('getAnswers')->willReturn($answers);

        $subject = new class (
            $mail,
            [],
            ['main' => ['cleverreach' => ApiService::MODE_OPTOUT]],
            true,
            'cleverreachFinisher',
            $this->createStub(ContentObjectRenderer::class)
        ) extends CleverReach {
            public function setEmail(string $email): self
            {
                $this->email = $email;

                return $this;
            }
        };

        $apiService = $this->createMock(ApiService::class);
        $apiService->expects(self::once())->method('removeReceiversFromGroup')->with(self::equalTo($mailAddress));
        $apiService->expects(self::once())->method('deleteReceiver')->with(self::equalTo($mailAddress));

        $config = $this->getConfiguration();
        $config->method('getUnsubscribeMethod')->willReturn(ConfigurationService::UNSCRIBE_DELETE);

        GeneralUtility::setSingletonInstance(ApiService::class, $apiService);
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $config);

        $subject->setEmail($mailAddress)->cleverreachFinisher();
    }

    public function testOptoutInactiveFinisher(): void
    {
        $nameField = $this->createStub(Field::class);
        $nameField->method('isSenderName')->willReturn(true);
        $nameField->method('isSenderEmail')->willReturn(false);
        $nameAnswer = $this->createStub(Answer::class);
        $nameAnswer->method('getField')->willReturn($nameField);
        $nameAnswer->method('getValue')->willReturn('Some Body');

        $mailAddress = 'somebody@domain.tld';
        $mailField = $this->createStub(Field::class);
        $mailField->method('isSenderName')->willReturn(false);
        $mailField->method('isSenderEmail')->willReturn(true);
        $mailAnswer = $this->createStub(Answer::class);
        $mailAnswer->method('getField')->willReturn($mailField);
        $mailAnswer->method('getValue')->willReturn($mailAddress);

        $answers = new ObjectStorage();
        $answers->attach($nameAnswer);
        $answers->attach($mailAnswer);

        $mail = $this->createStub(Mail::class);
        $mail->method('getAnswers')->willReturn($answers);

        $subject = new class (
            $mail,
            [],
            ['main' => ['cleverreach' => ApiService::MODE_OPTOUT]],
            true,
            'cleverreachFinisher',
            $this->createStub(ContentObjectRenderer::class)
        ) extends CleverReach {
            public function setEmail(string $email): self
            {
                $this->email = $email;

                return $this;
            }
        };

        $apiService = $this->createMock(ApiService::class);
        $apiService->expects(self::once())->method('disableReceiversInGroup')->with(
            self::equalTo($mailAddress),
            self::equalTo(123)
        );

        $config = $this->getConfiguration();
        $config->method('getUnsubscribeMethod')->willReturn(ConfigurationService::UNSCRIBE_INACTIVE);

        GeneralUtility::setSingletonInstance(ApiService::class, $apiService);
        GeneralUtility::setSingletonInstance(ConfigurationService::class, $config);

        $subject->setEmail($mailAddress)->cleverreachFinisher();
    }
}
