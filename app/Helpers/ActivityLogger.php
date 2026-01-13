<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log an activity.
     */
    public static function log(
        string $action,
        string $description,
        ?User $user = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {
        return ActivityLog::log(
            $action,
            $description,
            $user,
            $modelType,
            $modelId,
            $oldValues,
            $newValues
        );
    }

    /**
     * Log user creation.
     */
    public static function logUserCreated(Model $user, User $createdBy): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_CREATE,
            "Created user: {$user->name} ({$user->email})",
            $createdBy,
            User::class,
            $user->id,
            null,
            ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]
        );
    }

    /**
     * Log user update.
     */
    public static function logUserUpdated(Model $user, array $oldData, array $newData, User $updatedBy): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_UPDATE,
            "Updated user: {$user->name} ({$user->email})",
            $updatedBy,
            User::class,
            $user->id,
            $oldData,
            $newData
        );
    }

    /**
     * Log user deletion.
     */
    public static function logUserDeleted(Model $user, User $deletedBy): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_DELETE,
            "Deleted user: {$user->name} ({$user->email})",
            $deletedBy,
            User::class,
            $user->id,
            ['name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            null
        );
    }

    /**
     * Log user restoration.
     */
    public static function logUserRestored(Model $user, User $restoredBy): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_RESTORE,
            "Restored user: {$user->name} ({$user->email})",
            $restoredBy,
            User::class,
            $user->id,
            null,
            ['name' => $user->name, 'email' => $user->email, 'role' => $user->role]
        );
    }

    /**
     * Log merchant creation.
     */
    public static function logMerchantCreated($merchant, User $createdBy): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_CREATE,
            "Created merchant: {$merchant->business_name}",
            $createdBy,
            \App\Models\Merchant::class,
            $merchant->id,
            null,
            ['business_name' => $merchant->business_name, 'user_id' => $merchant->user_id]
        );
    }

    /**
     * Log merchant update.
     */
    public static function logMerchantUpdated($merchant, array $oldData, array $newData, User $updatedBy): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_UPDATE,
            "Updated merchant: {$merchant->business_name}",
            $updatedBy,
            \App\Models\Merchant::class,
            $merchant->id,
            $oldData,
            $newData
        );
    }

    /**
     * Log merchant deletion.
     */
    public static function logMerchantDeleted($merchant, User $deletedBy): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_DELETE,
            "Deleted merchant: {$merchant->business_name}",
            $deletedBy,
            \App\Models\Merchant::class,
            $merchant->id,
            ['business_name' => $merchant->business_name],
            null
        );
    }

    /**
     * Log login.
     */
    public static function logLogin(User $user): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_LOGIN,
            "User logged in: {$user->email}",
            $user,
            User::class,
            $user->id
        );
    }

    /**
     * Log logout.
     */
    public static function logLogout(User $user): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_LOGOUT,
            "User logged out: {$user->email}",
            $user,
            User::class,
            $user->id
        );
    }

    /**
     * Log password reset.
     */
    public static function logPasswordReset(User $user): ActivityLog
    {
        return self::log(
            ActivityLog::ACTION_PASSWORD_RESET,
            "Password reset for user: {$user->email}",
            $user,
            User::class,
            $user->id
        );
    }
}

