<?php

namespace crmeb\repositories;


use app\admin\model\sms\SmsRecord;
use crmeb\services\sms\Sms;
use think\facade\Log;

/**
 * 短信发送
 * Class ShortLetterRepositories
 * @package crmeb\repositories
 */
class ShortLetterRepositories
{
    /**
     * 发送短信
     * @param $switch 发送开关
     * @param $phone 手机号码
     * @param array $data 模板替换内容
     * @param string $template 模板编号
     * @param string $logMsg 错误日志记录
     * @return bool|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function send($switch, $phone, array $data, string $template, $logMsg = '')
    {
        if ($switch && $phone) {
            $statusStr = array(
                "0" => "短信发送成功",
                "-1" => "参数不全",
                "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
                "30" => "密码错误",
                "40" => "账号不存在",
                "41" => "余额不足",
                "42" => "帐户已过期",
                "43" => "IP地址限制",
                "50" => "内容含有敏感词"
            );
            $smsapi = "http://api.smsbao.com/";
            $user = "hhw1016"; //短信平台帐号
            $pass = md5("a12345678"); //短信平台密码
            $code = $data['code'];
            $content="【乐享易购】您的验证码为{$code}，在1分钟内有效。";//要发送的短信内容
            $phones = $phone;//要发送短信的手机号码
            $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phones."&c=".urlencode($content);
            $result =file_get_contents($sendurl) ;
            if ($result == "0"){
                return  true;
            }else{
                return  false;
            }
        } else {
            return false;
        }
    }
}