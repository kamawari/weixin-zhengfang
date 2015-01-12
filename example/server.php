<?php

//error_reporting(0);
include_once ('../lib/function.php');

/**
 * 微信公众平台 PHP SDK 示例文件
 *
 * @author NetPuter <netputer@gmail.com>
 */

require ('../src/Wechat.php');

/**
 * 微信公众平台演示类
 */
class MyWechat extends Wechat
{
    
    /**
     * 用户关注时触发，回复「欢迎关注」
     *
     * @return void
     */
    protected function onSubscribe() {
        $this->responseText("Hi亲，感谢关注厦门理工小助手\n发送图书馆加关键词即可查书(如图书馆韩寒)\n发送成绩即可查询本学期成绩\n发送考试安排即可查询考试安排\n有问题直接发消息给我哦\n么么哒！");
    }
    
    /**
     * 用户已关注时,扫描带参数二维码时触发，回复二维码的EventKey (测试帐号似乎不能触发)
     *
     * @return void
     */
    protected function onScan() {
        
        //$this->responseText('二维码的EventKey：' . $this->getRequest('EventKey'));
        
    }
    
    /**
     * 用户取消关注时触发
     *
     * @return void
     */
    protected function onUnsubscribe() {
        
        // 「悄悄的我走了，正如我悄悄的来；我挥一挥衣袖，不带走一片云彩。」
        
    }
    
    /**
     * 上报地理位置时触发,回复收到的地理位置
     *
     * @return void
     */
    protected function onEventLocation() {
        
        //$this->responseText('收到了位置推送：' . $this->getRequest('Latitude') . ',' . $this->getRequest('Longitude'));
        
    }
    
    /**
     * 收到文本消息时触发，回复收到的文本消息内容
     *
     * @return void
     */
    protected function onText() {
        $content = $this->getRequest('content');
        $openid = $this->getRequest('FromUserName');
        $time = $this->getRequest('CreateTime');
        
        if (mb_substr($content, 0, 3, 'utf-8') == '图书馆') {
            $string = library($content);
            $this->responseText($string);
            return;
        }
        
        if ($content == '成绩') {
            $checkbind = checkBind($openid);
            if ($checkbind['status'] == 0) {
                $url = 'http://guoerwx.sinaapp.com/login.php?openid=' . $openid;
                $this->responseText('请先绑定学号' . "\n" . '<a href=' . "\"" . $url . "\"" . '>点击绑定学号</a>');
                return;
            } else if ($checkbind['status'] == 1) {
                $string = getGrade($checkbind['data']['username'], $checkbind['data']['password']);
                if (!empty($string)) {
                    $this->responseText($string);
                } else {
                    $this->responseText('成绩还没出来，放心一定会过的！');
                }
                return;
            }
        }
        
        if ($content == '考试安排') {
            $checkbind = checkBind($openid);
            if ($checkbind['status'] == 0) {
                $url = 'http://guoerwx.sinaapp.com/login.php?openid=' . $openid;
                $this->responseText('请先绑定学号' . "\n" . '<a href=' . "\"" . $url . "\"" . '>点击绑定学号</a>');
                return;
            } else if ($checkbind['status'] == 1) {
                $string = getExam($checkbind['data']['username'], $checkbind['data']['password']);
                if (!empty($string)) {
                    $this->responseText($string);
                } else {
                    $this->responseText('考试安排还没出来，先去复习吧！');
                }
                return;
            }
        }
        
        if ($content == '解绑') {
            $data = unBind($openid);
            if ($data['status'] == 0) {
                $this->responseText('您还未绑定！');
            } else if ($data['status'] == 1) {
                $this->responseText('解绑成功！');
            }
        }
        
        if ($content == '帮助') {
            $this->responseText("发送图书馆加关键词即可查书(如图书馆韩寒)\n发送成绩即可查询本学期成绩\n发送考试安排即可查询考试安排\n发送课表即可查询当日课表\n有问题直接发消息给我");
            return;
        }
        if (mb_substr($content,0,2,'utf-8') == '课表') {
            $checkbind = checkBind($openid);
            if ($checkbind['status'] == 0) {
                $url = 'http://guoerwx.sinaapp.com/login.php?openid=' . $openid;
                $this->responseText('请先绑定学号' . "\n" . '<a href=' . "\"" . $url . "\"" . '>点击绑定学号</a>');
                return;
            } else if ($checkbind['status'] == 1) {
                $week = mb_substr($content,2,1,'utf-8');
                if($week==''){
                	$week = date('w',$time);
                }
                $string = getClass($checkbind['data']['username'],$checkbind['data']['password'],$week);
            }
            $this->responseText($string);
            return;
        }


        
        if ($content == '测试') {
            $this->responseText($time);
            return;
        }
        return;
    }
    
    /**
     * 收到图片消息时触发，回复由收到的图片组成的图文消息
     *
     * @return void
     */
    protected function onImage() {
        $items = array(new NewsResponseItem('标题一', '描述一', $this->getRequest('picurl'), $this->getRequest('picurl')), new NewsResponseItem('标题二', '描述二', $this->getRequest('picurl'), $this->getRequest('picurl')),);
        
        //$this->responseNews($items);
        
    }
    
    /**
     * 收到地理位置消息时触发，回复收到的地理位置
     *
     * @return void
     */
    protected function onLocation() {
        
        //$num = 1 / 0;
        // 故意触发错误，用于演示调试功能
        
        //$this->responseText('收到了位置消息：' . $this->getRequest('location_x') . ',' . $this->getRequest('location_y'));
        
    }
    
    /**
     * 收到链接消息时触发，回复收到的链接地址
     *
     * @return void
     */
    protected function onLink() {
        
        //$this->responseText('收到了链接：' . $this->getRequest('url'));
        
    }
    
    /**
     * 收到语音消息时触发，回复语音识别结果(需要开通语音识别功能)
     *
     * @return void
     */
    protected function onVoice() {
        $this->responseText('收到了语音消息,识别结果为：' . $this->getRequest('Recognition'));
    }
    
    /**
     * 收到自定义菜单消息时触发，回复菜单的EventKey
     *
     * @return void
     */
    protected function onClick() {
        
        //$this->responseText('你点击了菜单：' . $this->getRequest('EventKey'));
        
    }
    
    /**
     * 收到未知类型消息时触发，回复收到的消息类型
     *
     * @return void
     */
    protected function onUnknown() {
        
        //$this->responseText('收到了未知类型消息：' . $this->getRequest('msgtype'));
        
    }
}

$wechat = new MyWechat('weixin', TRUE);
$wechat->run();
