<?php

namespace App\Policies;

use App\Models\Story;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Mese megtekintése és törlése: a tulajdonos vagy az admin (Backend Schema 10.3, E-02).
 * Viewing and deleting a story: its owner or the admin (Backend Schema 10.3, E-02).
 */
class StoryPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Story $story)
    {
        // (int): PHP 8.1 előtt a PDO a számokat szövegként adja vissza. / Before PHP 8.1, PDO returns numbers as strings.
        return (int) $user->id === (int) $story->user_id || $user->isAdmin();
    }

    public function delete(User $user, Story $story)
    {
        return $this->view($user, $story);
    }
}
