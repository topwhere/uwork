<?php
/**
 * @copyright Copyright (c) 2022 勾股工作室
 * @license https://opensource.org/licenses/GPL-3.0
 * @link https://www.gougucms.com
 */

declare (strict_types = 1);

namespace app\schedule\controller;

use app\base\BaseController;
use app\model\Schedule as ScheduleList;
use schedule\Schedule as ScheduleIndex;
use think\facade\Db;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['a.title', 'like', '%' . $param['keywords'] . '%'];
            }
            if (empty($param['uid'])) {
                $param['uid'] = $this->uid;
            }
            $where[] = ['a.admin_id', 'in', $param['uid']];
            if (!empty($param['project_id'])) {
                $task_ids = Db::name('Task')->where(['delete_time' => 0, 'project_id' => $param['project_id']])->column('id');
                $where[] = ['a.tid', 'in', $task_ids];
            }
            if (!empty($param['cate'])) {
                $where[] = ['t.cate', '=', $param['cate']];
            }
            if (!empty($param['type'])) {
                $where[] = ['t.type', '=', $param['type']];
            }
            $where[] = ['a.delete_time', '=', 0];
            $rows = empty($param['limit']) ? get_config('app . page_size') : $param['limit'];
            $list = ScheduleList::where($where)
                ->field('a.*,u.name,d.title as department,w.title as work_cate')
                ->alias('a')
                ->join('Admin u', 'a.admin_id = u.id', 'LEFT')
                ->join('Department d', 'u.did = d.id', 'LEFT')
                ->join('Task t', 't.id = a.tid', 'LEFT')
                ->join('WorkCate w', 'w.id = t.cate', 'LEFT')
                ->order('a.create_time asc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
                    $item->start_time = empty($item->start_time) ? '' : date('Y-m-d H:i', $item->start_time);
                    $item->end_time = empty($item->end_time) ? '' : date('H:i', $item->end_time);
                });
            return table_assign(0, '', $list);
        } else {
            View::assign('cate', get_work_cate());
            View::assign('project', get_project($this->uid));
            return view();
        }
    }

    //获取工作记录列表
    public function get_list()
    {
        $param = get_params();
        $where = array();
        $where['a.tid'] = $param['tid'];
        $where['a.delete_time'] = 0;
        $list = Db::name('Schedule')
            ->field('a.*,u.name')
            ->alias('a')
            ->join('Admin u', 'u.id = a.admin_id')
            ->order('a.create_time desc')
            ->where($where)
            ->select()->toArray();
        foreach ($list as $k => $v) {
            $list[$k]['start_time'] = empty($v['start_time']) ? '' : date('Y-m-d H:i', $v['start_time']);
            $list[$k]['end_time'] = empty($v['end_time']) ? '' : date('H:i', $v['end_time']);
        }
        return to_assign(0, '', $list);
    }

    //工作记录
    public function calendar()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $uid = $this->uid;
            if (!empty($param['uid'])) {
                $uid = $param['uid'];
            }
            $where = [];
            $where[] = ['start_time', '>=', strtotime($param['start'])];
            $where[] = ['end_time', '<=', strtotime($param['end'])];
            $where[] = ['admin_id', '=', $uid];
            $where[] = ['status', '=', 1];
            $schedule = Db::name('Schedule')->where($where)->field('id,title,labor_time,start_time,end_time')->select()->toArray();
            $events = [];
            $countEvents = [];
            foreach ($schedule as $k => $v) {
                $v['backgroundColor'] = '#12bb37';
                $v['borderColor'] = '#12bb37';
                $v['title'] = '[' . $v['labor_time'] . '工时] ' . $v['title'];
                $v['start'] = date('Y-m-d H:i', $v['start_time']);
                $v['end'] = date('Y-m-d H:i', $v['end_time']);
                $temData = date('Y-m-d', $v['start_time']);
                if (array_key_exists($temData, $countEvents)) {
                    $countEvents[$temData]['times'] += $v['labor_time'];
                } else {
                    $countEvents[$temData]['times'] = $v['labor_time'];
                    $countEvents[$temData]['start'] = date('Y-m-d', $v['start_time']);
                }
                unset($v['start_time']);
                unset($v['end_time']);
                $events[] = $v;
            }
            foreach ($countEvents as $kk => $vv) {
                $vv['backgroundColor'] = '#eeeeee';
                $vv['borderColor'] = '#eeeeee';
                $vv['title'] = '【当天总工时：' . $vv['times'] . '】';
                $vv['end'] = $vv['start'];
                $vv['id'] = 0;
                unset($vv['times']);
                $events[] = $vv;
            }
            $input_arrays = $events;
            $range_start = parseDateTime($param['start']);
            $range_end = parseDateTime($param['end']);
            $timeZone = null;
            if (isset($_GET['timeZone'])) {
                $timeZone = new DateTimeZone($_GET['timeZone']);
            }

            // Accumulate an output array of event data arrays.
            $output_arrays = array();
            foreach ($input_arrays as $array) {
                // Convert the input array into a useful Event object
                $event = new ScheduleIndex($array, $timeZone);
                // If the event is in-bounds, add it to the output
                if ($event->isWithinDayRange($range_start, $range_end)) {
                    $output_arrays[] = $event->toArray();
                }
            }
            return json($output_arrays);
        } else {
            return view();
        }
    }

    //保存日志数据
    public function add()
    {
        $param = get_params();
        $admin_id = $this->uid;
        if (request()->isPost()) {
            if ($param['id'] == 0) {
                if (isset($param['start_time_a'])) {
                    $param['start_time'] = strtotime($param['start_time_a'] . '' . $param['start_time_b']);
                }
                if (isset($param['end_time_a'])) {
                    $param['end_time'] = strtotime($param['end_time_a'] . '' . $param['end_time_b']);
                }
                if ($param['start_time'] > time()) {
                    return to_assign(1, "开始时间不能大于现在时间");
                }
                if ($param['end_time'] <= $param['start_time']) {
                    return to_assign(1, "结束时间需要大于开始时间");
                }
                $where1[] = ['delete_time', '=', 0];
                $where1[] = ['admin_id', '=', $admin_id];
                $where1[] = ['start_time', 'between', [$param['start_time'], $param['end_time'] - 1]];

                $where2[] = ['delete_time', '=', 0];
                $where2[] = ['admin_id', '=', $admin_id];
                $where2[] = ['start_time', '<=', $param['start_time']];
                $where2[] = ['start_time', '>=', $param['end_time']];

                $where3[] = ['delete_time', '=', 0];
                $where3[] = ['admin_id', '=', $admin_id];
                $where3[] = ['end_time', 'between', [$param['start_time'] + 1, $param['end_time']]];

                $record = Db::name('Schedule')
                    ->where(function ($query) use ($where1) {
                        $query->where($where1);
                    })
                    ->whereOr(function ($query) use ($where2) {
                        $query->where($where2);
                    })
                    ->whereOr(function ($query) use ($where3) {
                        $query->where($where3);
                    })
                    ->count();
                if ($record > 0) {
                    return to_assign(1, "您所选的时间区间已有工作记录，请重新选时间");
                }

                //$task_detail = Db::name('Task')->where(['id',$param['tid']])->find();
                $param['labor_time'] = ($param['end_time'] - $param['start_time']) / 3600;
                $param['admin_id'] = $admin_id;
                $param['did'] = get_admin($admin_id)['did'];
                $param['create_time'] = time();
                $sid = Db::name('Schedule')->strict(false)->field(true)->insertGetId($param);
                if ($sid > 0) {
                    add_log('add', $sid, $param);
                    return to_assign();
                } else {
                    return to_assign(1, '操作失败');
                }
            } else {
                $param['update_time'] = time();
                $res = Db::name('Schedule')->strict(false)->field(true)->update($param);
                if ($res !== false) {
                    add_log('edit', $param['id'], $param);
                    return to_assign();
                } else {
                    return to_assign(1, '操作失败');
                }
            }
        } else {
            return to_assign(1, "错误的请求");
        }
    }

    //更改工时
    public function update_labor_time()
    {
        $param = get_params();
        if (isset($param['start_time_a'])) {
            $param['start_time'] = strtotime($param['start_time_a'] . '' . $param['start_time_b']);
        }
        if (isset($param['end_time_a'])) {
            $param['end_time'] = strtotime($param['end_time_a'] . '' . $param['end_time_b']);
        }
        if ($param['start_time'] > time()) {
            return to_assign(1, "开始时间不能大于当前时间");
        }
        if ($param['end_time'] <= $param['start_time']) {
            return to_assign(1, "结束时间需要大于开始时间");
        }
        $where1[] = ['status', '=', 1];
        $where1[] = ['id', '<>', $param['id']];
        $where1[] = ['admin_id', '=', $param['admin_id']];
        $where1[] = ['start_time', 'between', [$param['start_time'], $param['end_time'] - 1]];

        $where2[] = ['status', '=', 1];
        $where2[] = ['id', '<>', $param['id']];
        $where2[] = ['admin_id', '=', $param['admin_id']];
        $where2[] = ['start_time', '<=', $param['start_time']];
        $where2[] = ['start_time', '>=', $param['end_time']];

        $where3[] = ['status', '=', 1];
        $where3[] = ['id', '<>', $param['id']];
        $where3[] = ['admin_id', '=', $param['admin_id']];
        $where3[] = ['end_time', 'between', [$param['start_time'] + 1, $param['end_time']]];

        $record = Db::name('Schedule')
            ->where(function ($query) use ($where1) {
                $query->where($where1);
            })
            ->whereOr(function ($query) use ($where2) {
                $query->where($where2);
            })
            ->whereOr(function ($query) use ($where3) {
                $query->where($where3);
            })
            ->count();
        if ($record > 0) {
            return to_assign(1, "您所选的时间区间已有工作记录，请重新选时间");
        }
        $param['labor_time'] = ($param['end_time'] - $param['start_time']) / 3600;
        $res = Db::name('Schedule')->strict(false)->field(true)->update($param);
        if ($res !== false) {
            return to_assign(0, "操作成功");
            add_log('edit', $param['id'], $param);
        } else {
            return to_assign(1, "操作失败");
        }
    }

    //删除工作记录
    public function delete()
    {
        if (request()->isDelete()) {
            $id = get_params("id");
            $data['id'] = $id;
            $data['delete_time'] = time();
            if (Db::name('schedule')->update($data) !== false) {
                add_log('delete', $data['id'], $data);
                return to_assign(0, "删除成功");
            } else {
                return to_assign(1, "删除失败");
            }
        } else {
            return to_assign(1, "错误的请求");
        }
    }

    //查看工作记录详情
    public function detail($id)
    {
        $id = get_params('id');
        $schedule = Db::name('Schedule')->where(['id' => $id])->find();
        if (!empty($schedule)) {
            $schedule['start_time_1'] = date('H:i', $schedule['start_time']);
            $schedule['end_time_1'] = date('H:i', $schedule['end_time']);
            $schedule['start_time'] = date('Y-m-d', $schedule['start_time']);
            $schedule['end_time'] = date('Y-m-d', $schedule['end_time']);
            $schedule['create_time'] = date('Y-m-d H:i:s', $schedule['create_time']);
            $schedule['user'] = Db::name('Admin')->where(['id' => $schedule['admin_id']])->value('name');
        }
        if (request()->isAjax()) {
            return to_assign(0, "", $schedule);
        } else {
            return $schedule;
        }
    }
}
