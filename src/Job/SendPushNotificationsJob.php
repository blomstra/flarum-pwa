<?php

/*
 * This file is part of askvortsov/flarum-pwa
 *
 *  Copyright (c) 2021 Alexander Skvortsov.
 *
 *  For detailed copyright and license information, please view the
 *  LICENSE file that was distributed with this source code.
 */

namespace Askvortsov\FlarumPWA\Job;

use Askvortsov\FlarumPWA\FirebaseConfigInvalid;
use Askvortsov\FlarumPWA\FirebasePushSender;
use Askvortsov\FlarumPWA\PushSender;
use ErrorException;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Queue\AbstractJob;
use Flarum\Settings\SettingsRepositoryInterface;

class SendPushNotificationsJob extends AbstractJob
{
    private BlueprintInterface $blueprint;

    /**
     * @var int[]
     */
    private array $recipientIds;

    public function __construct(BlueprintInterface $blueprint, array $recipientIds = [])
    {
        $this->blueprint = $blueprint;
        $this->recipientIds = $recipientIds;
    }

    /**
     * @throws ErrorException
     */
    public function handle(PushSender $native, FirebasePushSender $firebase, SettingsRepositoryInterface $settings): void
    {
        $native->notify($this->blueprint, $this->recipientIds);

        try {
            $firebase->notify($this->blueprint, $this->recipientIds);
        } catch (FirebaseConfigInvalid) {
        }
    }

    private function hasValidFirebaseSettings(SettingsRepositoryInterface $settings): bool
    {
        try {
            return (bool) json_encode($settings->get('askvortsov-pwa.firebaseConfig'));
        } catch (\Throwable) {
            return false;
        }
    }
}
