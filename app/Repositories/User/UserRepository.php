<?php
namespace App\Repositories\User;

use App\Mail\MailStatistic;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Notification;
use Pusher\Pusher;
use Illuminate\Support\Facades\Mail;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return User::class;
    }

    public function getAllUser()
    {
        return $this->model::orderby('name', 'ASC')
            ->paginate(config('paginate.pagination'));
    }

    public function getUserByOrderDelivered($id)
    {
        return $this->model::with(['orders' => function ($query) {
            $query->where('order_status_id', config('orderstatus.delivered'));
        }])->where('id', $id)->first();
    }

    public function findAdmin()
    {
        return $this->model->where('role_id', config('auth.roles.admin'))->get();
    }

    public function notify($user, $data)
    {
        $options = [
            'cluster' => 'ap1',
            'useTLS' => true,
        ];

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $pusher->trigger('NotificationEvent', 'send-notification', $data);

        Notification::send($user, new OrderNotification($data));
    }
}
