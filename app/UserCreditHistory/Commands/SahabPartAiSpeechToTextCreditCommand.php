<?php

namespace App\UserCreditHistory\Commands;


use App\Exceptions\UserCreditInsufficientFund;
use App\Models\User;
use App\Models\UserCreditHistory;
use App\UserCreditHistory\UserCreditHistoryCommandInterface;

class SahabPartAiSpeechToTextCreditCommand implements UserCreditHistoryCommandInterface
{
    /**
     * 
     * @return string
     */
    public static function getTypeName(): string
    {
        return "Services|VoiceToText";
    }

    /**
     * 
     * @return string
     */
    public static function getTypeShownName(): string
    {
        return "سرویس تبدیل صوت به متن";
    }

    /**
     * @param User $user
     * @param int $amount 
     * @param null|string $description
     * 
     * 
     * @throws UserCreditInsufficientFund
     * 
     * @return UserCreditHistory
     */
    public static function execute(User $user, int $amount, ?string $description = null): UserCreditHistory
    {
        $user->refresh(); // to update credit

        if ($user->credit < $amount)
            throw new UserCreditInsufficientFund($user, $amount);

        $user->credit -= $amount;
        $user->save();

        return $user
            ->creditHistories()
            ->create([
                "type" => self::getTypeName(),
                "type_name" => self::getTypeShownName(),
                "is_increase" => 0,
                "amount" => $amount,
                "updated_credit" => $user->credit,
                "description" => $description
            ])
            ->refresh();
    }

    /**
     * @param User $user
     * @param int $amount 
     * @param null|string $description
     * 
     * 
     * @return UserCreditHistory
     */
    public static function revert(User $user, int $amount, ?string $description = null): UserCreditHistory
    {
        $user->refresh(); // to update credit

        $user->credit += $amount;
        $user->save();

        return $user
            ->creditHistories()
            ->create([
                "type" => self::getTypeName(),
                "type_name" => self::getTypeShownName(),
                "is_increase" => 1,
                "amount" => $amount,
                "updated_credit" => $user->credit,
                "description" => $description
            ])
            ->refresh();
    }
}
