<?php


namespace app\model;


use think\Model;

class UserModel extends Model
{
    protected $name = "user";
    protected $pk = "id";
    protected static $user_temp = null;

    public static function getUser(bool $must = false)
    {
        $id = request()->header('Userid', '');
        $token = request()->header('Token', '');
        if (!$id) {
            $id = request()->cookie('user_id', '');
        }
        if (!$token) {
            $token = request()->cookie('token', '');
        }
        if ($id && $token) {
            if (self::$user_temp) return self::$user_temp;
            $user = TokenModel::where('user_id', $id)->where('token', $token)->field('user_id,token,create_time')->find();
            if ($user) {
                $status = UserModel::where('id', $user['user_id'])->find();
                if ($status && $status['status'] === 0) {
                    if (time() > ($user['create_time'] + 60 * 60 * 24 * 15)) {//如果创建时间大于15天则删除
                        $user->delete();
                    } else {
                        if ((time() - $user['create_time']) > (864000)) { //token定时15天清理一次，10-15天内如果使用了则重新计算时间
                            $user->create_time = time();
                            $user->save();
                        }
                        self::$user_temp = $user;
                        return $user;
                    }
                }
            }
        }
        if ($must) {
            json(['code' => 0, 'msg' => '请登录后操作'])->send();
            exit();
        }
        return false;
    }
}