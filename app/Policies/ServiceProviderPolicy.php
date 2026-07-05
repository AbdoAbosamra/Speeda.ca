<?php

namespace App\Policies;

use App\Models\ServiceProvider;
use App\Models\User;

class ServiceProviderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceProvider $serviceProvider): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceProvider $serviceProvider): bool
    {
        return $user->id === $serviceProvider->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceProvider $serviceProvider): bool
    {
        return $user->id === $serviceProvider->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceProvider $serviceProvider): bool
    {
        return $user->id === $serviceProvider->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceProvider $serviceProvider): bool
    {
        return $user->id === $serviceProvider->user_id;
    }

    /**
     * Determine whether the user can verify/unverify the service provider.
     * Only administrators should be able to do this.
     */
    public function verify(User $user, ServiceProvider $serviceProvider): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can manage the gallery (upload/replace/delete images).
     */
    public function manageGallery(User $user, ServiceProvider $serviceProvider): bool
    {
        return $user->id === $serviceProvider->user_id;
    }
}
