<?php
include_once 'simple_html_dom.php';
include_once 'config/config.php';
function library($content) {
    $key = mb_substr($content, 3, 1000, 'utf-8');
    $key = urlencode($key);
    $url = "http://210.34.212.78/NTRdrBookRetr.aspx?strKeyValue=" . $key . "&strType=text&strSortType=&strpageNum=8&strSort=desc";
    $html = file_get_html($url);
    $data = array();
    $into = $html->find('.into');
    $StrTmpRecno = $html->find('#StrTmpRecno');
    foreach ($into as $key => $value) {
        $title = $value->find('.title,a');
        $data[$key]['title'] = $title[1];
        $author = $value->find('.author,strong');
        $data[$key]['author'] = $author[1];
        $publisher = $value->find('.publisher,strong');
        //出版社
        $data[$key]['publisher'] = $publisher[1];
        $dates = $value->find('.dates', 2);
        //索书号
        $data[$key]['code'] = $dates;
    }
    
    //ajax获取在馆数和复本数
    $ajaxkey = '';
    foreach ($StrTmpRecno as $key => $value) {
        $id = $value->value;
        $data[$key]['id'] = $id;
        $ajaxkey.= $id . ";";
    }
    $ch = curl_init('http://210.34.212.78/GetlocalInfoAjax.aspx?ListRecno=' . $ajaxkey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $curl = curl_exec($ch);
    curl_close($ch);
    $xml = simplexml_load_string($curl);
    $i = 0;
    foreach ($xml as $key => $value) {
        $hldcount = $xml->books[$i]->book[0]->hldcount;
        
        //在馆数
        $data[$i]['count'] = $hldcount;
        $hldallnum = $xml->books[$i]->book[0]->hldallnum;
        
        //复本数
        $data[$i]['allnum'] = $hldallnum;
        $i++;
    }
    //构建回复信息
    $string = '';
    foreach ($data as $key => $value) {
        $infourl = 'http://210.34.212.78/NTRdrBookRetrInfo.aspx?BookRecno=' . $value['id'];
        $string.= "书名：" . '<a href=' . "\"" . $infourl . "\"" . '>' . strip_tags($value['title']) . '</a>' . "\n";
        $string.= "作者：" . strip_tags($value['author']) . "\n";
        $string.= strip_tags($value['publisher']) . "\n";
        $string.= strip_tags($value['code']) . "\n";
        
        $string.= "在馆数：" . $value['count'] . "\n";
        $string.= "复本数：" . $value['allnum'] . "\n\n";
    }
    
    $string.= '<a href=' . "\"" . $url . "\"" . '>点击查看更多</a>';
    return $string;
}

//检查是否绑定学号
function checkBind($openid) {
    global $config;
    $con=mysqli_connect($config['mysql_host'],$config['mysql_user'],$config['mysql_pass'],$config['mysql_db']);
    if($con){
        $sql = "select `username`,`password` from `jxgl_user` where `openid`='" . $openid . "'";
        $data = mysqli_query($con,$sql);
        if (($data->num_rows == 0 )) {
            return array('status' => 0);//未绑定
        } else {
            while($row = mysqli_fetch_array($data)){
                $username = $row['username'];
                $password = $row['password'];
            }
            return array('status' => 1, 'data' => array('username' => $username, 'password' => $password));
        }
    }else{
        return false;
    }
}

//解绑学号
function unBind($openid) {
    global $config;
    $con=mysqli_connect($config['mysql_host'],$config['mysql_user'],$config['mysql_pass'],$config['mysql_db']);
    if($con){
        $sql = "select 'id' from `jxgl_user` where `openid`='" . $openid . "'";
        $data = mysqli_query($con,$sql);
        if (($data->num_rows == 0 )) {
            return array('status' => 0);
        } else {
            $sql = "delete from `jxgl_user` where `openid` = '" . $openid . "'";
            mysqli_query($con,$sql);
            return array('status' => 1);
        }
    }else{
        return false;
    }
}

//验证学号密码
function checkPassword($username, $password) {
    global $jxglurl;
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    
    //这里设置文件头可见
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    $header = curl_exec($ch);
    curl_close($ch);
    
    preg_match('/ASP.NET_SessionId=(.*);/', $header, $matches[0]);
    $SessionId = $matches[0][1];
    

    $xmgxy = '';
    preg_match('/__VIEWSTATE\" value=\"(.*)\" \/>/', $header, $matches[0]);
    $VIEWSTATE = $matches[0][1];
    
    $attr = array('Button1' => '登录', 'RadioButtonList1' => '学生', 'TextBox1' => $username, 'TextBox2' => $password, '__VIEWSTATE' => $VIEWSTATE);
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $attr);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    //curl_setopt($ch, CURLOPT_HEADER, 1);
    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    $strpos = strstr($data, "window.open('xs_main.aspx?xh=");
    if ($strpos === false) {
        return 0;
    } else {
        return 1;
    }
}

//获取成绩
function getGrade($username, $password) {
    global $jxglurl;
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    
    //这里设置文件头可见
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    $header = curl_exec($ch);
    curl_close($ch);
    
    preg_match('/ASP.NET_SessionId=(.*);/', $header, $matches[0]);
    $SessionId = $matches[0][1];
    
    //preg_match('/xmgxy=(.*);/', $header,$matches[0]);
    // $xmgxy = $matches[0][1];
    $xmgxy = '';
    preg_match('/__VIEWSTATE\" value=\"(.*)\" \/>/', $header, $matches[0]);
    $VIEWSTATE = $matches[0][1];
    $attr = array('Button1' => '登录', 'RadioButtonList1' => '学生', 'TextBox1' => $username, 'TextBox2' => $password, '__VIEWSTATE' => $VIEWSTATE);
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $attr);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_exec($ch);
    curl_close($ch);
    
    $ch = curl_init($jxglurl . "xscj_gc.aspx?xh=" . $username);
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $header = curl_exec($ch);
    curl_close($ch);
    
    preg_match('/__VIEWSTATE\" value=\"(.*)\" \/>/', $header, $matches[0]);
    $VIEWSTATE = $matches[0][1];
    
    $attr = array('Button1' => '按学期查询', 'ddlXN' => '2014-2015', 'ddlXQ' => '1', '__VIEWSTATE' => $VIEWSTATE);
    
    $ch = curl_init($jxglurl . 'xscj_gc.aspx?xh=' . $username);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $attr);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    
    $html = str_get_html($data);
    $grade = array();
    $tr = $html->find('table#Datagrid1 tr');
    $string = '';
    foreach ($tr as $key => $value) {
        if ($key != 0) {
            $string.= $value->find('td', 3)->plaintext . " ";
            $string.= $value->find('td', 8)->plaintext . "\n";
        }
    }
    return $string;
}

//获取考试安排
function getExam($username, $password) {
    global $jxglurl;
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    
    //这里设置文件头可见
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    $header = curl_exec($ch);
    curl_close($ch);
    
    preg_match('/ASP.NET_SessionId=(.*);/', $header, $matches[0]);
    $SessionId = $matches[0][1];
    
    // preg_match('/xmgxy=(.*);/', $header,$matches[0]);
    // $xmgxy = $matches[0][1];
    $xmgxy = '';
    preg_match('/__VIEWSTATE\" value=\"(.*)\" \/>/', $header, $matches[0]);
    $VIEWSTATE = $matches[0][1];
    $attr = array('Button1' => '登录', 'RadioButtonList1' => '学生', 'TextBox1' => $username, 'TextBox2' => $password, '__VIEWSTATE' => $VIEWSTATE);
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $attr);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_exec($ch);
    curl_close($ch);
    
    $ch = curl_init($jxglurl . 'xskscx.aspx?xh=' . $username);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    $html = str_get_html($data);
    $tr = $html->find('table#DataGrid1 tr');
    
    $string = '';
    foreach ($tr as $key => $value) {
        if ($key != 0) {
            if (strlen($value->find('td', 3)->plaintext) != 6) {
                $string.= '课程：' . $value->find('td', 1)->plaintext . "\n";
                $string.= '时间：' . $value->find('td', 3)->plaintext . "\n";
                $string.= '考场：' . $value->find('td', 4)->plaintext . "\n";
                $string.= '座号：' . $value->find('td', 6)->plaintext . "\n\n";
            }
        }
    }
    return $string;
}

//获取课表
function getClass($username, $password, $week) {
    global $jxglurl;
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_HEADER, 1);
    
    //这里设置文件头可见
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    $header = curl_exec($ch);
    
    curl_close($ch);
    preg_match('/ASP.NET_SessionId=(.*);/', $header, $matches[0]);
    $SessionId = $matches[0][1];
    
    $xmgxy = '';
    preg_match('/__VIEWSTATE\" value=\"(.*)\" \/>/', $header, $matches[0]);
    $VIEWSTATE = $matches[0][1];
    $attr = array('Button1' => '登录', 'RadioButtonList1' => '学生', 'TextBox1' => $username, 'TextBox2' => $password, '__VIEWSTATE' => $VIEWSTATE);
    $ch = curl_init($jxglurl . 'default_ysdx.aspx');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $attr);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $header = curl_exec($ch);
    curl_close($ch);
    
    preg_match('/__VIEWSTATE\" value=\"(.*)\" \/>/', $header, $matches[0]);
    $VIEWSTATE = $matches[0][1];
    
    $ch = curl_init($jxglurl . '/xskbcx.aspx?xh=' . $username . '&xm=""&gnmkdm=N121603');
    $attr = array('__EVENTARGUMENT' => '', '__EVENTTARGET' => 'xqd', '__VIEWSTATE' => $VIEWSTATE, 'xnd' => '2013-2014', 'xqd' => '2');
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    curl_setopt($ch, CURLOPT_COOKIE, "xmgxy=" . $xmgxy . ";ASP.NET_SessionId=" . $SessionId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    
    $html = str_get_html($data);
    foreach ($html->find('meta') as $element) {
        if (strpos($element->content, 'charset')) $element->content = 'text/html; charset=utf-8';
    }
    $tr = $html->find('table#Table1 tr td');
    switch ($week) {
        case '1':
            $week_zh = '周一';
            break;

        case '2':
            $week_zh = '周二';
            break;

        case '3':
            $week_zh = '周三';
            break;

        case '4':
            $week_zh = '周四';
            break;

        case '5':
            $week_zh = '周五';
            break;

        case '6':
            $week_zh = '周六';
            break;

        default:
            $week_zh = '周日';
            break;
    }
    $string = '';
    foreach ($tr as $key => $value) {
        if (mb_strpos($value->plaintext,$week_zh,0,'utf-8'))
            $string .= $value->plaintext . "\n\n";
    }
    if(empty($string))
        $string = $week_zh . '没课哦！';
    return $string;
}

//curl get请求
function curlGet($url,$cookie=false){
    $jxglurl = $config['jxglurl'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);//这里设置文件头可见
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    if($cookie){
        curl_setopt($ch, CURLOPT_COOKIE, $cookie); //设置cookie
    }
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

//curl POST请求
function curlPost($url,$data,$cookie=false){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $attr);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:26.0) Gecko/20100101 Firefox/26.0 FirePHP/0.7.4");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host" => $jxglurl, "Referer" => $jxglurl . "default_ysdx.aspx", "Accept-Language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3", "Accept-Encoding" => "gzip, deflate", "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Connection" => "keep-alive", "x-insight" => "activate"));
    
    if($cookie){
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);//设置cookie
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

