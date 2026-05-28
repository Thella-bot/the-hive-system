use App\Models\Module;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('module.{moduleId}', function ($user, $moduleId) {
    $module = Module::find($moduleId);

    if (!$module) {
        return false;
    }

    return $module->students()->where('user_id', $user->id)->exists();
});