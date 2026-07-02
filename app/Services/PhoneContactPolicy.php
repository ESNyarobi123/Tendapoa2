<?php

namespace App\Services;

use App\Models\Job;
use App\Rules\NoPhoneNumberInText;

/**
 * Nambari za simu kwenye maandishi ya mtumiaji:
 * - Zimezuiwa kabisa kwenye maelezo ya kazi, maombi, na maoni.
 * - Zinaruhusiwa kwenye chat ya DM tu baada ya escrow + kukubali kwa mfanyakazi aliyechaguliwa.
 */
class PhoneContactPolicy
{
    public const BLOCKED_MESSAGE = NoPhoneNumberInText::MESSAGE;

    /**
     * Mteja na mfanyakazi aliyekubali kazi (baada ya escrow) wanaweza kushiriki nambari kwenye chat yao.
     */
    public static function allowsPhoneSharingInChat(Job $job, int $senderId, ?int $receiverId = null): bool
    {
        if (! $job->funded_at || ! $job->accepted_by_worker_at) {
            return false;
        }

        $clientId = (int) $job->user_id;
        $workerId = (int) $job->accepted_worker_id;
        if ($workerId < 1) {
            return false;
        }

        if (! in_array($job->status, [
            Job::S_IN_PROGRESS,
            Job::S_SUBMITTED,
            Job::S_COMPLETED,
            Job::S_DISPUTED,
        ], true)) {
            return false;
        }

        if (! in_array($senderId, [$clientId, $workerId], true)) {
            return false;
        }

        if ($receiverId !== null) {
            $receiverId = (int) $receiverId;
            if (! in_array($receiverId, [$clientId, $workerId], true) || $receiverId === $senderId) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  'chat'|'job_description'|'application'|'comment'  $surface
     */
    public static function userTextContainsBlockedPhone(
        string $text,
        string $surface,
        ?Job $job = null,
        ?int $senderId = null,
        ?int $receiverId = null,
    ): bool {
        if (! NoPhoneNumberInText::containsPhoneNumber($text)) {
            return false;
        }

        if ($surface === 'chat' && $job !== null && $senderId !== null) {
            return ! self::allowsPhoneSharingInChat($job, $senderId, $receiverId);
        }

        return true;
    }
}
