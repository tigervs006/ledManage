<?php

namespace core\traits;

/**
 * Trait ServicesTrait
 * @package core\traits
 * @method int saveAll(array $data) 批量新增数据
 * @method \core\basic\BaseModel search(?array $map) 使用搜索器
 * @method \core\basic\BaseModel saveOne(array $data) 新增一条数据
 * @method mixed value(array $where, ?string $field = '') 获取单个字段的值
 * @method mixed batchUpdate(array $ids, array $data, ?string $key) 批量更新数据
 * @method bool setInc(int $id, int $incValue, ?string $field = 'click') 自增阅读量或其它
 * @method mixed getOne(array $map, ?string $field, ?array $with = []) 根据条件获取单条数据
 * @method array getColumn(string $field, ?array $where = null, string $key = '') 获取某个列的数组
 * @method int|bool delete(int|array|string $id, ?string $key = null, ?bool $force = false) 删除一条或多条数据
 * @method \core\basic\BaseModel updateOne(int|array|string $id, array $data, ?string $key = null) 更新一条数据
 * @method int getCount(?array $map, ?string $key = null, ?array $betweenTime = null, ?array $whereLike = null) 计算数据总量
 * @method array|\core\basic\BaseModel|\think\Model|null get(int|string|array $id, ?string $field, ?array $with = []) 获取单条数据
 * @method array getPrenext(int $id, ?array $map = null, ?string $field = 'id, title', ?string $firstPre = '已经是第一篇了', ?string $lastNext = '这是最后一篇了') 获取上/下一篇文章
 * @method \think\Paginator getPaginate(array $map, int $page = 1, int $rows = 15, ?string $fullpath = null, ?string $field = '*', ?array $order = ['id' => 'desc'], ?array $with = null, ?array $query = []) 用于前端的分页列表
 * @method array|\think\Collection getData(?array $map = null, ?array $order = ['id' => 'desc'], ?string $field = '*', ?array $betweenTime = null, ?array $whereLike = null, ?array $with = null) 获取所有带时间段/关联模型的数据
 * @method array|\think\Collection getList(int $current, int $pageSize, ?array $map = null, ?string $field = '*', ?array $order = ['id' => 'desc'], ?array $betweenTime = null, ?array $whereLike = null, ?array $with = null) 获取带分页/时间段/关联模型的列表
 */
trait ServicesTrait
{
    /**
     * 递归查找子级id
     * @return array
     * @param array $idsArr
     * @param int|string $id
     * @param array|\think\Collection $data
     */
    public function getChildrenIds(array|\think\Collection $data, int|string $id, array $idsArr = []): array
    {
        foreach ($data as $val) {
            if ($id == $val['pid']) {
                $idsArr[] = $val['id'];
                $idsArr = array_merge($idsArr, self::getChildrenIds($data, $val['id']));
            }
        }
        return $idsArr;
    }
    /**
     * 生成栏目树状结构
     * @return array
     * @param int|null $pid 父级id
     * @param string|null $pname 父级名称
     * @param array|\think\Collection $data data
     */
    public function getTreeData(array|\think\Collection $data, ?int $pid = 0, ?string $pname = '顶级栏目'): array
    {
        $tree = [];
        foreach ($data as $val) {
            if ($pid == $val['pid']) {
                $pname && $val['pname'] = $pname;
                $children = self::getTreeData($data, $val['id'], $pname ? $val['cname'] : null);
                $children && $val['children'] = $children;
                $tree[] = $val;
            }
        }
        return $tree;
    }
}
