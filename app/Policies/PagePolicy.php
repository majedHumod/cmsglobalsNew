<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view pages');
    }

    public function create(User $user): bool
    {
        return $user->can('create pages');
    }

    public function update(User $user, Page $page): bool
    {
        if (! $user->can('edit pages')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $page->user_id === (int) $user->id;
    }

    public function delete(User $user, Page $page): bool
    {
        if (! $user->can('delete pages')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $page->user_id === (int) $user->id;
    }
}
