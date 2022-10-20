<?php
/*
 * +----------------------------------------------------------------------------------
 * | https://www.tigervs.com
 * +----------------------------------------------------------------------------------
 * | Email: Kevin@tigervs.com
 * +----------------------------------------------------------------------------------
 * | Copyright (c) Shenzhen Tiger Technology Co., Ltd. 2018~2022. All rights reserved.
 * +----------------------------------------------------------------------------------
 */

declare (strict_types = 1);
namespace app\console\controller\dashboard;

use think\response\Json;
use core\basic\BaseController;

class MonitorController extends BaseController
{
    public function index(): Json
    {
        $monitor = '[{"name":"厦门市","value":95,"type":0},{"name":"杭州市","value":32,"type":0},{"name":"朔州市","value":33,"type":1},{"name":"蚌埠市","value":93,"type":1},{"name":"鄂尔多斯市","value":21,"type":0},{"name":"漯河市","value":14,"type":1},{"name":"上海市","value":5,"type":2},{"name":"湘潭市","value":64,"type":1},{"name":"泉州市","value":66,"type":2},{"name":"六盘水市","value":82,"type":2},{"name":"上海市","value":62,"type":0},{"name":"洛阳市","value":54,"type":1},{"name":"三亚市","value":82,"type":2},{"name":"秦皇岛市","value":97,"type":1},{"name":"景德镇市","value":41,"type":1},{"name":"丽水市","value":18,"type":0},{"name":"海口市","value":22,"type":0},{"name":"香港岛","value":75,"type":2},{"name":"石家庄市","value":17,"type":2},{"name":"德阳市","value":32,"type":2},{"name":"金华市","value":79,"type":0},{"name":"衡阳市","value":59,"type":1},{"name":"十堰市","value":9,"type":1},{"name":"海口市","value":19,"type":1},{"name":"新竹县","value":3,"type":2},{"name":"威海市","value":44,"type":0},{"name":"六安市","value":7,"type":1},{"name":"高雄市","value":89,"type":0},{"name":"临沧市","value":85,"type":1},{"name":"临夏回族自治州","value":14,"type":2},{"name":"澳门半岛","value":10,"type":0},{"name":"彰化县","value":22,"type":1},{"name":"淮安市","value":48,"type":1},{"name":"泉州市","value":16,"type":2},{"name":"鹤壁市","value":55,"type":0},{"name":"达州市","value":2,"type":1},{"name":"唐山市","value":67,"type":0},{"name":"临夏回族自治州","value":52,"type":1},{"name":"七台河市","value":61,"type":2},{"name":"三沙市","value":89,"type":1},{"name":"六盘水市","value":27,"type":0},{"name":"广安市","value":1,"type":0},{"name":"宿迁市","value":88,"type":2},{"name":"随州市","value":63,"type":0},{"name":"贵阳市","value":49,"type":2},{"name":"宜昌市","value":12,"type":1},{"name":"离岛","value":10,"type":1},{"name":"上海市","value":59,"type":1},{"name":"邢台市","value":19,"type":0},{"name":"黔东南苗族侗族自治州","value":29,"type":0},{"name":"金华市","value":85,"type":1},{"name":"上海市","value":48,"type":0},{"name":"六盘水市","value":57,"type":1},{"name":"三沙市","value":60,"type":1},{"name":"大庆市","value":100,"type":1},{"name":"黄南藏族自治州","value":72,"type":0},{"name":"巴彦淖尔市","value":48,"type":1},{"name":"三门峡市","value":40,"type":1},{"name":"海北藏族自治州","value":82,"type":1},{"name":"贵港市","value":72,"type":1},{"name":"台东县","value":47,"type":1},{"name":"黄冈市","value":20,"type":1},{"name":"和田地区","value":88,"type":0},{"name":"玉树藏族自治州","value":23,"type":1},{"name":"海外","value":5,"type":2},{"name":"昌都地区","value":4,"type":1},{"name":"上海市","value":40,"type":1},{"name":"九龙","value":86,"type":2},{"name":"自贡市","value":24,"type":1},{"name":"沧州市","value":79,"type":1},{"name":"北京市","value":55,"type":1},{"name":"九龙","value":58,"type":1},{"name":"商丘市","value":93,"type":0},{"name":"吉林市","value":58,"type":1},{"name":"运城市","value":93,"type":1},{"name":"赤峰市","value":90,"type":1},{"name":"上海市","value":14,"type":2},{"name":"东营市","value":24,"type":2},{"name":"天津市","value":76,"type":2},{"name":"巢湖市","value":13,"type":0},{"name":"海东市","value":86,"type":1},{"name":"漳州市","value":44,"type":1},{"name":"晋中市","value":86,"type":2},{"name":"昌都地区","value":3,"type":2},{"name":"渭南市","value":48,"type":1},{"name":"上海市","value":53,"type":2},{"name":"铁岭市","value":98,"type":2},{"name":"上海市","value":81,"type":1},{"name":"丽江市","value":40,"type":2},{"name":"六盘水市","value":64,"type":0},{"name":"上饶市","value":55,"type":1},{"name":"赣州市","value":54,"type":1},{"name":"九龙","value":60,"type":1},{"name":"十堰市","value":94,"type":2},{"name":"固原市","value":41,"type":2},{"name":"南昌市","value":50,"type":1},{"name":"澳门半岛","value":33,"type":1},{"name":"白山市","value":26,"type":0},{"name":"商丘市","value":99,"type":0},{"name":"营口市","value":5,"type":2}]';
        return $this->json->successful(['list' => json_decode($monitor, true)]);
    }
}
