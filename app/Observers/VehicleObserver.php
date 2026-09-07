<?php

namespace App\Observers;

use App\Jobs\WarmVehicleImages;
use App\Models\Vehicle;

/**
 * On the model, not in the admin controller.
 *
 * The controller is one way photographs get in; a seeder, a tinker session, an
 * import or whatever replaces that admin screen later are others. Hanging this
 * off saved() means every route into the database is covered, including the ones
 * that do not exist yet.
 */
class VehicleObserver
{
    public function saved(Vehicle $vehicle): void
    {
        // Only when the pictures actually changed. Editing a price should not
        // queue a job that rebuilds nothing.
        if (!$vehicle->wasRecentlyCreated
            && !$vehicle->wasChanged('cover_image')
            && !$vehicle->wasChanged('gallery_images')) {
            return;
        }

        $paths = array_values(array_filter(array_merge(
            [$vehicle->cover_image],
            is_array($vehicle->gallery_images) ? $vehicle->gallery_images : []
        )));

        if ($paths) {
            // afterResponse, not the queue: nothing on this server consumes one.
            WarmVehicleImages::dispatchAfterResponse($paths);
        }
    }
}
