<?php


namespace App\Helpers;


use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Jetstream\Http\Livewire\LogoutOtherBrowserSessionsForm;

class UserSession extends LogoutOtherBrowserSessionsForm
{
    /**
     * gets all login sessions for current logged in user
     * @return \Illuminate\Support\Collection
     */
    public function currentLoggedInUserSessions()
    {
        return $this->getSessionsProperty();
    }

    /**
     * searches in the database for all sessions of the passed in user id.
     * @param $user_id String used to search the session by user id
     * @return \Illuminate\Support\Collection
     */
    public function find($user_id)
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return collect(
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $user_id)
                ->orderBy('last_activity', 'desc')
                ->get()
        )->map(function ($session) {
            return (object)[
                'agent' => $this->createAgent($session),
                'ip_address' => $session->ip_address,
                'is_current_device' => $session->id === request()->session()->getId(),
                'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
            ];
        });
    }

    public static function ID() {
        return self::Details()['id'];
    }

    public static function Name() {
        return self::Details()['name'];
    }

    public static function Email() {
        return self::Details()['email'];
    }

    public static function Phone() {
        return self::Details()['phone'];
    }

    public static function Roles() {
        return self::Details()['roles'];
    }

    public static function PrimaryRole() {
        return self::Details()['primary_role'];
    }

    public static function Details() {
        return self::session()['details'];
    }

    private static function session(){
        return session('user_session');
    }


    public static function DemoAccount($username, $password='') {
        $demo_accounts = ['+265987654321', '+265123456789'];

        return in_array($username, $demo_accounts);
    }

    public static function Start($text="")  {
        return "CON {$text}";
    }

    public static function Continue($text="")  {
        return "CON {$text}";
    }

    public static function End($text="")  {
        return "END {$text}";
    }

    public static function Input($current_input, $previous_input) {
        return str_replace("{$previous_input}","","{$current_input}");
    }
}
