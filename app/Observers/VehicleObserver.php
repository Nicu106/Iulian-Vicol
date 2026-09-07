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
            // The queue. motorclass-v2-queue.service consumes it, restarts itself
            // if it dies and comes back on boot, so there is no ceiling on how
            // long the work may take and no PHP-FPM process is held while it runs.
            // If the worker is ever down the hourly motorclass-v2-images.timer
            // builds whatever is missing anyway, and until either happens the
            // endpoint still answers on demand. Three ways for this to be right,
            // none of which is somebody remembering to run a command.
            WarmVehicleImages::dispatch($paths);
        }
    }
}
