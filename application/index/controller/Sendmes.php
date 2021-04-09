<?php
namespace app\index\controller;
use think\Controller;
class Sendmes extends Acesstoken{
	public function index(){
    	$openid = db('user')->field(['openid'])->whereTime('heartbeat','>',date("Y-m-d",strtotime("-2 day")))->select();
      	$content = array();
		$content[] = array("Title"=>"多图文1标题", "Description"=>"爆装备爱不完，一切全靠打", "PicUrl"=>"https://s3m.mediav.com/galileo/387490-a8c93ed9b7b82f87427ec4e423c5a911.png", "Url" =>"http://baidu.com");
		//$content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
		//$content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
		for($j = 0; $j < count($openid); $j++)
		{
    		$openi = $openid[$j]["openid"];
   			$result = $this->send_custom_message($openi, "news", $content);
		}
    }
  
  	public function index1(){
     	$content = array();
      	$content[] = array("Title"=>"多图文1标题", "Description"=>"爆装备爱不完，一切全靠打", "PicUrl"=>"https://s3m.mediav.com/galileo/387490-a8c93ed9b7b82f87427ec4e423c5a911.png", "Url" =>"http://baidu.com");
		$content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
		$content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
    	    $itemTpl = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>";
        $item_str = "";
        foreach ($content as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <Content><![CDATA[]]></Content>
    <ArticleCount>%s</ArticleCount>
    <Articles>$item_str</Articles></xml>";

        $result = sprintf($xmlTpl, 'drgheoruighrge', '1654684788879', time(), count($content));
            	dump($result);
    }
}