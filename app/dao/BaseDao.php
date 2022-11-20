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
namespace app\dao;

use think\Collection;
use core\basic\BaseModel;
use think\db\exception\DbException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

abstract class BaseDao
{
    /**
     * 当前表别名
     * @var string
     */
    protected string $alias;

    /**
     * join表名
     * @var string
     */
    protected string $joinAlias;

    /**
     * 默认状态
     * @var array|int[]
     */
    protected array $status = ['status' => 1];

    /**
     * 设置当前模型
     * @return string
     */
    abstract protected function setModel(): string;

    /**
     * 设置join链表模型
     */
    protected function setJoinModel(): string {
        return app()->make($this->setModel());
    }

    /**
     * 获取单条数据
     * @return array|BaseModel|\think\Model|null
     * @param int|string|array $id id
     * @param string|null $field 字段
     * @param array|null $with 关联模型
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function get(int|string|array $id, ?string $field, ?array $with = []): \think\Model|BaseModel|array|null
    {
        if (is_array($id)) {
            $map = $id;
        } else {
            $map = [$this->getPk() => $id];
        }
        return $this->getModel()->where($map)->when(count($with), function ($query) use ($with) {
            $query->with($with);
        })->field($field ?? '*')->find();
    }

    /**
     * 获取模型
     * @return BaseModel
     */
    protected function getModel(): BaseModel
    {
        return app()->make($this->setModel());
    }

    /**
     * 获取当前模型主键
     * @return string
     */
    protected function getPK(): string
    {
        return $this->getModel()->getPk();
    }

    /**
     * 自增字段
     * @return bool
     * @param int $id id
     * @param int $incValue 自增值
     * @param string|null $field 自增字段
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function setInc(int $id, int $incValue, ?string $field = 'click'): bool
    {
        $data = $this->getModel()->find($id);
        $data->$field += $incValue;
        return $data->isAutoWriteTimestamp(false)->save();
    }

    /**
     * 根据条件获取单条数据
     * @return \think\Model|array|BaseModel|null
     * @param array $map 条件
     * @param string|null $field 字段
     * @param array|null $with 关联模型
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function getOne(array $map, ?string $field, ?array $with = []): \think\Model|BaseModel|array|null
    {
        return $this->get($map, $field, $with);
    }

    /**
     * 计算数据总量
     * @return int
     * @param array|null $map 条件
     * @param string|null $key 字段
     * @param array|null $betweenTime 时间段
     * @param array|null $whereLike 模糊搜索
     * @throws DbException
     */
    public function getCount(?array $map, ?string $key = null, ?array $betweenTime = null, ?array $whereLike = null): int
    {
        if (is_null($map) && empty($betweenTime) && empty($whereLike)) {
            return $this->getModel()->count($key ?: $this->getPK());
        } else {
            return $this->getModel()->where($map)
                ->when($betweenTime, function ($query) use ($betweenTime) {
                $query->whereBetweenTime(...$betweenTime); })
                ->when($whereLike, function ($query) use ($whereLike) {
                    $query->whereLike(...$whereLike);
                })->count();
        }
    }

    /**
     * 获取某个列数组
     * @return array
     * @param array|null $map 条件
     * @param string $field 字段
     * @param string|null $key 索引
     */
    public function getColumn(string $field, ?array $map = null, ?string $key = ''): array
    {
        if (is_null($map)) {
            return $this->getModel()->column($field, $key);
        } else {
            return $this->getModel()->where($map)->column($field, $key);
        }
    }

    /**
     * 新增一条数据
     * @return BaseModel
     * @param array $data
     */
    public function saveOne(array $data): BaseModel
    {
        return $this->getModel()::create($data);
    }

    /**
     * 批量新增数据
     * @return int
     * @param array $data
     */
    public function saveAll(array $data): int
    {
        return $this->getModel()->insertAll($data);
    }

    /**
     * 删除一条或多条数据
     * @return int|bool
     * @param int|array|string $id
     * @param null|string $key key
     * @param null|bool $force 强制删除
     */
    public function delete(int|array|string $id, ?string $key = null, ?bool $force = false): int|bool
    {
        if ($force) {
            return $this->getModel()->destroy($id, $force);
        } else {
            $where = [is_null($key) ? $this->getPk() : $key => $id];
            return $this->getModel()->where($where)->useSoftDelete('delete_time',time())->delete();
        }
    }

    /**
     * 更新一条数据
     * @return BaseModel
     * @param array $data
     * @param string|null $key
     * @param array|int|string $id
     */
    public function updateOne(int|array|string $id, array $data, ?string $key = null): BaseModel
    {
        if (is_array($id)) {
            $where = $id;
        } else {
            $where =[is_null($key) ? $this->getPk() : $key => $id];
        }
        return $this->getModel()::update($data, $where);
    }

    /**
     * 批量更新数据
     * @return int|BaseModel
     * @param array $ids
     * @param array $data
     * @param string|null $key
     */
    public function batchUpdate(array $ids, array $data, ?string $key): int|BaseModel
    {
        return $this->getModel()->whereIn(is_null($key) ? $this->getPK() : $key, $ids)->update($data);
    }

    /**
     * 获取单个字段值
     * @param array $where
     * @param string|null $field
     * @return mixed
     */
    public function value(array $where, ?string $field = ''): mixed
    {
        $pk = $this->getPk();
        return $this->getModel()->where($where)->value($field ?: $pk);
    }

    /**
     * 上/下一篇文章
     * @return array
     * @param int $id id
     * @param null|array $map 条件
     * @param null|string $field 字段
     * @param string|null $firstPre 第一篇
     * @param string|null $lastNext 最后一篇
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function getPrenext(int $id, ?array $map = null, ?string $field = 'id, title', ?string $firstPre = '已经是第一篇了', ?string $lastNext = '这是最后一篇了'): array
    {
        $nextMap = array(['id', '>', $id]);
        $prevMap = array(['id', '<', $id]);
        if ($map) {
            $nextMap = array(['id', '>', $id], ...$map);
            $prevMap = array(['id', '<', $id], ...$map);
        }
        $next = $this->getModel()->where($nextMap)->field($field)->with(['channel'])->limit(1)->select();
        $pre = $this->getModel()->where($prevMap)->field($field)->with(['channel'])->order('id', 'desc')->limit(1)->select();
        if ($pre->isEmpty()) {
            $pre = array(
                'title' => $firstPre
            );
        } else {
            $pre = array(
                'id' => $pre[0]['id'],
                'title' => $pre[0]['title'],
                'dirname' => $pre[0]['channel']['dirname'],
                'fullpath' => $pre[0]['channel']['fullpath']
            );
        }
        if ($next->isEmpty()) {
            $next = array(
                'title' => $lastNext
            );
        } else {
            $next = array(
                'id' => $next[0]['id'],
                'title' => $next[0]['title'],
                'dirname' => $next[0]['channel']['dirname'],
                'fullpath' => $next[0]['channel']['fullpath']
            );
        }
        return compact('pre', 'next');
    }

    /**
     * @return array|Collection
     * @author Kevin
     * @param int $current
     * @param int $pageSize
     * @param bool|null $json
     * @param array|null $map
     * @param array|null $order
     * @param array|null $jsonMap
     * @param array|null $jsonField
     * @createAt 2022/11/11 1:31
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function jsonSearch(int $current, int $pageSize, ?bool $json = false, ?array $jsonField = null, ?array $map = null, ?array $jsonMap = null, ?array $order = ['id' => 'desc']): array|\think\Collection
    {
        return $this->getModel()
            ->when($json && $jsonField, function ($query) use ($jsonMap, $jsonField) {
                $query->json($jsonField)->where($jsonMap);
        }, function ($query) use ($map, $order) { $query->where($map)->order($order); })->page($current, $pageSize)->select();
    }

    /**
     * 用于前端的分页列表
     * @return \think\Paginator
     * @param array $map 条件
     * @param int $page 当前页
     * @param int $rows 数据量
     * @param string|null $fullpath
     * @param string|null $field 字段
     * @param array|null $order 排序
     * @param array|null $with 关联模型
     * @param array|null $query url参数
     * @throws DbException
     */
    public function getPaginate(array $map, int $page = 1, int $rows = 15, ?string $fullpath = null, ?string $field = '*', ?array $order = ['id' => 'desc'], ?array $with = null, ?array $query = []): \think\Paginator
    {
        return $this->getModel()->where($map)->when($with, function ($query) use ($with) {
            $query->with($with);
        })->field($field)->order($order)->paginate(['page' => $page, 'list_rows' => $rows, 'query' => $query, 'path' => '/' . $fullpath . 'page/[PAGE].html']);
    }

    /**
     * 根据条件获取所有数据
     * @return array|Collection
     * @param array|null $map 条件
     * @param array|null $order 排序
     * @param string|null $field 字段
     * @param array|null $betweenTime 时间段
     * @param array|null $whereLike 模糊查找
     * @param array|null $with 关联模型
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function getData(?array $map = null, ?array $order = ['id' => 'desc'], ?string $field = '*', ?array $betweenTime = null, ?array $whereLike = null, ?array $with = null): array|\think\Collection
    {
        return $this->getModel()->where($map)
            ->when($betweenTime, function ($query) use ($betweenTime) {
                $query->whereBetweenTime(...$betweenTime); })
            ->when($whereLike, function ($query) use ($whereLike) {
                $query->whereLike(...$whereLike);})
            ->when($with, function ($query) use ($with) {
                $query->with($with); })
            ->field($field)->order($order)->select();
    }

    /**
     * 获取带分页的列表
     * @return array|Collection
     * @param int $current 当前页
     * @param int $pageSize 容量
     * @param array|null $map 条件
     * @param string|null $field 字段
     * @param array|null $order 排序
     * @param array|null $betweenTime 时间段
     * @param array|null $whereLike 模糊查找
     * @param array|null $with 关联模型
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function getList(int $current, int $pageSize, ?array $map = null, ?string $field = '*', ?array $order = ['id' => 'desc'], ?array $betweenTime = null, ?array $whereLike = null, ?array $with = null): array|\think\Collection
    {
        return $this->getModel()->where($map)
        ->when($betweenTime, function ($query) use ($betweenTime) {
            $query->whereBetweenTime(...$betweenTime);
        })->when($whereLike, function ($query) use ($whereLike) {
            $query->whereLike(...$whereLike);
        })->when($with, function ($query) use ($with) {
            $query->with($with);
        })->field($field)->order($order)->page($current, $pageSize)->select();
    }
}
