<?php


namespace app\api\controller;


use app\admin\model\sms\SmsRecord;
use app\admin\model\system\SystemAdmin;
use app\admin\model\user\UserBill;
use app\http\validates\user\RegisterValidates;
use app\models\store\StoreOrder;
use app\models\user\User;
use app\models\user\UserRecharge;
use app\models\user\UserToken;
use app\models\user\WechatUser;
use app\Request;
use crmeb\jobs\TestJob;
use crmeb\repositories\ShortLetterRepositories;
use crmeb\services\CacheService;
use crmeb\services\UtilService;
use think\facade\Cache;
use think\exception\ValidateException;
use think\facade\Config;
use think\facade\Log;
use think\facade\Queue;
use think\facade\Session;

/**微信小程序授权类
 * Class AuthController
 * @package app\api\controller
 */
class AuthController
{

    public function notify(){

        $token = "89WNLVEEBHDK4NPVKUQY958IA83T84B2";
        //回调过来的post值
        $bill_no = $_POST["bill_no"];                  //一个24位字符串，是此订单在020ZF服务器上的唯一编号
        $orderid = $_POST["orderid"];                  //是您在发起付款接口传入的您的自定义订单号
        $price = $_POST["price"];                      //单位：分。是您在发起付款接口传入的订单价格
        $actual_price = $_POST["actual_price"];        //单位：分。一定存在。表示用户实际支付的金额。
        $orderuid = $_POST["orderuid"];                //如果您在发起付款接口带入此参数，我们会原封不动传回。
        $key = $_POST["key"];
        $notify_key = md5($actual_price.$bill_no.$orderid.$orderuid.$price.$token);
        Log::error("订单号:".$orderid.'充值：'.$price.'key:'.$key.'校验key:'.$notify_key);
        if($key == $notify_key) {

            $userCharge = UserRecharge::where(['order_id'=>$orderid])->find();
            if (!empty($userCharge)) {
                //充值状态：1待付款，2成功，-1失败
                if ($userCharge->paid == 0) {
                    //找到这个用户
                    $user = User::where(['uid'=>$userCharge['uid']])->find();
                    //给用户加钱
                    $user->now_money += $userCharge->price;
                    if ($user->save()) {
                        //更新充值状态---成功
                        $userCharge->paid = 1;
                        $userCharge->pay_time = time();
                    }
                }
                //更新充值记录表
                UserBill::income('充值',$userCharge->uid, 'now_money', 'recharge', $userCharge->price, $orderid, $user->now_money, '充值' . $userCharge->price . '元');

                $userCharge->save();
                echo "success";       //请不要修改或删除
                exit();
            }
        }
    }
    /**
     * H5账号登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(Request $request)
    {

        $user = User::where('account', $request->param('account'))->find();
        if ($user) {
            if ($user->pwd !== md5($request->param('password')))
                return app('json')->fail('账号或密码错误');
            if ($user->pwd === md5(123456))
                return app('json')->fail('请修改您的初始密码，再尝试登陆！');
        } else {
            return app('json')->fail('账号或密码错误');
        }



        // 设置推广关系
        User::setSpread(intval($request->param('spread')), $user->uid);

        $token = UserToken::createToken($user, 'user');

        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['token' => $token->token, 'expires_time' => $token->expires_time]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 退出登录
     * @param Request $request
     */
    public function logout(Request $request)
    {
        $request->tokenData()->delete();
        return app('json')->success('成功');
    }

    public function verifyCode()
    {
        $unique = password_hash(uniqid(true), PASSWORD_BCRYPT);
        Cache::set('sms.key.' . $unique, 0, 300);

        return app('json')->success(['key' => $unique]);
    }

    public function captcha(Request $request)
    {
        ob_clean();
        $rep = captcha();
        $key = app('session')->get('captcha.key');
        $uni = $request->get('key');
        if ($uni)
            Cache::set('sms.key.cap.' . $uni, $key, 300);

        return $rep;
    }

    /**
     * 验证验证码是否正确
     *
     * @param $uni
     * @param string $code
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function checkCaptcha($uni, string $code): bool
    {
        $cacheName = 'sms.key.cap.' . $uni;
        if (!Cache::has($cacheName)) {
            return false;
        }

        $key = Cache::get($cacheName);

        $code = mb_strtolower($code, 'UTF-8');

        $res = password_verify($code, $key);

        if ($res) {
            Cache::delete($cacheName);
        }

        return $res;
    }

    /**
     * 验证码发送
     * @param Request $request
     * @return mixed
     */
    public function verify(Request $request)
    {
        list($phone, $type, $key, $code) = UtilService::postMore([['phone', 0], ['type', ''], ['key', ''], ['code', '']], $request, true);

        $keyName = 'sms.key.' . $key;
        $nowKey = 'sms.' . date('YmdHi');

        if (!Cache::has($keyName))
            return app('json')->make(401, '发送验证码失败');

        if (($num = Cache::get($keyName)) > 2) {
            if (!$code)
                return app('json')->make(402, '请输入验证码');

            if (!$this->checkCaptcha($key, $code))
                return app('json')->fail('验证码输入有误');
        }

        $total = 1;
        if ($has = Cache::has($nowKey)) {
            $total = Cache::get($nowKey);
            if ($total > Config::get('sms.maxMinuteCount', 20))
                return app('json')->success('已发送');
        }

        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (User::checkPhone($phone) && $type == 'register') return app('json')->fail('手机号已注册');
        if (!User::checkPhone($phone) && $type == 'login') return app('json')->fail('账号不存在！');
        $default = Config::get('sms.default', 'yunxin');
        $defaultMaxPhoneCount = Config::get('sms.maxPhoneCount', 10);
        $defaultMaxIpCount = Config::get('sms.maxIpCount', 50);
        $maxPhoneCount = Config::get('sms.stores.' . $default . '.maxPhoneCount', $defaultMaxPhoneCount);
        $maxIpCount = Config::get('sms.stores.' . $default . '.maxIpCount', $defaultMaxIpCount);
        if (SmsRecord::where('phone', $phone)->where('add_ip', $request->ip())->whereDay('add_time')->count() >= $maxPhoneCount) {
            return app('json')->fail('您今日发送得短信次数已经达到上限');
        }
        if (SmsRecord::where('add_ip', $request->ip())->whereDay('add_time')->count() >= $maxIpCount) {
            return app('json')->fail('此IP今日发送次数已经达到上限');
        }
        $time = 60;
        if (CacheService::get('code_' . $phone))
            return app('json')->fail($time . '秒内有效');
        $code = rand(100000, 999999);
        $data['code'] = $code;
        $res = ShortLetterRepositories::send(true, $phone, $data, 'VERIFICATION_CODE');
        if ($res !== true)
            return app('json')->fail('短信平台验证码发送失败' . $res);
        CacheService::set('code_' . $phone, $code, $time);
        Cache::set($keyName, $num + 1, 300);
        Cache::set($nowKey, $total, 61);

        return app('json')->success('发送成功');
    }

    /**
     * H5注册新用户
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {

        list($account, $captcha, $password, $spread) = UtilService::postMore([['account', ''], ['captcha', ''], ['password', ''], ['spread', 0]], $request, true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $invite_code = input('post.invite_code');
        $name = input('post.name');

        if (!$invite_code){
            return app('json')->fail('请输入邀请码');
        }
        if (!$name){
            return app('json')->fail('请输入姓名');
        }
        if (!SystemAdmin::where(['invite_code'=>$invite_code])->find()){
            return app('json')->fail('邀请码无效');
        }
        $pid = SystemAdmin::where(['invite_code'=>$invite_code])->value('id');

        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 16)
            return app('json')->fail('密码必须是在6到16位之间');
        if ($password == '123456') return app('json')->fail('密码太过简单，请输入较为复杂的密码');
        $registerStatus = User::register($account, $password, $spread,$pid,$name);
        if ($registerStatus) return app('json')->success('注册成功');
        return app('json')->fail(User::getErrorInfo('注册失败'));
    }

    /**
     * 密码修改
     * @param Request $request
     * @return mixed
     */
    public function reset(Request $request)
    {
        list($account, $captcha, $password) = UtilService::postMore([['account', ''], ['captcha', ''], ['password', '']], $request, true);
        try {
            validate(RegisterValidates::class)->scene('register')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 16)
            return app('json')->fail('密码必须是在6到16位之间');
        if ($password == '123456') return app('json')->fail('密码太过简单，请输入较为复杂的密码');
        $resetStatus = User::reset($account, $password);
        if ($resetStatus) return app('json')->success('修改成功');
        return app('json')->fail(User::getErrorInfo('修改失败'));
    }

    /**
     * 手机号登录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mobile(Request $request)
    {
        list($phone, $captcha, $spread) = UtilService::postMore([['phone', ''], ['captcha', ''], ['spread', 0]], $request, true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');

        //数据库查询
        $user = User::where('account', $phone)->find();
        if (!$user)
            return app('json')->fail('用户不存在');
        if (!$user->status)
            return app('json')->fail('已被禁止，请联系管理员');

        // 设置推广关系
        User::setSpread($spread, $user->uid);

        $token = UserToken::createToken($user, 'user');

        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['token' => $token->token, 'expires_time' => $token->expires_time]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * H5切换登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function switch_h5(Request $request)
    {
        $from = $request->post('from', 'wechat');
        $user = $request->user();
        if ($from === 'h5') {
            $user = User::where('phone', $user['phone'])->where('user_type', '<>', 'h5')->find();
            $user->login_type = 'wechat';
            $user->save();
        } else {
            //数据库查询
            $user = User::where('account|phone', $user['phone'])->where('user_type', 'h5')->find();
            if (!$user)
                return app('json')->fail('H5用户不存在,无法切换');

            if (!$user->status) return app('json')->fail('已被禁止，请联系管理员');

            $wechatUserInfo = WechatUser::where('uid', $request->uid())->find();//当前登陆用户信息
            $wechatH5UserInfo = WechatUser::where('uid', $user->uid)->find();//H5登陆切换用户信息

            if ($wechatH5UserInfo->unionid && $wechatUserInfo->unionid != $wechatH5UserInfo->unionid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');
            if ($wechatH5UserInfo->openid && $wechatUserInfo->openid != $wechatH5UserInfo->openid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');
            if ($wechatH5UserInfo->routine_openid && $wechatUserInfo->routine_openid != $wechatH5UserInfo->routine_openid)
                return app('json')->fail('您的账号已绑定特定用户无法切换到此用户上');

            switch ($from) {
                case 'wechat':
                    if (!$wechatH5UserInfo->openid)
                        $wechatH5UserInfo->openid = $wechatUserInfo->openid;
                    if (!$wechatH5UserInfo->unionid && $wechatUserInfo->unionid)
                        $wechatH5UserInfo->unionid = $wechatUserInfo->unionid;
                    break;
                case 'routine':
                    if (!$wechatH5UserInfo->routine_openid)
                        $wechatH5UserInfo->routine_openid = $wechatUserInfo->routine_openid;
                    if (!$wechatH5UserInfo->unionid && $wechatUserInfo->unionid)
                        $wechatH5UserInfo->unionid = $wechatUserInfo->unionid;
                    break;
            }
            $wechatH5UserInfo->save();
            User::where('uid', $request->uid())->update(['login_type' => 'h5']);
        }
        $token = UserToken::createToken($user, 'user');
        if ($token) {
            event('UserLogin', [$user, $token]);
            return app('json')->success('登录成功', ['userInfo' => $user, 'token' => $token->token, 'expires_time' => $token->expires_time, 'time' => strtotime($token->expires_time)]);
        } else
            return app('json')->fail('登录失败');
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function binding_phone(Request $request)
    {
        list($phone, $captcha, $step) = UtilService::postMore([
            ['phone', ''],
            ['captcha', ''],
            ['step', 0]
        ], $request, true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail('请先获取验证码');
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail('验证码错误');

        $userInfo = User::where('uid', $request->uid())->find();
        $userPhone = $userInfo->phone;
        if (!$userInfo) return app('json')->fail('用户不存在');
        if ($userInfo->phone) return app('json')->fail('您的账号已经绑定过手机号码！');
        if (User::where('phone', $phone)->where('user_type', '<>', 'h5')->count())
            return app('json')->success('此手机已经绑定，无法多次绑定！');
        if (User::where('account', $phone)->where('phone', $phone)->where('user_type', 'h5')->find()) {
            if (!$step) return app('json')->success('H5已有账号是否绑定此账号上', ['is_bind' => 1]);
            $userInfo->phone = $phone;
        } else {
            $userInfo->account = $phone;
            $userInfo->phone = $phone;
        }
        if ($userInfo->save() || $userPhone == $phone)
            return app('json')->success('绑定成功');
        else
            return app('json')->fail('绑定失败');
    }

    public function sys_control(){
        $list = StoreOrder::where(['status'=>0,'paid'=>1])->select();
            if ($list){
                foreach ($list as $kev=>$v){

                    if ($this->getReward() == 'success'){

                        StoreOrder::pass($v['id']);

                    }else{
                        StoreOrder::refuse($v['id']);
                    }


                }
            }

    }

    public function getReward($total=10000)
    {
        $win1 = floor((60*$total)/100);
        $win2 = floor((40*$total)/100);


        $return = array();
        for ($i=0;$i<$win1;$i++)
        {
            $return[] = 'success';
        }
        for ($j=0;$j<$win2;$j++)
        {
            $return[] = 'fail';
        }

        shuffle($return);
        return $return[array_rand($return)];
    }
}