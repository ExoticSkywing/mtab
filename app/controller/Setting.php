<?php


namespace app\controller;


use app\BaseController;
use app\model\SettingModel;
use think\facade\Cache;
use think\facade\Db;

class Setting extends BaseController
{
    function saveSetting(): \think\response\Json
    {
        $this->getAdmin();
        is_demo_mode(true);
        $list = $this->request->post('form');
        $tmp = [];
        foreach ($list as $key => $value) {
            $tmp[] = [
                'keys' => $key,
                'value' => $value
            ];
        }
        Db::table('setting')->replace()->insertAll($tmp);
        Cache::delete('webConfig');
        (new \app\controller\admin\Index(app()))->authorization();
        return $this->success('保存成功');
    }

    function refreshCache(): \think\response\Json
    {
        $this->getAdmin();
        Cache::delete('webConfig');
        return $this->success('刷新成功');
    }

    function getSetting(): \think\response\Json
    {
        $admin = $this->getAdmin();
        $role = $this->request->post('role', []);
        $info = SettingModel::Config();
        $tmp = [];
        $url = '';
        if (in_array('ext_name', $role)) {
            if (file_exists(public_path() . '/browserExt.zip')) {
                $url = '/browserExt.zip';
            }
        }
        if ($info) {
            if (count($role) > 0) {
                foreach ($info as $key => $val) {
                    if (in_array($key, $role)) {
                        $tmp[$key] = $val;
                    }
                }
            }
            return json(['msg' => "ok", "data" => $tmp, 'success' => $this->auth, 'code' => 1, "url" => $url]);
        }
        return json(['msg' => 'ok', 'data' => false, 'success' => $this->auth, 'code' => 0, 'url' => $url]);
    }
    function delExt(): \think\response\Json
    {
        $this->getAdmin();
        if (file_exists(public_path() . '/browserExt.zip')) {
            unlink(public_path() . '/browserExt.zip');
        }
        return $this->success("ok");
    }
}
