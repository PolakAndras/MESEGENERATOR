<?php

namespace App\Support;

use App\Models\User;

/**
 * A felhasználótípus szerinti korlátok egyetlen helyen (Backend Schema 6.): vendég = guest, bejelentkezett = user.
 * Így a backlog „eltérő korlátok” pontja csak a config/story.php módosítása lesz.
 *
 * Limits per user type in a single place (Backend Schema 6.): not logged in = guest, logged in = user.
 * This way the backlog item "different limits" only means editing config/story.php.
 */
class StoryLimits
{
    public static function for(?User $user): array
    {
        return config('story.limits.'.($user ? 'user' : 'guest'));
    }

    public static function current(): array
    {
        return self::for(auth()->user());
    }
}
