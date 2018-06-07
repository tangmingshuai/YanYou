<?php
/**
 * Created by PhpStorm.
 * User: Think
 * Date: 2018/6/2
 * Time: 17:30
 */

namespace App\Logic;

use App\Facades\Common;
use Symfony\Component\DomCrawler\Crawler;

class AccountInfoLogic
{
    /**
     * 参考github Restful 状态码设置
     * 200表示账号密码认证通过
     * 422表示认证信息缺失或不正确
     * 401用来表示校验错误
     * 410表示请求资源已不存在，代指学生账号已过期
     * 503表示服务暂不可用，代指Ip被冻结
     * 504表示网关超时，代指学校网站维护
     */

    const ACCOUNT_WRONG_PASSWORD = 422;
    const NEED_CAPTURE = 401;
    const ACCOUNT_EXPIRED = 410;
    const TIMEOUT = 504;
    const Freeze = 503;
    const SUCCESS = 200;

    /**
     * Notes:
     * @param $userInfoArray:携带账号密码的数组
     * @return array|null|string
     * 该函数主要用来判断用户是否为在校学生
     * 以及获取可直接访问办事大厅部分服务的有效cookie
     * 为了提高效率，使用了Guzzle扩展完成curl操作
     * 因此返回的cookie为Guzzle扩展中的Cookiejar对象(转化成合法cookie比较耗时，先这样吧
     * 该对象可经反序列化后直接在Guzzle中使用
     */
    public function judgeAccount($userInfoArray)
    {
        $res = Common::get('http://id.scuec.edu.cn/authserver/login');
        $data = $res['res']->getBody()->getContents();
//        $cookie = implode('; ', $res->getHeader('Set-Cookie'));
        $cookie_jar= $res['cookie'];

        $crawler = new Crawler();
        $crawler->addHtmlContent($data);
        for ($i = 10; $i < 15; $i++) {
            $key = $crawler->filter('#casLoginForm > input[type="hidden"]:nth-child(' . $i . ')')
                ->attr('name');
            $value = $crawler->filter('#casLoginForm > input[type="hidden"]:nth-child(' . $i . ')')
                ->attr('value');
            $userInfoArray[$key] = $value;
        }

        $res = Common::post(
            $userInfoArray,
            'http://id.scuec.edu.cn/authserver/login?goto=http%3A%2F%2Fssfw.scuec.edu.cn%2Fssfw%2Fj_spring_ids_security_check',
            'form_params',
            'http://ehall.scuec.edu.cn/new/index.html',
            $cookie_jar
        );

        $data = $res['res']->getBody()->getContents();
        $user_name = Common::domCrawler($data, 'filterXPath', '//*[@class="auth_username"]/span/span'); //尝试从登录后页面获取姓名，判断是否登录成功
        if ($user_name) {
//            $res_cookie = $res['cookie'];
//            for ($i = 0; $i < count($res_cookie); $i++) {
//                $cookie .= $res_cookie[$i]['Name'] . $res_cookie[$i]['Value'] . '；';
//            }
            $res_cookie = $res['res']->getHeader('Set-Cookie');
            return array(
                'status' => self::SUCCESS,
                'message' => "user valid and get cookie successfully",
                'data' => array(
//                    'studentname' => trim($key), //移除span标签中的空格，返回学生姓名
                    //因为请求设置了禁止跳转，cookie中获取的cookie携带跳转信息，移除这部分信息以防止之后无法访问指定页面
//                    'cookie' => explode('Path=/authserver/;HttpOnly;', $res_cookie[0])[0]
//                        . explode('domain=.scuec.edu.cn; path=/', $res_cookie[2])[0] . $cookie
                    'cookie' => serialize($res['cookie'])
                )
            );
        } else {
            $wrong_msg = Common::domCrawler($data, 'filter', '#msg'); //登录失败，返回页面错误信息
            switch ($wrong_msg) {
                case '您提供的用户名或者密码有误':
                    return array(
                        'status' => self::ACCOUNT_WRONG_PASSWORD,
                        'message' => "wrong password",
                        'data' => null
                    );
                case '请输入验证码':
                    return array(
                        'status' => self::NEED_CAPTURE,
                        'message' => "need capture",
                        'data' => null
                    );
                case 'expired':
                    return array(
                        'status' => self::ACCOUNT_EXPIRED,
                        'message' => "account expired",
                        'data' => null
                    );
            }

            return $key;
        }
    }

    public function getStudentName($userInfoArray)
    {
        $res = $this->judgeAccount($userInfoArray);
        $cookie = unserialize($res['data']['cookie']);

        $res = Common::get(
            'http://id.scuec.edu.cn/authserver/login?goto=http%3A%2F%2Fssfw.scuec.edu.cn%2Fssfw%2Fj_spring_ids_security_check',
            $cookie,
            'http://ssfw.scuec.edu.cn/ssfw/index.do'
        );

        $jar1 = $res['cookie'];

        $res = Common::get(
            'http://ssfw.scuec.edu.cn/ssfw/pkgl/kcbxx/4/2017-2018-2.do?flag=4&xnxqdm=2017-2018-2',
            $jar1,
            'http://ssfw.scuec.edu.cn/ssfw/index.do'
        );


        return $res['res']->getbody();
    }
}
