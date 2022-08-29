<?php
declare (strict_types = 1);
namespace app\services\auth;

use think\Collection;
use think\facade\Cache;
use app\dao\auth\AuthDao;
use app\services\BaseServices;
use core\exceptions\ApiException;
use core\exceptions\AuthException;

/**
 * @method Collection queryMenu(string $ids, ?array $where = []) 查询用户菜单
 */
class AuthServices extends BaseServices
{
    /**
     * @param AuthDao $dao
     */
    public function __construct(AuthDao $dao)
    {
        $this->dao = $dao;
    }

    public function saveMenu(array $data, string $message): void
    {
        $id = $data['id'] ?? 0;
        unset($data['id']); // 释放$data中的id
        $this->transaction(function () use ($id, $data, $message) {
            $res = $id ? $this->dao->updateOne($id, $data, 'id') : $this->dao->saveOne($data);
            !$res && throw new ApiException($message . '菜单失败');
        });
    }

    /**
     * 验证用户权限
     * @return void
     * @param array $token
     * @throws \Throwable
     */
    public function verifyAuthority(array $token): void
    {
        $gid = $token['gid'];
        /* 获取当前访问路由 */
        $rules = request()->rule()->getRule();
        $groupServices = app()->make(GroupServices::class);
        $groupRole = $groupServices->value(['id' => $gid], 'menu');
        /* 缓存用户组ID为KEY的路由权限 */
        $roleMenu = Cache::remember("{$gid}_role_menu", function () use ($groupRole) {
            return $this->dao->queryMenu($groupRole, ['type' => 3])->toArray();
        }, 3600 * 24 * 7);
        if (!in_array($rules, array_column($roleMenu, 'routes'))) {
            throw new AuthException("Access Denied! You don't have permission to access this resource", 403);
        }
    }

    /**
     * 生成菜单树状结构
     * @return array
     * @param array|Collection $data data
     * @param int|null $pid 父级id
     * @param string|null $pname 父级名称
     * @param string|null $plocale
     */
    public function getTreeMenu(array|Collection $data, ?int $pid = 0, ?string $pname = '顶级菜单', ?string $plocale = 'menu.top'): array
    {
        $tree = [];
        foreach ($data as $val) {
            if ($pid == $val['pid']) {
                $fullPath = '/';
                $val['plocale'] = $plocale;
                $pname && $val['pname'] = $pname;
                $ids = explode('-', $val['paths']);
                if (1 == $val['type']) {
                    foreach ($ids as $id) {
                        if (!$id) {
                            $fullPath .= $val['name'];
                        } else {
                            $fullPath .=  $this->dao->value(['id' => $id], 'name') . '/';
                        }
                    }
                    /* 如果是顶级菜单，直接返回；如果是二级及以上，则拼接当前的name */
                    $val['path'] = !$pid ? $fullPath : $fullPath . $val['name'];
                }
                $children = self::getTreeMenu($data, $val['id'], $pname ? $val['name'] : null, $val['locale']);
                $children && $val['children'] = $children;
                $tree[] = $val;
            }
        }
        return $tree;
    }
}
